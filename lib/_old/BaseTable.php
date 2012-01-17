<?php

abstract class BaseTable
{
  protected $_db = null;
  protected $_filterParams = array();

  protected $_pager = null;
  protected $_query = null;


  const ROWS_PER_PAGE = 50;


  public function __construct($db, $filterParams = array())
  {
    $this->_db = $db;
    $this->_filterParams = $filterParams;

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


  abstract public function getFilterForm();
  
  abstract protected function getSelect();

  abstract protected function getWhereClause();

  abstract protected function getSortByChoices();

}