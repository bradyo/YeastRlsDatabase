<?php

class YeastStrainFilterForm extends sfForm
{
  public function configure()
  {
    $backgroundChoices = array_merge(
      array('' => ''),
      $this->getOption('background_choices', array())
      );

    $matingTypeChoices = array_merge(
      array('' => ''),
      $this->getOption('mating_type_choices', array())
      );

    $locationChoices = array_merge(
      array('' => ''),
      $this->getOption('location_choices', array())
      );

    $sortByChoices = array_merge(
      array('' => ''),
      $this->getOption('sort_by_choices', array())
      );

    $sortOrderChoices = array(
      'asc' => 'Ascending',
      'desc' => 'Descending',
      );

    $this->setWidgets(array(
      'search'      => new sfWidgetFormInput(),
      'name'        => new sfWidgetFormInput(),
      'genotype'    => new sfWidgetFormInput(),
      'background'  => new sfWidgetFormChoice(array('choices' => $backgroundChoices)),
      'mating_type' => new sfWidgetFormChoice(array('choices' => $matingTypeChoices)),
      'location'    => new sfWidgetFormChoice(array('choices' => $locationChoices)),
      'sort_by'     => new sfWidgetFormChoice(array('choices' => $sortByChoices)),
      'sort_order'  => new sfWidgetFormChoice(array('choices' => $sortOrderChoices)),
      ));
    
    $this->setValidators(array(
      'search' => new sfValidatorString(array(
        'required'=>false
        )),
      'name' => new sfValidatorString(array(
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
      'location' => new sfValidatorChoice(array(
        'required' => false,
        'choices' => array_keys($locationChoices)
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

    $this->widgetSchema->setNameFormat('%s');
  }
}
