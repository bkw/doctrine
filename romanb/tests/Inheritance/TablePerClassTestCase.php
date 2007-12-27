<?php

/**
 * Concrete Table Inheritance mapping tests.
 */
class Doctrine_Inheritance_TablePerClass_TestCase extends Doctrine_UnitTestCase
{
    public function prepareData() 
    { }

    public function prepareTables()
    {
      //$this->tables = array('STI_User');
      parent::prepareTables();
    }

    public function testMetadataTableSetup()
    {
        //$table = $this->conn->getTable2('CCTI_User');
        //echo get_class($table);
        //echo "<br />";
        //var_dump($table->getColumns());
        //$this->fail('foo');
        
        $supMngrTable = $this->conn->getTable('CCTI_SuperManager');
        $usrTable = $this->conn->getTable('CCTI_User');
        $mngrTable = $this->conn->getTable('CCTI_Manager');
        $customerTable = $this->conn->getTable('CCTI_Customer');
        $this->assertTrue($supMngrTable !== $usrTable);
        $this->assertTrue($supMngrTable !== $mngrTable);
        $this->assertTrue($usrTable !== $mngrTable);
        $this->assertTrue($customerTable !== $usrTable);
        
        $this->assertEqual(3, count($usrTable->getColumns()));
        $this->assertEqual(4, count($mngrTable->getColumns()));
        $this->assertEqual(4, count($customerTable->getColumns()));
        $this->assertEqual(5, count($supMngrTable->getColumns()));
        
        $this->assertEqual('ccti_user', $usrTable->getTableName());
        $this->assertEqual('ccti_manager', $mngrTable->getTableName());
        $this->assertEqual('ccti_customer', $customerTable->getTableName());
        $this->assertEqual('ccti_supermanager', $supMngrTable->getTableName());
        
        //var_dump($mngrTable->getColumns());
    }
}


class CCTI_User extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setInheritanceType(Doctrine::INHERITANCETYPE_TABLE_PER_CLASS);
        $this->setTableName('ccti_user');
        $this->hasColumn('ccti_id as id', 'varchar', 30, array ('primary' => true));
        $this->hasColumn('ccti_foo as foo', 'integer', 4, array ('notnull'=>true));
        $this->hasColumn('ccti_name as name', 'varchar', 50, array ());
    }
}

class CCTI_Manager extends CCTI_User 
{
    public function setTableDefinition()
    {
        $this->setTableName('ccti_manager');
        $this->hasColumn('ccti_salary as salary', 'varchar', 50, array());
    }
}

class CCTI_Customer extends CCTI_User
{
    public function setTableDefinition()
    {
        $this->setTableName('ccti_customer');
        $this->hasColumn('ccti_bonuspoints as bonuspoints', 'varchar', 50, array());
    }
}

class CCTI_SuperManager extends CCTI_Manager
{
    public function setTableDefinition()
    {
        $this->setTableName('ccti_supermanager');
        $this->hasColumn('ccti_gosutitle as gosutitle', 'varchar', 50, array());
    }
}
