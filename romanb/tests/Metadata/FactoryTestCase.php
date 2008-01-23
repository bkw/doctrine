<?php

class Doctrine_Metadata_Factory_TestCase extends Doctrine_UnitTestCase
{
    public function prepareData() 
    { }

    public function prepareTables()
    {
        $this->tables[] = 'Metadata_User';
        //$this->tables[] = 'Manager';
        //$this->tables[] = 'Customer';
        //$this->tables[] = 'SuperManager';
        parent::prepareTables();   
    }
    
    public function setUp()
    {
        parent::setUp();
        $this->prepareTables();
    }

    public function testMetadataSetup()
    {        
        $userClass = $this->conn->getMetadata('Metadata_User');
        $this->assertTrue($userClass instanceof Doctrine_MetadataClass);
        $this->assertEqual('cti_user', $userClass->getTableName());
        $this->assertEqual(4, count($userClass->getColumns()));
        $this->assertIdentical(array(), $userClass->getOption('parents'));
        
        
        $managerClass = $this->conn->getMetadata('Metadata_Manager');
        $this->assertTrue($managerClass instanceof Doctrine_MetadataClass);
        $this->assertIdentical(array('Metadata_User'), $managerClass->getOption('parents'));
        $this->assertEqual('cti_manager', $managerClass->getTableName());
        $this->assertEqual(4, count($managerClass->getFields()));
        
        
        $suManagerClass = $this->conn->getMetadata('Metadata_SuperManager');
        $this->assertTrue($suManagerClass instanceof Doctrine_MetadataClass);
        $this->assertIdentical(array('Metadata_Manager', 'Metadata_User'), $suManagerClass->getOption('parents'));
        $this->assertEqual('cti_manager', $suManagerClass->getTableName());
        $this->assertEqual(4, count($suManagerClass->getFields()));
        
        var_dump($suManagerClass->getColumns());
        
        /*
        $suManagerTable = $this->conn->getTable('CTI_SuperManager');
        $userTable = $this->conn->getTable('CTI_User');
        $customerTable = $this->conn->getTable('CTI_Customer');
        $managerTable = $this->conn->getTable('CTI_Manager');
        $this->assertTrue($suManagerTable !== $userTable);
        $this->assertTrue($suManagerTable !== $customerTable);
        $this->assertTrue($userTable !== $customerTable);
        $this->assertTrue($managerTable !== $suManagerTable);
        
        // expected column counts
        $this->assertEqual(2, count($suManagerTable->getColumns()));
        $this->assertEqual(4, count($userTable->getColumns()));
        $this->assertEqual(2, count($managerTable->getColumns()));
        $this->assertEqual(2, count($customerTable->getColumns()));
        
        // expected table names
        $this->assertEqual('cti_user', $userTable->getTableName());
        $this->assertEqual('cti_manager', $managerTable->getTableName());
        $this->assertEqual('cti_customer', $customerTable->getTableName());
        $this->assertEqual('cti_supermanager', $suManagerTable->getTableName());
        
        // expected joined parents option
        $this->assertEqual(array(), $userTable->getOption('joinedParents'));
        $this->assertEqual(array('CTI_User'), $managerTable->getOption('joinedParents'));
        $this->assertEqual(array('CTI_User'), $customerTable->getOption('joinedParents'));
        $this->assertEqual(array('CTI_Manager', 'CTI_User'), $suManagerTable->getOption('joinedParents'));
        
        // check inheritance map
        $this->assertEqual(array(
                'CTI_User' => array('type' => 1),
                'CTI_Manager' => array('type' => 2),
                'CTI_Customer' => array('type' => 3),
                'CTI_SuperManager' => array('type' => 4)), $userTable->getOption('inheritanceMap'));
                
        
        //$this->assertEqual(array('CTI_User', 'CTI_Manager', ''))
        */
    }
}


class Metadata_User extends Doctrine_Record
{    
    public static function initMetadata(Doctrine_MetadataClass $class)
    {
        $class->setInheritanceType(Doctrine::INHERITANCETYPE_JOINED,
                array('CTI_User' => array('type' => 1),
                      'CTI_Manager' => array('type' => 2),
                      'CTI_Customer' => array('type' => 3),
                      'CTI_SuperManager' => array('type' => 4))
        );
        //$class->setDiscriminatorValue(1);
        
        $class->setTableName('cti_user');
        $class->mapField('cti_id as id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $class->mapField('cti_foo as foo', 'integer', 4);
        $class->mapField('cti_name as name', 'string', 50);
        $class->mapField('type', 'integer', 4);
        
        //$class->setNamedQuery('findByName', 'SELECT u.* FROM User u WHERE u.name = ?');
    }
}

class Metadata_Manager extends Metadata_User 
{
    public static function initMetadata(Doctrine_MetadataClass $class)
    {
        $class->setTableName('cti_manager');
        $class->mapField('ctim_salary as salary', 'varchar', 50, array());
    }
}
/*
class Customer extends CTI_User
{
    public function setTableDefinition()
    {
        $this->setTableName('cti_customer');
        $this->hasColumn('ctic_bonuspoints as bonuspoints', 'varchar', 50, array());
    }
}
*/
class Metadata_SuperManager extends Metadata_Manager
{
    public static function initMetadata(Doctrine_MetadataClass $class)
    {
        $class->setTableName('cti_supermanager');
        $class->mapField('ctism_gosutitle as gosutitle', 'varchar', 50, array());
    }
}
