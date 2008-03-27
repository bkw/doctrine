<?php
require_once 'lib/DoctrineTestInit.php';
 
class Orm_Component_TestTest extends Doctrine_OrmTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures('forum', 'common', array('users', 'admins'));
    }
    
    public function testTest()
    {
        $this->assertEquals(0, 0);
    }
    
    public function testFixture()
    {
        $forumUsers = $this->sharedFixture['connection']->query("FROM ForumUser u");
        $this->assertEquals(2, count($forumUsers));
        $forumUsers[0]->delete();
        unset($forumUsers[0]);
        $this->assertEquals(1, count($forumUsers));
    }
    
    public function testFixture2()
    {
        $forumUsers = $this->sharedFixture['connection']->query("FROM ForumUser u");
        $this->assertEquals(2, count($forumUsers));
    }
    
    public function testFixture3()
    {
        $forumAdmins = $this->sharedFixture['connection']->query("FROM ForumAdministrator adm");
        $this->assertEquals(1, count($forumAdmins));
        $forumAdmins[0]->delete();
    }
}