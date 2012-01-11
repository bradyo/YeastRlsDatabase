<?php

abstract class GenericTable
{
  protected $_db = null;
  protected $_filterParams = array();
  protected $_pager = null;
  protected $_query = null;
  protected $_queryParams = array();

  const ROWS_PER_PAGE = 100;


  public function __construct($db, $filterParams = array())
  {
    $this->_db = $db;
    $this->_filterParams = $filterParams;
  }


  public function getFilterForm()
  {
    $filterForm = new GenericFilterForm();
    $filterForm->bind($this->_filterParams);
    return $filterForm;
  }

  protected function getQuery()
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

    $query .= $this->getGroupBy();
    $query .= $this->getOrderByClause();

    // cache values
    $this->_query = $query;
    $this->_queryParams = $queryParams;

    return $this->_query;
  }

  protected function getQueryParams()
  {
    return $this->_queryParams;
  }


  protected function getGroupBy()
  {
    return null;
  }

  protected function getOrderByClause()
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

  
  abstract protected function getSelect();

  abstract protected function getWhereClause();

  abstract protected function getSortByChoices();

}
