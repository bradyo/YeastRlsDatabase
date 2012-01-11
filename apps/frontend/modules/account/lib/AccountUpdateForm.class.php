<?php

class AccountUpdateForm extends sfForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'email' => new sfWidgetFormInput(),
      'lab' => new sfWidgetFormInput(),
      'location' => new sfWidgetFormInput(),
      'phone' => new sfWidgetFormInput(),
    ));
    $this->widgetSchema->setNameFormat('update_account[%s]');

    $this->setValidators(array(
      'email' => new sfValidatorEmail(
        array('required'=>false),
        array('invalid' => 'enter a valid e-mail')
      ),
      'lab' => new sfValidatorString(array('required'=>false)),
      'location' => new sfValidatorString(array('required'=>false)),
      'phone' => new sfValidatorString(array('required'=>false)),
    ));
  }
}
