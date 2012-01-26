<?php

class Build_Builder {
    const FILTER_FILENAME = 'filter.csv';
    const STRAINS_FILENAME = 'strains.csv';
    const EXPERIMENTS_FOLDER = 'experiments';
    
    private $inputPath;
    private $archivePath;
    private $dbConfig;
    private $execPathForR;
    private $logFilePath;
    
    private $matingTypeService;
    private $geneService;

    public function __construct($config) {
        $this->inputPath = $config['inputPath'];
        $this->archivePath = $config['archivePath'];
        $this->execPathForR = $config['rExecPath'];
        $this->dbConfig = $config['db'];
        $this->logFilePath = $config['logFilePath'];

        // set up database connection
        $dsn = $this->dbConfig['dsn'];
        $username = $this->dbConfig['username'];
        $password = $this->dbConfig['password'];
        $this->db = new PDO($dsn, $username, $password);
        
        // set up services
        $this->matingTypeService = new Service_MatingTypeService();
        $this->geneService = new Service_GeneService($this->db);
        
        // csv parsing may break if this is not set in php env
        ini_set('auto_detect_line_endings', true);
    }

    public function run() {
        $this->deactivateWebsite();
        $this->rebuildDatabase();
        $this->reactivateWebsite();
        $this->createArchive();
    }

    private function rebuildDatabase() {
        $this->truncateTargetTables();
        $dir = opendir($this->inputPath);
        while (false !== ($filename = readdir($dir))) {
            $path = $this->inputPath . DIRECTORY_SEPARATOR . $filename;
            if (! is_dir($path) || $filename == '.' || $filename == '..') {
                continue;
            }
            $this->processInputFolder($filename);
        }
        $this->optimizeTargetTables();
    }
    
    private function truncateTargetTables() {
        $targetTables = $this->getTargetTables();
        foreach ($targetTables as $table) {
            $this->db->exec('TRUNCATE ' . $table);
        }
    }
    
    private function optimizeTargetTables() {
        $targetTables = $this->getTargetTables();
        foreach ($targetTables as $table) {
            $this->db->exec('OPTIMIZE ' . $table);
        }
    }
    
    private function getTargetTables() {
        return array(
            'build_meta',
            'citation',
            'experiment',
            'strain',
            'sample_cell',
            'cell',
            'sample',
            'comparison',
            'across_media',
            'across_mating'
        );
    }
    
    private function processInputFolder($namespace) {
        // load filter file if it exists
        $filter = null;
        $filterFilePath = $this->inputPath . DIRECTORY_SEPARATOR . $namespace 
                . DIRECTORY_SEPARATOR . self::FILTER_FILENAME;
        if (is_file($filterFilePath)) {
            $filter = new Build_Filter($filterFilePath, $this->geneService, $this->matingTypeService);
        }
        
        // import strains
        $strainsFilePath = $this->inputPath 
                . DIRECTORY_SEPARATOR . $namespace 
                . DIRECTORY_SEPARATOR . self::STRAINS_FILENAME;
        if (is_file($strainsFilePath)) {
            $importer = new Build_StrainsImporter($this->db, $this->geneService, $this->matingTypeService);
            $importer->setFilter($filter);
            $importer->import($strainsFilePath, $namespace);
        }
        
        // import experiments
        $inputExperimentsPath = $this->inputPath 
                . DIRECTORY_SEPARATOR . $namespace 
                . DIRECTORY_SEPARATOR . self::EXPERIMENTS_FOLDER;
        if (is_dir($inputExperimentsPath)) {
            $dir = opendir($inputExperimentsPath);
            $importer = new Build_ExperimentImporter($this->db, $this->geneService, $this->matingTypeService);
            while (($filename = readdir($dir)) !== false) {
                if (! $this->isExperimentFile($filename)) {
                    continue;
                }
                $expFilePath = $inputExperimentsPath . DIRECTORY_SEPARATOR . $filename;
                $importer->import($namespace, $expFilePath);
            }
        }
    }
    
    private function isExperimentFile($filename) {
        return preg_match('/^.+\.csv$/', $filename);
    }

    private function deactivateWebsite() {
        // todo
    }

    private function reactivateWebsite() {
        // todo
    }

    private function createArchive() {
        // todo
    }
}