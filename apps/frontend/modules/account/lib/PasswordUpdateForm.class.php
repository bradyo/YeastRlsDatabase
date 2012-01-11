<?php

class PasswordUpdateForm extends sfForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'password' => new sfWidgetFormInputPassword(),
      'password_repeat' => new sfWidgetFormInputPassword()
    ));
    $this->widgetSchema->setNameFormat('update_password[%s]');

    $this->setValidators(array(
      'password' => new sfValidatorString(
        array('required'=>true),
        array('required' => 'required')
      ),
      'password_repeat' => new sfValidatorString(
        array('required'=>true),
        array('required' => 'required')
      ),
    ));
    $this->validatorSchema->setPostValidator(
      new sfValidatorSchemaCompare(
        'password', 
        sfValidatorSchemaCompare::EQUAL,
        'password_repeat',
        array(),
        array('invalid' => 'passwords do not match')
      )
    );
  }
}
