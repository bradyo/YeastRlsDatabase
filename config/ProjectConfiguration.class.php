<?php

require_once dirname(__FILE__) . '/../lib/vendor/symfony-1.4.16/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration {

    public function setup() {
        $this->setLogDir(dirname(__FILE__) . '/../data/logs');
        $this->setCacheDir(dirname(__FILE__) . '/../data/cache');
    }
}
