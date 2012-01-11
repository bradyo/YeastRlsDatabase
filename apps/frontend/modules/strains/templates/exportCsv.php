<?php
use_helper('Csv');

$columns = array(
  'name' => 'Name',
  'background' => 'Background',
  'mating_type' => 'Mating Type',
  'genotype' => 'Full Genotype',
  'genotype_short' => 'Short Genotype',
  'genotype_unique' => 'Pooling Genotype',
  'freezer_code' => 'Freezer Code',
  'comment' => 'Comment',
  '' => '',
  'owner' => 'Owner',
  'email' => 'E-mail',
  'lab' => 'Lab',
  'location' => 'Location',
  'Phone' => 'Phone'
  );

if (count($rows) > 0) {
  $headers = array_map('quoteCsv', array_values($columns));
  echo join(',', $headers), "\n";

  foreach ($rows as $id => $row) {
    foreach (array_keys($columns) as $key) {
      if (isset($row[$key])) {
        echo quoteCsv($row[$key]), ',';
      } else {
        echo ',';
      }
    }
    echo "\n";
  }
} else {
  echo "no rows to display\n";
}