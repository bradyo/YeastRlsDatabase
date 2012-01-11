<?php

/**
 * requires PDO database handle as 'dbh' option
 */
class AccountCreateForm extends sfForm
{
  public function configure()
  {
    $dbh = $this->getOption('dbh');
    $credentialChoices = $this->_getCredentialChoices($dbh);

    $this->setWidgets(array(
      'username' => new sfWidgetFormInput(),
      'email' => new sfWidgetFormInput(),
      'credentials' => new sfWidgetFormChoice(
        array('choices' => $credentialChoices, 'multiple' => true, 'expanded' => true)
      )
    ));
    //$this->setDefault('credentials', array_keys($credentialChoices));

    $this->widgetSchema->setNameFormat('account[%s]');
    $this->setValidators(array(
      'username' => new UsernameValidator(
        array('required' => true, 'dbh' => $dbh),
        array(
          'required' => 'required',
          'invalid' => 'username already exists'
          )
      ),
      'email' => new sfValidatorEmail(
        array('required'=>true),
        array(
          'required' => 'required',
          'invalid' => 'enter a valid e-mail'
          )
      ),
      'credentials' => new sfValidatorChoice(
        array(
          'choices' => array_keys($credentialChoices),
          'multiple' => true,
          'required' => false
          )
      ),
    ));

  }

  private function _getCredentialChoices($dbh)
  {
    $sth = $dbh->prepare('SELECT id, name, description FROM credential');
    $sth->execute();

    // hide some credentials we dont want showing on this form
    $hiddenCredentialNames = array(
      'manage users',
      'manage groups',
      'update override',
      'yeast rls executive',
    );

    $credentialChoices = array();
    foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $row) {
      if ( ! in_array($row['name'], $hiddenCredentialNames)) {
        $id = $row['id'];
        $description = $row['description'];
        $credentialChoices[$id] = $description;
      }
    }

    return $credentialChoices;
  }
}
