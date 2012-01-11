<?php

class MediasFilterForm extends sfForm
{
  public function configure()
  {
    $backgroundChoices = $this->getOption('background_choices', array(''));
    $backgroundChoices = array_merge(array(''=>''), $backgroundChoices);

    $matingTypeChoices = $this->getOption('mating_type_choices', array(''));
    $matingTypeChoices = array_merge(array(''=>''), $matingTypeChoices);

    $sortByChoices     = $this->getOption('sort_by_choices', array(''));
    $sortOrderChoices  = array('asc' => 'Ascending', 'desc' => 'Descending');
    

    $this->setWidgets(array(
      'search'            => new sfWidgetFormInput(),
      'genotype'          => new sfWidgetFormInput(),
      'background'        => new sfWidgetFormChoice(array('choices' => $backgroundChoices)),
      'mating_type'       => new sfWidgetFormChoice(array('choices' => $matingTypeChoices)),
      'single'            => new sfWidgetFormInputCheckbox(),
      'sort_by'           => new sfWidgetFormChoice(array('choices' => $sortByChoices)),
      'sort_order'        => new sfWidgetFormChoice(array('choices' => $sortOrderChoices)),
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
      'mating_type' => new sfValidatorChoice(array(
        'required' => false,
        'choices' => array_keys($matingTypeChoices)
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
