<?php

class GenotypeLookup
{
  public static function getCleanGenotype($dbh, $genotype)
  {
    $dbh = Doctrine_Manager::getInstance()->getConnection('ncbi_gene_yeast')->getDbh();
    $sth = $dbh->prepare('
        SELECT g.id, g.symbol FROM gene g
        LEFT JOIN gene_synonym s ON s.gene_id = g.id
        WHERE LOWER(g.symbol) = ?
          OR LOWER(g.locus_tag) = ?
        ');

    $genotype = trim($genotype);
    $values = str_replace('-', '', $genotype);
    $values = preg_split("/\s+/", $genotype);

    // using database, convert values making up genotype to a gene symbol
    foreach ($values as &$value) {
      // check case, if it is mixed just skip it
      $isUpper = false;
      if (strtoupper($value) == $value) {
        $isUpper = true;
      } else if (strtolower($value) == $value) {
        $isUpper = false;
      } else {
        continue;
      }
      $value = strtolower($value);

      // convert locus tag,
      $sth->execute(array($value, $value));
      $geneSymbols = array();
      while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
        $geneSymbol = $row['symbol'];
        $geneSymbols[$geneSymbol] = $geneSymbol;
      }
      $geneSymbols = array_keys($geneSymbols);

      if (count($geneSymbols) == 1) {
        $geneSymbol = $geneSymbols[0];
        $value = $geneSymbol;
      }

      if ($isUpper) {
        $value = strtoupper($value);
      } else {
        $value = strtolower($value);
      }
    }
    sort($values);

    $genotype = join(' ', array_values($values));
    return $genotype;
  }

}
