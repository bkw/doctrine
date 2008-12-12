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
 * Doctrine_CTI_SoftDeleteOnChild_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_CTI_SoftDeleteOnChild_TestCase extends Doctrine_UnitTestCase
{

    public function prepareData()
    { }

    public function prepareTables()
    {
        $this->tables = array('CTISoftDeleteOnChildTest');

        parent::prepareTables();
    }
    
    public function testSoftDeleteTableIsInitializedProperly()
    {
        Doctrine_Manager::getInstance()->setAttribute('use_dql_callbacks', true);
        
        $i1 = new CTISoftDeleteOnChildTest();
        $i1->parent_name = 'blah1';        
        $i1->name = 'some name1';
        $i1->title = 'some title1';
        $i1->save();
        
        $i2 = new CTISoftDeleteOnChildTest();
        $i2->parent_name = 'blah2';        
        $i2->name = 'some name2';
        $i2->title = 'some title2';
        $i2->save();
        
        $i3 = new CTISoftDeleteOnChildTest();
        $i3->parent_name = 'blah3';        
        $i3->name = 'some name3';
        $i3->title = 'some title3';
        $i3->save();
        
        $i4 = new CTISoftDeleteOnChildTest();
        $i4->parent_name = 'blah4';        
        $i4->name = 'some name4';
        $i4->title = 'some title4';
        $i4->save();
        
        $i3->delete();
        
        Doctrine_Manager::getInstance()->setAttribute('use_dql_callbacks', false);
    }

    public function testDoctrineQueryIsFilteredWithDeleteFlagCondition()
    {
        Doctrine_Manager::getInstance()->setAttribute('use_dql_callbacks', true);
        $q = Doctrine_Query::create()
                    ->from('CTISoftDeleteOnChildTest s')
                    ->where('s.parent_name = ?', array('blah3'));

        $this->assertEqual($q->getSql(), 'SELECT c.id AS c__id, c2.parent_name AS c__parent_name, c.name AS c__name, c.title AS c__title, c.deleted_at AS c__deleted_at FROM c_t_i_soft_delete_on_child_test c INNER JOIN c_t_i_soft_delete_on_child_parent c2 ON c.id = c2.id WHERE c2.parent_name = ? AND (c.deleted_at IS NULL)');
        $params = $q->getFlattenedParams();
        $this->assertEqual(count($params), 1);
        $this->assertEqual($params[0], 'blah3');

        $test = $q->fetchOne();
        $this->assertFalse($test);
        Doctrine_Manager::getInstance()->setAttribute('use_dql_callbacks', false);
    }

    public function testUpdatingSoftDeleteItems()
    {
        $i = Doctrine_Query::create()->query('FROM CTISoftDeleteOnChildTest')->getFirst();

        $i->parent_name = 'updated name';
        $i->title = 'updated title';

        $i->save();

        $this->conn->clear();

        $t = Doctrine_Query::create()->from('CTISoftDeleteOnChildTest')->fetchOne();

        $this->assertEqual($t->parent_name, 'updated name');
        $this->assertEqual($t->title, 'updated title');
    }
    
    public function testSoftDeleteRecordStillExists()
    {
        // We do not turn on callbacks, since we want to know if the record is still in database
        $q = Doctrine_Query::create()->query('FROM CTISoftDeleteOnChildTest');
        
        $this->assertEqual(4, $q->count());
    }
}


/* MODELS */
abstract class CTISoftDeleteOnChildTestAbstract extends Doctrine_Record
{}

class CTISoftDeleteOnChildParent extends CTISoftDeleteOnChildTestAbstract
{
    public function setTableDefinition()
    {
        $this->hasColumn('parent_name', 'string', 200);
    }
}

class CTISoftDeleteOnChildTest extends CTISoftDeleteOnChildParent
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 200);
        $this->hasColumn('title', 'string', 200);
    }
    public function setUp()
    {
        $this->actAs('SoftDelete');
    }
}