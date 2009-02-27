<?php
class Doctrine_Ticket_1116_TestCase extends Doctrine_UnitTestCase
{
	public function setUp()
	{
		//switch to a real db to trigger the Exception
		$this->dbh = new Doctrine_Adapter_Mock('mysql');
		//$this->dbh = new PDO("mysql:host=localhost;dbname=testing", 'root', 'password');

		$this->conn = Doctrine_Manager::getInstance()->openConnection($this->dbh);
		$this->conn->export->exportClasses(array('Ticket_1116_User'));
	}


	public function testTicket()
	{
		$q = new Doctrine_Query();
		$q->select('s.*')
		  ->from('Ticket_1116_User s')
		  ->where('s.username = ?', array('test'));

		// to see the error switch dbh to a real db, the next line will trigger the error
		$test = $q->fetchOne();  //will only fail with "real" mysql
		$this->assertFalse($test);

		$sql    = $q->getSql(); // just getSql()?!?! and it works ? the params are ok after this call
		$params = $q->getParams();
		$this->assertEqual(count($params), 2); // now we have array('test',null) very strange .....

		$this->assertEqual($sql, "SELECT u.id AS u__id, u.username AS u__username, u.deleted AS u__deleted FROM user u WHERE u.username = ? AND (u.deleted = 0 OR u.deleted IS NULL)");
//		$this->assertEqual($params, array('test', false));

		//now also this works! (always works witch mock only fails with mysql)
		$test = $q->fetchOne();
		$this->assertFalse($test);
	}
}


class Ticket_1116_User extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->setTableName('user');
		$this->hasColumn('id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
		$this->hasColumn('username', 'string', 255);
	}


	public function setUp()
	{
		parent::setUp();
		$softdelete0 = new Doctrine_Template_SoftDelete();
		$this->actAs($softdelete0);
	}
}