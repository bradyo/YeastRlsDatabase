<?php
use_helper('Csv');

if (count($rows) > 0) {
  echo join(",", array_keys($rows[0])), "\n";
  foreach ($rows as $id => $row) {
    echo join(",", array_map('quoteCsv', array_values($row))), "\n";
  }
} else {
  echo "no rows to display\n";
}