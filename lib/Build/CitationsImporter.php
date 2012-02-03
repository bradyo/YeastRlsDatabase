<?php

class Build_CitationsImporter 
{
    const PUBMED_ID_COLUMN = 0;
    const TITLE_COLUMN = 1;
    const FIRST_AUTHOR_COLUMN = 2;
    const YEAR_COLUMN = 3;
    const SUMMARY_COLUMN = 4;

    /**
     * @var PDO 
     */
    private $db;
    
    /**
     * @param PDO $db Database to import into
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * @param string $inputPath Path to input citations csv file
     */
    public function import($inputPath) {
        // loop over rows in input file and import if not filtered out
        $inputFile = fopen($inputPath, 'r');
        $headers = fgetcsv($inputFile);
        $citationService = new Service_CitationService($this->db);
        while (($rowData = fgetcsv($inputFile)) !== false) {
            $citationData = array(
                'pubmed_id' => $rowData[self::PUBMED_ID_COLUMN],
                'title' => $rowData[self::TITLE_COLUMN],
                'first_author' => $rowData[self::FIRST_AUTHOR_COLUMN],
                'year' => $rowData[self::YEAR_COLUMN],
                'summary' => $rowData[self::SUMMARY_COLUMN],
            );
            $citationService->insertCitationData($citationData);
        }
        fclose($inputFile);
    }
}
