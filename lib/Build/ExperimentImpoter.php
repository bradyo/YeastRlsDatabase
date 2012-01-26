<?php

class Build_ExperimentImporter
{
    private $db;
    private $filter;
    private $genotypeService;
    private $matingTypeService;
    
    private $insertExperimentStmt;
    
    /**
     * @param PDO $db
     * @param Service_GeneService $geneService
     * @param Service_MatingTypeService $matingTypeService 
     */
    public function __construct($db, $geneService, $matingTypeService) {
        $this->db = $db;
        $this->genotypeService = $geneService;
        $this->matingTypeService = $matingTypeService;
    }
    
    public function setFilter($filter) {
        $this->filter = $filter;
    }
    
    public function import($namespace, $filePath) {
        
    }
    
    private function insertExperiment($experimentData) {
        $insertStmt = $this->insertExperimentStmt;
        if ($insertStmt == null) {
            $columns = array(
                'namespace',
                'contact_email',
                'name',
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO experiment ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
        }
        // execute insert statement
        $params = array(
            $experimentData['namespace'],
            $experimentData['contactEmail'],
            $experimentData['name'],
        );
        $insertStmt->execute($params);
    }
}
