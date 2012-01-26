<?php

/**
 * Description of Experimentclass
 *
 * @author brady
 */
class Experiment implements ArrayAccess
{
  private $dbh = null;
  static private $sthInsert = null;

  private $id = null;
  private $name = null;

  public function __construct($dbh, $values)
  {
    $this->dbh = $dbh;
    $this->setValues($values);
  }

  public function setValues($values)
  {
    if (isset($values['name'])) {
      $this->name = (string)$values['name'];
    }
  }

  public function save()
  {
    // insert data into experiment table
    if (self::$sthInsert == null) {
      self::$sthInsert = $this->dbh->prepare('INSERT INTO experiment (name) VALUES (?)');
    }
    self::$sthInsert->execute(array($this->name));
    $this->id = $this->dbh->lastInsertId();
  }

  /**
   * array access implementation
   */
  public function offsetExists( $offset )
  {
    return isset( $this->$offset );
  }

  public function offsetSet( $offset, $value)
  {
    $this->$offset = $value;
  }

  public function offsetGet( $offset )
  {
    return $this->$offset;
  }

  public function offsetUnset( $offset )
  {
    unset( $this->$offset );
  }
}

