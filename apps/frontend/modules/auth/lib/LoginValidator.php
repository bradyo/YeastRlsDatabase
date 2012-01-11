<?php
 
class LoginValidator extends sfValidatorSchema
{
  public function __construct($username, $password, $options = array(), $messages = array())
  {
    $this->addOption('username', $username);
    $this->addOption('password', $password);

    parent::__construct(null, $options, $messages);
  }
 
  protected function doClean($values)
  {
    if (null === $values) {
      $values = array();
    }

    if (!is_array($values)) {
      throw new InvalidArgumentException('You must pass an array parameter to the clean() method');
    }

    $username = null;
    if (isset($values[$this->getOption('username')])) {
      $username = $values[$this->getOption('username')];
    }
    
    $password = null;
    if (isset($values[$this->getOption('password')])) {
      $password = $values[$this->getOption('password')];
    }
    
    // fetch the user
    $dbh = Doctrine_Manager::getInstance()->getConnection('core')->getDbh();
    $q = 'SELECT username, algorithm, password, salt FROM user WHERE username = ? LIMIT 1';
    $sth = $dbh->prepare($q);
    $sth->execute(array($username));
    $user = $sth->fetch(PDO::FETCH_ASSOC);    
    if ($user) {
      // password is OK?
      $hashFunction = $user['algorithm'];
      if (is_callable($hashFunction)) {
        $computed = $hashFunction($user['salt'].$password);
        if ($computed == $user['password']) {
          return array('username' => $username);
        }
      }
    }
    
    // validation failed
    $error = new sfValidatorError($this, 'invalid', array(
      'username'  => $username,
      'password' => $password,
    ));
    throw $error;
  }
  
}