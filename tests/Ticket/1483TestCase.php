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
 * Doctrine_Ticket_1483_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_1483_TestCase extends Doctrine_UnitTestCase
{
    public function prepareTables()
    {
        $this->tables[] = 'T1483_Phrase';
        
        parent::prepareTables();
    }
    
    
    public function prepareData()
    {
        $i = new T1483_Phrase();

        $i->Translation['EN']->name = 'some name';
        $i->Translation['FI']->name = 'joku nimi';

        $i->save();
    }


    public function testTicket()
    {
        $table = 'Phrase';

        try {
            $q = Doctrine_Query::create()
                //->select("$table.*, t2_$table.*, t_$table.*")
                ->from("T1483_$table $table")
                ->leftJoin("$table.Translation t2_$table WITH t2_$table.lang = 'EN' INDEXBY t2_$table.lang")
                ->leftJoin("$table.Translation t_$table WITH t_$table.lang = 'FI' INDEXBY t_$table.lang");
    
            //echo '<pre>'.var_export($q->getSqlQuery(), true).'</pre>';
    
            $items = $q->execute();
    
            //echo '<pre>'.var_export($items->toArray(), true).'</pre>';
            
            $this->pass();
        } catch (Doctrine_Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}


class T1483_Phrase extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('phrase');
        
        $this->hasColumn('name', 'string', 255);
    }
    
    
    public function setUp()
    {
        $this->actAs('I18n', array('fields' => array('name')));
    }
}