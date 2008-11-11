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
 * Doctrine_CTII18nOnChild_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_CTII18nOnChild_TestCase extends Doctrine_UnitTestCase
{

    public function prepareData()
    { }

    public function prepareTables()
    {
        $this->tables = array('CTII18nOnChildTest');

        parent::prepareTables();
    }

    public function testTranslatedColumnsAreRemovedFromMainComponent()
    {
        $i = new CTII18nOnChildTest();
        
        $columns = $i->getTable()->getColumns();

        $this->assertFalse(isset($columns['title']));
        $this->assertFalse(isset($columns['name']));
    }

    public function testTranslationTableIsInitializedProperly()
    {
        $i = new CTII18nOnChildTest();
        $i->id = 1;
        $i->parent_name = 'blah';
        
        $i->Translation['EN']->name = 'some name';
        $i->Translation['EN']->title = 'some title';
        $this->assertEqual($i->Translation->getTable()->getComponentName(), 'CTII18nOnChildTestTranslation');

        $i->Translation['FI']->name = 'joku nimi';
        $i->Translation['FI']->title = 'joku otsikko';
        $i->Translation['FI']->lang = 'FI';

        $i->save();

        $this->conn->clear();

        $t = Doctrine_Query::create()->from('CTII18nOnChildTestTranslation')->fetchOne();

        $this->assertEqual($t->name, 'some name');
        $this->assertEqual($t->title, 'some title');
        $this->assertEqual($t->lang, 'EN');

    }


    public function testUpdatingI18nItems()
    {
        $i = Doctrine_Query::create()->query('FROM CTII18nOnChildTest')->getFirst();

        $i->Translation['EN']->name = 'updated name';
        $i->Translation['EN']->title = 'updated title';

        $i->Translation->save();

        $this->conn->clear();

        $t = Doctrine_Query::create()->from('CTII18nOnChildTestTranslation')->fetchOne();

        $this->assertEqual($t->name, 'updated name');
        $this->assertEqual($t->title, 'updated title');
    }


    public function testDataFetching()
    {
        $i = Doctrine_Query::create()->from('CTII18nOnChildTest i')->innerJoin('i.Translation t INDEXBY t.lang')->orderby('t.lang')->fetchOne(array(), Doctrine::HYDRATE_ARRAY);

        $this->assertEqual($i['Translation']['EN']['name'], 'updated name');
        $this->assertEqual($i['Translation']['EN']['title'], 'updated title');
        $this->assertEqual($i['Translation']['EN']['lang'], 'EN');

        $this->assertEqual($i['Translation']['FI']['name'], 'joku nimi');
        $this->assertEqual($i['Translation']['FI']['title'], 'joku otsikko');
        $this->assertEqual($i['Translation']['FI']['lang'], 'FI');
    }
    
    public function testIndexByLangIsAttachedToNewlyCreatedCollections()
    {
    	$coll = new Doctrine_Collection('CTII18nOnChildTestTranslation');

        $coll['EN']['name'] = 'some name';
        
        $this->assertEqual($coll['EN']->lang, 'EN');
    }

    public function testIndexByLangIsAttachedToFetchedCollections()
    {
        $coll = Doctrine_Query::create()->from('CTII18nOnChildTestTranslation')->execute();

        $this->assertTrue($coll['FI']->exists());
    }
}


/* MODELS */
abstract class CTII18nOnChildTestAbstract extends Doctrine_Record
{}

class CTII18nTestOnChildParent extends CTII18nOnChildTestAbstract
{
    public function setTableDefinition()
    {
        $this->hasColumn('parent_name', 'string', 200);
    }
}

class CTII18nOnChildTest extends CTII18nTestOnChildParent
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 200);
        $this->hasColumn('title', 'string', 200);
    }
    public function setUp()
    {
        $this->actAs('I18n', array('fields' => array('name', 'title')));
    }
}