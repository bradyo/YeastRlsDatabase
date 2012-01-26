<?php

class Build_StrainsImporter 
{
    const NAME_COLUMN = 0;
    const CONTACT_EMAIL_COLUMN = 1;
    const BACKGROUND_COLUMN = 2;
    const MATING_TYPE_COLUMN = 3;
    const GENOTYPE_COLUMN = 4;
    const SHORT_GENOTYPE_COLUMN = 5;
    const FREEZER_CODE_COLUMN = 6;
    const COMMENT_COLUMN = 7;
    
    private $db;
    private $filter;
    private $geneService;
    private $matingTypeService;
    private $strainInsertStmt;
    
    /**
     * @param PDO $db Database to import strains into
     * @param Service_GeneService $geneService
     * @param Service_MatingTypeService $matingTypeService 
     */
    public function __construct($db, $geneService, $matingTypeService) {
        $this->db = $db;
        $this->geneService = $geneService;
        $this->matingTypeService = $matingTypeService;
    }
    
    /**
     * @param Build_Filter $filter
     */
    public function setFilter($filter) {
        $this->filter = $filter;
    }
    
    /**
     * Applies filter to input file and inports into database
     * @param string $inputPath Path to input strains csv file
     * @param string $namesapce Namespace for strains
     */
    public function import($inputPath, $namespace) {
        // loop over rows in input file and import if not filtered out
        $inputFile = fopen($inputPath, 'r');
        $headers = fgetcsv($inputFile);
        $this->db->beginTransaction();
        while (($rowData = fgetcsv($inputFile)) !== false) {
            $strainData = $this->getStrainData($namespace, $rowData);
            if ($this->filter == null || $this->filter->isStrainAllowed($strainData)) {
                print_r($strainData);
                $this->importStrainData($strainData);
            }
        }
        $this->db->commit();
        fclose($inputFile);
    }
    
    private function getStrainData($namespace, $rowData) {
        $shortGenotype = $rowData[self::SHORT_GENOTYPE_COLUMN];
        $poolingGenotype = $this->geneService->getNormalizedGenotype($shortGenotype);

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
    
    private function importStrainData($strainData) {
        // get (or create) prepared statemenet for insert
        $insertStmt = $this->strainInsertStmt;
        if ($insertStmt == null) {
            $columns = array(
                'namespace',
                'name',
                'contact_email',
                'background',
                'mating_type',
                'genotype',
                'genotype_short',
                'genotype_unique',
                'freezer_code',
                'comment'
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO strain ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
        }
        // execute insert statement
        $params = array(
            $strainData['namespace'],
            $strainData['name'],
            $strainData['contactEmail'],
            $strainData['background'],
            $strainData['matingType'],
            $strainData['genotype'],
            $strainData['shortGenotype'],
            $strainData['poolingGenotype'],
            $strainData['freezerCode'], 
            $strainData['comment'],
        );
        $insertStmt->execute($params);
    }
}
