<?php

class MatingTypesFilterForm extends sfForm
{
  public function configure()
  {
    $backgroundChoices = $this->getOption('background_choices', array(''));
    $backgroundChoices = array_merge(array(''=>''), $backgroundChoices);

    $mediaChoices = $this->getOption('media_choices', array(''));
    $mediaChoices = array_merge(array(''=>''), $mediaChoices);

    $sortByChoices     = $this->getOption('sort_by_choices', array(''));
    $sortOrderChoices  = array('asc' => 'Ascending', 'desc' => 'Descending');
    

    $this->setWidgets(array(
      'search'      => new sfWidgetFormInput(),
      'genotype'    => new sfWidgetFormInput(),
      'background'  => new sfWidgetFormChoice(array('choices' => $backgroundChoices)),
      'media'       => new sfWidgetFormChoice(array('choices' => $mediaChoices)),
      'single'      => new sfWidgetFormInputCheckbox(),
      'sort_by'     => new sfWidgetFormChoice(array('choices' => $sortByChoices)),
      'sort_order'  => new sfWidgetFormChoice(array('choices' => $sortOrderChoices)),
    ));

    $this->widgetSchema->setNameFormat('%s');

    $this->setValidators(array(
      'search' => new sfValidatorString(array(
        'required'=>false
      )),
      'genotype' => new sfValidatorString(array(
        'required'=>false
      )),
      'background' => new sfValidatorChoice(array(
        'required' => false,
        'choices' => array_keys($backgroundChoices)
      )),
      'media' => new sfValidatorChoice(array(
        'required' => false,
        'choices' => array_keys($mediaChoices)
      )),
      'single' => new sfValidatorBoolean(array(
        'required' => false,
        )),
      'sort_by' => new sfValidatorChoice(array(
        'required' => false,
        'choices' => array_keys($sortByChoices)
      )),
      'sort_order' => new sfValidatorChoice(array(
        'required' => false,
        'choices' => array_keys($sortOrderChoices)
      )),
    ));
  }
}
