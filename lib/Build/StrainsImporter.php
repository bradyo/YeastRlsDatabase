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
    private $strainService;
    
    /**
     * @param PDO $db Database to import strains into
     */
    public function __construct($db) {
        $this->db = $db;
        $this->geneService = new Service_GeneService($db);
        $this->matingTypeService = new Service_MatingTypeService();
        $this->strainService = new Service_StrainService();
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
        while (($rowData = fgetcsv($inputFile)) !== false) {
            $shortGenotype = $rowData[self::SHORT_GENOTYPE_COLUMN];
            $poolingGenotype = $this->geneService->getNormalizedGenotype($shortGenotype);

            $matingType = $rowData[self::MATING_TYPE_COLUMN];
            $matingType = $this->matingTypeService->getNormalizedMatingType($matingType);

            $strainData = array(
                'namespace' => $namespace,
                'name' => $rowData[self::NAME_COLUMN],
                'contact_email' => $rowData[self::CONTACT_EMAIL_COLUMN],
                'background' => $rowData[self::BACKGROUND_COLUMN],
                'mating_type' => $matingType,
                'genotype' => $rowData[self::GENOTYPE_COLUMN],
                'short_genotype' => $rowData[self::SHORT_GENOTYPE_COLUMN],
                'pooling_genotype' => $poolingGenotype,
                'freezer_code' => $rowData[self::FREEZER_CODE_COLUMN],
                'comment' => $rowData[self::COMMENT_COLUMN],
            );
            if ($this->filter == null || $this->filter->isStrainAllowed($strainData)) {
                $this->importStrainData($strainData);
            }
        }
        fclose($inputFile);
    }
}
