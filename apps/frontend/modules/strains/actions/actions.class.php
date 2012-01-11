<?php

/**
 * yeast actions.
 *
 * @package    core
 * @subpackage yeast
 * @author     Brady Olsen
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class yeastActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $db = Doctrine_Manager::getInstance()->getConnection('core')->getDbh();
    $browser = new YeastStrainBrowser($db, $request->getGetParameters());
    $this->filterForm = $browser->getFilterForm();

    $page = $request->getParameter('page', 1);
    $this->rows = $browser->getPageRows($page);
    $this->pager = $browser->getPager($page);
  }

  public function executeShow(sfWebRequest $request)
  {
    $db = Doctrine_Manager::getInstance()->getConnection('core')->getDbh();
    
    // get the id if name is given
    $id = null;
    if ($request->hasParameter('name')) {
      $name = $request->getParameter('name');
      $stmt = $db->prepare('SELECT id FROM yeast_strain WHERE name = ?');
      $stmt->execute(array($name));
      if ($row = $stmt->fetch()) {
        $id = $row['id'];
      }
    }
    else {
      $id = $request->getParameter('id');
    }

    $browser = new YeastStrainBrowser($db);
    $this->strain = $browser->getRow($id);
    if ($this->strain == null) {
      $this->forward404();
    }
  }

  public function executeExport(sfWebRequest $request)
  {
    // if all is selected, we want to use get parameters to apply filters,
    // otherwise we want to get the strains matching the posted strain ids
    $selectedIds = null;
    if ($request->getPostParameter('exportType') == 'selected') {
      $selectedIds = array_keys($request->getPostParameter('export', array()));
    }

    // get the selected data (if selectedIds is null, all data is returned)
    $db = Doctrine_Manager::getInstance()->getConnection('core')->getDbh();
    $browser = new YeastStrainBrowser($db, $request->getGetParameters());
    $this->rows = $browser->getRows($selectedIds);

    // set correct http headers and pass to corresponding view
    $filename = 'yeast_strains_'.date('Y-m-d').'.csv';
    $this->setExportHeader($filename);
    return 'Csv';
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

  public function executeUpdate(sfWebRequest $request)
  {
    // process input if posting
    if ($request->isMethod('post')) {
      $db = Doctrine_Manager::getInstance()->getConnection('core')->getDbh();

      $username = $this->getUser()->getAttribute('username');
      $canOverride = $this->getUser()->hasCredential('update override');
      $updater = new YeastStrainUpdater($db, $username, $canOverride);

      if ($request->hasParameter('process')) {
        // process input and display update form
        $input = $request->getPostParameter('inputText');
        $updater->processInput($input, $username);

        $this->errors = $updater->getErrors();
        $this->failedStrains = $updater->getFailedStrains();
        $this->addStrains = $updater->getAddStrains();
        $this->heldStrains = $updater->getHeldStrains();
        return 'Verify';
      }
      elseif ($request->hasParameter('update')) {
        $params = $request->getPostParameters();
        $updater->update($request->getPostParameters());

        $this->errors = $updater->getErrors();
        $this->addedStrains = $updater->getAddedStrains();
        $this->updatedStrains = $updater->getUpdatedStrains();
        $this->skippedStrains = $updater->getSkippedStrains();
        return 'Result';
      }
    }
  }
}
