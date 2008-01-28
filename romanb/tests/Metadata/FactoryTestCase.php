<?php

class Doctrine_Metadata_Factory_TestCase extends Doctrine_UnitTestCase
{
    public function prepareData() 
    { }

    public function prepareTables()
    {
        $this->tables[] = 'Metadata_User';
        $this->tables[] = 'Metadata_Manager';
        $this->tables[] = 'Metadata_Customer';
        $this->tables[] = 'Metadata_SuperManager';
        parent::prepareTables();   
    }
    
    public function setUp()
    {
        parent::setUp();
        $this->prepareTables();
    }

    public function testMetadataSetupOnClassTableInheritanceHierarchy()
    {        
        $userClass = $this->conn->getMetadata('Metadata_User');
        $this->assertTrue($userClass instanceof Doctrine_ClassMetadata);
        $this->assertEqual('cti_user', $userClass->getTableName());
        $this->assertEqual(3, count($userClass->getFields()));
        $this->assertIdentical(array(), $userClass->getOption('parents'));
        $this->assertEqual('type', $userClass->getInheritanceOption('discriminatorColumn'));
        $this->assertEqual('integer', $userClass->getInheritanceOption('discriminatorType'));
        $this->assertIdentical(array(
              1 => 'CTI_User',
              2 => 'CTI_Manager',
              3 => 'CTI_Customer',
              4 => 'CTI_SuperManager'), $userClass->getInheritanceOption('discriminatorMap'));
        
        
        $managerClass = $this->conn->getMetadata('Metadata_Manager');
        $this->assertTrue($managerClass instanceof Doctrine_ClassMetadata);
        $this->assertIdentical(array('Metadata_User'), $managerClass->getOption('parents'));
        $this->assertEqual('cti_manager', $managerClass->getTableName());
        $this->assertEqual(4, count($managerClass->getFields()));
        $this->assertEqual('type', $managerClass->getInheritanceOption('discriminatorColumn'));
        $this->assertEqual('integer', $managerClass->getInheritanceOption('discriminatorType'));
        $this->assertIdentical(array(
              1 => 'CTI_User',
              2 => 'CTI_Manager',
              3 => 'CTI_Customer',
              4 => 'CTI_SuperManager'), $managerClass->getInheritanceOption('discriminatorMap'));
        
        
        $suManagerClass = $this->conn->getMetadata('Metadata_SuperManager');
        $this->assertTrue($suManagerClass instanceof Doctrine_ClassMetadata);
        $this->assertIdentical(array('Metadata_Manager', 'Metadata_User'), $suManagerClass->getOption('parents'));
        $this->assertEqual('cti_supermanager', $suManagerClass->getTableName());
        $this->assertEqual(5, count($suManagerClass->getFields()));
        $this->assertEqual('type', $suManagerClass->getInheritanceOption('discriminatorColumn'));
        $this->assertEqual('integer', $suManagerClass->getInheritanceOption('discriminatorType'));
        $this->assertIdentical(array(
              1 => 'CTI_User',
              2 => 'CTI_Manager',
              3 => 'CTI_Customer',
              4 => 'CTI_SuperManager'), $suManagerClass->getInheritanceOption('discriminatorMap'));
        
        //var_dump($suManagerClass->getColumns());
    }
    
    public function testMetadataSetupOnSingleTableInheritanceHierarchy()
    {        
        $userClass = $this->conn->getMetadata('Metadata_STI_User');
        $this->assertTrue($userClass instanceof Doctrine_ClassMetadata);
        $this->assertEqual('cti_user', $userClass->getTableName());
        $this->assertEqual(3, count($userClass->getFields()));
        $this->assertIdentical(array(), $userClass->getOption('parents'));
        $this->assertEqual('type', $userClass->getInheritanceOption('discriminatorColumn'));
        $this->assertEqual('integer', $userClass->getInheritanceOption('discriminatorType'));
        $this->assertIdentical(array(
              1 => 'CTI_User',
              2 => 'CTI_Manager',
              3 => 'CTI_Customer',
              4 => 'CTI_SuperManager'), $userClass->getInheritanceOption('discriminatorMap'));
        
        $managerClass = $this->conn->getMetadata('Metadata_STI_Manager');
        $this->assertTrue($managerClass instanceof Doctrine_ClassMetadata);
        $this->assertIdentical(array('Metadata_STI_User'), $managerClass->getOption('parents'));
        $this->assertEqual('cti_user', $managerClass->getTableName());
        $this->assertEqual(4, count($managerClass->getFields()));
        $this->assertEqual('type', $managerClass->getInheritanceOption('discriminatorColumn'));
        $this->assertEqual('integer', $managerClass->getInheritanceOption('discriminatorType'));
        $this->assertIdentical(array(
              1 => 'CTI_User',
              2 => 'CTI_Manager',
              3 => 'CTI_Customer',
              4 => 'CTI_SuperManager'), $managerClass->getInheritanceOption('discriminatorMap'));
        
        
        $suManagerClass = $this->conn->getMetadata('Metadata_STI_SuperManager');
        $this->assertTrue($suManagerClass instanceof Doctrine_ClassMetadata);
        $this->assertIdentical(array('Metadata_STI_Manager', 'Metadata_STI_User'), $suManagerClass->getOption('parents'));
        $this->assertEqual('cti_user', $suManagerClass->getTableName());
        $this->assertEqual(5, count($suManagerClass->getFields()));
        $this->assertEqual('type', $suManagerClass->getInheritanceOption('discriminatorColumn'));
        $this->assertEqual('integer', $suManagerClass->getInheritanceOption('discriminatorType'));
        $this->assertIdentical(array(
              1 => 'CTI_User',
              2 => 'CTI_Manager',
              3 => 'CTI_Customer',
              4 => 'CTI_SuperManager'), $suManagerClass->getInheritanceOption('discriminatorMap'));
        
        //var_dump($suManagerClass->getColumns());
    }
}


class Metadata_User extends Doctrine_Record
{    
    public static function initMetadata(Doctrine_ClassMetadata $class)
    {
        $class->setTableName('cti_user');
        $class->setInheritanceType(Doctrine::INHERITANCETYPE_JOINED,
                array('discriminatorColumn' => 'type',
                      'discriminatorType' => 'integer',
                      'discriminatorMap' => array(
                          1 => 'CTI_User',
                          2 => 'CTI_Manager',
                          3 => 'CTI_Customer',
                          4 => 'CTI_SuperManager')
                )
        );
        $class->setSubclasses(array('Metadata_Manager', 'Metadata_Customer', 'Metadata_SuperManager'));
        $class->mapField('cti_id as id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $class->mapField('cti_foo as foo', 'integer', 4);
        $class->mapField('cti_name as name', 'string', 50);
        
        //$class->setNamedQuery('findByName', 'SELECT u.* FROM User u WHERE u.name = ?');
    }
}

class Metadata_Manager extends Metadata_User 
{
    public static function initMetadata(Doctrine_ClassMetadata $class)
    {
        $class->setTableName('cti_manager');
        $class->setSubclasses(array('Metadata_SuperManager'));
        $class->mapField('ctim_salary as salary', 'varchar', 50, array());
    }
}

class Metadata_Customer extends Metadata_User
{
    public static function initMetadata(Doctrine_ClassMetadata $class)
    {
        $class->setTableName('cti_customer');
        $class->setColumn('ctic_bonuspoints as bonuspoints', 'varchar', 50, array());
    }
}

class Metadata_SuperManager extends Metadata_Manager
{
    public static function initMetadata(Doctrine_ClassMetadata $class)
    {
        $class->setTableName('cti_supermanager');
        $class->mapField('ctism_gosutitle as gosutitle', 'varchar', 50, array());
    }
}



class Metadata_STI_User extends Doctrine_Record
{    
    public static function initMetadata($class)
    {
        $class->setTableName('cti_user');
        $class->setInheritanceType(Doctrine::INHERITANCETYPE_SINGLE_TABLE,
                array('discriminatorColumn' => 'type',
                      'discriminatorType' => 'integer',
                      'discriminatorMap' => array(
                          1 => 'CTI_User',
                          2 => 'CTI_Manager',
                          3 => 'CTI_Customer',
                          4 => 'CTI_SuperManager')
                )
        );
        $class->setSubclasses(array('Metadata_STI_Manager', 'Metadata_STI_Customer', 'Metadata_STI_SuperManager'));
        $class->mapField('cti_id as id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $class->mapField('cti_foo as foo', 'integer', 4);
        $class->mapField('cti_name as name', 'string', 50);
        
        //$class->setNamedQuery('findByName', 'SELECT u.* FROM User u WHERE u.name = ?');
    }
}

class Metadata_STI_Manager extends Metadata_STI_User 
{
    public static function initMetadata($class)
    {
        $class->setTableName('cti_manager');
        $class->setSubclasses(array('Metadata_STI_SuperManager'));
        $class->mapField('ctim_salary as salary', 'varchar', 50, array());
    }
}

class Metadata_STI_Customer extends Metadata_STI_User
{
    public static function initMetadata($class)
    {
        $this->setTableName('cti_customer');
        $this->hasColumn('ctic_bonuspoints as bonuspoints', 'varchar', 50, array());
    }
}

class Metadata_STI_SuperManager extends Metadata_STI_Manager
{
    public static function initMetadata($class)
    {
        $class->setTableName('cti_supermanager');
        $class->mapField('ctism_gosutitle as gosutitle', 'varchar', 50, array());
    }
}

