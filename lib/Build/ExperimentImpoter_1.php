<?php

class Build_ExperimentImporter
{
    const NAME_COLUMN = 0;
    const CONTACT_EMAIL_COLUMN = 1;
    const BACKGROUND_COLUMN = 2;
    const MATING_TYPE_COLUMN = 3;
    const GENOTYPE_COLUMN = 4;
    const SHORT_GENOTYPE_COLUMN = 5;
    
    private $filter;
    private $genotypeService;
    private $matingTypeService;
    
    /**
     * @param Build_Filter $filter
     * @param Service_GenotypeService $genotypeService
     * @param Service_MatingTypeService $matingTypeService 
     */
    public function __construct($filter, $genotypeService, $matingTypeService) {
        $this->filter = $filter;
        $this->genotypeService = $genotypeService;
        $this->matingTypeService = $matingTypeService;
    }
    
    /**
     * Applies filter to input experiment file and copies matching rows to output file.
     * @param string $inputPath Path to input strains csv file
     * @param string $outputPath Path to output strains csv file
     */
    public function filter($inputPath, $outputPath) {
        
        echo "processing $inputPath\n"; 
        // load file rows as array
        $inputDataRows = array();
        $inputFile = fopen($inputPath, 'r');
        while(($rowData = fgetcsv($inputFile)) !== false) {
            $inputDataRows[] = $rowData;
        }
        fclose($inputFile);
        
        if (count($inputDataRows) < 2) {
            $message = 'Experiment file does not have any data';
            throw new Exception($message);
        }
        
        // get normalized headers
        $headers = $inputDataRows[0];
        $headers = array_map('strtolower', $headers);
        $headers = array_map('trim', $headers);
        if (! isset($headers['label']) && isset($headers['name'])) {
            $headers['label'] = $headers['name'];
        }

        // check to make sure columns 'name' and 'lifespans' exist (required)
        $hasIdColumn = in_array('id', $headers);
        $hasLabelColumn =  in_array('label', $headers);
        $hasLifespanColumn = in_array('lifespans', $headers);
        $hasRequiredColumns = ($hasIdColumn && $hasLabelColumn && $hasLifespanColumn);
        if ( ! $hasRequiredColumns ) {
            $message = 'Experiment file requires "id", "name" and "lifespans" columns';
            throw new Exception($message);
        }

        // loop over input rows and check against filter
        $outputDataRows = array();
        $headerColumns = array_flip($headers);
        for ($row = 1; $row < count($inputDataRows); $row++) {
            $inputDataRow = $inputDataRows[$i];
            
            $outputRowData = array(
                $inputDataRow[$headerColumns['id']],
                $inputDataRow[$headerColumns['reference']],
                $inputDataRow[$headerColumns['label']],
                $inputDataRow[$headerColumns['strain']],
                $inputDataRow[$headerColumn['media']],
                $inputDataRow[$headerColumn['temperature']],
                $inputDataRow[$headerColumn['lifespans']],
            );
            
            if ($this->filter == null) {
                fputcsv($outputFile, $outputDataRow);    
            }
            else {
                $shortGenotype = $rowData[self::SHORT_GENOTYPE_COLUMN];
                $poolingGenotype = $this->genotypeService->getNormalizedGenotype($shortGenotype);
                $background = $rowData[self::BACKGROUND_COLUMN];
                $matingType = $this->matingTypeService->getNormalizedMatingType($rowData[self::MATING_TYPE_COLUMN]);
                $key = $this->filter->getStrainKey($poolingGenotype, $background, $matingType);
                if ($this->filter->hasStrainKey($key)) {
                    fputcsv($outputFile, $rowData);    
                }
            }
            $row++;
        }
        
        // write output file data if there is any
        if (count($outputDataRows) > 0) {
            $outputFile = fopen($outputPath, 'w');
            fputcsv($outputFile, $headers);
            for ($i = 1; $i < count($outputDataRows); $i++) {
                fputcsv($outputFile, $outputDataRows[$i]);  
            }
            fclose($outputFile);
        }
    }
    
}
