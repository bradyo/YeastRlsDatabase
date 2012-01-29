<?php

class Build_Filter
{
    const SHORT_GENOTYPE_COLUMN = 0;
    const MEDIA_COLUMN = 1;
    const BACKGROUND_COLUMN = 2;
    const MATING_TYPE_COLUMN = 3;
    const REFERENCE_COLUMN = 4;
    const COMMENT_COLUMN = 5;
    
    const DEFAULT_MEDIA = "YPD";
 
    private $genotypeService;
    private $matingTypeService;
    private $filters;
    
    /**
     * @param string $filterPath Path to filters csv file
     * @param Service_GenotypeService $genotypeService
     * @param Service_MatingTypeService $matingTypeService 
     */
    public function __construct($filterPath, $genotypeService, $matingTypeService) {
        $this->genotypeService = $genotypeService;
        $this->matingTypeService = $matingTypeService;
        
        $filters = array();
        $filterFile = fopen($filterPath, 'r');
        fgetcsv($filterFile); // discard header
        while (false !== ($rowData = fgetcsv($filterFile))) {
            $shortGenotype = trim($rowData[self::SHORT_GENOTYPE_COLUMN]);
            $poolingGenotype = $this->genotypeService->getNormalizedGenotype($shortGenotype);
            $media = trim($rowData[self::MEDIA_COLUMN]);
            if (empty($media)) {
                $media = self::DEFAULT_MEDIA;
            }
            $background = trim($rowData[self::BACKGROUND_COLUMN]);
            $matingType = trim($rowData[self::MATING_TYPE_COLUMN]);
            $normalMatingType = $this->matingTypeService->getNormalizedMatingType($matingType);
            $reference = trim($rowData[self::REFERENCE_COLUMN]);
            $comment = trim($rowData[self::COMMENT_COLUMN]);
            
            $key = $this->getStrainKey($poolingGenotype, $background, $normalMatingType);
            $data = array(
                'shortGenotype' => $shortGenotype,
                'poolingGenotype' => $poolingGenotype,
                'media' => $media,
                'background' => $background,
                'matingType' => $normalMatingType,
                'reference' => $reference,
                'comment' => $comment,
            );
            $filters[$key] = $data;
        }
        fclose($filterFile);
        $this->filters = $filters;
    }
    
    public function isEmpty() {
        return count($this->filters) == 0;
    }
    
    public function hasStrainKey($key) {
        return isset($this->filters[$key]);
    }
    
    public function getStrainKey($poolingGenotype, $background, $matingType) {
        return join('/', array(
            $poolingGenotype,
            $background,
            $matingType,
        )); 
    }
    
    public function isStrainAllowed($strainData) {
        $poolingGenotype = $strainData['poolingGenotype'];
        $background = $strainData['background'];
        $matingType = $strainData['matingType'];
        $key = $this->getStrainKey($poolingGenotype, $background, $matingType);
        if (isset($this->filters[$key])) {
            return true;
        } else {
            return false;
        }
    } 
    
    public function getCitations() {
        
    }
}
