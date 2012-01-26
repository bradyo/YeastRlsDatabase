<?php

/**
 * Description of Gene
 *
 * @author brady
 */
class Gene implements ArrayAccess
{
  private $id = null;
  private $ncbiGeneId = null;
  private $ncbiTaxId = null;
  private $symbol = null;
  private $locusTag = null;
  private $synonyms = array();
  private $dbxrefs = array();


  public function __construct($values)
  {
    $this->setValues($values);
  }

  public function setValues($values)
  {
    if (isset($values['id'])) {
      $this->id = $values['id'];
    }
    if (isset($values['ncbi_gene_id'])) {
      $this->ncbiGeneId = $values['ncbi_gene_id'];
    }
    if (isset($values['ncbi_tax_id'])) {
      $this->ncbiTaxId = $values['ncbi_tax_id'];
    }
    if (isset($values['symbol'])) {
      $this->symbol = $values['symbol'];
    }
    if (isset($values['locus_tag'])) {
      $this->locusTag = $values['locus_tag'];
    }
    if (isset($values['synonyms']) && is_array($values['synonyms'])) {
      $this->synonyms = $values['synonyms'];
    }
    if (isset($values['dbxrefs']) && is_array($values['dbxrefs'])) {
      $this->dbxrefs = $values['dbxrefs'];
    }
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

