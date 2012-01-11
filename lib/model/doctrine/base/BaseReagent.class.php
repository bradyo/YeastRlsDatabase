<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Reagent', 'core');

/**
 * BaseReagent
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property string $owner
 * @property text $comment
 * @property string $is_locked
 * @property User $Owner
 * 
 * @method integer getId()        Returns the current record's "id" value
 * @method string  getName()      Returns the current record's "name" value
 * @method string  getOwner()     Returns the current record's "owner" value
 * @method text    getComment()   Returns the current record's "comment" value
 * @method string  getIsLocked()  Returns the current record's "is_locked" value
 * @method User    getOwner()     Returns the current record's "Owner" value
 * @method Reagent setId()        Sets the current record's "id" value
 * @method Reagent setName()      Sets the current record's "name" value
 * @method Reagent setOwner()     Sets the current record's "owner" value
 * @method Reagent setComment()   Sets the current record's "comment" value
 * @method Reagent setIsLocked()  Sets the current record's "is_locked" value
 * @method Reagent setOwner()     Sets the current record's "Owner" value
 * 
 * @package    core
 * @subpackage model
 * @author     Brady Olsen
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseReagent extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('reagent');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             'length' => 4,
             ));
        $this->hasColumn('name', 'string', 128, array(
             'type' => 'string',
             'notnull' => true,
             'length' => 128,
             ));
        $this->hasColumn('owner', 'string', 128, array(
             'type' => 'string',
             'length' => 128,
             ));
        $this->hasColumn('comment', 'text', null, array(
             'type' => 'text',
             ));
        $this->hasColumn('is_locked', 'string', 1, array(
             'type' => 'string',
             'length' => 1,
             ));

        $this->option('type', 'INNODB');
        $this->option('charset', 'utf8');
        $this->option('collate', 'utf8_unicode_ci');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('User as Owner', array(
             'local' => 'owner',
             'foreign' => 'username'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}