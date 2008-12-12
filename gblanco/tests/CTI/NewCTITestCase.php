<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

/**
 * Doctrine_CTI_NewCTI_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_CTI_NewCTI_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    { }
    public function prepareData()
    { }
    
    public function testExportGeneratesAllInheritedTables()
    {
        $sql = $this->conn->export->exportClassesSql(array('CTINTest', 'CTINTest2', 'CTINTestOneToManyRelated', 'NNoIdTestParent', 'NNoIdTestChild', 'CTINTestOneForeignRelated', 'CTINTestOneLocalRelated'));

        $this->assertEqual($sql[0], 'CREATE TABLE n_no_id_test_parent (myid INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(2147483647))');
        $this->assertEqual($sql[1], 'CREATE TABLE n_no_id_test_child (myid INTEGER, child_column VARCHAR(2147483647), PRIMARY KEY(myid))');
        $this->assertEqual($sql[2], 'CREATE TABLE c_t_i_n_test_parent4 (id INTEGER, age INTEGER, PRIMARY KEY(id))');
        $this->assertEqual($sql[3], 'CREATE TABLE c_t_i_n_test_parent3 (id INTEGER, added INTEGER, PRIMARY KEY(id))');
        $this->assertEqual($sql[4], 'CREATE TABLE c_t_i_n_test_parent2 (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(200), verified INTEGER, type INTEGER, related_id INTEGER)');

        foreach ($sql as $query) {
            $this->conn->exec($query);
        }
    }

    public function testCreateRecord()
    {
      $rec = new CTINTest();
      $rec->name = 'name 1';
      $rec->verified = true;
      $rec->age = 3;
      $rec->added = true;
      $rec->child_u = 'child u1';
      $rec->child_s = 'child s1';
      $rec->save();

      $rec = new CTINTest2();
      $rec->name = 'name 2';
      $rec->verified = true;
      $rec->age = 4;
      $rec->child_u2 = 'child u22';
      $rec->child_s = 'child s2';
      $rec->added = false;
      $rec->save();
    }

    public function testFind()
    {
       $this->conn->clear();
       $q = new Doctrine_Query();
       $q->from('CTINTestParent2 c')->where("c.id = 1");

       $this->assertEqual($q->getSql(),'SELECT c.id AS c__id, c.name AS c__name, c.verified AS c__verified, c.type AS c__type, c.related_id AS c__related_id, c2.added AS c2__added, c3.age AS c3__age, c4.child_u AS c4__child_u, c4.child_s AS c4__child_s, c5.child_u2 AS c5__child_u2, c5.child_s AS c5__child_s FROM c_t_i_n_test_parent2 c LEFT JOIN c_t_i_n_test_parent3 c2 ON c.id = c2.id LEFT JOIN c_t_i_n_test_parent4 c3 ON c.id = c3.id LEFT JOIN c_t_i_n_test c4 ON c.id = c4.id LEFT JOIN c_t_i_n_test2 c5 ON c.id = c5.id WHERE c.id = 1');

       $coll = $q->execute();
       $rec = $coll->getFirst();
       $this->assertEqual(get_class($rec), 'CTINTest');
       $data = $rec->toArray();
       $this->assertEqual($data['name'], 'name 1');
       $this->assertEqual($data['added'], true);
       $this->assertEqual($data['child_u'], 'child u1');
       $this->assertEqual($data['child_s'], 'child s1');
       $this->assertEqual(isset($data['child_u2']), false);
    }


    public function testGetRightClass()
    {
      $this->conn->clear();
      $record = $this->conn->getTable('CTINTestParent2')->find(2);
      $this->isRecordTwo($record);
    }

    public function isRecordTwo($record)
    {
      $this->assertEqual(get_class($record), 'CTINTest2');
      $data = $record->toArray();
      $this->assertEqual($data['name'], 'name 2');
      $this->assertEqual($data['added'], false);
      $this->assertEqual($data['child_u2'], 'child u22');
      $this->assertEqual($data['child_s'], 'child s2');
      $this->assertEqual(isset($data['child_u']), false);
    }
    
    public function testForeignRelated()
    {
      $rec = new CTINTestOneForeignRelated;
      $rec->name = 'name fr';
      $rec->Ctp = $this->conn->getTable('CTINTestParent2')->find(2);
      $rec->save();

      $this->conn->clear();
      $record = $this->conn->getTable('CTINTestOneForeignRelated')->find(1);
      $this->isRecordTwo($record->Ctp);
    }

    public function testLocalRelated()
    {
      $rec = new CTINTestOneLocalRelated;
      $rec->name = 'name lo';
      $rec->Ctp = $this->conn->getTable('CTINTestParent2')->find(2);
      $rec->save();

      $this->conn->clear();
      $record = $this->conn->getTable('CTINTestOneLocalRelated')->find(1);
      $this->isRecordTwo($record->Ctp);
    }  

    public function testRelated()
    {
      $rec = $this->conn->getTable('CTINTestParent2')->find(2);
      $this->assertEqual($rec->Frelated->name , 'name fr');
      $this->assertEqual($rec->Lrelated->name , 'name lo');
    }
}


abstract class CTINAbstractBase extends Doctrine_Record
{ }


class CTINTestParent1 extends CTINAbstractBase
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 200);
    }
}


class CTINTestParent2 extends CTINTestParent1
{
    public function setTableDefinition()
    {
    	parent::setTableDefinition();

      $this->hasColumn('verified', 'boolean', 1);
      $this->hasColumn('type', 'integer', 2);
      $this->hasColumn('related_id', 'integer', 4);
      $this->setJoinedInheritanceMap(array(
          'CTINTest'=>array('type'=>1),
          'CTINTest2'=>array('type'=>2)
        ));
    }

    public function setUp()
    {
        $this->hasOne('CTINTestOneForeignRelated as Frelated', array('local' => 'related_id', 'foreign' => 'id'));
        $this->hasOne('CTINTestOneLocalRelated as Lrelated',   array('local' => 'id', 'foreign' => 'cti_id'));
    }

}


class CTINTestParent3 extends CTINTestParent2
{
    public function setTableDefinition()
    {
        $this->hasColumn('added', 'integer');
    }
}


class CTINTestParent4 extends CTINTestParent3
{
    public function setTableDefinition()
    {
        $this->hasColumn('age', 'integer', 4);
    }
}


class CTINTest extends CTINTestParent4
{
    public function setTableDefinition()
    {
        $this->hasColumn('child_u', 'string', 200);
        $this->hasColumn('child_s', 'string', 200);
    }
}


class CTINTest2 extends CTINTestParent4
{

    public function setTableDefinition()
    {
        $this->hasColumn('child_u2', 'string', 200);
        $this->hasColumn('child_s', 'string', 200);
    }
}


class CTINTestOneToManyRelated extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string');
        $this->hasColumn('cti_id', 'integer');
    }
    
    public function setUp()
    {
        $this->hasMany('CTINTest', array('local' => 'cti_id', 'foreign' => 'id'));
    }
}


class CTINTestOneForeignRelated extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string');
    }
    
    public function setUp()
    {
        $this->hasOne('CTINTestParent2 as Ctp', array('local' => 'id', 'foreign' => 'related_id'));
    }
}


class CTINTestOneLocalRelated extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string');
        $this->hasColumn('cti_id', 'integer');
    }
    
    public function setUp()
    {
        $this->hasOne('CTINTestParent2 as Ctp', array('local' => 'cti_id', 'foreign' => 'id'));
    }
}


class NNoIdTestParent extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('myid', 'integer', null, array('autoincrement' => true, 'primary' => true));
        $this->hasColumn('name', 'string');
    }
}


class NNoIdTestChild extends NNoIdTestParent
{
    public function setTableDefinition()
    {
        $this->hasColumn('child_column', 'string');
    }
}
