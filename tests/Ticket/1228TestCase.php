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
 * Doctrine_Template_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_1228_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = "RelA";
        $this->tables[] = "RelB";
        $this->tables[] = "RelC";
        parent::prepareTables();
    }

    public function prepareData() 
    {
        $c1 = new RelC();
        $c1->name = "c 1";
        $c1->save();

        $b1 = new RelB();
        $b1->name = "b 1";
        $b1->rel_c_id = $c1->id;
        $b1->save();

        $a1 = new RelA();
        $a1->name = "a 1";
        $a1->rel_b_id = $b1->id;
        $a1->save();

        /* uncomment this to make it work
        $b2 = new RelB();
        $b2->name = "b 2";
        $b2->save();
        */

        $a2 = new RelA();
        $a2->name = "a 2";
        // uncomment this, too
        // $a2->rel_b_id = $b2->id;
        $a2->save();

        $c3 = new RelC();
        $c3->name = "c 3";
        $c3->save();

        $b3 = new RelB();
        $b3->name = "b 3";
        $b3->rel_c_id = $c3->id;
        $b3->save();

        $a3 = new RelA();
        $a3->name = "a 3";
        $a3->rel_b_id = $b3->id;
        $a3->save();
    }

    public function testHydrationSkippingRelationIfNotSetOnSibling()
    {
        $q = new Doctrine_Query();
        $q->from('RelA a');
        $q->leftJoin('a.b ab');
        $q->leftJoin('ab.c abc');
        $q->orderBy('a.id ASC');
        $res = $q->execute();
        $this->assertEqual('a 1', $res->getFirst()->get('name'));
        $this->assertTrue($res->getFirst()->get('b')->exists());
        $this->assertTrue($res->getFirst()->get('b')->get('c')->exists());
    }
    
}

class RelA extends Doctrine_Record {

  public function setTableDefinition() {
    $this->setTableName('rel_a');
    $this->hasColumn('name', 'string', 25, array());
    $this->hasColumn('rel_b_id', 'integer', 10, array());
  }

  public function setUp() {
    $this->HasOne('RelB as b', array('local' => 'rel_b_id', 'foreign' => 'id'));
  }

}

class RelB extends Doctrine_Record {

  public function setTableDefinition() {
    $this->setTableName('rel_b');
    $this->hasColumn('name', 'string', 25, array());
    $this->hasColumn('rel_c_id', 'integer', 10, array());
  }

  public function setUp() {
    $this->HasOne('RelC as c', array('local' => 'rel_c_id', 'foreign' => 'id'));
  }

}

class RelC extends Doctrine_Record {

  public function setTableDefinition() {
    $this->setTableName('rel_c');
    $this->hasColumn('name', 'string', 25, array());
  }

  public function setUp() {

  }

}
