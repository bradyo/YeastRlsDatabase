<?php

/**
 * Handles the updating and addition of yeast strains.
 *
 * @author brady
 */
class YeastStrainUpdater
{
  private $_db = null;
  private $_username = null;
  private $_hasOverride = null;

  private $_errors = array();

  private $_failedStrains = array();
  private $_addStrains = array();
  private $_heldStrains = array();

  private $_addedStrains = array();
  private $_updatedStrains = array();
  private $_skippedStrains = array();

  public function __construct($db, $username, $canOverride)
  {
    $this->_db = $db;
    $this->_username = $username;
    $this->_hasOverride = $canOverride;
  }

  public function getErrors() {
    return $this->_errors;
  }

  public function getFailedStrains() {
    return $this->_failedStrains;
  }

  public function getAddStrains() {
    return $this->_addStrains;
  }

  public function getHeldStrains() {
    return $this->_heldStrains;
  }

  public function getAddedStrains() {
    return $this->_addedStrains;
  }

  public function getUpdatedStrains() {
    return $this->_updatedStrains;
  }

  public function getSkippedStrains() {
    return $this->_skippedStrains;
  }


  public function processInput($inputText)
  {
    $inputText = trim($inputText);
    $lines = preg_split("/(\r\n|\n)/", $inputText);

    $headerString = strtolower($lines[0]);
    $headerIndexes = $this->getHeaderIndexes($headerString);

    // check the header to make sure it has required fields
    $isValid = $this->isHeaderValid($headerIndexes);
    if (!$isValid) {
      // fatal error, header is incomplete
      $this->_errors[] = 'header requires the following fields: '
         . join(', ', $this->getRequiredHeaders());
      return;
    }

    // go over lines and check each one
    $query = '
      SELECT name, owner, background, mating_type, genotype, genotype_short,
        freezer_code, comment, is_locked
      FROM yeast_strain WHERE name = ?
      ';
    $stmt = $this->_db->prepare($query);

    for ($i = 1; $i < count($lines); $i++) {
      $values = explode("\t", $lines[$i]);
      $values = array_map('trim', $values);
      $newStrain = $this->getStrain($values, $headerIndexes);

      // check if strain exists
      $name = $newStrain['name'];
      $stmt->execute(array($name));
      
      $existingStrain = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($existingStrain) {
        // check if strain is owned by another user
        if ($existingStrain['owner'] == $this->_username) {
          // user owns this strain, but it may be locked
          if ($existingStrain['is_locked'] == true && !$this->_hasOverride) {
            $this->_failedStrains[$name] = 'strain has been locked by an administrator';
          } else {
            $error = $this->checkStrain($newStrain);
            if ($error == null) {
              $this->_heldStrains[$name] = $newStrain;
            } else {
              $this->_failedStrains[$name] = 'strain is invalid: ' . $error;
            }
          }
        } else {
          // the strain cannot be edited unless user has override
          if ($this->_hasOverride) {
            $this->_heldStrains[$name] = $newStrain;
          } else {
            $this->_failedStrains[$name] = 'strain name is already taken by user "'
              . $existingStrain['owner'] . '"';
          }
        }
      } else {
        // strain does not exist, so we'll add it if valid
        $error = $this->checkStrain($newStrain);
        if ($error == null) {
          $this->_addStrains[$name] = $newStrain;
        } else {
          $this->_failedStrains[$name] = 'strain is invalid: ' . $error;
        }
      }
    }
  }

  private function getStrain($values, $headerIndexes)
  {
    $strain = array();
    foreach ($this->getHeaders() as $header) {
      if (isset($headerIndexes[$header])) {
        $i = $headerIndexes[$header];
        $strain[$header] = $values[$i];
      }
    }
    $strain['data'] = json_encode($strain);
    return $strain;
  }


  private function getRequiredHeaders()
  {
    $requiredHeaders = array(
      'name',
      'background',
      'mating type',
      'full genotype',
      'short genotype',
      'freezer code',
      'comment'
      );
    return $requiredHeaders;
  }

  private function getHeaders()
  {
    $targetHeaders = array(
      'name',
      'background',
      'mating type',
      'full genotype',
      'short genotype',
      'freezer code',
      'comment',
      'owner',
      'is locked',
      );
    return $targetHeaders;
  }

  private function getHeaderIndexes($headerString)
  {
    $targetHeaders = $this->getHeaders();
    $headerIndexes = array();
    $headers = explode("\t", $headerString);
    for ($i = 0; $i < count($headers); $i++) {
      $header = $headers[$i];
      if (in_array($header, $targetHeaders) && !isset($headerIndexes[$header])) {
        $headerIndexes[$header] = $i;
      }
    }
    return $headerIndexes;
  }

  private function isHeaderValid($headerIndexes)
  {
    $isValid = true;
    $requiredHeaders = $this->getRequiredHeaders();
    $headers = array_keys($headerIndexes);
    foreach ($requiredHeaders as $requiredHeader) {
      if (!in_array($requiredHeader, $headers)) {
        $isValid = false;
      }
    }
    return $isValid;
  }

  private function checkStrain($strain)
  {
    // check mating type
    $matingType = $this->getMatingType($strain['mating type']);
    if ($matingType === null) {
      $error = 'mating type invalid, use "a", "MATa", "α", "alpha", "MATalpha", '
        . '"MATα", "diploid", "α/a", "a/α", "MATdiploid", or "sterile"';
      return $error;
    }

    // check short genotype
    $terms = preg_split('/\s+/', $strain['short genotype']);
    foreach ($terms as $term) {
      // haploid strains should not have any "/" in thier pooling genotype
      if ($matingType != 'diploid' && strrpos($term, '/')) {
        $error = 'short genotype "'.$poolingGenotype.'" should not contain a "/"';
        return $error;
      }

      // string should not contain delta for deletions (should be all lowercase)
      if (strrpos($term, 'Δ')) {
        $error = 'short genotype "'.$poolingGenotype.'" should not contain "Δ" character';
        return $error;
      }
    }
    
     // check if owner exists
    if (isset($strain['owner'])) {
      $owner = $strain['owner'];
      $q = Doctrine_Query::create()
        ->from('User u')
        ->where('u.username = ?', $owner)
        ->limit(1);
      $result = $q->fetchOne();
      if ($result == null) {
        $error = 'username "'.$owner.'" does not exist';
        return $error;
      }
    }
  }
  
  private function getMatingType($input)
  {
    $validAs        = array('a', 'MATa');
    $validAlphas    = array('α', 'alpha', 'MATalpha', 'MATα');
    $validDiploids  = array('diploid', 'α/a', 'a/α', 'MATdiploid');
    $validOthers    = array('sterile', '');

    $input = strtolower(trim($input));
    if (in_array($input, array_map('strtolower', $validAlphas))) {
      return 'MATalpha';
    }
    elseif (in_array($input, array_map('strtolower', $validAs))) {
      return 'MATa';
    }
    elseif (in_array($input, array_map('strtolower', $validDiploids))) {
      return 'diploid';
    }
    elseif (in_array($input, array_map('strtolower', $validOthers))) {
      return $input;
    }
    return null;
  }


  public function update($params)
  {
    $actions = $params['action'];
    $datas = $params['data'];

    foreach ($actions as $strainName => $action) {
      $data = json_decode($datas[$strainName], true);

      if ($action == 'add') {
        try {
          $strain = new YeastStrain();
          $strain = $this->initStrain($strain, $data);
          $strain->owner = $this->_username;
          $strain->save();
          $this->_addedStrains[] = $strainName;
        }
        catch (Doctrine_Exception $e) {
          $this->_errors[] = 'failed to save strain data to database';
        }
      }
      elseif ($action == 'update') {
        $q = Doctrine_Query::create()
          ->from('YeastStrain s')
          ->where('s.name = ?', $strainName);
        $strain = $q->fetchOne();
        
        if ($strain['owner'] !== $this->_username && $this->_hasOverride == false) {
          $this->_errors[] = 'strain "'.$strainName.'" belongs to another user';
        }
        elseif ($strain['is_locked'] && $this->_hasOverride == false) {
          $this->_errors[] = 'strain "'.$strainName.'" has been locked';
        }
        else {
          $strain = $this->initStrain($strain, $data);
          $strain->save();
          $this->_updatedStrains[] = $strainName;
        }
      }
      else {
        $this->_skippedStrains[] = $strainName;
      }
    }
  }

  private function initStrain($strain, $data)
  {
    $strain['name'] = trim($data['name']);
    $strain['background'] = trim($data['background']);
    $strain['mating_type'] = trim($data['mating type']);
    $strain['genotype'] = trim($data['full genotype']);
    $strain['genotype_short'] = trim($data['short genotype']);

    $geneDb = Doctrine_Manager::getInstance()->getConnection('ncbi_gene')->getDbh();
    $strain['genotype_unique'] = GenotypeLookup::getCleanGenotype($geneDb,
       $strain['genotype_short']);
    $strain['freezer_code'] = trim($data['freezer code']);
    $strain['comment'] = trim($data['comment']);

    if ($this->_hasOverride) {
      if (isset($data['is locked'])) {
        if (in_array($data['is locked'], array('false', '0'))) {
          $strain['is_locked'] = '0';
        } else {
          $strain['is_locked'] = '1';
        }
      }
      if (isset($data['owner'])) {
          $strain['owner'] = trim($data['owner']);
      }
    }


    return $strain;
  }
}
