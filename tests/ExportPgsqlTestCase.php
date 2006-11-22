<?php
class Doctrine_Export_Pgsql_TestCase extends Doctrine_Driver_UnitTestCase {
    public function __construct() {
        parent::__construct('pgsql');
    }
    public function testCreateDatabaseDoesNotExecuteSql() {
        try {
            $this->export->createDatabase('db');
            $this->fail();
        } catch(Doctrine_Export_Firebird_Exception $e) {
            $this->pass();
        }
    }
    public function testDropDatabaseExecutesSql() {
        try {
            $this->export->dropDatabase('db');
            $this->fail();
        } catch(Doctrine_Export_Firebird_Exception $e) {
            $this->pass();
        }
    }
    public function testAlterTableThrowsExceptionWithoutValidTableName() {
        try {
            $this->export->alterTable(0,0,array());

            $this->fail();
        } catch(Doctrine_Export_Exception $e) {
            $this->pass();
        }
    }
    public function testCreateTableThrowsExceptionWithoutValidTableName() {
        try {
            $this->export->createTable(0,array(),array());

            $this->fail();
        } catch(Doctrine_Export_Exception $e) {
            $this->pass();
        }
    }
    public function testCreateTableThrowsExceptionWithEmptyFieldsArray() {
        try {
            $this->export->createTable('sometable',array(),array());

            $this->fail();
        } catch(Doctrine_Export_Exception $e) {
            $this->pass();
        }
    }
    public function testCreateIndexExecutesSql() {
        $this->export->createIndex('sometable', 'relevancy', array('fields' => array('title' => array(), 'content' => array())));
        
        $this->assertEqual($this->adapter->pop(), 'CREATE INDEX relevancy ON sometable (title, content)');
    }

    public function testDropIndexExecutesSql() {
        $this->export->dropIndex('sometable', 'relevancy');
        
        $this->assertEqual($this->adapter->pop(), 'DROP INDEX relevancy ON sometable');
    }
    public function testDropTableExecutesSql() {
        $this->export->dropTable('sometable');
        
        $this->assertEqual($this->adapter->pop(), 'DROP TABLE sometable');
    }
}
?>
