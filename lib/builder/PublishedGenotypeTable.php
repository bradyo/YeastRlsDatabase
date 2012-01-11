<?php

/**
 * Class to facilitate database interactions on the genotype_pubmed_id table
 *
 * @author brady
 */
class PublishedGenotypeTable
{
  private $_dbh = null;
  static private $_sthFind = null;

  public function __construct($dbh)
  {
    $this->_dbh = $dbh;
  }

  public function findPubmedIdsByGenotype($genotype)
  {
    if (self::$_sthFind == null) {
      self::$_sthFind = $this->_dbh->prepare('
        SELECT DISTINCT pubmed_id FROM genotype_pubmed_id 
				WHERE genotype = ?
				');
    }
    self::$_sthFind->execute(array($genotype));
    $rows = self::$_sthFind->fetchAll(PDO::FETCH_ASSOC);
    
		$pubmedIds = array();
		foreach ($rows as $row) {
			$pubmedIds[] = $row['pubmed_id'];
    }
		return $pubmedIds;
  }
  
}

