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
    private $cache;
    
    /**
     * @param PDO $db
     */
    public function __construct($db) {
        $this->db = $db;
        $this->submissionService = new Service_SubmissionService($db);
        $this->genotypeService = new Service_GeneService($db);
        $this->matingTypeService = new Service_MatingTypeService();
        $this->strainService = new Service_StrainService($db);
    }
    
    /**
     * @param Build_filter $filter 
     */
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
        
        // get rows in file, shold be at least 2 (one header, one data)
        $fileRows = array();
        ini_set('auto_detect_line_endings', true);
        $file = fopen($filePath, 'r');
        while (($vals = fgetcsv($file, 0, ",", '"')) !== false) {
            $fileRows[] = $vals;
        }
        fclose($file);

        // check that the file has sufficient rows
        if (count($fileRows) < 2) {
            $message = 'file has < 2 rows, requires at least one header and one data';
            throw new Exception($message);
        }

        // get headers
        $headers = array_shift($fileRows);
        $headers = array_map('trim', $headers);
        $headers = array_map('lcfirst', $headers);
        if (!isset($headers['label']) && in_array('name', $headers)) {
            $headers['label'] = $headers['name'];
        }

        // check to make sure required columns exist
        $hasIdColumn = in_array('id', $headers);
        $hasLabelColumn = in_array('label', $headers);
        $hasLifespanColumn = in_array('lifespans', $headers);
        if (!$hasIdColumn || !$hasLabelColumn || !$hasLifespanColumn) {
            $message = 'header requires "id", "name" and "lifespans" fields';
            throw new Exception($message);
        }
        
        // build header indexes array
        $headerIndexes = array_flip($headers);
        $headerIndexes = $headerIndexes;
        
        // process file rows into cell sets data
        $combinedSetsData = array();
        $strainNames = array();
        $setId = 1;
        $rowSetIds = array();
        foreach ($fileRows as $row) {
            $rowId = trim($row[$headerIndexes['id']]);
            if (empty($rowId)) {
                continue;
            }
            $rowSetIds[$rowId] = $setId;
            
            $referenceRowIds = array();
            if (in_array('reference', $headers)) {
                $reference = $row[$headerIndexes['reference']];
                $values = explode(',', $reference);
                $values = array_map('trim', $values);
                $values = array_map('intval', $values);
                foreach ($values as $value) {
                    if ($value != 0) {
                        $referenceRowIds[] = $value;
                    }
                }
            }
            
            $label = $row[$headerIndexes['label']];
            
            // read lifespans up to endCode column if it exists
            $lifespans = array();
            $endStates = array();
            if (isset($headerIndexes['endCode'])) {
                $lifespansColumn = $headerIndexes['lifespans'];
                $endCodeColumn = $headerIndexes['endCode'];
                $length = $endCodeColumn - $lifespansColumn;
                $lifespans = array_slice($row, $lifespansColumn, $length);
                $endStates = array_slice($row, $endCodeColumn, $length);
            } else {
                $lifespans = array_slice($row, $headerIndexes['lifespans']);
            }

            $strainName = null;
            if (in_array('strain', $headers)) {
                $value = (string) $row[$headerIndexes['strain']];
                $value = trim($value);

                // if strain looks like plate row well, convert to deletion collection id
                $matches = array();
                if (preg_match('/^(\d+)\s*([a-h])\s*(\d+)$/i', $value, $matches)) {
                    $plate = $matches[1];
                    $col = $matches[2];
                    $well = $matches[3];
                    $value = 'DC:' . $plate . $col . $well;
                }
                if (!empty($value)) {
                    $strainName = $value;
                    
                    // save strain names to look up ids for later
                    if (! in_array($strainName, $strainNames)) {
                        $strainNames[] = $strainName;
                    }
                }
            }

            $media = 'YPD';
            if (in_array('media', $headers)) {
                $value = (string) $row[$headerIndexes['media']];
                $value = trim($value);
                if (!empty($value)) {
                    $media = $value;
                }
            }

            // Clean up temperature a bit: remove trailing "C" and extra zeros.
            // Treat temperature value as a normalized string so that it pools
            // correctly.
            $temperature = '30';
            if (in_array('temperature', $headers)) {
                $value = $row[$headerIndexes['temperature']];
                $value = strtolower($value);
                $value = trim($value);
                $value = rtrim($value, 'c');
                if (strstr('.', $value) !== false) {
                    $value = rtrim($value, '0');
                    $value = rtrim($value, '.');
                }
                if (!empty($value)) {
                    $temperature = $value;
                }
            }

            // merge row data into set data based on combine key
            $combineKey = join('/', array(
                $namespace,
                $filename,
                $label,
                $strainName,
                $media,
                $temperature
            ));
            if (isset($combinedSetsData[$combineKey])) {
                // append references to existing data
                $existingReferences = $combinedSetsData[$combineKey]['referenceRowIds'];
                $combinedSetsData[$combineKey]['referenceRowIds'] 
                    = array_merge($existingReferences, $referenceRowIds);
                
                // append lifespans to existing data
                $existingLifespans = $combinedSetsData[$combineKey]['lifespans'];
                $combinedSetsData[$combineKey]['lifespans'] 
                    = array_merge($existingLifespans, $lifespans);
                
                // append end codes to existing data
                $existingEndCodes = $combinedSetsData[$combineKey]['endStates'];
                $combinedSetsData[$combineKey]['endStates'] 
                    = array_merge($existingEndCodes, $endStates);
            } else {
                // create new data set array
                $combinedSetsData[$combineKey] = array(
                    'pooledBy' => 'file',
                    'poolingKey' => $combineKey,
                    'setId' => $setId,
                    'referenceRowIds' => $referenceRowIds,
                    'label' => $label,
                    'strainName' => $strainName,
                    'media' => $media,
                    'temperature' => $temperature,
                    'lifespans' => $lifespans,
                    'endStates' => $endStates
                );
                $setId++;
            }
        }
        
        // Update combined sets with additional data
        foreach ($combinedSetsData as &$setData) {
            // Transform reference row ids to reference set ids
            $refRowIds = $setData['referenceRowIds'];
            $refSetIds = array();
            foreach ($refRowIds as $refRowId) {
                $refSetId = $rowSetIds[$refRowId];
                if (! in_array($refSetId, $refSetIds)) {
                    $refSetIds[] = $refSetId;
                }
            }
            $setData['referenceSetIds'] = $refSetIds;
        }
        
        // get experiment id
        $experimentData = array(
            'namespace' => $namespace,
            'name' => basename($filePath),
            'contactEmail' => $contactEmail,
        );
        $experimentId = $this->insertExperiment($experimentData);
        
        // get citations mapping
        
        
        // Insert combined sets into sample table. Save a mapping from
        // combined set id to sample id so we can insert comparisons.
        $sampleIdMap = array(); // key = file set id, value = sample id
        $sampleService = new Service_SampleService($this->db);
        $cellService = new Service_CellService($this->db);
        $strainsData = $this->strainService->getStrainsDataByNames($namespace, $strainNames);
        foreach ($combinedSetsData as $setData) {
            // get set strain data
            $strainName = $setData['strainName'];
            $strainData = $strainsData[$strainName];
            
            // transform combined set data into suitable sample data
            $sampleData = $setData;
            $sampleData['strain'] = $setData['strainName'];
            $sampleData['background'] = $strainData['background'];
            $sampleData['matingType'] = $strainData['mating_type'];
            $sampleData['genotype'] = $strainData['genotype_unique'];
            $sampleData['cellsData'] = json_encode(array(
                'lifespans' => $setData['lifespans'],
                'endStates' => $setData['endStates'],
            ));
            
            // check data against filter
            $sampleKey = $this->filter->getSampleKey(
                    $sampleData['genotype'], $sampleData['media'], 
                    $sampleData['matingType'], $sampleData['background']);
            if ( ! $this->filter->isSampleAllowed($sampleKey)) {
                // skip all cells in this sample set
                continue;
            }
            $sampleId = $sampleService->insertSampleData($namespace, $sampleData);
            
            // save set id to sample id mapping for later
            $setId = $setData['setId'];
            $sampleIdMap[$setId] = $sampleId;
            
            // get citations for this sample set
            $citationPubmedIds = $this->filter->getPubmedIds($sampleKey);
            
            // import all cells and link to sample
            $commonCellData = array(
                'experimentId' => $experimentId,
                'strainId' => ($strainData !== null) ? $strainData['id'] : null,
                'label' => $setData['label'],
                'media' => $setData['media'],
                'temperature' => $setData['temperature'],
            );
            $cellsCount = count($setData['lifespans']);
            for ($i = 0; $i < $cellsCount; $i++) {
                // save cell data to db
                $cellData = $commonCellData;
                $cellData['lifespan'] = $setData['lifespans'][$i];
                $cellData['endState'] = $setData['endStates'][$i];
                $cellId = $cellService->insertCellData($cellData);

                // link cell to sample
                $sampleService->insertSampleCell($sampleId, $cellId);
                
                // import links to citations
                foreach ($citationPubmedIds as $citationId) {
                    $cellService->insertCellCitation($cellId, $citationId);
                }
            }
        }
    }
    
    private function insertExperiment($experimentData) {
        $stmt = $this->cache['insertExperimentStmt'];
        if ($stmt === null) {
            $columns = array(
                'namespace',
                'contact_email',
                'name',
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO experiment ({$columnsString}) VALUES ({$valuesString})";
            $stmt = $this->db->prepare($sql);
            $this->cache['insertExperimentStmt'] = $stmt;
        }
        // execute insert statement
        $params = array(
            $experimentData['namespace'],
            $experimentData['contactEmail'],
            $experimentData['name'],
        );
        $stmt->execute($params);
        return $this->db->lastInsertId();
    }
}
