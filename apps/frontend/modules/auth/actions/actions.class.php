<?php

/**
 * auth actions.
 *
 * @package    yoda
 * @subpackage auth
 * @author     Brady Olsen
 */
class authActions extends sfActions
{
 /**
  * Executes login action
  *
  * @param sfRequest $request A request object
  */ 
  public function executeLogin(sfWebRequest $request)
  {
    // save refering page for redirect after login
    if ($this->getUser()->isAuthenticated()) {
      $this->forward('default', 'index');
    }
   
    if(!$this->getUser()->hasAttribute('requested_uri')) {
      $this->getUser()->setAttribute('requested_uri', $request->getUri());
    }

    $this->form = new LoginForm();
    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('login'));
      if ($this->form->isValid())
      {
        $username = $this->form->getValue('username');
                
        // authenticate user and redirect them
        $this->getUser()->setAuthenticated(true);
        $this->getUser()->setAttribute('username', $username);

        // add credentials
        $dbh = Doctrine_Manager::getInstance()->getConnection('core')->getDbh();
        $sth = $dbh->prepare('
          SELECT name FROM (
              SELECT credential.name as "name" FROM credential
              LEFT JOIN user_credential ON user_credential.credential_id = credential.id
              LEFT JOIN user ON user.id = user_credential.user_id
              WHERE user.username = ?
              UNION
              SELECT credential.name as "name" FROM credential
              LEFT JOIN group_credential ON group_credential.credential_id = credential.id
              LEFT JOIN user_group ON user_group.group_id = group_credential.group_id
              LEFT JOIN user ON user.id = user_group.user_id
              WHERE user.username = ?
          ) all_credential
          ');
        $sth->execute(array($username, $username));
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
          $credential = $row['name'];
          $this->getUser()->addCredential($credential);
        }

        $creds = $this->getUser()->getCredentials();
        $this->getUser()->setFlash('successMessage',
          sprintf('You are now logged in as "%s".<br />Your credentials: %s', $username, join(', ', $creds)));

        $redirectUri = $this->getUser()->getAttribute('requested_uri', 'default/index');
        $this->getUser()->getAttributeHolder()->remove('requested_uri');
        $this->redirect($redirectUri);
      }
    }
  }

 /**
  * Executes logout action
  *
  * @param sfRequest $request A request object
  */ 
  public function executeLogout()
  {
    $this->getUser()->clearCredentials();
    $this->getUser()->setAttribute('username', null);
    $this->getUser()->setAuthenticated(false);
    $this->redirect('default/index');
  }

  public function executeSecure()
  {
    
  }

}
