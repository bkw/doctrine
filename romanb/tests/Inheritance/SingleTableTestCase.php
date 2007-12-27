<?php


class Doctrine_Inheritance_SingleTable_TestCase extends Doctrine_UnitTestCase
{
    public function prepareData() 
    { }

    public function prepareTables()
    {
      //$this->tables = array('STI_User');
      parent::prepareTables();
    }

    public function testMetadataTableCreation()
    {    
        $superManagerTable = $this->conn->getTable('STI_SuperManager');
        $userTable = $this->conn->getTable('STI_User');
        $managerTable = $this->conn->getTable('STI_Manager');
        $customerTable = $this->conn->getTable('STI_Customer');
        
        $this->assertTrue($superManagerTable === $userTable);
        $this->assertTrue($customerTable === $managerTable);
        $this->assertTrue($superManagerTable === $managerTable);
        $this->assertTrue($userTable === $customerTable);
        $this->assertEqual(6, count($userTable->getColumns()));
        
        $this->assertEqual(array(), $userTable->getOption('joinedParents'));
        $this->assertEqual(array(), $superManagerTable->getOption('joinedParents'));
        $this->assertEqual(array(), $managerTable->getOption('joinedParents'));
        $this->assertEqual(array(), $customerTable->getOption('joinedParents'));
        
        // check inheritance map
        $this->assertEqual(array(
                'STI_User' => array('type' => 1),
                'STI_Manager' => array('type' => 2),
                'STI_Customer' => array('type' => 3),
                'STI_SuperManager' => array('type' => 4)), $userTable->getOption('inheritanceMap'));
        
        //var_dump($superManagerTable->getComponentName());
    }
}


class STI_User extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setInheritanceType(Doctrine::INHERITANCETYPE_SINGLE_TABLE,
                array('STI_User' => array('type' => 1),
                      'STI_Manager' => array('type' => 2),
                      'STI_Customer' => array('type' => 3),
                      'STI_SuperManager' => array('type' => 4))
        );
        $this->setTableName('sti_entity');
        $this->hasColumn('sti_id as id', 'varchar', 30, array ('primary' => true));
        $this->hasColumn('sti_foo as foo', 'integer', 4, array ('notnull'=>true));
        $this->hasColumn('sti_name as name', 'varchar', 50, array ());
    }
}

class STI_Manager extends STI_User 
{
    public function setTableDefinition()
    {
        $this->hasColumn('stim_salary as salary', 'varchar', 50, array());
    }
}

class STI_Customer extends STI_User
{
    public function setTableDefinition()
    {
        $this->hasColumn('stic_bonuspoints as bonuspoints', 'varchar', 50, array());
    }
}

class STI_SuperManager extends STI_Manager
{
    public function setTableDefinition()
    {
        $this->hasColumn('stism_gosutitle as gosutitle', 'varchar', 50, array());
    }
}
