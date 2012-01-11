<?php

class YeastStrainBrowser
{
  private $_db = null;
  private $_filterParams = array();
  private $_whereClause = null;

  const ROWS_PER_PAGE = 100;


  public function __construct($db, $filterParams = array())
  {
    $this->_db = $db;
    $this->_filterParams = $filterParams;
  }

  public function getFilterForm()
  {
    $options = array(
      'background_choices' => $this->getBackgroundChoices(),
      'mating_type_choices' => $this->getMatingTypeChoices(),
      'location_choices' => $this->getLocationChoices(),
      'sort_by_choices' => $this->getOrderByChoices(),
      );
    $filterForm = new YeastStrainFilterForm(array(), $options);
    $filterForm->bind($this->_filterParams);
    return $filterForm;
  }

  public function getBackgroundChoices()
  {
    $query = 'SELECT DISTINCT background FROM yeast_strain';
    $stmt = $this->_db->query($query);

    $choices = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $background = $row['background'];
      if (!empty($background)) {
        $choices[$background] = $background;
      }
    }
    return $choices;
  }

  public function getMatingTypeChoices()
  {
    $query = 'SELECT DISTINCT mating_type FROM yeast_strain';
    $stmt = $this->_db->query($query);

    $choices = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $matingType = $row['mating_type'];
      if (!empty($matingType)) {
        $choices[$matingType] = $matingType;
      }
    }
    return $choices;
  }

  public function getLocationChoices()
  {
    $query = '
      SELECT DISTINCT u.location FROM yeast_strain y
      LEFT JOIN user u ON u.username = y.owner';
    $stmt = $this->_db->query($query);

    $choices = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $location = $row['location'];
      if (!empty($location)) {
        $choices[$location] = $location;
      }
    }
    return $choices;
  }
  
  private function getSelect()
  {
    $select = '
      SELECT
        s.id,
        s.name,
        s.owner,
        s.background,
        s.mating_type,
        s.genotype,
        s.genotype_short,
        s.genotype_unique,
        s.freezer_code,
        s.comment,
        s.is_locked,
        s.created_at,
        s.updated_at,
        u.email,
        u.lab,
        u.location,
        u.phone
      FROM yeast_strain s
      LEFT JOIN user u ON u.username = s.owner
      ';
    return $select;
  }

  private function getWhereClause()
  {
    if (isset($this->_whereClause)) {
      return $this->_whereClause;
    }

    $filterValues = $this->_filterParams;
    $whereClause = new WhereClause();

    // Filter strains for those containing the input search in relevant fields.
    if (!empty($filterValues['search'])) {
      $input = $filterValues['search'];
      $whereClause->addWhere('(
        s.name LIKE ?
        OR s.owner LIKE ?
        OR s.background LIKE ?
        OR s.mating_type LIKE ?
        OR s.genotype LIKE ?
        OR s.genotype_short LIKE ?
        OR s.genotype_unique LIKE ?
        OR s.freezer_code LIKE ?
        OR s.comment LIKE ?
        OR u.email LIKE ?
        OR u.lab LIKE ?
        OR u.location LIKE ?
        OR u.phone LIKE ?
        )',
        array_fill(0, 13, '%'.$input.'%')
      );
    }

    // Filter by strain names. Allow multiple inputs separated by a comma.
    if (!empty($filterValues['name'])) {
      $units = preg_split("/[\s,]+/", $filterValues['name']);
      $qs = join(',', array_fill(0, count($units), '?'));
      $whereClause->addWhere('s.name IN ('.$qs.')', $units);
    }

    // Filter by genotype. Allow multiple inputs separated by a comma.
    // Transform the input to unique genotype using getCleanGenotype.
    if (!empty($filterValues['genotype'])) {
      $input = $filterValues['genotype'];
      if (strpos($input, ',') !== null) {
        $units = explode(',', $input);        
      } else {
        $units = array($input);
      }

      $geneDb = Doctrine_Manager::getInstance()->getConnection('ncbi_gene_yeast')->getDbh();
      $uniqueGenotypes = array();
      foreach ($units as $unit) {
        $uniqueGenotypes[] = GenotypeLookup::getCleanGenotype($geneDb, $unit);
      }

      # do binary comparison to make casing significant
      $qs = join(',', array_fill(0, count($uniqueGenotypes), '?'));
      $whereClause->addWhere('BINARY s.genotype_unique IN ('.$qs.')', $uniqueGenotypes);
    }

    // Filter by background. Since this is a drop down select, match exact value.
    if (!empty($filterValues['background'])) {
      $input = $filterValues['background'];
      $whereClause->addWhere('s.background = ?', array($input));
    }

    // Filter by mating type. Since this is a drop down select, match exact value.
    if (!empty($filterValues['mating_type'])) {
      $input = $filterValues['mating_type'];
      $whereClause->addWhere('s.mating_type = ?', array($input));
    }

    // Filter by user location. Since this is a drop down select, match exact value.
    if (!empty($filterValues['location'])) {
      $input = $filterValues['location'];
      $whereClause->addWhere('u.location = ?', array($input));
    }

    // Filter by owner.
    if (!empty($filterValues['owner'])) {
      $input = $filterValues['owner'];
      $whereClause->addWhere('s.owner = ?', array($input));
    }

    // cache the result
    $this->_whereClause = $whereClause;
    
    return $whereClause;
  }


  private function getOrderByClause()
  {
    $orderByClause = null;
    $filterParams = $this->_filterParams;
    if (isset($filterParams['sort_by'])) {
      $orderByInput = $filterParams['sort_by'];
      $allowedFields = array_keys($this->getOrderByChoices());
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

  public function getOrderByChoices()
  {
    $choices = array(
      'name'            => 'Name',
      'background'      => 'Background',
      'mating_type'     => 'Mating Type',
      'genotype_unique' => 'Genotype',
      'owner'           => 'Owner',
      'location'        => 'Location',
      );
    return $choices;
  }


  public function getPager($page)
  {
    // set up the pager
    $query = $this->getSelect();
    $queryParams = array();
    $whereClause = $this->getWhereClause();
    if (($where = $whereClause->getWhere()) !== null) {
      $query .= ' WHERE ' . $where;
      $queryParams = $whereClause->getParameters();
    }

    $pagerQuery = 'SELECT COUNT(*) FROM ('.$query.') t';
    $sth = $this->_db->prepare($pagerQuery);
    $sth->execute($queryParams);
    $rowCount = $sth->fetchColumn();

    // create pager    
    $pager = new SimplePager($rowCount, self::ROWS_PER_PAGE, $page);
    return $pager;
  }



  public function getPageRows($page)
  {
    // set up the query
    $query = $this->getSelect();
    $queryParams = array();

    // add where clause
    $whereClause = $this->getWhereClause();
    if (($where = $whereClause->getWhere()) !== null) {
      $query .= ' WHERE ' . $where;
      $queryParams = $whereClause->getParameters();
    }

    // add order by clause
    if (($orderBy = $this->getOrderByClause()) !== null) {
      $query .= ' ' . $orderBy;
    }

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
      $qs = join(',', array_fill(0, count($ids), '?'));
      $query .= ' WHERE s.id IN ('.$qs.')';
      $query .= $this->getOrderByClause();
      $queryParams = $ids;
    }
    else {
      // set up the query
      $query = $this->getSelect();
      $queryParams = array();

      // add where clause
      $whereClause = $this->getWhereClause();
      if (($where = $whereClause->getWhere()) !== null) {
        $query .= ' WHERE ' . $where;
        $queryParams = $whereClause->getParameters();
      }

      // add order by clause
      if (($orderBy = $this->getOrderByClause()) !== null) {
        $query .= ' ' . $orderBy;
      }
    }

    // execute query and get rows
    $stmt = $this->_db->prepare($query);
    $stmt->execute($queryParams);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }

  public function getRow($id)
  {
    $query = $this->getSelect() . ' WHERE s.id = ?';
    $stmt = $this->_db->prepare($query);
    $stmt->execute(array($id));
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row;
  }
}