<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('MouseStrain', 'core');

/**
 * BaseMouseStrain
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property string $owner
 * @property string $background
 * @property string $genotype
 * @property string $genotype_short
 * @property string $genotype_unique
 * @property text $comment
 * @property string $is_locked
 * @property User $Owner
 * 
 * @method integer     getId()              Returns the current record's "id" value
 * @method string      getName()            Returns the current record's "name" value
 * @method string      getOwner()           Returns the current record's "owner" value
 * @method string      getBackground()      Returns the current record's "background" value
 * @method string      getGenotype()        Returns the current record's "genotype" value
 * @method string      getGenotypeShort()   Returns the current record's "genotype_short" value
 * @method string      getGenotypeUnique()  Returns the current record's "genotype_unique" value
 * @method text        getComment()         Returns the current record's "comment" value
 * @method string      getIsLocked()        Returns the current record's "is_locked" value
 * @method User        getOwner()           Returns the current record's "Owner" value
 * @method MouseStrain setId()              Sets the current record's "id" value
 * @method MouseStrain setName()            Sets the current record's "name" value
 * @method MouseStrain setOwner()           Sets the current record's "owner" value
 * @method MouseStrain setBackground()      Sets the current record's "background" value
 * @method MouseStrain setGenotype()        Sets the current record's "genotype" value
 * @method MouseStrain setGenotypeShort()   Sets the current record's "genotype_short" value
 * @method MouseStrain setGenotypeUnique()  Sets the current record's "genotype_unique" value
 * @method MouseStrain setComment()         Sets the current record's "comment" value
 * @method MouseStrain setIsLocked()        Sets the current record's "is_locked" value
 * @method MouseStrain setOwner()           Sets the current record's "Owner" value
 * 
 * @package    core
 * @subpackage model
 * @author     Brady Olsen
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseMouseStrain extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('mouse_strain');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             'length' => 4,
             ));
        $this->hasColumn('name', 'string', 128, array(
             'type' => 'string',
             'notnull' => true,
             'unique' => true,
             'length' => 128,
             ));
        $this->hasColumn('owner', 'string', 128, array(
             'type' => 'string',
             'length' => 128,
             ));
        $this->hasColumn('background', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('genotype', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('genotype_short', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('genotype_unique', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
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