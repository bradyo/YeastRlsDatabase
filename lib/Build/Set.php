<?php
/* 
 * Class for storing set data and saving to database
 */

/**
 * Description of Set
 *
 * @author brady
 */
class Set
{
  private $dbh = null;
  static private $sthInsert = null;

  private $id = null;
  private $name = null;
  private $media = 'YPD';
  private $temperature = 30;
  private $experimentName = null; 
  private $strain = null; // instance of YeastStrain
  private $lifespans = array(); // array of lifespans (integer days)
  private $lifespanStartCount = 0;

  public function __construct($dbh, $values = array())
  {
    $this->dbh = $dbh;
    $this->initValues($values);
  }

  public function __set($key, $val)
  {
    $this->$key = $val;
  }

  public function __get($key)
  {
    return $this->{$key};
  }

  public function initValues($values)
  {
    if (isset($values['name'])) {
      $this->name = (string)$values['name'];
    }
    if (isset($values['media'])) {
      $this->media = (string)$values['media'];
    }
    if (isset($values['temperature'])) {
      $this->temperature = doubleval($values['temperature']);
    }
    if (isset($values['experiment'])) {
      $this->experimentName = $values['experiment'];
    }
    if (isset($values['strain']) && $values['strain'] instanceof YeastStrain) {
      $this->strain = $values['strain'];
    }
    if (isset($values['lifespans']) && is_array($values['lifespans'])) {
      $this->lifespans = $values['lifespans'];
    }
    if (isset($values['lifespan_start_count'])) {
      $this->lifespanStartCount = $values['lifespan_start_count'];
    }
  }
  
  public function save()
  {
    // get the cached set insert query if it exists, otherwise create it
    if (self::$sthInsert == null) {
      self::$sthInsert = $this->dbh->prepare('
        INSERT INTO "set" (
          name,
          media,
          temperature,
          experiment,
          strain,
          lifespans,
          lifespan_start_count
        ) VALUES (?, ?, ?, ?, ?, ?, ?) ');
    }

    $strainName = null;
    if ($this->strain !== null) {
      $strainName = $this->strain['name'];
    }

    $lifespansString = null;
    if (count($this->lifespans) > 0) {
      $lifespansString = join(',', $this->lifespans);
    }

    // save the data to set table
    $params = array(
      $this->name,
      $this->media,
      $this->temperature,
      $this->experimentName,
      $strainName,
      $lifespansString,
      $this->lifespanStartCount,
      );
    self::$sthInsert->execute($params);
    $this->id = $this->dbh->lastInsertId();
  }

}


