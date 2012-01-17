<?php

/**
 * Processes raw data files in "data/input", applying filters as needed. Processed
 * data files are output to "data/sources", which can be used directly by the
 * database build script. 
 */

define('BASE_PATH', dirname(__FILE__) . '/..');
require_once(BASE_PATH . '/config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
sfContext::createInstance($configuration);

$config = array(
    'db' => sfContext::getInstance()->getDatabaseConnection(),
);
$builder = new Build_Builder($config);
$builder->run();