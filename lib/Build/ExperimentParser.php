<?php

class Build_ExperimentFileParser
{
  /**
   * @var array associative array holding combined row data.
   */
  private $_combinedRows = array();

  /**
   * Loads the experiment file and assembes combined rows
   * 
   * @param string $filename filename of experiment file to read
   * @throws Exception
   */
  public function __construct($filename)
  {
    // get rows in file, shold be at least 2 (one header, one data)
    $fileRows = array();
    ini_set('auto_detect_line_endings', true);
    $handle = fopen($filename, 'r');
    while(($vals = fgetcsv($handle, 4096, ",", '"')) !== false) {
      $fileRows[] = $vals;
    }
    fclose($handle);

    if (count($fileRows) < 2) {
      $message = 'file has < 2 rows, requires at least one header and one data';
      throw new Exception($message);
    }

    // get header
    $headers = array_shift($fileRows);
    $headers = array_map('strtolower', $headers);
    $headers = array_map('trim', $headers);
    $headers = $headers;

    // build header indexes array
    $headerIndexes = array_flip($headers);
    $headerIndexes = $headerIndexes;

    // check to make sure columns 'name' and 'lifespans' exist (required)
    $hasIdHeader = in_array('id', $headers);
    $hasNameHeader = in_array('name', $headers) || in_array('label', $headers);
    $hasLifespansHeader = in_array('lifespans', $headers);
    if (!$hasIdHeader || !$hasNameHeader  || !$hasLifespansHeader) {
      $message = 'header requires "id", "name" and "lifespans" fields';
      throw new Exception($message);
    }

    $combinedRows = array();
    foreach ($fileRows as $row) {
      $id = trim($row[$headerIndexes['id']]);
			if (empty($id)) {
				continue;
			}
      if (isset($headerIndexes['name'])) {
        $name = $row[$headerIndexes['name']];
      } else {
        $name = $row[$headerIndexes['label']];
      }
      $lifespans = array_slice($row, $headerIndexes['lifespans']);
      $lifespanCount = count($lifespans);
      $lifespans = $this->_getCleanLifespans($lifespans);

      $reference = null;
      $references = array();
      if (in_array('reference', $headers)) {
        $reference = $row[$headerIndexes['reference']];
        $values = explode(',', $reference);
        $values = array_map('trim', $values);
        foreach ($values as $value) {
          if (!empty($value)) {
            $references[] = (string)$value;
          }
        }
      }

      $strain = null;
      if (in_array('strain', $headers)) {
        $strain = (string)$row[$headerIndexes['strain']];
				$strain = trim($strain);       

        // if strain looks like plate row well, convert to deletionc collection id
        $matches = array();
        if (preg_match('/^(\d+)\s*([a-h])\s*(\d+)$/i', $strain, $matches)) {
          $plate = $matches[1];
          $col = $matches[2];
          $well = $matches[3];
          $strain = 'DC:'.$plate.$col.$well;
        }
      }

      $media = null;
      if (in_array('media', $headers)) {
        $media = (string)$row[$headerIndexes['media']];
				$media = trim($media);
      }

			// clean up temperature a bit: remove trailing "C" and extra zeros
      $temperature = '30';
      if (in_array('temperature', $headers)) {
				$value = $row[$headerIndexes['temperature']];
				$value = strtolower($value);
				$value = trim($value);
				$value = rtrim($value, 'c');

				// if decimal, remove trailing zeros
				if (strpos($value, '.')) {
					$value = rtrim($value, '0');
				}
				$temperature = $value;
      }

      // combine row data if keys match (name, ref, media, temp, carbon source)
      $combineKey = join('/', array(
        $name,
        $strain,
        $media,
        $temperature
        ));

      if (isset($combinedRows[$combineKey])) {
        // append lifespans to existing data
        $existingLifespans = $combinedRows[$combineKey]['lifespans'];
        $combinedLifespans = array_merge($existingLifespans, $lifespans);
        $combinedRows[$combineKey]['lifespans'] = $combinedLifespans;

        // sum lifespan counts
        $existingLifespanCount = $combinedRows[$combineKey]['lifespan_start_count'];
        $combinedLifespanCount = $existingLifespanCount + $lifespanCount;
        $combinedRows[$combineKey]['lifespan_start_count'] = $combinedLifespanCount;

        // append references to existing data
        $existingReferences = $combinedRows[$combineKey]['references'];
        $existingReference = $combinedRows[$combineKey]['reference'];
        $combinedReferences = array_merge($existingReferences, $references);
        $combinedRows[$combineKey]['references'] = $combinedReferences;
				$combinedRows[$combineKey]['reference'] = $existingReference.','.$reference;
        
        $existingIds = $combinedRows[$combineKey]['row_ids'];
        $combinedIds = array_merge($existingIds, array($id));
        $combinedRows[$combineKey]['row_ids'] = $combinedIds;
      }
      else {
        // create new data array
        $combinedRows[$combineKey] = array(
          'row_ids' => array($id),
          'name' => $name,
          'reference' => $reference,
          'references' => $references,
          'strain' => $strain,
          'media' => $media,
          'temperature' => $temperature,
          'lifespans' => $lifespans,
          'lifespan_start_count' => $lifespanCount
          );
      }
    }

    // make sure no combined referenced rows share the same name, this would indicate
    // two rows share the same name (and should therefore be combined) but do
    // not share the same combined key
    $referenceNames = array();
    foreach ($combinedRows as $combinedRow) {
      foreach ($combinedRow['references'] as $referenceName) {
        // skip numeric ones, that represent id (we id is unique)
        if(!ctype_digit($referenceName)) {
          $referenceNames[$referenceName] = $referenceName;
        }
      }
    }

    $foundCombinedRows = array(); // indexed by reference name
    foreach ($referenceNames as $referenceName) {
      foreach ($combinedRows as $combinedRow) {
        $rowName = $combinedRow['name'];
        if ($rowName == $referenceName) {
          // add the row to found rows
          if (!isset($foundCombinedRows[$referenceName])) {
            $foundCombinedRows[$referenceName] = 0;
          }
          $foundCombinedRows[$referenceName]++;
        }
      }
    }
    
    $invalidReferences = array();
    foreach ($foundCombinedRows as $referenceName => $count) {
      if ($count > 1) {
        $invalidReferences[] = $referenceName;
      }
    }
    if (count($invalidReferences) > 0) {
      $message = "exception: the following reference names resolve to more "
        ."than one file combined set: ". join(', ', $invalidReferences)
        .". File skipped\n";
      throw new Exception($message);
    }

		// remove any references that are in row_ids (references to self)
		foreach ($combinedRows as &$combinedRow) {
			$cleanReferences = array();
			foreach ($combinedRow['references'] as $referenceId) {
				if (!in_array($referenceId, $combinedRow['row_ids'])) {
			  	$cleanReferences[] = $referenceId;
				}
			}
			$combinedRow['references'] = $cleanReferences;
		}

    $this->_combinedRows = $combinedRows;
  }

  /**
   * Gets the combined rows
   * 
   * @return array an of ExperimentFileRow
   */
  public function getCombinedRows()
  {
    return $this->_combinedRows;
  }


  /**
   * Filters lifespans array so that it only contains positive integers
   *
   * @param array $values array of lifespans to filter
   * @return array of clean lifespans, containing only positive integers
   */
  private function _getCleanLifespans($values)
  {
    $cleanValues = array();
    foreach ($values as $value) {
      // remove non-numbers and zeros
      if (preg_match('/^\s*(\d+)\s*$/', $value, $matches)) {
				$value = $matches[1];
				if (intval($value) > 0) {
	        $cleanValues[] = $value;
				}
      }
    }
    return $cleanValues;
  }
}
