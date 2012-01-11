<?php

class MatingTypesTable
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

  public static function getMatingTypeChoices()
  {
    return array(
      'MATa',
      'MATalpha',
      'Homo Diploid',
    );
  }

  public static function getSortByChoices()
  {
    return array(
      'genotype'    => 'Genotype',
      'background'  => 'Background',
      'media'       => 'Media',
      'temperature' => 'Temperature',
    );
  }

  public function getBackgroundChoices()
  {
    $stmt = $this->_db->prepare('SELECT DISTINCT background FROM cross_mating_type');
    $stmt->execute();

    $backgrounds = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $background = $row['background'];
      $backgrounds[$background] = $background;
    }
    return $backgrounds;
  }

  public function getMediaChoices()
  {
    $stmt = $this->_db->prepare('SELECT DISTINCT media FROM cross_mating_type');
    $stmt->execute();

    $mediaTypes = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $mediaType = $row['media'];
      $mediaTypes[$mediaType] = $mediaType;
    }
    return $mediaTypes;
  }

  public function getFilterForm()
  {
    $options = array(
      'background_choices'  => $this->getBackgroundChoices(),
      'media_choices'       => $this->getMediaChoices(),
      'sort_by_choices'     => $this->getSortByChoices()
    );
    $filterForm = new MatingTypesFilterForm(null, $options);
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
    $select = '
      select c.id, c.genotype, c.background, c.media, c.temperature,
        r1.id, r1.set_lifespan_mean, r1.set_lifespan_count,
          r1.ref_lifespan_mean, r1.ref_lifespan_count,
          r1.percent_change, r1.ranksum_p,
        r2.id, r2.set_lifespan_mean, r2.set_lifespan_count,
          r2.ref_lifespan_mean, r2.ref_lifespan_count,
          r2.percent_change, r2.ranksum_p,
        r3.id, r3.set_lifespan_mean, r3.set_lifespan_count,
          r3.ref_lifespan_mean, r3.ref_lifespan_count,
          r3.percent_change, r3.ranksum_p
      from cross_mating_type c
      left join result r1 on r1.id = c.a_result_id
      left join result r2 on r2.id = c.alpha_result_id
      left join result r3 on r3.id = c.homodip_result_id
      ';
    return $select;
  }

  private function getWhereClause()
  {
    $filterValues = $this->_filterParams;
    $whereClause = new WhereClause();

    // search filter
    if (!empty($filterValues['search'])) {
      $input = $filterValues['search'];
      $whereClause->addWhere('
        (c.genotype LIKE ? OR c.background LIKE ? OR c.media LIKE ?)
        ',
        array_fill(0, 3, '%'.$input.'%')
      );
    }

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
      foreach ($inputs as $input) {
        $genotypeInputs[] = GenotypeLookup::getCleanGenotype($this->_db, $input);
      }

      $params = array_merge($inputs, $genotypeInputs);
      $qs = join(',', array_fill(0, count($params), '?'));
      $whereClause->addWhere('c.genotype IN ('.$qs.')', $params);
    }

    if (!empty($filterValues['background'])) {
      $input = $filterValues['background'];
      $whereClause->addWhere('c.background = ?', array($input));
    }

    // filter by media
    if (!empty($filterValues['media'])) {
      $input = $filterValues['media'];
      $whereClause->addWhere('c.media = ?', array($input));
    }

    if (isset($filterValues['single'])) {
      $whereClause->addWhere("c.genotype NOT LIKE '% %'
         AND c.genotype = LOWER(c.genotype)", array());
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
    $query = 'SELECT count(*) FROM cross_mating_type c';
    $whereClause = $this->getWhereClause();
    $queryParams = array();
    if (($where = $whereClause->getWhere()) !== null) {
      $query .= ' WHERE '.$where;
      $queryParams = $whereClause->getParameters();
    }
    $query .= $this->getOrderByClause();
    $queryParams = $this->getQueryParams();

    $pagerQuery = $query;
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

    $tableRows = $stmt->fetchAll(PDO::FETCH_NUM);
    return $tableRows;
  }

  public function getRows($ids = null)
  {
    if (is_array($ids) && count($ids) < 1) {
      return array();
    }

    if (is_array($ids)) {
      $query = $this->getSelect();
      $qs = join(',', array_fill(0, count($ids), '?'));
      $query .= ' WHERE c.id IN ('.$qs.')';
      $query .= $this->getOrderByClause();
      $queryParams = $ids;
    } else {
      $query = $this->getQuery();
      $queryParams = $this->getQueryParams();
    }

    // execute query and get rows
    $stmt = $this->_db->prepare($query);
    $stmt->execute($queryParams);
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);
    return $rows;
  }

}