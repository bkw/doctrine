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
 * Doctrine_Data_Import_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Data_Import_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'User';
        $this->tables[] = 'Phonenumber';
        $this->tables[] = 'Album';
        $this->tables[] = 'I18nTest';
        $this->tables[] = 'ImportNestedSet';
        parent::prepareTables();
    }
    
    public function testInlineMany()
    {
        $yml = <<<END
---
User: 
  User_1: 
    name: jwage
    password: changeme
    Phonenumber: 
      Phonenumber_1: 
        phonenumber: 6155139185
END;
        try {
            file_put_contents('test.yml', $yml);
            Doctrine::loadData('test.yml');

            $this->conn->clear();

            $query = new Doctrine_Query();
            $query->from('User u, u.Phonenumber')
                  ->where('u.name = ?', 'jwage');

            $user = $query->execute()->getFirst();
            
            $this->assertEqual($user->name, 'jwage');
            $this->assertEqual($user->Phonenumber->count(), 1);
            $this->assertEqual($user->Phonenumber[0]->phonenumber, '6155139185');
            
            $data = new Doctrine_Data();
            $data->exportData('test.yml', 'yml', array('User', 'Phonenumber'));

            $array = Doctrine_Parser::load('test.yml', 'yml');
            $this->assertTrue(isset($array['Phonenumber']['Phonenumber_1']['phonenumber']));
            $this->assertTrue(isset($array['Phonenumber']['Phonenumber_1']['Entity']));
            $this->assertTrue(isset($array['User']['User_4']['name']));
            $this->assertTrue(isset($array['User']['User_4']['Email']));
            
            $this->pass();
        } catch (Exception $e) {
            $this->fail();
        }

        unlink('test.yml');
    }

    public function testInlineOne()
    {
        $yml = <<<END
---
Album:
  Album_1:
    name: zYne- Christmas Album
    User:
      name: zYne-
      password: changeme
END;
        try {
            file_put_contents('test.yml', $yml);
            Doctrine::loadData('test.yml');

            $this->conn->clear();

            $query = new Doctrine_Query();
            $query->from('User u, u.Album a, a.User u2')
                  ->where('u.name = ?', 'zYne-');

            $user = $query->execute()->getFirst();

            $this->assertEqual($user->name, 'zYne-');
            $this->assertEqual($user->Album->count(), 1);
            $this->assertEqual($user->Album[0]->name, 'zYne- Christmas Album');

            $this->pass();
        } catch (Exception $e) {
            $this->fail();
        }
        unlink('test.yml');
    }

    public function testNormalMany()
    {
        $yml = <<<END
---
User: 
  User_1: 
    name: jwage2
    password: changeme
    Phonenumber: [Phonenumber_1, Phonenumber_2]
Phonenumber:
  Phonenumber_1:
    phonenumber: 6155139185
  Phonenumber_2:
    phonenumber: 6153137679
END;
        try {
            file_put_contents('test.yml', $yml);
            Doctrine::loadData('test.yml');

            $this->conn->clear();

            $query = new Doctrine_Query();
            $query->from('User u, u.Phonenumber')
                  ->where('u.name = ?', 'jwage2');

            $user = $query->execute()->getFirst();

            $this->assertEqual($user->name, 'jwage2');
            $this->assertEqual($user->Phonenumber->count(), 2);
            $this->assertEqual($user->Phonenumber[0]->phonenumber, '6155139185');
            $this->assertEqual($user->Phonenumber[1]->phonenumber, '6153137679');

            $this->pass();
        } catch (Exception $e) {
            $this->fail();
        }
        unlink('test.yml');
    }

    public function testI18nImport()
    {
        $yml = <<<END
---
I18nTest:
  I18nTest_1:
    id: 1234
    Translation:
      en:
        name: english name
        title: english title
      fr:
        name: french name
        title: french title
END;
        try {
            file_put_contents('test.yml', $yml);
            Doctrine::loadData('test.yml');

            $this->conn->clear();

            $query = new Doctrine_Query();
            $query->from('I18nTest i, i.Translation t')
                  ->where('i.id = ?', 1234);

            $i = $query->execute()->getFirst();

            $this->assertEqual($i->id, 1234);
            $this->assertEqual($i->Translation['en']->name, 'english name');
            $this->assertEqual($i->Translation['fr']->name, 'french name');
            $this->assertEqual($i->Translation['en']->title, 'english title');
            $this->assertEqual($i->Translation['fr']->title, 'french title');

            $this->pass();
        } catch (Exception $e) {
            $this->fail();
        }

        unlink('test.yml'); 
    }

    public function testImportNestedSetData()
    {
        $yml = <<<END
---
ImportNestedSet:
  ImportNestedSet_1:
    name: Root
    children:
      ImportNestedSet_2:
        name: Child 1
      ImportNestedSet_3:
        name: Child 2
        children:
          ImportNestedSet_4:
            name: Sub-Child 1
END;
        try {
            file_put_contents('test.yml', $yml);
            Doctrine::loadData('test.yml');

            $this->conn->clear();

            $query = new Doctrine_Query();
            $query->from('ImportNestedSet');

            $i = $query->execute(array(), Doctrine::FETCH_ARRAY);

            $this->assertEqual($i[0]['name'], 'Root');
            $this->assertEqual($i[0]['lft'], 1);
            $this->assertEqual($i[0]['rgt'], 8);
            $this->assertEqual($i[0]['level'], 0);

            $this->assertEqual($i[1]['name'], 'Child 2');
            $this->assertEqual($i[1]['lft'], 4);
            $this->assertEqual($i[1]['rgt'], 7);
            $this->assertEqual($i[1]['level'], 1);

            $this->assertEqual($i[2]['name'], 'Sub-Child 1');
            $this->assertEqual($i[2]['lft'], 5);
            $this->assertEqual($i[2]['rgt'], 6);
            $this->assertEqual($i[2]['level'], 2);

            $this->assertEqual($i[3]['name'], 'Child 1');
            $this->assertEqual($i[3]['lft'], 2);
            $this->assertEqual($i[3]['rgt'], 3);
            $this->assertEqual($i[3]['level'], 1);
            $this->pass();
        } catch (Exception $e) {
            $this->fail();
        }

        unlink('test.yml'); 
    }

    public function testMany2ManyManualDataFixtures()
    {
        self::prepareTables();
        $yml = <<<END
---
User:
  User_1:
    name: jwage400
    pass: changeme

Groupuser:
  Groupuser_1:
    User: User_1
    Group: Group_1

Group:
  Group_1:
    name: test
END;
        try {
            file_put_contents('test.yml', $yml);
            Doctrine::loadData('test.yml');

            $this->conn->clear();

            $testRef = Doctrine_Query::create()->from('Groupuser')->execute()->getFirst();

            $this->assertTrue($testRef->group_id > 0);
            $this->assertTrue($testRef->user_id > 0);

            $this->pass();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        unlink('test.yml');
    }
}

class ImportNestedSet extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 255);
    }

    public function setUp()
    {
        $this->actAs('NestedSet');
    }
}