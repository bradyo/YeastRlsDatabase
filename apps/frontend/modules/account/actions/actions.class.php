<?php

class accountActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    // fetch from database
    $this->accountUpdateForm = new AccountUpdateForm();
    $this->passwordUpdateForm = new PasswordUpdateForm();

    // need to set core as current connection, otherwise table tries to 
    // use last connection defined in databases.yml (this is a bug in Doctrine)
    Doctrine_Manager::getInstance()->setCurrentConnection('core');

    $username = $this->getUser()->getAttribute('username');
    $table = Doctrine_Core::getTable('user');
    $user = $table->findOneBy('username', $username);

    // if a user who cannot update account tries to, just give them a message
    if ($request->isMethod('post') && !$this->getUser()->hasCredential('update account')) {
      $this->getUser()->setFlash('errorMessage',
        'Your account lacks the credentials to make changes to your account.');
      $this->redirect('account/index');
    }

    if ($request->isMethod('post') && $this->getUser()->hasCredential('update account')) {
      if ($request->hasParameter('update_account')) {
        // check the update account form
        $this->accountUpdateForm->bind($request->getParameter('update_account'));
        if ($this->accountUpdateForm->isValid()) {
          $values = $this->accountUpdateForm->getValues();

          $user['email'] = $values['email'];
          $user['lab'] = $values['lab'];
          $user['location'] = $values['location'];
          $user['phone'] = $values['phone'];
          $user->save();

          $this->getUser()->setFlash('successMessage', 'Your account settings have been updated.');
          $this->redirect('account/index');
        }
      }
      if ($request->hasParameter('update_password')) {
        // check the update password form
        $this->passwordUpdateForm->bind($request->getParameter('update_password'));
        if ($this->passwordUpdateForm->isValid()) {
          $values = $this->passwordUpdateForm->getValues();

          $salt = sha1(uniqid(mt_rand(), true));
          $password = $values['password'];
          $user['algorithm'] = 'sha1';
          $user['salt'] = $salt;
          $user['password'] = sha1($salt.$password);
          $user->save();
          
          $this->getUser()->setFlash('successMessage', 'Your password has been changed');
          $this->redirect('account/index');
        }
      }
    }

    // fetch from database
    $defaults = array(
      'email' => $user['email'],
      'lab' => $user['lab'],
      'location' => $user['location'],
      'phone' => $user['phone'],
    );
    $this->accountUpdateForm->setDefaults($defaults);

    // fetch credentials to show to user
    $this->credentials = $this->getUser()->getCredentials();
  }

  /**
  * Executes action to create a new account
  *
  * @param sfRequest $request A request object
  */
  public function executeCreate(sfWebRequest $request)
  {
    $dbh = Doctrine_Manager::getInstance()->getConnection('core')->getDbh();
    $this->form = new AccountCreateForm(array(), array('dbh' => $dbh));

    if ($request->isMethod('post')) {
      $this->form->bind($request->getParameter('account'));
      if ($this->form->isValid()) {
        $values = $this->form->getValues();
        
        // add the user
        $username = $values['username'];
        $email = $values['email'];
        $password = $this->_getRandomPassword();
        $salt = sha1(uniqid(mt_rand(), true));
        $algorithm = 'sha1';
        $insertPassword = sha1($salt.$password);

        $sth = $dbh->prepare('INSERT INTO user
          (username, email, algorithm, salt, password, created_at, updated_at)
          VALUES (?, ?, ?, ?, ?, NOW(), NOW()) ');
        $sth->execute(array($username, $email, $algorithm, $salt, $insertPassword));
        $userId = $dbh->lastInsertId();

        $sth = $dbh->prepare('INSERT INTO user_credential
          (user_id, credential_id) VALUES (?, ?) ');
        $credentialIds = $values['credentials'];
        foreach ($credentialIds as $credentialId) {
          $sth->execute(array($userId, $credentialId));
        }

        // send an email to the user
        try {
          $this->_sendNewAccountEmail($email, $username, $password);
        }
        catch (Exception $e) {
          $message = sprintf('Failed to e-mail user new login information.'
             .' Please notify the user of their new username and password:<br \>'
             .'username: %s<br />'
             .'password: %s<br />',
             $username, $password);
          $this->getUser()->setFlash('errorMessage', $message);
        }

        $message = sprintf('User "%s" created successfully.', $username);
        $this->getUser()->setFlash('successMessage', $message);

        $this->redirect('account/create');
      }
    }
  }


  private function _sendNewAccountEmail($email, $username, $password)
  {
    $messageBody = "Welcome to the Core collaboration group. "
      ."To access Core resources, visit: http://kaeberleinlab.org/core/\n\n"
      ."Your username is: $username\n"
      ."Your password is: $password\n\n";

    $message = $this->getMailer()->compose(
      array('core-admin@kaeberleinlab.org' => 'Core Admin'),
      $email,
      'a new Core account has been created for you',
      $messageBody
      );
    $this->getMailer()->send($message);
  }

  private function _getRandomPassword()
  {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $pass = '' ;
    for($i = 0; $i < 7; $i++) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
    }
    return $pass;
  }
}
