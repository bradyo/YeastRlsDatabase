<?php

class SetTable
{
  private $_db = null;
  private $_filterParams = array();
  private $_pager = null;
  private $_query = null;
  private $_queryParams = array();

  const ROWS_PER_PAGE = 100;


  public function __construct($db, $filterParams = array())
  {
    $this->_db = $db;
    $this->_filterParams = $filterParams;
  }

  public function getFilterForm()
  {
    $options = array(
      'sort_by_choices' => $this->getSortByChoices(),
      'media_choices' => $this->getMediaChoices()
      );
    $filterForm = new SetsFilterForm(array(), $options);
    $filterForm->bind($this->_filterParams);
    return $filterForm;
  }

  private function getQuery() 
  {
    // return existing query if already made
    if ($this->_query !== null) {
      return $this->_query;
    }

    // create query
    $query = $this->getSelect();

    // add where clause
    $whereClause = $this->getWhereClause();
    $queryParams = array();
    if (($where = $whereClause->getWhere()) !== null) {
      $query .= ' WHERE '.$where;
      $queryParams = $whereClause->getParameters();
    }

    // add order by
    $query .= $this->getOrderByClause();

    // cache values
    $this->_query = $query;
    $this->_queryParams = $queryParams;

    return $this->_query;
  }

  private function getQueryParams()
  {
    return $this->_queryParams;
  }

  private function getSelect()
  {
    $select = 'SELECT
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
      LEFT JOIN yeast_strain y ON s.strain = y.name
      ';
    return $select;
  }

  public function getMediaChoices()
  {
    $stmt = $this->_db->prepare('
      SELECT DISTINCT media FROM "set"
      ORDER BY media
      ');
    $stmt->execute();

    $items = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $item = $row['media'];
      $items[$item] = $item;
    }
    return $items;
  }

  public function getSortByChoices()
  {
    return array(
      'experiment' => 'Experiment',
      'name' => 'Name',
      'strain' => 'Strain',
      'genotype' => 'Genotype',
      'media' => 'Media',
      'temperature' => 'Temperature',
      'lifespan_count' => 'Lifespan Count',
      'lifespan_mean' => 'Lifespan Mean',
    );
  }

  private function getWhereClause()
  {
    $filterValues = $this->_filterParams;
    $whereClause = new WhereClause();

    // search filter
    if (!empty($filterValues['search'])) {
      $input = $filterValues['search'];
      $whereClause->addWhere('(
        s.name LIKE ? OR
        s.media LIKE ? OR
        s.temperature LIKE ? OR
        s.experiment LIKE ? OR
        y.name LIKE ? OR
        y.background LIKE ? OR
        y.genotype LIKE ? OR
        y.genotype_unique LIKE ?
        )',
        array_fill(0, 8, '%'.$input.'%')
      );
    }

    // filter by experiments, use joined table result_experiment
    if (!empty($filterValues['experiment'])) {
      $input = $filterValues['experiment'];
      $params = preg_split("/[\s,]+/", $input);
      $qs = join(',', array_fill(0, count($params), '?'));
      $whereClause->addWhere('s.experiment IN ('.$qs.')', $params);
    }

    // filter by media, use joined table result_experiment
    if (!empty($filterValues['media'])) {
      $input = $filterValues['media'];
      $whereClause->addWhere('s.media = ?', array($input));
    }

    if (!empty($filterValues['strain'])) {
      $input = $filterValues['strain'];
      if (strpos($input, ',') !== null) {
        $params = array_map('trim', explode(',', $input));
      } else {
        $params = array($input);
      }
      $qs = join(',', array_fill(0, count($params), '?'));
      $whereClause->addWhere('y.name IN ('.$qs.')', $params);
    }

    // get resolved genotype inputs, and if there is a wildcard in each
    if (!empty($filterValues['genotype'])) {
      // split input by comma, and process each value
      $input = $filterValues['genotype'];
      $inputs = array();
      if (strpos($input, ',') !== false) {
        $inputs = array_map('trim', explode(',', $input));
      } else {
        $inputs[] = $input;
      }
      
      $genotypeInputs = array();
      $isWildcardGenotypeArray = array();
      foreach ($inputs as $input) {
        $genotypeInput = GenotypeLookup::getCleanGenotype($this->_db, $input);
        if (stripos($genotypeInput, '%') !== false) {
          $genotypeInput = preg_replace('/\s*%\s*/', '', $genotypeInput); // remove "%"
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
            $termWhereClauses[] = 'y.genotype_unique LIKE ?';
            $whereParams[] = '%'.$term.'%';
          }
          $orWhereStrings[] = '(' . join(" AND ", $termWhereClauses) . ')';
        }
        else {
          $qs = join(',', array_fill(0, count($genotypeInputs), '?'));
          $orWhereStrings[] = 'y.genotype_unique IN ('.$qs.')';
          $whereParams = array_merge($whereParams, $genotypeInputs);
        }
      }
      $whereString = '(' . join(' OR ', $orWhereStrings) . ')';
      $whereClause->addWhere($whereString, $whereParams);
    }

    // filter by percent change
    if (!empty($filterValues['lifespan_mean'])) {
      $input = doubleval($filterValues['lifespan_mean']);
      $op = '>';
      if (isset($filterValues['lifespan_mean_op'])) {
        $inputOp = $filterValues['lifespan_mean_op'];
        $validOps = array('<', '>');
        if (in_array($inputOp, $validOps)) {
          $op = $inputOp;
        }
      }
      $whereClause->addWhere('s.lifespan_mean '.$op.' ?', array($input));
    }


    if (isset($filterValues['single'])) {
      $whereClause->addWhere("y.genotype_unique NOT LIKE '% %'
         AND y.genotype_unique = LOWER(y.genotype_unique)", array());
    }

    return $whereClause;
  }

  private function getOrderByClause()
  {
    $orderByClause = null;
    $filterParams = $this->_filterParams;
    if (isset($filterParams['sort_by'])) {
      $orderByInput = $filterParams['sort_by'];
      $allowedFields = array_keys($this->getSortByChoices());
      if (in_array($orderByInput, $allowedFields)) {
        // set sort order
        $sortOrder = 'ASC';
        if (isset($filterParams['sort_order'])) {
          $sortOrderInput = strtoupper($filterParams['sort_order']);
          if (in_array($sortOrderInput, array('ASC', 'DESC'))) {
            $sortOrder = $sortOrderInput;
          }
        }
        // build order by clause
        $orderByClause = ' ORDER BY '.$orderByInput.' '.$sortOrder;
      }
    }
    return $orderByClause;
  }

  public function getPager($page)
  {
    // return pager object if already created
    if ($this->_pager !== null) {
      return $this->_pager;
    }

    // set up the pager
    $query = $this->getQuery();
    $queryParams = $this->getQueryParams();

    $pagerQuery = 'SELECT COUNT(*) FROM ('.$query.')';
    $sth = $this->_db->prepare($pagerQuery);

    $sth->execute($queryParams);
    $rowCount = $sth->fetchColumn();

    $this->_pager = new SimplePager($rowCount, self::ROWS_PER_PAGE, $page);
    return $this->_pager;
  }

  public function getPageRows($page)
  {
    $query = $this->getQuery();
    $queryParams = $this->getQueryParams();

    // add limits to query based on pager
    $query .= ' LIMIT ?, ?';
    $pager = $this->getPager($page);
    $queryParams[] = $pager->getRowStart();
    $queryParams[] = $pager->getRowsPerPage();

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
      $qs = join(',', array_fill(0, count($ids), '?'));
      $query .= ' WHERE s.id IN ('.$qs.')';
      $query .= $this->getOrderByClause();
      $queryParams = $ids;
    } else {
      $query = $this->getQuery();
      $queryParams = $this->getQueryParams();
    }

    // execute query and get rows
    $stmt = $this->_db->prepare($query);
    $stmt->execute($queryParams);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }

  public function getSet($id)
  {
    $query = $this->getSelect();
    $query .= ' WHERE s.id = ?';
    $stmt = $this->_db->prepare($query);
    $stmt->execute(array($id));
    
    $set = $stmt->fetch(PDO::FETCH_ASSOC);
    return $set;
  }

  public function getAsSetPooledResultIds($id)
  {
    // get result ids where set is not reference
    $query = '
      SELECT r.pooled_by, r.id FROM result_set rs
      LEFT JOIN result r ON r.id = rs.result_id
      WHERE rs.set_id = ?
      ';
    $stmt = $this->_db->prepare($query);
    $stmt->execute(array($id));

    $pooledResultIds = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $pooledBy = $row['pooled_by'];
      if (!isset($pooledResultIds[$pooledBy])) {
        $pooledResultIds[$pooledBy] = array();
      }
      $pooledResultIds[$pooledBy][] = $row['id'];
    }
    return $pooledResultIds;
  }

  public function getAsRefPooledResultIds($id)
  {
    // get result ids where set is reference
    $query = '
      SELECT r.pooled_by, r.id FROM result_ref rr
      LEFT JOIN result r ON r.id = rr.result_id
      WHERE rr.set_id = ?
      ';
    $stmt = $this->_db->prepare($query);
    $stmt->execute(array($id));
    
    $pooledResultIds = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $pooledBy = $row['pooled_by'];
      if (!isset($pooledResultIds[$pooledBy])) {
        $pooledResultIds[$pooledBy] = array();
      }
      $pooledResultIds[$pooledBy][] = $row['id'];
    }
    return $pooledResultIds;
  }
}
