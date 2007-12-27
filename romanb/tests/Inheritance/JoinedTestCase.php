<?php

class Doctrine_Inheritance_Joined_TestCase extends Doctrine_UnitTestCase
{
    public function prepareData() 
    { }

    public function prepareTables()
    {
      //$this->tables = array('STI_User');
      parent::prepareTables();
    }

    public function testTicket()
    {
        //$table = $this->conn->getTable2('CTI_User');
        //echo get_class($table);
        //echo "<br />";
        //var_dump($table->getColumns());
        //$this->fail('foo');
        
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
        $this->assertEqual(3, count($userTable->getColumns()));
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
                
        
        //var_dump($managerTable->getColumns());
    }
}


class CTI_User extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setInheritanceType(Doctrine::INHERITANCETYPE_JOINED,
                array('CTI_User' => array('type' => 1),
                      'CTI_Manager' => array('type' => 2),
                      'CTI_Customer' => array('type' => 3),
                      'CTI_SuperManager' => array('type' => 4))
        );
        $this->setTableName('cti_user');
        $this->hasColumn('sti_id as id', 'varchar', 30, array ('primary' => true, 'autoincrement' => true));
        $this->hasColumn('sti_foo as foo', 'integer', 4, array ('notnull'=>true));
        $this->hasColumn('sti_name as name', 'varchar', 50, array ());
    }
}

class CTI_Manager extends CTI_User 
{
    public function setTableDefinition()
    {
        $this->setTableName('cti_manager');
        $this->hasColumn('stim_salary as salary', 'varchar', 50, array());
    }
}

class CTI_Customer extends CTI_User
{
    public function setTableDefinition()
    {
        $this->setTableName('cti_customer');
        $this->hasColumn('stic_bonuspoints as bonuspoints', 'varchar', 50, array());
    }
}

class CTI_SuperManager extends CTI_Manager
{
    public function setTableDefinition()
    {
        $this->setTableName('cti_supermanager');
        $this->hasColumn('stism_gosutitle as gosutitle', 'varchar', 50, array());
    }
}
