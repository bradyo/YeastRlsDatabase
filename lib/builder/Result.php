<?php

/**
 * Description of Result
 *
 * @author brady
 */
class Result
{
  private $dbh = null;
  static private $sthInsert = null;
  static private $sthInsertSet = null;
  static private $sthInsertRef = null;

  private $id = null;
  private $setName = null;
  private $setStrain = null;
  private $setBackground = null;
  private $setMatingType = null;
  private $setGenotype = null;
  private $setMedia = null;
  private $setTemperature = null;
	private $setLifespanStartCount = 0;

  private $refName = null;
  private $refStrain = null;
  private $refBackground = null;
  private $refMatingType = null;
  private $refGenotype = null;
  private $refMedia = null;
  private $refTemperature = null;
	private $refLifespanStartCount = 0;

  private $pooledBy = null;

  private $setIds = array();
  private $refIds = array();


  public function __construct($dbh, $values)
  {
    $this->dbh = $dbh;
    $this->setValues($values);
  }

  public function setValues($values)
  {
    // process set values
    if (isset($values['set_name'])) {
      $this->setName = (string)$values['set_name'];
    }
    if (isset($values['set_strain'])) {
      $this->setStrain = (string)$values['set_strain'];
    }
    if (isset($values['set_background'])) {
      $this->setBackground = (string)$values['set_background'];
    }
    if (isset($values['set_mating_type'])) {
      $this->setMatingType = (string)$values['set_mating_type'];
    }
    if (isset($values['set_genotype'])) {
      $this->setGenotype = (string)$values['set_genotype'];
    }
    if (isset($values['set_media'])) {
      $this->setMedia = (string)$values['set_media'];
    }
    if (isset($values['set_temperature'])) {
      $this->setTemperature = doubleval($values['set_temperature']);
    }
    if (isset($values['set_lifespan_start_count'])) {
      $this->setLifespanStartCount = intval($values['set_lifespan_start_count']);
    }

    // process ref values
    if (isset($values['ref_name'])) {
      $this->refName = (string)$values['ref_name'];
    }
    if (isset($values['ref_strain'])) {
      $this->refStrain = (string)$values['ref_strain'];
    }
    if (isset($values['ref_background'])) {
      $this->refBackground = (string)$values['ref_background'];
    }
    if (isset($values['ref_mating_type'])) {
      $this->refMatingType = (string)$values['ref_mating_type'];
    }
    if (isset($values['ref_genotype'])) {
      $this->refGenotype = (string)$values['ref_genotype'];
    }
    if (isset($values['ref_media'])) {
      $this->refMedia = (string)$values['ref_media'];
    }
    if (isset($values['ref_temperature'])) {
      $this->refTemperature = doubleval($values['ref_temperature']);
    }
    if (isset($values['ref_lifespan_start_count'])) {
      $this->refLifespanStartCount = intval($values['ref_lifespan_start_count']);
    }

    if (isset($values['pooled_by'])) {
      $this->pooledBy = (string)$values['pooled_by'];
    }

    // process sets
    if (isset($values['set_ids']) && is_array($values['set_ids'])) {
      $this->setIds = array();
      foreach ($values['set_ids'] as $setId) {
        $this->setIds[] = $setId;
      }
    }

    // process refs
    if (isset($values['ref_ids']) && is_array($values['ref_ids'])) {
      $this->refIds = array();
      foreach ($values['ref_ids'] as $setId) {
        $this->refIds[] = $setId;
      }
    }
  }

  public function __set($key, $val)
  {
    $this->$key = $val;
  }

  public function __get($key)
  {
    return $this->$key;
  }

  public function save()
  {
    // save to data to result table
    if (self::$sthInsert == null) {
      self::$sthInsert = $this->dbh->prepare('
        INSERT INTO result (
          set_name,
          set_strain,
          set_background,
          set_mating_type,
          set_genotype,
          set_media,
          set_temperature,
          set_lifespan_start_count,
          ref_name,
          ref_strain,
          ref_background,
          ref_mating_type,
          ref_genotype,
          ref_media,
          ref_temperature,
          ref_lifespan_start_count,
          pooled_by
        )
        VALUES ('.join(',', array_fill(0, 17, '?')).') ');
    }

    self::$sthInsert->execute(array(
      $this->setName,
      $this->setStrain,
      $this->setBackground,
      $this->setMatingType,
      $this->setGenotype,
      $this->setMedia,
      $this->setTemperature,
      $this->setLifespanStartCount,
      $this->refName,
      $this->refStrain,
      $this->refBackground,
      $this->refMatingType,
      $this->refGenotype,
      $this->refMedia,
      $this->refTemperature,
      $this->refLifespanStartCount,
      $this->pooledBy
      ));
    $this->id = $this->dbh->lastInsertId();

    // save sets to result_set table
    if (self::$sthInsertSet == null) {
      self::$sthInsertSet = $this->dbh->prepare('
        INSERT INTO result_set (result_id, set_id) VALUES (?, ?) ');
    }
    foreach ($this->setIds as $setId) {
      $params = array($this->id, $setId);
      self::$sthInsertSet->execute($params);
    }

    // save refs to result_ref table
    if (self::$sthInsertRef == null) {
      self::$sthInsertRef = $this->dbh->prepare('
        INSERT INTO result_ref (result_id, set_id) VALUES (?, ?) ');
    }
    foreach ($this->refIds as $setId) {
      $params = array($this->id, $setId);
      self::$sthInsertRef->execute($params);
    }
  }
  
}

