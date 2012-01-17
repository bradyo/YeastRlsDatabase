<?php

// get the output director from the command line
$outputDir = "output";

// dump result table to file
$dbh = new PDO('sqlite:' . $outputDir . '/rls.db');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $dbh->prepare('SELECT * FROM result');
$stmt->execute();

$filename = $outputDir . '/rls.csv';
$fout = fopen($filename, 'w');

// output header
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $keys = array_keys($row);
  fwrite($fout, join(',', $keys) . "\n");
}

// output data
while ($row) {
  // escape data if needed
  foreach ($row as &$value) {
    if (strpos($value, ',') || strpos($value, '"')) {
      $value = str_replace('"', '""', $value);
      $value = '"' . $value . '"';
    }
  }
  fwrite($fout, join(',', $row) . "\n");

  // get next row
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
}
fclose($fout);
