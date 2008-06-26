<?php
/*
 * Created on Jun 26, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class Doctrine_Ticket_1175_TestCase extends Doctrine_UnitTestCase
{
	public function prepareTables()
    {
        $this->tables[] = 'gImage';

        $this->tables[] = 'gUser';
        $this->tables[] = 'gUserImage';
        $this->tables[] = 'gUserFile';

        $this->tables[] = 'gBlog';
        $this->tables[] = 'gBlogImage';
        $this->tables[] = 'gBlogFile';

        parent::prepareTables();
    }

    public function testLeftJoinToInheritanceChildTable()
    {
        $u = new gUser();
        $u->first_name = 'Some User';
        $u->save();

        $img = new gUserImage();
        $img->filename = 'user image 1';
        $u->Images[] = $img;
        
        $img = new gUserImage();
        $img->filename = 'user image 2';
        $u->Images[] = $img;
      
        $b = new gBlog();
        $b->title = 'First Blog';

        $img = new gBlogImage();
        $img->filename = 'blog image 1';
        $b->Images[] = $img;

        $b->save();

        $q = new Doctrine_Query();
        $u = $q->from('gUser u')->leftJoin('u.Images i')->leftJoin('u.Files f')->where('u.id = ?',array(1))->fetchOne();
        $this->assertEqual(count($u->Images),2);
    }
}

class gImage extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('owner_id', 'integer', 4);
        $this->hasColumn('filename', 'string', 64);
        $this->hasColumn('otype','integer',4);
        $this->setAttribute(Doctrine::ATTR_EXPORT, Doctrine::EXPORT_ALL ^ Doctrine::EXPORT_CONSTRAINTS);

        $this->setSubClasses(array('gUserImage' => array('otype' => 1),'gBlogImage' => array('otype' => 2)));
    }
}

class gUserImage extends gImage
{
    public function setUp()
    {
        parent::setUp();
        $this->hasOne('gUser as User', array('local' => 'owner_id','foreign' => 'id'));
    }
}

class gBlogImage extends gImage
{
    public function setUp()
    {
        parent::setUp();
        $this->hasOne('gBlog as Blog', array('local' => 'owner_id','foreign' => 'id'));
    }
	
}

class gFile extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('owner_id', 'integer', 4);
        $this->hasColumn('filename', 'string', 64);
        $this->hasColumn('otype','integer',4);
        $this->setAttribute(Doctrine::ATTR_EXPORT, Doctrine::EXPORT_ALL ^ Doctrine::EXPORT_CONSTRAINTS);

        $this->setSubClasses(array('gUserFile' => array('otype' => 1),'gBlogFile' => array('otype' => 2)));
    }
}

class gUserFile extends gFile
{
    public function setUp()
    {
        parent::setUp();
        $this->hasOne('gUser as User', array('local' => 'owner_id','foreign' => 'id'));
    }
}

class gBlogFile extends gFile
{
    public function setUp()
    {
        parent::setUp();
        $this->hasOne('gBlog as Blog', array('local' => 'owner_id','foreign' => 'id'));
    }
}

class gBlog extends Doctrine_Record
{
	public function setTableDefinition()
    {
        $this->hasColumn('id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('title', 'string', 128);
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('gBlogImage as Images', array('local' => 'id','foreign' => 'owner_id'));
        $this->hasMany('gBlogFile as Files', array('local' => 'id','foreign' => 'owner_id'));
    }

}

class gUser extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
        $this->hasColumn('first_name', 'string', 128);
        $this->hasColumn('last_name', 'string', 128);
    }    

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('gUserImage as Images', array('local' => 'id','foreign' => 'owner_id'));
        $this->hasMany('gUserFile as Files', array('local' => 'id','foreign' => 'owner_id'));
    }
}
