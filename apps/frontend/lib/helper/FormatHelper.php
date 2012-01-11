<?php 

function format($value, $decimalPlaces = 2)
{
  $output = null;
  if (is_numeric($value) && !empty($value)) {
    $output = number_format($value, $decimalPlaces);
  }
  return $output;
}