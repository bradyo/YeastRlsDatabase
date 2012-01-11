<?php

class AddForm extends sfForm
{
  public function configure()
  {
    $facilityChoices = array(
      'Kaeberlein Lab' => 'Kaeberlein Lab',
      'Kennedy Lab' => 'Kennedy Lab',
      'GDMC' => 'GDMC',
    );

    $this->setWidgets(array(
      'description' => new sfWidgetFormTextarea(array(), array(
        'rows' => '10',
        'cols' => '80',
      )),
      'facility' => new sfWidgetFormChoice(array(
        'choices' => $facilityChoices
      )),
      'key_data' => new sfWidgetFormTextarea(array(), array(
        'rows' => '20',
        'cols' => '80',
      )),
      'message' => new sfWidgetFormTextarea(array(), array(
        'rows' => '4',
        'cols' => '80',
      )),
    ));

    $this->setValidators(array(
      'facility' => new sfValidatorChoice(array(
        'required' => true,
        'choices' => array_keys($facilityChoices)
      )),
      'description' => new sfValidatorString(array(
        'required' => true,
      )),
      'key_data' => new sfValidatorString(array(
        'required' => true
      )),
      'message' => new sfValidatorString(array(
        'required' => false
      )),
    ));

    $this->widgetSchema->setNameFormat('experiment[%s]');
    $this->disableLocalCSRFProtection();
  }
}
