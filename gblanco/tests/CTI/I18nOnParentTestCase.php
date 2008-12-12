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
 * Doctrine_CTI_I18nOnParent_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_CTI_I18nOnParent_TestCase extends Doctrine_UnitTestCase
{

    public function prepareData()
    { }

    public function prepareTables()
    {
        $this->tables = array('CTII18nOnParentTest');

        parent::prepareTables();
    }

    public function testTranslatedColumnsAreRemovedFromMainComponent()
    {
        $i = new CTII18nOnParentTest();
        
        $columns = $i->getTable()->getColumns();

        $this->assertFalse(isset($columns['title']));
        $this->assertFalse(isset($columns['name']));
    }

    public function testTranslationTableIsInitializedProperly()
    {
        $i = new CTII18nOnParentTest();
        $i->id = 1;
        $i->child_name = 'blah';
        
        $i->Translation['EN']->name = 'some name';
        $i->Translation['EN']->title = 'some title';
        $this->assertEqual($i->Translation->getTable()->getComponentName(), 'CTII18nOnParentTestParentTranslation');

        $i->Translation['FI']->name = 'joku nimi';
        $i->Translation['FI']->title = 'joku otsikko';
        $i->Translation['FI']->lang = 'FI';

        $i->save();

        $this->conn->clear();

        $t = Doctrine_Query::create()->from('CTII18nOnParentTestParentTranslation')->fetchOne();

        $this->assertEqual($t->name, 'some name');
        $this->assertEqual($t->title, 'some title');
        $this->assertEqual($t->lang, 'EN');

    }


    public function testUpdatingI18nItems()
    {
        $i = Doctrine_Query::create()->query('FROM CTII18nOnParentTest')->getFirst();

        $i->Translation['EN']->name = 'updated name';
        $i->Translation['EN']->title = 'updated title';

        $i->Translation->save();

        $this->conn->clear();

        $t = Doctrine_Query::create()->from('CTII18nOnParentTestParentTranslation')->fetchOne();

        $this->assertEqual($t->name, 'updated name');
        $this->assertEqual($t->title, 'updated title');
    }


    public function testDataFetching()
    {
        $i = Doctrine_Query::create()->from('CTII18nOnParentTest i')->innerJoin('i.Translation t INDEXBY t.lang')->orderby('t.lang')->fetchOne(array(), Doctrine::HYDRATE_ARRAY);

        $this->assertEqual($i['Translation']['EN']['name'], 'updated name');
        $this->assertEqual($i['Translation']['EN']['title'], 'updated title');
        $this->assertEqual($i['Translation']['EN']['lang'], 'EN');

        $this->assertEqual($i['Translation']['FI']['name'], 'joku nimi');
        $this->assertEqual($i['Translation']['FI']['title'], 'joku otsikko');
        $this->assertEqual($i['Translation']['FI']['lang'], 'FI');
    }
    
    public function testIndexByLangIsAttachedToNewlyCreatedCollections()
    {
    	$coll = new Doctrine_Collection('CTII18nOnParentTestParentTranslation');

        $coll['EN']['name'] = 'some name';
        
        $this->assertEqual($coll['EN']->lang, 'EN');
    }

    public function testIndexByLangIsAttachedToFetchedCollections()
    {
        $coll = Doctrine_Query::create()->from('CTII18nOnParentTestParentTranslation')->execute();

        $this->assertTrue($coll['FI']->exists());
    }
}


/* MODELS */
abstract class CTII18nOnParentTestAbstract extends Doctrine_Record
{}

class CTII18nOnParentTestParent extends CTII18nOnParentTestAbstract
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

class CTII18nOnParentTest extends CTII18nOnParentTestParent
{
    public function setTableDefinition()
    {
        $this->hasColumn('child_name', 'string', 200);
    }
}