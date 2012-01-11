<?php

class EditForm extends sfForm
{
  public function configure()
  {
    $facilityChoices = array(
      'Kaeberlein Lab' => 'Kaeberlein Lab',
      'Kennedy Lab' => 'Kennedy Lab',
      'GDMC' => 'GDMC',
    );

    $statusChoices = array(
      'pending' => 'pending',
      'accepted' => 'accepted',
      'rejected' => 'rejected',
      'completed' => 'completed',
    );

    $this->setWidgets(array(
      'requested_by' => new sfWidgetFormInputText(),
      'number' => new sfWidgetForminputText(),
      'status' => new sfWidgetFormChoice(array(
        'choices' => $statusChoices,
      )),
      'description' => new sfWidgetFormTextarea(array(), array(
        'rows' => '10',
        'cols' => '80',
      )),
      'key_data' => new sfWidgetFormTextarea(array(), array(
        'rows' => '20',
        'cols' => '80',
      )),
    ));

    $this->setValidators(array(
      'requested_by' => new sfValidatorString(array(
        'required' => false,
      )),
      'number' => new sfValidatorNumber(array(
        'required' => false,
      )),
      'status' => new sfValidatorChoice(array(
        'required' => false,
        'choices' => array_keys($statusChoices)
      )),
      'description' => new sfValidatorString(array(
        'required' => true,
      )),
      'key_data' => new sfValidatorString(array(
        'required' => true
      )),
    ));

    $this->widgetSchema->setNameFormat('experiment[%s]');
    $this->disableLocalCSRFProtection();
  }
}
