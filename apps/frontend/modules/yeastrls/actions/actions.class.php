<?php


class yeastrlsActions extends sfActions
{
  private $_dbh = null;
  private $_plotsDbh = null;
  private $_buildDate = null;

  private function getDb()
  {
    if ($this->_dbh !== null) {
      return $this->_dbh;
    }

    // check for 'updating' file, if it is present, that means an update
    // is in progress
    $path = sfConfig::get('sf_data_dir') . '/yeastrls/updating';
    if (file_exists($path)) {
      $this->forward('yeast-rls', 'status');
    }

    // create connection to rls.db sqlite file.
    $path = sfConfig::get('sf_data_dir') . '/yeastrls/rls.db';
    if (file_exists($path) && filesize($path) > 0) {
      $this->_dbh = new PDO('sqlite:'.$path);
    }
    if ($this->_dbh == null) {
      $this->forward('yeastrls', 'error');
    }

    return $this->_dbh;
  }

  private function getPlotsDb()
  {
    if ($this->_plotsDbh !== null) {
      return $this->_plotsDbh;
    }

    // check for 'updating' file, if it is present, that means an update
    // is in progress
    $path = sfConfig::get('sf_data_dir') . '/yeastrls/updating';
    if (file_exists($path)) {
      $this->forward('yeast-rls', 'status');
    }

    // create connection to rls.db sqlite file
    $path = sfConfig::get('sf_data_dir') . '/yeastrls/plots.db';
    if (file_exists($path) && filesize($path) > 0) {
      $this->_plotsDbh = new PDO('sqlite:'.$path);
    }
    if ($this->_plotsDbh == null) {
      $this->forward('yeastrls', 'error');
    }
    
    return $this->_plotsDbh;
  }

  public function preExecute()
  {
    if ($this->getActionName() != 'status' && $this->getActionname() != 'error') {
      $this->_dbh = $this->getDb();

      // fetch the build date
      $stmt = $this->_dbh->prepare('SELECT value FROM meta WHERE name="built_at" LIMIT 1');
      $stmt->execute();
      $row = $stmt->fetch();
      $this->_buildDate = substr($row['value'], 0, 10);

      // add build date to view so subMenu template can use it
      $this->buildDate = $this->_buildDate;
    }

  }


  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('yeast-rls/results');
  }

  public function executeError(sfWebRequest $request)
  {
    $this->message = 'Cannot connect to database file.';
  }

  public function executeStatus(sfWebRequest $request)
  {
     $this->message = 'The database is currently being updated.'
        .' Check back in a few minutes.';
  }

  
  public function executeResults(sfWebRequest $request)
  {
    $resultTable = new ResultTable($this->_dbh, $request->getGetParameters());
    $this->filterForm = $resultTable->getFilterForm();

    $page = $request->getParameter('page', 1);
    $this->rows = $resultTable->getPageRows($page);
    $this->pager = $resultTable->getPager($page);

    $this->poolingOptions = array(
      'file'=>'File',
      'strain'=>'Strain',
      'genotype'=>'Genotype'
      );
    $this->pooledBy = $request->getParameter('pooled_by', 'file');
  }

  public function executeResult(sfWebRequest $request)
  {
    $id = $request->getGetParameter('id');
    $resultTable = new ResultTable($this->_dbh);
    $this->result = $resultTable->getResult($id);
    if ($this->result == null) {
      $this->forward404();
    }

    $this->sets = $resultTable->getResultSets($id);
    $this->refSets = $resultTable->getResultRefSets($id);
  }

  public function executeResultsexport(sfWebRequest $request)
  {
    // if all is selected, we want to use get parameters to apply filters,
    // otherwise we want to get the strains matching the posted strain ids
    $selectedIds = null;
    if ($request->getPostParameter('exportType') == 'selected') {
      $selectedIds = array_keys($request->getPostParameter('export', array()));
    }

    // get the selected data (if selectedIds is null, all data is returned)
    $resultTable = new ResultTable($this->_dbh, $request->getGetParameters());
    $this->rows = $resultTable->getRows($selectedIds);

    // need to get the max length of data, so rows can be added for shorter ones
    $maxLength = 0;
    foreach ($this->rows as &$row) {
      $setValues = explode(',', $row['set_lifespans']);
      $count = count($setValues);
      if ($count > $maxLength) {
        $maxLength = $count;
      }
      sort($setValues);
      $row['set_lifespans_array'] = $setValues;

      $refValues = explode(',', $row['ref_lifespans']);
      $count = count($refValues);
      if ($count > $maxLength) {
        $maxLength = $count;
      }
      sort($refValues);
      $row['ref_lifespans_array'] = $refValues;
    }
    $this->maxLength = $maxLength;

    // set file output
    $filename = 'yeast_rlsdb_results_'.$this->_buildDate.'.csv';
    $this->setExportHeader($filename);
    return 'Csv';
  }



  public function executeSets(sfWebRequest $request)
  {
    $setTable = new SetTable($this->_dbh, $request->getGetParameters());
    $this->filterForm = $setTable->getFilterForm();

    $page = $request->getParameter('page', 1);
    $this->rows = $setTable->getPageRows($page);
    $this->pager = $setTable->getPager($page);
  }

  public function executeSet(sfWebRequest $request)
  {
    $id = $request->getGetParameter('id');
    $setTable = new SetTable($this->_dbh);
    $this->set = $setTable->getSet($id);
    if ($this->set == null) {
      $this->forward404();
    }

    $this->asSetPooledResultIds = $setTable->getAsSetPooledResultIds($id);
    $this->asRefPooledResultIds = $setTable->getAsRefPooledResultIds($id);
  }

  public function executeSetsexport(sfWebRequest $request)
  {
    // if all is selected, we want to use get parameters to apply filters,
    // otherwise we want to get the strains matching the posted strain ids
    $selectedIds = null;
    if ($request->getPostParameter('exportType') == 'selected') {
      $selectedIds = array_keys($request->getPostParameter('export', array()));
    }

    // get the selected data (if selectedIds is null, all data is returned)
    $setTable = new SetTable($this->_dbh, $request->getGetParameters());
    $this->rows = $setTable->getRows($selectedIds);

    // set correct http headers and pass to corresponding view
    $filename = 'yeast_rlsdb_sets_'.$this->_buildDate.'.csv';
    $this->setExportHeader($filename);
    return 'Csv';
  }



  public function executeMedias(sfWebRequest $request)
  {
    if ($request->isMethod('post') && $request->getPostParameter('submit') == 'export') {
      $this->forward('yeast-rls', 'medias-export');
    }

    $mediasTable = new MediasTable($this->_dbh, $request->getGetParameters());
    $this->filterForm = $mediasTable->getFilterForm();

    $page = $request->getParameter('page', 1);
    $this->rows = $mediasTable->getPageRows($page);
    $this->pager = $mediasTable->getPager($page);
    $this->medias = $mediasTable->getMediaChoices();
  }

  public function executeMediasexport(sfWebRequest $request)
  {
    // if all is selected, we want to use get parameters to apply filters,
    // otherwise we want to get the genotypes matching the posted values
    $selectedGenotypes = null;
    if ($request->getPostParameter('exportType') == 'selected') {
      $selectedGenotypes = array_keys($request->getPostParameter('export', array()));
    }

    // get the selected data (if selectedIds is null, all data is returned)
    $mediasTable = new MediasTable($this->_dbh, $request->getGetParameters());
    $this->rows = $mediasTable->getRows($selectedGenotypes);

    // set correct http headers and pass to corresponding view
    $filename = 'yeast_rlsdb_medias_'.$this->_buildDate.'.csv';
    $this->setExportHeader($filename);
    return 'Csv';
  }



  public function executeMatingtypes(sfWebRequest $request)
  {
    if ($request->isMethod('post') && $request->getPostParameter('submit') == 'export') {
      $this->forward('yeast-rls', 'mating-types-export');
    }

    $table = new MatingTypesTable($this->_dbh, $request->getGetParameters());
    $this->filterForm = $table->getFilterForm();

    $page = $request->getParameter('page', 1);
    $this->rows = $table->getPageRows($page);
    $this->pager = $table->getPager($page);
    $this->matingTypes = $table->getMatingTypeChoices();
  }

  public function executeMatingtypesexport(sfWebRequest $request)
  {
    // if all is selected, we want to use get parameters to apply filters,
    // otherwise we want to get the genotypes matching the posted values
    $selectedGenotypes = null;
    if ($request->getPostParameter('exportType') == 'selected') {
      $selectedGenotypes = array_keys($request->getPostParameter('export', array()));
    }

    // get the selected data (if selectedIds is null, all data is returned)
    $table = new MatingTypesTable($this->_dbh, $request->getGetParameters());
    $this->rows = $table->getRows($selectedGenotypes);

    // set correct http headers and pass to corresponding view
    $filename = 'yeast-rls_cross-mating-type_'.$this->_buildDate.'.csv';
    $this->setExportHeader($filename);
    return 'Csv';
  }



  public function executeLog(sfWebRequest $request)
  {
    // fetch the bulid date from the meta table
    $sth = $this->_dbh->prepare('SELECT name, value FROM meta');
    $sth->execute();

    // fetch build meta data
    $this->metaData = array();
    foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $this->metaData[] = $row;
    }

    // fetch the log data
    $query = 'SELECT filename, message FROM build_log ORDER BY filename DESC';
    $sth = $this->_dbh->prepare($query);
    $sth->execute();

    $this->rows = array();
    foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $filename = $row['filename'];
      if (!isset($this->rows[$filename])) {
        $this->rows[$filename] = array();
      }
      $this->rows[$filename][] = $row['message'];
    }
  }



  public function executeDownload(sfWebRequest $request)
  {
    $filename = basename($request->getParameter('filename'));
    $path = sfConfig::get('sf_data_dir') . '/yeastrls/' . $filename;
    if (!file_exists($path)) {
      $this->forward404();
    }

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
    $response->setContent(readfile($path));
    return sfView::HEADER_ONLY;
  }

  private function setExportHeader($filename)
  {
    $this->setLayout(false);
    sfConfig::set('sf_web_debug', false);
    sfConfig::set('sf_escaping_strategy', 'off');
    
    $response = $this->getResponse();
    $response->clearHttpHeaders();
    $response->setHttpHeader('Pragma: public', true);
    $response->addCacheControlHttpHeader('Cache-Control', 'must-revalidate');
    $response->setHttpHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
    $response->setHttpHeader("Last-Modified",  gmdate("D, d M Y H:i:s") . " GMT");
    $response->setHttpHeader('Content-Transfer-Encoding', 'binary', true);
    $response->setHttpHeader('Content-Disposition', 'attachment; filename='.$filename);
    $response->setContentType('text/plain');
    $response->sendHttpHeaders();
  }

  public function executePlot(sfWebRequest $request)
  {
      $this->setLayout(false);
      sfConfig::set('sf_web_debug', false);
      sfConfig::set('sf_escaping_strategy', 'off');

      $type = $request->getParameter('type');
      $validTypes = array('set', 'result', 'cross_mating_type', 'cross_media');
      if (!in_array($type, $validTypes)) {
        $this->forward404();
      }

      $filename = $request->getParameter('filename', '');

      $dbh = $this->getPlotsDb();
      $query = "SELECT data FROM \"$type\" r WHERE r.filename = ?";
      $sth = $dbh->prepare($query);
      $sth->execute(array($filename));
      $row = $sth->fetch();
      if (!$row) {
          $this->forward404();
      }

      $data = stripslashes($row['data']);

      $response = $this->getResponse();
      $response->clearHttpHeaders();
      $response->setContentType('image/png');
      $response->setHttpHeader('Content-Length', strlen($data));
      $response->sendHttpHeaders();
      echo $data;

      return sfView::NONE;
  }

}
