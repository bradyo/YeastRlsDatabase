<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('GroupCredential', 'core');

/**
 * BaseGroupCredential
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $group_id
 * @property integer $credential_id
 * @property Group $Group
 * @property Credential $Credential
 * 
 * @method integer         getGroupId()       Returns the current record's "group_id" value
 * @method integer         getCredentialId()  Returns the current record's "credential_id" value
 * @method Group           getGroup()         Returns the current record's "Group" value
 * @method Credential      getCredential()    Returns the current record's "Credential" value
 * @method GroupCredential setGroupId()       Sets the current record's "group_id" value
 * @method GroupCredential setCredentialId()  Sets the current record's "credential_id" value
 * @method GroupCredential setGroup()         Sets the current record's "Group" value
 * @method GroupCredential setCredential()    Sets the current record's "Credential" value
 * 
 * @package    core
 * @subpackage model
 * @author     Brady Olsen
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseGroupCredential extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('group_credential');
        $this->hasColumn('group_id', 'integer', 4, array(
             'type' => 'integer',
             'primary' => true,
             'length' => 4,
             ));
        $this->hasColumn('credential_id', 'integer', 4, array(
             'type' => 'integer',
             'primary' => true,
             'length' => 4,
             ));

        $this->option('type', 'INNODB');
        $this->option('charset', 'utf8');
        $this->option('collate', 'utf8_unicode_ci');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Group', array(
             'local' => 'group_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $this->hasOne('Credential', array(
             'local' => 'credential_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));
    }
}