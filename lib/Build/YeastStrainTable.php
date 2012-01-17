<?php

/**
 * Class to facilitate database interactions on the yeast_strain table
 *
 * @author brady
 */
class YeastStrainTable
{
  private $_dbh = null;
  static private $_sthFind = null;

  public function __construct($dbh)
  {
    $this->_dbh = $dbh;
  }

  public function findByName($name)
  {
    if (self::$_sthFind == null) {
      self::$_sthFind = $this->_dbh->prepare('
        SELECT * FROM yeast_strain WHERE name = ? LIMIT 1 ');
    }
    self::$_sthFind->execute(array($name));
    $row = self::$_sthFind->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $yeastStrain = new YeastStrain($row);
      return $yeastStrain;
    }
  }
  
}

