<?php

class LoginForm extends sfForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'username' => new sfWidgetFormInput(), 
      'password' => new sfWidgetFormInputPassword(),
    ));
    $this->widgetSchema->setNameFormat('login[%s]');

    $this->setValidators(array(
      'username' => new sfValidatorPass(),
      'password' => new sfValidatorPass(),
    ));
    $this->validatorSchema->setPostValidator(new LoginValidator(
      'username', 'password', array(),
      array('invalid' => 'Login credentials incorrect.')
      ));
  }
}
