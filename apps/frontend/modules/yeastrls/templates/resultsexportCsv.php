<?php
use_helper('Csv');

echo ",";
echo ",";
foreach ($rows as $row) {
  echo 'Set', ",";
  echo 'Reference', ",";
  echo ",";
}
echo "\n";

echo "Experiment,";
echo ",";
foreach ($rows as $row) {
  echo quoteCsv($row['experiments']), ",";
  echo ","; // skip reference
  echo ",";
}
echo "\n";

echo "Name,";
echo ",";
foreach ($rows as $row) {
  echo quoteCsv($row['set_name']), ",";
  echo quoteCsv($row['ref_name']), ",";
  echo ",";
}
echo "\n";

echo "Background,";
echo ",";
foreach ($rows as $row) {
  echo quoteCsv($row['set_background']), ",";
  echo quoteCsv($row['ref_background']), ",";
  echo ",";
}
echo "\n";

echo "Mating Type,";
echo ",";
foreach ($rows as $row) {
  echo quoteCsv($row['set_mating_type']), ",";
  echo quoteCsv($row['ref_mating_type']), ",";
  echo ",";
}
echo "\n";

echo "Genotype,";
echo ",";
foreach ($rows as $row) {
  echo quoteCsv($row['set_genotype']), ",";
  echo quoteCsv($row['ref_genotype']), ",";
  echo ",";
}
echo "\n";

echo "Media,";
echo ",";
foreach ($rows as $row) {
  echo quoteCsv($row['set_media']), ",";
  echo quoteCsv($row['ref_media']), ",";
  echo ",";
}
echo "\n";

echo "Temperature (C),";
echo ",";
foreach ($rows as $row) {
  echo $row['set_temperature'], ",";
  echo $row['ref_temperature'], ",";
  echo ",";
}
echo "\n";

echo "Lifespan Mean (days),";
echo ",";
foreach ($rows as $row) {
  echo $row['set_lifespan_mean'], ",";
  echo $row['ref_lifespan_mean'], ",";
  echo ",";
}
echo "\n";

echo "Lifespan Std Dev (days),";
echo ",";
foreach ($rows as $row) {
  echo $row['set_lifespan_stdev'], ",";
  echo $row['ref_lifespan_stdev'], ",";
  echo ",";
}
echo "\n";

echo "Mean Change (%),";
echo ",";
foreach ($rows as $row) {
  echo $row['percent_change'], ",";
  echo ",";
  echo ",";
}
echo "\n";


echo "Ranksum p,";
echo ",";
foreach ($rows as $row) {
  echo $row['ranksum_p'], ",";
  echo ",";
  echo ",";
}
echo "\n";

echo "Raw Data,";
echo ",";
echo "\n";

for ($i = 0; $i < $maxLength; $i++) {
  echo $i + 1, ",";
  echo ",";
  foreach ($rows as $row) {
    echo isset($row['set_lifespans_array'][$i]) ? $row['set_lifespans_array'][$i] : '', ",";
    echo isset($row['ref_lifespans_array'][$i]) ? $row['ref_lifespans_array'][$i] : '', ",";
    echo ",";
  }
  echo "\n";
}
