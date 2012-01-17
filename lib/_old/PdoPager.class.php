<?php

class PdoPager
{
  private $rowsPerPage = 100;
  private $pages;
  private $pagesCount;
  private $page;

  public function __construct($query, $queryParams, $rowsPerPage = 100, $page = 1, $dbName = 'core')
  {
    // get number of rows from query
    $dbh = Doctrine_Manager::getInstance()->getConnection($dbName)->getDbh();
    $countSth = $dbh->prepare($query);
    $countSth->execute($queryParams);
    $rowCount = $countSth->fetchColumn();

    $this->rowsPerPage = $rowsPerPage;
    $this->pages = array();
    $this->pagesCount = ceil($rowCount / $rowsPerPage);
    for ($i = 1; $i <= $this->pagesCount; $i++) {
      $this->pages[] = $i;
    }
    $this->page = $page;
  }

  public function getPages()
  {
    return $this->pages;
  }

  public function getPage()
  {
    return $this->page;
  }

  public function getPagePrevious()
  {
    return ($this->page == 1) ? 1 : $this->page - 1;
  }

  public function getPageNext()
  {
    return ($this->page == $this->pagesCount) ? $this->pagesCount : $this->page + 1;
  }

  public function getPageFirst()
  {
    return 1;
  }

  public function getPageLast()
  {
    return $this->pagesCount;
  }

  public function getRowStart()
  {
    return ($this->page - 1) * $this->rowsPerPage;
  }

  public function getRowsPerPage()
  {
    return $this->rowsPerPage;
  }

}