<?php

/**
 * Description of WhereClause
 *
 * @author brady
 */
class WhereClause
{
  // array of wheres, key is prepare query, value is array of parameters
  private $_wheres = array();

  public function addWhere($query, $params = array())
  {
    $this->_wheres[] = array(
      'query' => $query,
      'params' => $params
      );
  }

  public function getWhere($mergeWith = 'AND')
  {
    $query = null;
    $queryUnits = array();
    foreach ($this->_wheres as $where) {
      $queryUnits[] = $where['query'];
    }

    if (count($queryUnits) > 0) {
      $query = join(' '.$mergeWith.' ', $queryUnits);
    }
    return $query;
  }

  public function getParameters()
  {
    $params = array();
    foreach ($this->_wheres as $where) {
      $unitParams = $where['params'];
      foreach ($unitParams as $value) {
        $params[] = $value;
      }
    }
    return $params;
  }

}
