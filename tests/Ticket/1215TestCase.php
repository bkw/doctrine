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
 * Doctrine_Ticket_1215_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_1215_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables() 
    {
        $this->tables = array('Ticket_1215_TreeManyRoots');
        parent::prepareTables();
    }
    
    /*
    TEST NO LONGER VALID AS DOCTRINE-GENERATED ROOT IDS ARE NO LONGER SUPPORTED.
    SEE Doctrine_Tree_NestedSet#createRoot(), Doctrine_Tree_NestedSet#getNextRootId() AND
    Doctrine_Tree_NestedSet#getMaxRootId() FOR DETAILS.
    
    public function prepareData()
    {
        $tree = Doctrine::getTable('Ticket_1215_TreeManyRoots')->getTree();

        $root1 = new Ticket_1215_TreeManyRoots();
        $root1->name = 'Name 1';
        $tree->createRoot($root1);

        $root2 = new Ticket_1215_TreeManyRoots();
        $root2->name = 'Name 2';
        $tree->createRoot($root2);
    }

    public function testMaxRootId() 
    {
        $tree = Doctrine::getTable('Ticket_1215_TreeManyRoots')->getTree();
        $this->assertEqual($tree->getMaxRootId(), 2);
    }

    public function testMaxRootIdWithCollKey()
    {
        $component = new Ticket_1215_TreeManyRoots();
        $component->setAttribute(Doctrine::ATTR_COLL_KEY, 'name');

        $tree = Doctrine::getTable('Ticket_1215_TreeManyRoots')->getTree();
        $this->assertEqual($tree->getMaxRootId(), 2);
    }*/
}

class Ticket_1215_TreeManyRoots extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string');

        $this->actAs('NestedSet', array(
            'hasManyRoots' => true,
            'rootColumnName' => 'root'
        ));
    }
}