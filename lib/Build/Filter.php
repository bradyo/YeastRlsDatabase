<?php

class Build_Filter
{
    const SHORT_GENOTYPE_COLUMN = 0;
    const MEDIA_COLUMN = 1;
    const BACKGROUND_COLUMN = 2;
    const MATING_TYPE_COLUMN = 3;
    const PUBMED_ID_COLUMN = 4;
    const CITATION_SUMMARY_COLUMN = 5;
    const COMMENT_COLUMN = 6;
    
    const DEFAULT_MEDIA = "YPD";
 
    private $genotypeService;
    private $matingTypeService;
    
    private $filters;
    private $allowedStrainKeys;
    private $allowedSampleKeys;
    private $pubmedIdsBySampleKey;
    
    /**
     * @param PDO $db 
     * @param string $filterPath Path to filters csv file
     */
    public function __construct($db, $filterPath) {
        $this->genotypeService = new Service_GeneService($db);
        $this->matingTypeService = new Service_MatingTypeService();
        
        $this->filters = array();
        $this->allowedStrainKeys = array();
        $this->allowedSampleKey = array();
        $this->pubmedIdsBySampleKey = array();
        $filterFile = fopen($filterPath, 'r');
        fgetcsv($filterFile); // discard header
        while (false !== ($rowData = fgetcsv($filterFile))) {
            // normalize data
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
            $pubmedId = trime($rowData[self::PUBMED_ID_COLUMN]);
            
            // save data to filters
            $data = array(
                'short_genotype' => $shortGenotype,
                'pooling_genotype' => $poolingGenotype,
                'media' => $media,
                'background' => $background,
                'mating_type' => $normalMatingType,
                'pubmed_id' => $pubmedId,
                'citation_summary' => $reference,
                'comment' => $comment,
            );
            $this->filters[] = $data;
            
            $strainKey = $this->getStrainKey($poolingGenotype, $background, $matingType);
            if (! in_array($strainKey, $this->allowedStrainKeys)) {
                $this->allowedStrainKeys[$strainKey] = $strainKey;
            }
           
            $sampleKey = $this->getSampleKey($poolingGenotype, $media, $background, $matingType);
            if (! in_array($sampleKey, $this->allowedSampleKeys)) {
                $this->allowedSampleKeys[$sampleKey] = $sampleKey;
            }
            
            if (isset($this->pubmedIdsBySampleKey[$sampleKey])) {
                $this->pubmedIdsBySampleKey[$sampleKey][] = $pubmedId;
            } else {
                $this->pubmedIdsBySampleKey[$sampleKey] = array($pubmedId);
            }
        }
        fclose($filterFile);
    }
    
    public function isEmpty() {
        return count($this->filters) == 0;
    }
    
    public function getStrainKey($poolingGenotype, $background, $matingType) {
        return join('/', array(
            $poolingGenotype,
            $background,
            $matingType,
        )); 
    }
    
    public function isStrainAllowed($strainKey) {
        return (isset($this->allowedStrainKeys[$strainKey]));
    }
    
    public function getSampleKey($genotype, $media, $matingType, $background) {
        return join('/', array(
            $genotype,
            $media,
            $matingType,
            $background,
        )); 
    }
    
    public function isSampleAllowed($sampleKey) {
        return isset($this->allowedSampleKey[$sampleKey]);
    }
    
    public function getPubmedIdsBySampleKey($sampleKey) {
        return $this->pubmedIdsBySampleKey[$sampleKey];
    }
}
