<?php

class ResultTable extends GenericTable
{
  public function getFilterForm()
  {
    $options = array(
      'sort_by_choices' => $this->getSortByChoices(),
      'media_choices' => $this->getMediaChoices()
      );
    $filterForm = new ResultsFilterForm(array(), $options);
    $filterForm->bind($this->_filterParams);
    return $filterForm;
  }

  protected function getSelect()
  {    
    $select = '
      SELECT
        result.id as "id",
        result.experiments as "experiments",
        result.set_name as "set_name",
        result.set_background as "set_background",
        result.set_mating_type as "set_mating_type",
        result.set_genotype as "set_genotype",
        result.set_media as "set_media",
        result.set_temperature as "set_temperature",
        result.set_lifespan_count as "set_lifespan_count",
        result.set_lifespan_mean as "set_lifespan_mean",
        result.set_lifespan_stdev as "set_lifespan_stdev",
        result.set_lifespans as "set_lifespans",
        result.ref_name as "ref_name",
        result.ref_background as "ref_background",
        result.ref_mating_type as "ref_mating_type",
        result.ref_genotype as "ref_genotype",
        result.ref_media as "ref_media",
        result.ref_temperature as "ref_temperature",
        result.ref_lifespan_count as "ref_lifespan_count",
        result.ref_lifespan_mean as "ref_lifespan_mean",
        result.ref_lifespan_stdev as "ref_lifespan_stdev",
        result.ref_lifespans as "ref_lifespans",
        result.percent_change as "percent_change",
        result.ranksum_p as "ranksum_p"
      FROM result
      LEFT JOIN result_experiment ON result_experiment.result_id = result.id
      ';
    return $select;
  }

  public function getMediaChoices()
  {
    // only get medias for the selected pooling method
    $poolingMethod = 'file';
    $filterValues = $this->_filterParams;
    if (isset($filterValues['pooled_by'])) {
      $input = $filterValues['pooled_by'];
      if (in_array($input, array('strain', 'genotype'))) {
        $poolingMethod = $input;
      }
    }

    $stmt = $this->_db->prepare('
      SELECT DISTINCT set_media FROM result
      WHERE result.pooled_by = ?
      ORDER BY set_media
      ');
    $stmt->execute(array($poolingMethod));

    $items = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $item = $row['set_media'];
      $items[$item] = $item;
    }
    return $items;
  }

  protected function getWhereClause()
  {
    $filterValues = $this->_filterParams;
    $whereClause = new WhereClause();

    // filter pooling (none, strain, genotype)
    if (isset($filterValues['pooled_by'])) {
      $input = $filterValues['pooled_by'];
      if (in_array($input, array('strain', 'genotype'))) {
        $whereClause->addWhere('pooled_by = ?', array($input));
      } else {
        $whereClause->addWhere('pooled_by = "file"');
      }
    } else {
      $whereClause->addWhere('pooled_by = "file"');
    }

    // search filter
    if (!empty($filterValues['search'])) {
      $input = $filterValues['search'];
      $whereClause->addWhere('
        (result.set_name LIKE ?
        OR result.ref_name LIKE ?
        OR result.set_genotype LIKE ?
        OR result.experiments LIKE ?
        OR result.set_media LIKE ?
        OR result.set_background LIKE ?
        OR result.set_mating_type LIKE ?
        )',
        array_fill(0, 7, '%'.$input.'%')
      );
    }

    // genotype filter
    if (!empty($filterValues['genotype'])) {
      // split input by comma, and process each value
      $input = $filterValues['genotype'];
      $inputs = array();
      if (strpos($input, ',') !== false) {
        $inputs = array_map('trim', explode(',', $input));
      } else {
        $inputs[] = $input;
      }

      // get resolved genotype inputs, and if there is a wildcard in each
      $genotypeInputs = array();
      $isWildcardGenotypeArray = array();
      foreach ($inputs as $input) {
        $genotypeInput = GenotypeLookup::getCleanGenotype($this->_db, $input);
        if (stripos($genotypeInput, '%') !== false) {
          $genotypeInput = preg_replace('/\s*%\s*/', '', $genotypeInput); // remove "*"
          $isWildcardGenotypeArray[] = true;
        } else {
          $isWildcardGenotypeArray[] = false;
        }
        $genotypeInputs[] = $genotypeInput;
      }

      // return genotypes where all the resolved terms are in the genotype, but
      // with any number of other terms included
      $orWhereStrings = array();
      $whereParams = array();
      for ($i = 0; $i < count($genotypeInputs); $i++) {
        $genotypeInput = $genotypeInputs[$i];
        
        if ($isWildcardGenotypeArray[$i] == true) {
          // OR together with other genotype inputs
          $terms = preg_split('/\s+/', $genotypeInput);

          $termWhereClauses = array();
          foreach ($terms as $term) {
            $termWhereClauses[] = 'result.set_genotype LIKE ?';
            $whereParams[] = '%'.$term.'%';
          }
          $orWhereStrings[] = '(' . join(" AND ", $termWhereClauses) . ')';
        }
        else {
          $qs = join(',', array_fill(0, count($genotypeInputs), '?'));
          $orWhereStrings[] = 'result.set_genotype IN ('.$qs.')';
          $whereParams = array_merge($whereParams, $genotypeInputs);
        }
      }
      $whereString = '(' . join(' OR ', $orWhereStrings) . ')';
      $whereClause->addWhere($whereString, $whereParams);


      // narrow by epistais experiments if checked
      if (!empty($filterValues['epistasis']) && $filterValues['pooled_by'] == 'file') {
          $experiments = array();

          // find experiments where double deletion has both single deletions
          $subGenotypes = array();
          foreach ($genotypeInputs as $genotype) {
              $terms = preg_split("/\s+/", $genotype);
              if (count($terms) == 2) {
                  $subGenotypes = $terms;

                  $query = 'SELECT DISTINCT(r.experiments) as experiments FROM result r';
                  $params = array();
                  foreach ($subGenotypes as $i => $subGenotype) {
                      $query .= " INNER JOIN result r$i
                        ON r$i.experiments = r.experiments
                        AND r$i.set_genotype = ?
                        AND r$i.set_lifespan_count IS NOT NULL
                        AND r$i.set_lifespan_count > 0
                      ";
                      $params[] = $subGenotype;
                  }
                  $query .= ' WHERE r.set_genotype = ? AND r.pooled_by = ?';
                  $params[] = $genotype;
                  $params[] = 'file';

                  $stmt = $this->_db->prepare($query);
                  $stmt->execute($params);

                  while ($row = $stmt->fetch()) {
                      $experiments[] = $row['experiments'];
                  }
              }
          }

          // limit search query to experiments containing both single mutants
          if (count($experiments) > 0) {
              $qs = join(',', array_fill(0, count($experiments), '?'));
              $whereClause->addWhere('result.experiments IN ('.$qs.')', $experiments);
          } else {
              $whereClause->addWhere('1 = 2');
          }
      }
    }

    // filter by experiments, use joined table result_experiment
    if (!empty($filterValues['experiment'])) {
      $input = $filterValues['experiment'];
      $params = preg_split("/[\s,]+/", $input);
      $qs = join(',', array_fill(0, count($params), '?'));
      $whereClause->addWhere('result_experiment.experiment IN ('.$qs.')', $params);
    }

    // filter by media, use joined table result_experiment
    if (!empty($filterValues['media'])) {
      $input = $filterValues['media'];
      $whereClause->addWhere('set_media = ?', array($input));
    }


    // filter by percent change
    if (!empty($filterValues['percent_change'])) {
      $input = doubleval($filterValues['percent_change']);
      $op = '>';
      if (isset($filterValues['percent_change_op'])) {
        $inputOp = $filterValues['percent_change_op'];
        $validOps = array('<', '>');
        if (in_array($inputOp, $validOps)) {
          $op = $inputOp;
        }
      }
      $whereClause->addWhere('result.percent_change '.$op.' ?', array($input));
    }

    // filter by p value
    if (!empty($filterValues['ranksum_p'])) {
      $input = doubleval($filterValues['ranksum_p']);
      $op = '>';
      if (isset($filterValues['ranksum_p_op'])) {
        $inputOp = $filterValues['ranksum_p_op'];
        $validOps = array('<', '>');
        if (in_array($inputOp, $validOps)) {
          $op = $inputOp;
        }
      }
      $whereClause->addWhere('result.ranksum_p '.$op.' ?', array($input));
    }

    if (isset($filterValues['single'])) {
      $whereClause->addWhere("result.set_genotype NOT LIKE '% %'
         AND result.set_genotype = LOWER(result.set_genotype)", array());
    }

    return $whereClause;
  }

  protected function getGroupBy()
  {
    return ' GROUP BY result.id';
  }


  protected function getSortByChoices()
  {
    return array(
      'set_name'            => 'Name',
      'set_genotype'        => 'Genotype',
      'set_media'           => 'Media',
      'set_temperature'     => 'Temperature',
      'set_lifespan_count'  => 'Lifespan Count',
      'set_lifespan_mean'   => 'Lifespan Mean',
      'percent_change'      => 'Percent Change',
      'ranksum_p'           => 'p-value',
      );
  }

  public function getPageRows($page)
  {
    $query = $this->getSelect();
    $query .= ' WHERE '.$this->getWhereClause()->getWhere();
    $query .= ' GROUP BY result.id';
    $query .= $this->getOrderByClause();

    $queryParams = $this->getWhereClause()->getParameters();

//    print_r($query);
//    print_r($queryParams);

    // add limits to query based on pager
    $pager = $this->getPager($page);
    $rowStart = $pager->getRowStart();
    $numRows = $pager->getRowsPerPage();
    $query .= sprintf(" LIMIT %d,%d", $rowStart, $numRows);

    // execute query and get rows
    $stmt = $this->_db->prepare($query);
    $stmt->execute($queryParams);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }

  public function getRows($ids = null)
  {
    if (is_array($ids) && count($ids) < 1) {
      return array();
    }
    
    if (is_array($ids)) {
      $query = $this->getSelect();
      $qs = join(', ', array_fill(0, count($ids), '?'));
      $query .= ' WHERE result.id IN ('.$qs.')';
      $query .= ' GROUP BY result.id';
      $query .= $this->getOrderByClause();
      $queryParams = $ids;
    } else {
      $query = $this->getQuery();
      $queryParams = $this->getQueryParams();
    }

    $sth = $this->_db->prepare($query);
    $sth->execute($queryParams);
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }

  public function getResult($id)
  {
    $query = 'SELECT * FROM result WHERE id = ?';
    $stmt = $this->_db->prepare($query);
    $stmt->execute(array($id));

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
  }

  public function getResultSets($id)
  {
    $sth = $this->_db->prepare('
      SELECT
        s.id as "id",
        s.name as "name",
        s.media as "media",
        s.temperature as "temperature",
        s.experiment as "experiment",
        y.id as "yeast_strain_id",
        y.name as "strain",
        y.background as "background",
        y.mating_type as "mating_type",
        y.genotype_unique as "genotype",
        s.lifespan_count as "lifespan_count",
        s.lifespan_mean as "lifespan_mean",
        s.lifespan_stdev as "lifespan_stdev",
        s.lifespans as "lifespans"
      FROM "set" s
      LEFT JOIN yeast_strain y ON y.name = s.strain
      LEFT JOIN result_set rs ON rs.set_id = s.id
      WHERE rs.result_id = ?
      ');
    $sth->execute(array($id));
    
    $sets = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $sets;
  }

  public function getResultRefSets($id)
  {
    $sth = $this->_db->prepare('
      SELECT 
        s.id as "id",
        s.name as "name",
        s.media as "media",
        s.temperature as "temperature",
        s.experiment as "experiment",
        y.id as "yeast_strain_id",
        y.name as "strain",
        y.background as "background",
        y.mating_type as "mating_type",
        y.genotype_unique as "genotype",
        s.lifespan_count as "lifespan_count",
        s.lifespan_mean as "lifespan_mean",
        s.lifespan_stdev as "lifespan_stdev",
        s.lifespans as "lifespans"
      FROM "set" s
      LEFT JOIN yeast_strain y ON y.name = s.strain
      LEFT JOIN result_ref rs ON rs.set_id = s.id
      WHERE rs.result_id = ?
      ');
    $sth->execute(array($id));

    $refSets = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $refSets;
  }
}
