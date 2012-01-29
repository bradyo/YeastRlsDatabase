<?php

class Build_ExperimentImporter
{
    const OFFICIAL_NAMESPACE = 'official';
    const OFFICIAL_CONTACT_EMAIL = 'admin@sageweb.org';
    
    private $db;
    private $submissionService;
    private $genotypeService;
    private $matingTypeService;
    private $strainService;
    private $filter;
    
    private $insertExperimentStmt;
    private $insertCellStmt;
    
    /**
     * @param PDO $db
     * @param Service_SubmissionService $submissionService
     * @param Service_GeneService $geneService
     * @param Service_MatingTypeService $matingTypeService 
     */
    public function __construct($db, $geneService, $matingTypeService) {
        $this->db = $db;
        $this->submissionService = new Service_SubmissionService($db);
        $this->genotypeService = $geneService;
        $this->matingTypeService = $matingTypeService;
        $this->strainService = new Service_StrainService($db);
    }
    
    public function setFilter($filter) {
        $this->filter = $filter;
    }
    
    public function import($namespace, $filePath) {
        $filename = basename($filePath);
        
        // set up contact email
        $contactEmail = null;
        if ($namespace == self::OFFICIAL_NAMESPACE) {
            $contactEmail = self::OFFICIAL_CONTACT_EMAIL;
        } else {
            // look up contact email in submission data
            $submissionData = $this->submissionService->getSubmissionData($filename);
            if ($submissionData) {
                $contactEmail = $submissionData['contactEmail'];
            }
        }        
        
        // save experiment
        $experimentData = array(
            'namespace' => $namespace,
            'name' => basename($filePath),
            'contactEmail' => $contactEmail,
        );
        $experimentId = $this->insertExperiment($experimentData);
        
        // 
        $citationIds = array();
        
        
        // parse experiment file
        $parser = new Build_ExperimentFileParser($filePath);
        $rowsData = $parser->getCombinedRows();
        foreach ($rowsData as $rowData) {
            
            $cellData = $this->getCellData($namespace, $rowData);
            print_r($rowData);
            die();
        }
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
        return $this->db->lastInsertId();
    }
    
    private function getCellData($namespace, $rowData) {
        $shortGenotype = $rowData[self::SHORT_GENOTYPE_COLUMN];
        $poolingGenotype = $this->geneService->getNormalizedGenotype($shortGenotype);
        
        $strainData = $this->

        $matingType = $rowData[self::MATING_TYPE_COLUMN];
        $matingType = $this->matingTypeService->getNormalizedMatingType($matingType);
        
        $strainData = array(
            'namespace' => $namespace,
            'name' => $rowData[self::NAME_COLUMN],
            'contactEmail' => $rowData[self::CONTACT_EMAIL_COLUMN],
            'background' => $rowData[self::BACKGROUND_COLUMN],
            'matingType' => $matingType,
            'genotype' => $rowData[self::GENOTYPE_COLUMN],
            'shortGenotype' => $rowData[self::SHORT_GENOTYPE_COLUMN],
            'poolingGenotype' => $poolingGenotype,
            'freezerCode' => $rowData[self::FREEZER_CODE_COLUMN],
            'comment' => $rowData[self::COMMENT_COLUMN],
        );
        return $strainData;
    }
    
    private function importCellData($cellData) {
        // get (or create) prepared statemenet for insert
        $insertStmt = $this->cellInsertStmt;
        if ($insertStmt == null) {
            $columns = array(
                'namespace',
                
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO cell ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
        }
        // execute insert statement
        $params = array(
            $cellData['namespace'],
        );
        $insertStmt->execute($params);
    }
}
