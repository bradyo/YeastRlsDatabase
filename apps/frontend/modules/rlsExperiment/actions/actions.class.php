<?php


class rlsExperimentActions extends sfActions
{
  private $_db = null;
  private $_rlsDb = null;

  public function preExecute()
  {
    parent::preExecute();

    // stash core database pdo connection
    $this->_db = Doctrine_Manager::getInstance()->getConnection('core')->getDbh();

    // stash rls database pdo connection
    $path = sfConfig::get('sf_data_dir') . '/yeastrls/rls.db';
    if (file_exists($path) && filesize($path) > 0) {
      $this->_rlsDb = new PDO('sqlite:' . $path);
    }
  }

  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('rlsExperiment/list');
  }

  public function executeList(sfWebRequest $request)
  {
    $facility = $request->getParameter('facility', 'Kaeberlein Lab');
    $status = $request->getParameter('status', 'completed');

    $query = 'SELECT * FROM yeastrls_experiment';
    $whereClauses = array();
    $whereParams = array();
    if ($facility !== 'all') {
       $whereClauses[] .= 'facility = ?';
       $whereParams[] = $facility;
    }
    if ($status !== 'all') {
       $whereClauses[] .= 'status = ?';
       $whereParams[] = $status;
    }
    if (count($whereClauses) > 0) {
        $query .= ' WHERE ' . join(' AND ', $whereClauses);
    }
    $query .= ' ORDER BY number DESC, requested_at DESC';

    $stmt = $this->_db->prepare($query);
    $stmt->execute($whereParams);
    $this->experiments = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function executeShow(sfWebRequest $request)
  {
    $id = $request->getParameter('id');
    $stmt = $this->_db->prepare('
      SELECT * FROM yeastrls_experiment WHERE id = ?
      ');
    $stmt->execute(array($id));

    $experiment = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($experiment == false) {
      $this->forward404();
    }
    $this->experiment = $experiment;

    // check for report file
    $this->hasReport = false;
    $filename = sfConfig::get('sf_data_dir') . '/rlsExperiment/reports/'
      . $experiment['number'] . ".xlsx";
    if (file_exists($filename)) {
      $this->hasReport = true;
    }

    // check for csv file
    $this->hasCsv = false;
    $filename = sfConfig::get('sf_data_dir') . '/rlsExperiment/database-files/'
      . $experiment['number'] . ".csv";
    if (file_exists($filename)) {
      $this->hasCsv = true;
    }

    // see if the user is executive or manager, they will be presented
    // with workflow options
    $executive = UserTable::getRlsExperimentExecutive($experiment['facility']);
    $username = $this->getUser()->getAttribute('username');
    $isExecutive = ($executive !== null && $executive == $username);
    if ($username == 'brady' || $username == 'mkaeberlein') {
      $isExecutive = true;
    }
    $this->isExecutive = $isExecutive;


    $manager = UserTable::getRlsExperimentManager($experiment['facility']);
    $isManager = ($manager !== null && $manager == $username);
    $this->isManager = $isManager;

    // check if the user can view experiment keys (not dissectors, for blindness)
    // let executive, manager, or creator view experiment
    $isRequestor = ($experiment['requested_by'] == $username);
    $isGradStudent = in_array($username, array(
        'mkaeberlein',
        'sutphin',
        'joe',
        'jschleit',
    ));
    $isComplete = $experiment['status'] = 'completed';
    $this->showKey = ($isExecutive || $isManager || $isRequestor || $isGradStudent || $isComplete);

    // fetch the key data and join strain data
    if ($this->showKey) {
      // extract key data into associative array
      $outputRows = array();
      $experimentManager = new ExperimentManager();
      $keyRows = $experimentManager->getKeyData($experiment['key_data']);
      @array_shift($keyRows); // remove header
      foreach ($keyRows as $keyRow) {
        $row = @array(
          'id' => $keyRow[0],
          'reference' => $keyRow[1],
          'label' => $keyRow[2],
          'strain' => $keyRow[3],
          'media' => $keyRow[4],
          'temperature' => $keyRow[5],
          'cells' => $keyRow[6],
          'strain_background' => null,
          'strain_mating_type' => null,
          'strain_short_genotype' => null,
					'strain_full_genotype' => null,
          'strain_freezer_code' => null,
        );

        // add strain data to row
        $strainName = $keyRow[3];
        $stmt = $this->_db->prepare('
          SELECT background, mating_type, genotype_short, genotype, freezer_code
          FROM yeast_strain WHERE name = ?
        ');
        $stmt->execute(array($strainName));
        if ($strainRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $row['strain_background'] = $strainRow['background'];
          $row['strain_mating_type'] = $strainRow['mating_type'];
          $row['strain_short_genotype'] = $strainRow['genotype_short'];
					$row['strain_full_genotype'] = $strainRow['genotype'];
          $row['strain_freezer_code'] = $strainRow['freezer_code'];
        }
        $outputRows[] = $row;
      }
      $this->keyRows = $outputRows;
    }
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new AddForm();

    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('experiment'));
      if ($this->form->isValid()) {
        $values = $this->form->getValues();
        $inputText = trim($values['key_data']);

        $expManager = new ExperimentManager();
        if ($expManager->checkInput($this->_db, $inputText)) {
          // add experiment to database
          $query = '
            INSERT INTO yeastrls_experiment (
              facility, description, key_data, requested_by, requested_at,
              request_message, status
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?)
            ';
          $stmt = $this->_db->prepare($query);
          $stmt->execute(array(
            $values['facility'],
            $values['description'], 
            $inputText,
            $this->getUser()->getAttribute('username'),
            $values['message'],
            'pending'
          ));
          $experimentId = $this->_db->lastInsertId();

          // send a notification e-mail to executive
          if (sfConfig::get('app_rlsExperiment_sendEmails')) {
            frontendConfiguration::registerZend();
            try {
              $mail = new Zend_Mail();
              $mail->setFrom('core-noreply@kaeberleinlab.org', 'Core Resources');

              $executive = UserTable::getRlsExperimentExecutive($values['facility']);
              $emails = UserTable::getEmails(array($executive));
              foreach ($emails as $email) {
                $mail->addTo($email);
              }
              $mail->setSubject('A new yeast RLS experiment has been submitted ('
                  . date('j M, g:ia') . ')');
              $body = $this->getPartial('rlsExperiment/newEmail', array(
                'experimentId' => $experimentId,
                'message' => $values['message'],
              ));
              $mail->setBodyText(strip_tags($body));
              $mail->setBodyHtml($body);
              $mail->send();
            }
            catch (Exception $e) {
              $this->getUser()->setFlash('errorMessage', $e->getMessage());
            }
          }

          // set a flash message and redirect
          $message = 'Your experiment has been submitted. You will be notified '
            . 'when it is accepted into the disection queue.';
          $this->getUser()->setFlash('successMessage', $message);
          $this->redirect('rlsExperiment/show?id=' . $experimentId);
        }
        else {
          $this->errors = $expManager->getErrors();
        }
      } else {
        $errorMessage = "Missing required fields";
        $this->errors = array($errorMessage);
      }
    }

    $this->medias = $this->_getMedias();
  }

  public function executeEdit(sfWebRequest $request)
  {
    $experimentId = $request->getParameter('id');
    $experiment = $this->_getExpeirment($experimentId);
    if (!$experiment) {
      $this->forward404();
    }
    $this->experiment = $experiment;

    $username = $this->getUser()->getAttribute('username');
    $executive = UserTable::getRlsExperimentExecutive($experiment['facility']);
		$manager = UserTable::getRlsExperimentManager($experiment['facility']);
    if ($username !== $executive && $username !== $manager &&  $username != 'brady') {
      $this->forward404();
    }

    $this->form = new EditForm();
    $this->form->bind($experiment);
    
    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('experiment'));
      if ($this->form->isValid()) {
        $values = $this->form->getValues();
        $inputText = trim($values['key_data']);

        $expManager = new ExperimentManager();
        if ($expManager->checkInput($this->_db, $inputText)) {
          // update experiment in database
          $stmt = $this->_db->prepare('
            UPDATE yeastrls_experiment
            SET number = ?, description = ?, key_data = ?, requested_by = ?, status = ?
            WHERE id = ?
            ');
          $stmt->execute(array(
            $values['number'],
            $values['description'],
            $inputText,
            $values['requested_by'],
            $values['status'],
            $experimentId,
          ));

          // set a flash message and redirect
          $this->getUser()->setFlash('successMessage', 'Experiment updated successfully.');
          $this->redirect('rlsExperiment/show?id=' . $experimentId);
        }
        else {
          $this->errors = $expManager->getErrors();
        }
      } else {
        $errorMessage = "Missing required fields";
        $this->errors = array($errorMessage);
      }
    }

    $this->medias = $this->_getMedias();
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $id = $request->getParameter('id');
    $experiment = $this->_getExpeirment($id);
    if (!$experiment) {
      $this->forward404();
    }

    $username = $this->getUser()->getAttribute('username');
    $executive = UserTable::getRlsExperimentExecutive($experiment['facility']);
    if ($username !== $executive && $username != 'brady') {
      $this->forward404();
    }

    $newStatus = $request->getPostParameter('status');
    $message = $request->getPostParameter('message');

    // if experiment is accepted, get it's new experiment number
    $number = null;
    if ($newStatus == 'accepted') {
      $number = $this->_getNextExperimentNumber();
    }

    // upate the database
    $stmt = $this->_db->prepare('
      UPDATE yeastrls_experiment
      SET number = ?, status = ?, review_message = ?, reviewed_at = NOW()
      WHERE id = ?
      ');
    $stmt->execute(array($number, $newStatus, $message, $id));

    // notify the appropriate people of the status change
    if (sfConfig::get('app_rlsExperiment_sendEmails')) {
      $requestor = $experiment['requested_by'];
      frontendConfiguration::registerZend();
      try {
        $mail = new Zend_Mail();
        $mail->setFrom('core-noreply@kaeberleinlab.org', 'Core Resources');
        if ($newStatus == 'accepted') {
          // email experiment manager and requestor that the experiment was accepted
          $manager = UserTable::getRlsExperimentManager($experiment['facility']);
          $emails = UserTable::getEmails(array($manager, $requestor));
          foreach ($emails as $email) {
            $mail->addTo($email);
          }
          $mail->setSubject('A yeast RLS experiment has been accepted');
          $body = $this->getPartial('rlsExperiment/acceptedEmail', array(
            'experimentId' => $experiment['id'],
            'message' => $message,
          ));
          $mail->setBodyText(strip_tags($body));
          $mail->setBodyHtml($body);
        }
        elseif ($newStatus == 'rejected') {
          // email requestor that the experiment was rejected
          $emails = UserTable::getEmails(array($requestor));
          foreach ($emails as $email) {
            $mail->addTo($email);
          }
          $mail->setSubject('Your yeast RLS experiment has been rejected ('
                  . date('j M, g:ia') . ')');
          $body = $this->getPartial('rlsExperiment/rejectedEmail', array(
            'experimentId' => $experiment['id'],
            'message' => $message,
          ));
          $mail->setBodyText(strip_tags($body));
          $mail->setBodyHtml($body);
        }
        $mail->send();
      }
      catch (Exception $e) {
        $this->getUser()->setFlash('errorMessage', 'failed to send e-mails: '
          . $e->getMessage());
      }
    }

    $this->getUser()->setFlash('successMessage', 'Experiment updated successfully.');
    $this->redirect('rlsExperiment/show?id=' . $experiment['id']);
  }

   public function executeDownloadCsv(sfWebRequest $request)
  {
    $filename = basename($request->getParameter('filename'));
    $path = sfConfig::get('sf_data_dir') . '/rlsExperiment/database-files/' . $filename;
    if (!file_exists($path)) {
      $this->forward404();
    }

    $this->setDownloadHeader($filename);
    $this->getResponse()->setContent(readfile($path));
    return sfView::HEADER_ONLY;
  }

  public function executeDownloadReport(sfWebRequest $request)
  {
    $filename = basename($request->getParameter('filename'));
    $path = sfConfig::get('sf_data_dir') . '/rlsExperiment/reports/' . $filename;
    if (!file_exists($path)) {
      $this->forward404();
    }

    $this->setDownloadHeader($filename);
    $this->getResponse()->setContent(readfile($path));
    return sfView::HEADER_ONLY;
  }

  public function executeDownloadKey(sfWebRequest $request)
  {
    $filename = basename($request->getParameter('filename'));
    $path = sfConfig::get('sf_data_dir') . '/rlsExperiment/keys/' . $filename;
    if (!file_exists($path)) {
      $this->forward404();
    }

    $this->setDownloadHeader($filename);
    $this->getResponse()->setContent(readfile($path));
    return sfView::HEADER_ONLY;
  }

  private function setDownloadHeader($filename)
  {
    $this->setLayout(false);
    sfConfig::set('sf_web_debug', false);
    sfConfig::set('sf_escaping_strategy', 'off');

    $response = $this->getResponse();
    $response->clearHttpHeaders();
    $response->setHttpHeader('Pragma: public', true);
    $response->setHttpHeader('Content-Transfer-Encoding', 'binary', true);
    $response->setHttpHeader('Content-Disposition', 'attachment; filename='.$filename);
    $response->setContentType('text/plain');
    $response->sendHttpHeaders();
  }


  private function _getExpeirment($id)
  {
    $stmt = $this->_db->prepare('
      SELECT * FROM yeastrls_experiment WHERE id = ?
      ');
    $stmt->execute(array($id));
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  private function _getNextExperimentNumber($facility = "Kaeberlein Lab")
  {
    // if an experiment number is skipped due to users editing experiments,
    // we should get the next lowest experiment number not currently used,
    // or get the next number (max + 1)
    $stmt = $this->_db->prepare('
        SELECT DISTINCT number FROM yeastrls_experiment
        WHERE number IS NOT NULL
        ');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $numbers = array();
    foreach ($rows as $row) {
        if (!empty($row['number']) && intval($row['number']) > 0) {
            $numbers[] = intval($row['number']);
        }
    }
    sort($numbers);

    // find the lowest number not currently used (if exists)
    for ($i = 0; $i < count($numbers); $i++) {
        $number = $numbers[$i];
        if (isset($numbers[$i + 1])) {
            $nextNumber = $numbers[$i + 1];
            if ($nextNumber != $number + 1) {
                return $number + 1;
            }
        }
    }
    
    // if we didint find a number not used, we should return the next hightest number
    return (max($numbers) + 1);
  }

  private function _getMedias()
  {
    $medias = array();

    if ($this->_rlsDb !== null) {
      $stmt = $this->_rlsDb->prepare('SELECT DISTINCT media FROM "set"');
      $stmt->execute();
      foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $media = $row['media'];
        $medias[$media] = $media;
      }
      sort($medias);
    }
    return $medias;
  }

}
