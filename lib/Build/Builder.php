<?php

class Build_Builder {
    const FILTER_FILENAME = 'filter.csv';
    const STRAINS_FILENAME = 'strains.csv';

    private $inputPath;
    private $sourcesPath;
    private $archivePath;
    private $db;
    private $execPathForR;
    
    private $matingTypeService;
    private $genotypeService;

    public function __construct($config) {
        $this->inputPath = BASE_PATH . '/data/input';
        $this->sourcesPath = BASE_PATH . '/data/sources';
        $this->archivePath = BASE_PATH . '/data/archive';
        $this->execPathForR = '/usr/bin/R';
        $this->db = $config['db'];
        
        $this->matingTypeService = new Service_MatingType();
        $this->genotypeService = new Service_Genotype($this->db);
    }

    public function run() {
        $this->buildSources();
        $this->deactivateWebsite();
        $this->buildDatabase();
        $this->reactivateWebsite();
        $this->buildArchive();
    }

    private function buildSources() {
        // clear folders in sources directory
        $this->deleteDirectory($this->sourcesPath);
        $dir = opendir($this->sourcesPath);
        while (false !== ($filename = readdir($dir))) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $this->deleteDirectory($filename);
        }

        // process input folders
        $dir = opendir($this->inputPath);
        while (false !== ($filename = readdir($dir))) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $this->processInputFolder($filename);
        }
    }

    private function processInputFolder($folder) {
        echo "processing $folder \n";
        
        // load filters
        $filters = array();
        $filtersPath = $this->inputPath . DIRECTORY_SEPARATOR . $folder 
                . DIRECTORY_SEPARATOR . self::FILTER_FILENAME;
        if (file_exists($filtersPath)) {
            $file = fopen($filtersPath, 'r');
            fgetcsv($file); // discard header
            while (false !== ($rowData = fgetcsv($file))) {
                $strainData = array(
                    'shortGenotype' => $rowData[0],
                    'poolingGenotype' => $this->genotypeService->getNormalizedGenotype($rowData[0]),
                    'media' => $rowData[1],
                    'background' => $rowData[2],
                    'matingType' => $this->matingTypeService->getNormalizedMatingType($rowData[3]),
                    'reference' => $rowData[4],
                    'comment' => $rowData[5],
                );
                $key = join('/', array(
                    $strainData['poolingGenotype'],
                    $strainData['background'],
                    $strainData['matingType'],
                )); 
                echo "$key\n";
                die();
                $filters[$key] = $strainData;
            }
        }
        
        // create source folder
        mkdir($this->sourcesPath . DIRECTORY_SEPARATOR . $folder);
        
        // process strains file
        $strainsInputPath = $this->inputPath . DIRECTORY_SEPARATOR . $folder 
                . DIRECTORY_SEPARATOR . self::STRAINS_FILENAME;
        $strainsOutputPath = $this->sourcesPath . DIRECTORY_SEPARATOR . $folder
                . DIRECTORY_SEPARATOR . self::STRAINS_FILENAME;
        if (file_exists($strainsInputPath)) {
            // loop over input file, output to output file if strain passes filter
            $inputFile = fopen($strainsInputPath, 'r');
            touch($strainsOutputPath);
            $outputFile = fopen($strainsOutputPath, 'w');
            while (false !== ($rowData = fgetcsv($inputFile))) {
                $key = join('/', array(
                    $rowData[5],
                    $rowData[2],
                    $rowData[3],
                ));
                if (isset($filters[$key])) {
                    fputcsv($outputFile, $rowData);
                }
            }
            fclose($inputFile);
            fclose($outputFile);
        }
    }
 

    private function deleteDirectory($rootPath) {
        $dir = opendir($rootPath);
        while (false !== ($filename = readdir($dir))) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $path = $rootPath . '/' . $filename;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($rootPath);
    }

    private function deactivateWebsite() {
        // todo
    }

    private function buildDatabase() {
        // todo
    }

    private function reactivateWebsite() {
        // todo
    }

    private function buildArchive() {
        // todo
    }

}