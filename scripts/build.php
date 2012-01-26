<?php

/**
 * Process raw data files in "data/input" and compile into database.
 */

define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));
require_once(BASE_PATH . '/config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
sfContext::createInstance($configuration);

$config = array(
    'inputPath' =>  BASE_PATH . '/data/test-input',
    'archivePath' =>  BASE_PATH . '/data/archive',
    'rExecPath' => '/usr/bin/R',
    'db' => array(
        'dsn' => 'mysql:host=localhost;dbname=sageweb_yeast-rls',
        'username' => 'root',
        'password' => 'happy',
    ),
    'logFilePath' => BASE_PATH . '/logs/build.log',
);
$builder = new Build_Builder($config);
$builder->run();