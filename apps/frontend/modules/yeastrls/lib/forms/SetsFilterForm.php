<?php

class SetsFilterForm extends sfForm
{
  public function configure()
  {

    $mediaChoices = array_merge(
      array('' => ''),
      $this->getOption('media_choices', array())
      );

    $rangeChoices = array(
      '>' => '>',
      '<' => '<'
    );
    $sortOrderChoices = array(
      'asc' => 'Ascending',
      'desc' => 'Descending',
    );

    $sortByChoices = array_merge(
        array('' => ''),
        $this->getOption('sort_by_choices', array())
        );


    $this->setWidgets(array(
      'search'            => new sfWidgetFormInput(),
      'experiment'        => new sfWidgetFormInput(),
      'strain'            => new sfWidgetFormInput(),
      'genotype'          => new sfWidgetFormInput(),
      'media'             => new sfWidgetFormChoice(array('choices' => $mediaChoices)),
      'lifespan_mean_op'  => new sfWidgetFormChoice(array('choices' => $rangeChoices)),
      'lifespan_mean'     => new sfWidgetFormInput(),
      'single'            => new sfWidgetFormInputCheckbox(),
      'sort_by'           => new sfWidgetFormChoice(array('choices' => $sortByChoices)),
      'sort_order'        => new sfWidgetFormChoice(array('choices' => $sortOrderChoices)),
    ));

    $this->widgetSchema->setNameFormat('%s');

    $this->setValidators(array(
      'search' => new sfValidatorString(array(
        'required'=>false
      )),
      'experiment' => new sfValidatorString(array(
        'required'=>false
      )),
      'strain' => new sfValidatorString(array(
        'required'=>false
      )),
      'genotype' => new sfValidatorString(array(
        'required'=>false
      )),
      'media' => new sfValidatorChoice(array(
        'required'=>false,
        'choices' => array_keys($mediaChoices)
      )),
      'single' => new sfValidatorBoolean(array(
        'required' => false,
        )),
      'lifespan_mean_op' => new sfValidatorChoice(array(
        'required' => false,
        'choices' => array_keys($rangeChoices)
      )),
      'lifespan_mean' => new sfValidatorNumber(array(
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
  }
}
