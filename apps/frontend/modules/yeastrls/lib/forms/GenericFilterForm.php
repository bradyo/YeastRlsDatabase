<?php

class GenericFilterForm extends sfForm
{
  public function configure()
  {
    $this->widgetSchema->setNameFormat('%s');
  }
}
