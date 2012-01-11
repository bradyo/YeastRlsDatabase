<?php

class UsernameValidator extends sfValidatorString
{
  protected function configure($options = array(), $messages = array())
  {
    $this->addMessage('min_length', 'must be at least 3 characters.');
    $this->addMessage('exists', '"%value%" already exists.');

    $this->addRequiredOption('dbh');
  }

  protected function doClean($value)
  {
    $clean = (string)$value;

    // check length
    $length = mb_strlen($clean, $this->getCharset());
    if ($length < 3) {
      throw new sfValidatorError($this, 'min_length', array('value' => $value));
    }

    // check if name already exists
    $dbh = $this->getOption('dbh');
    $sth = $dbh->prepare('SELECT id FROM user WHERE username = ?');
    $sth->execute(array($value));
    if ($row = $sth->fetch()) {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    return $clean;
  }
  
}