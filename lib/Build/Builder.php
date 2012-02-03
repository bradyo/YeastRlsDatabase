<?php

class Build_Builder {
    const FILTER_FILENAME = 'filter.csv';
    const STRAINS_FILENAME = 'strains.csv';
    const EXPERIMENTS_FOLDER = 'experiments';
    const CITATIONS_FILENAME = 'citations.csv';
    
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
        
        // import globla citations file
        $citationsFilePath = $this->inputPath
                . DIRECTORY_SEPARATOR . self::CITATIONS_FILENAME;
        if (is_file($citationsFilePath)) {
            $importer = new Build_CitationsImporter($this->db);
            $this->db->beginTransaction();
            try {
                $importer->import($citationsFilePath);
                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollBack();
            }
        }
        
        // process each experiment input folder
        $dir = opendir($this->inputPath);
        while (false !== ($filename = readdir($dir))) {
            $path = $this->inputPath . DIRECTORY_SEPARATOR . $filename;
            if (! is_dir($path) || $filename == '.' || $filename == '..') {
                continue;
            }
            $this->processInputFolder($filename);
        }
        
        // udpate build meta data
        $stmt = $this->db->prepare('
            INSERT INTO build_meta (created_at) VALUES (?)
            ');
        $stmt->execute(array(date('Y-m-d H:i:s')));
        
        // optimize database indexes
        $this->optimizeTargetTables();
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

    private function processInputFolder($namespace) {
        // load filter file if it exists
        $filter = null;
        $filterFilePath = $this->inputPath . DIRECTORY_SEPARATOR . $namespace 
                . DIRECTORY_SEPARATOR . self::FILTER_FILENAME;
        if (is_file($filterFilePath)) {
            $filter = new Build_Filter($this->db, $filterFilePath);
        }
        
        // import strains
        $strainsFilePath = $this->inputPath 
                . DIRECTORY_SEPARATOR . $namespace 
                . DIRECTORY_SEPARATOR . self::STRAINS_FILENAME;
        if (is_file($strainsFilePath)) {
            $importer = new Build_StrainsImporter($this->db);
            $importer->setFilter($filter);
            
            // try importing strains in a transaction
            $this->db->beginTransaction();
            try {
                $importer->import($strainsFilePath, $namespace);
                $this->db->commit();
            } catch (Exception $e) {
                echo "error loading strains $strainsFilePath";
                $this->db->rollBack();
            }
        }
        
        // import experiments
        $inputExperimentsPath = $this->inputPath 
                . DIRECTORY_SEPARATOR . $namespace 
                . DIRECTORY_SEPARATOR . self::EXPERIMENTS_FOLDER;
        if (is_dir($inputExperimentsPath)) {
            $dir = opendir($inputExperimentsPath);
            $importer = new Build_ExperimentImporter($this->db);
            while (($filename = readdir($dir)) !== false) {
                if (! $this->isExperimentFile($filename)) {
                    continue;
                }
                $expFilePath = $inputExperimentsPath . DIRECTORY_SEPARATOR . $filename;
                echo "importing $expFilePath\n";
                
                // try importing the file in a transaction
                $this->db->beginTransaction();
                try {
                    $importer->import($namespace, $expFilePath);
                    $this->db->commit();
                } catch (Exception $e) {
                    echo "error loading experiment $expFilePath\n";
                    $this->db->rollBack();
                }
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