<?php

/**
 * Description of YeastStrain
 *
 * @author brady
 */
class YeastStrain implements ArrayAccess
{
  private $id = null;
  private $name = null;
  private $background = null;
  private $matingType = null;
  
  private $poolingGenotype = null;

  public function __construct($values = array())
  {
    $this->setValues($values);
  }

  public function setValues($values)
  {
    if (isset($values['id'])) {
      $this->id = $values['id'];
    }
    if (isset($values['name'])) {
      $this->name = $values['name'];
    }
    if (isset($values['background'])) {
      $this->background = $values['background'];
    }
    if (isset($values['mating_type'])) {
      $this->matingType = $values['mating_type'];
    }
    if (isset($values['genotype_unique'])) {
      $this->poolingGenotype = $values['genotype_unique'];
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

