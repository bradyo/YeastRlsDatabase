<?php

/**
 * Handles the adding new experiments to disection queue
 *
 * @author brady
 */
class ExperimentManager
{
  private $_errors = array();

  public function getErrors()
  {
    return $this->_errors;
  }

  public function checkInput($db, $inputText)
  {
    $inputText = trim($inputText);
    $lines = preg_split("/(\r\n|\n|\r)/", $inputText);

    if (count($lines) < 2) {
      // fatal error, not enough lines
      $this->_errors[] = 'key data requires at least 2 lines, header and data';
      return;
    }

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
    $ids = array();
    for ($i = 1; $i < count($lines); $i++) {
      $values = explode("\t", $lines[$i]);
      $values = array_map('trim', $values);

      // check id, it should be an integer and unique
      $id = $values[$headerIndexes['id']];
      if (!preg_match('/\d+/', $id)) {
        $this->_errors[] = 'row ' . $i . ': ID must be an integer';
      }
      if (in_array($id, $ids)) {
        $this->_errors[] = 'row ' . $i . ': ID ' . $id . ' used in a previous row';
      }
      $ids[] = $id;

      // check if strain exists
      $strain = $values[$headerIndexes['strain']];
      $query = 'SELECT 1 FROM yeast_strain WHERE name = ?';
      $stmt = $db->prepare($query);
      $stmt->execute(array($strain));
      if (($row = $stmt->fetch(PDO::FETCH_ASSOC)) == false) {
        $this->_errors[] = 'row ' . $i . ': strain "' . $strain . '" not in the '
          . 'yeast strains database.';
      }
    }

    // check references against ids found
    for ($i = 1; $i < count($lines); $i++) {
      $values = explode("\t", $lines[$i]);
      $values = array_map('trim', $values);

      // check references against ids
      $referenceString = $values[$headerIndexes['reference']];
      $referenceString = preg_replace('/\s+/', '', $referenceString);

      if (!empty($referenceString)) {

        $vals = preg_split('/,/', $referenceString);
        foreach ($vals as $val) {
          $refIds = preg_split('/\+/', $val);
          foreach ($refIds as $refId) {
            if (!in_array($refId, $ids)) {
              $this->_errors[] = 'row ' . $i . ': reference id "' . $refId .
                '" does not exist';
            }
          }
        }
      }
    }

    // if there were no errors, save to database
    if (count($this->_errors) == 0) {
      return true;
    } else {
      return false;
    }
  }

  public function getKeyData($inputText)
  {
    $inputText = trim($inputText);
    $lines = preg_split("/(\r\n|\n|\r)/", $inputText);

    $headers = explode("\t", $lines[0]);
    $headers = array_map('trim', $headers);

    $data = array();
    $data[] = $headers;
    for ($i = 1; $i < count($lines); $i++) {
      $values = explode("\t", $lines[$i]);
      $values = array_map('trim', $values);
      $data[] = $values;
    }
    return $data;
  }

  private function getRequiredHeaders()
  {
    return array(
      'id',
      'reference',
      'label',
      'strain',
      'media',
      'temperature',
    );
  }

  private function getHeaders()
  {
    return array(
      'id',
      'reference',
      'label',
      'strain',
      'media',
      'temperature',
      'cells',
    );
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

}
