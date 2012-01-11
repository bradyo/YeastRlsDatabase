<?php 
function fputcsv2 ($fh, array $fields, $delimiter = ',', $enclosure = '"', $mysql_null = false) {
    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');

    $output = array();
    foreach ($fields as $field) {
        if ($field === null && $mysql_null) {
            $output[] = 'NULL';
            continue;
        }

        $output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
            $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
        ) : $field;
    }

    fwrite($fh, join($delimiter, $output) . "\n");
}


function quoteCsv($field, $delimiter = ',', $enclosure = '"')
{
  $delimiter_esc = preg_quote($delimiter, '/');
  $enclosure_esc = preg_quote($enclosure, '/');

  $output = null;
  if (preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
    $output = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
  } else {
    $output = $field;
  }
  return $output;
}