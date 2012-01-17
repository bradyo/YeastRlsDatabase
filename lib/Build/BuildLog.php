<?php

/**
 * Description of Experimentclass
 *
 * @author brady
 */
class BuildLog implements ArrayAccess
{
  private $dbh = null;
  static private $sthInsert = null;

  private $id = null;
  private $filename = null;
  private $message = null;

  public function __construct($dbh, $filename, $message)
  {
    $this->dbh = $dbh;
    $this->filename = $filename;
    $this->message = $message;
  }

  public function save()
  {
    // insert data into experiment table
    if (self::$sthInsert == null) {
      self::$sthInsert = $this->dbh->prepare('
        INSERT INTO build_log
        (filename, message) VALUES (?, ?) ');
    }
    self::$sthInsert->execute(array($this->filename, $this->message));
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

