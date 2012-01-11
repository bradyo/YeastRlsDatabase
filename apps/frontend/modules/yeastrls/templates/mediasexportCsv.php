<?php
use_helper('Csv');

echo ",,,,,YPD 2%,,,,,,,0.5% D,,,,,,,0.05% D,,,,,,,3% Gly,,,,,,,\n";
echo join(",", array('id', 'genotype', 'background', 'mating_type', 'temperature'));
echo ",";
for ($i = 0; $i < 4; $i++) {
  echo join(",", array('id', 'mean_rls', 'count', 'wt_mean_rls', 'wt_count', 'percent_change', 'ranksum_p'));
  echo ",";
}
echo "\n";

if (count($rows) > 0) {
  foreach ($rows as $id => $row) {
    echo join(",", array_map('quoteCsv', array_values($row))), ",\n";
  }
} else {
  echo "no rows to display\n";
}