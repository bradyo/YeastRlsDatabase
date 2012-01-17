<?php
define(BASE_PATH, dirname(__FILE__) . '/..');
require_once(BASE_PATH . '/config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
sfContext::createInstance($configuration)->dispatch();
