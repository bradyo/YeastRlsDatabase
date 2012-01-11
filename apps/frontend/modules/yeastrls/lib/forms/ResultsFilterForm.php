<?php

class ResultsFilterForm extends sfForm
{
  public function configure()
  {
    $pooledByChoices = array(
      'file'      => 'File',
      'strain'    => 'Strain',
      'genotype'  => 'Genotype'
      );

    $mediaChoices = array_merge(
      array('' => ''),
      $this->getOption('media_choices', array())
      );

    $sortByChoices = array_merge(
      array('' => ''),
      $this->getOption('sort_by_choices', array())
      );
    
    $sortOrderChoices = array(
      'asc' => 'Ascending',
      'desc' => 'Descending',
      );

    $rangeChoices = array(
      '>' => '>',
      '<' => '<'
      );


    $this->setWidgets(array(
      'search'            => new sfWidgetFormInput(),
      'genotype'          => new sfWidgetFormInput(),
      'experiment'        => new sfWidgetFormInput(),
      'media'             => new sfWidgetFormChoice(array('choices' => $mediaChoices)),
      'single'            => new sfWidgetFormInputCheckbox(),
      'epistasis'         => new sfWidgetFormInputCheckbox(),
      'percent_change_op' => new sfWidgetFormChoice(array('choices' => $rangeChoices)),
      'percent_change'    => new sfWidgetFormInput(),
      'ranksum_p_op'      => new sfWidgetFormChoice(array('choices' => $rangeChoices)),
      'ranksum_p'         => new sfWidgetFormInput(),
      'sort_by'           => new sfWidgetFormChoice(array('choices' => $sortByChoices)),
      'sort_order'        => new sfWidgetFormChoice(array('choices' => $sortOrderChoices)),
      ));

    $this->setValidators(array(
      'search' => new sfValidatorString(array(
        'required'=>false
        )),
      'genotype' => new sfValidatorString(array(
        'required'=>false
        )),
      'media' => new sfValidatorChoice(array(
        'required'=>false,
          'choices' => array_keys($mediaChoices)
        )),
      'experiment' => new sfValidatorString(array(
        'required'=>false
        )),
      'single' => new sfValidatorBoolean(array(
        'required' => false,
        )),
      'epistasis' => new sfValidatorBoolean(array(
        'required' => false,
        )),
      'percent_change_op' => new sfValidatorChoice(array(
        'required' => false,
        'choices' => array_keys($rangeChoices)
        )),
      'percent_change' => new sfValidatorNumber(array(
        'required'=>false
        )),
      'ranksum_p_op' => new sfValidatorChoice(array(
        'required' => false,
        'choices' => array_keys($rangeChoices)
        )),
      'ranksum_p' => new sfValidatorNumber(array(
        'required'=>false
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
