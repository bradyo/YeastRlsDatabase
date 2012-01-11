<?php

function getFilenames($directory)
{
  $filenames = array();
  $dir = opendir($directory);
  if ($dir) {
    while (false !== ($filename = readdir($dir))) {
      if ($filename != '.' && $filename != '..') {
        $filenames[] = $directory.DIRECTORY_SEPARATOR.$filename;
      }
    }
  }
    sort($filenames);
  return $filenames;
}


function getCleanName($name)
{
  $name = strtolower($name);             // remove casing
  $name = str_replace('-', '', $name);   // remove hyphens
  return $name;
}



function getStartTime()
{
  // set up timer for elapsed time
  $mtime = microtime();
  $mtime = explode(' ', $mtime);
  $mtime = $mtime[1] + $mtime[0];
  return $mtime;
}

function getElapsedTime($startTime)
{
  // output elapsed time in seconds
  $mtime = microtime();
  $mtime = explode(" ", $mtime);
  $mtime = $mtime[1] + $mtime[0];
  $endtime = $mtime;
  return ($endtime - $startTime);
}

/**
 * Delete a file, or a folder and its contents (recursive algorithm)
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.3
 * @link        http://aidanlister.com/repos/v/function.rmdirr.php
 * @param       string   $dirname    Directory to delete
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function rmdirr($dirname)
{
  // Sanity check
  if (!file_exists($dirname)) {
    return false;
  }

  // Simple delete for a file
  if (is_file($dirname) || is_link($dirname)) {
    return unlink($dirname);
  }

  // Loop through the folder
  $dir = dir($dirname);
  while (false !== $entry = $dir->read()) {
    // Skip pointers
    if ($entry == '.' || $entry == '..') {
      continue;
    }

    // Recurse
    rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
  }

  // Clean up
  $dir->close();
  return rmdir($dirname);
}
