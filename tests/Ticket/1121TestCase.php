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
 * Doctrine_Ticket_1121_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_1121_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'Ticket_1121_User';
        $this->tables[] = 'Ticket_1121_Profile';
        parent::prepareTables();
    }

    public function testTest()
    {
        $q = Doctrine_Query::create()
                ->from('Ticket_1121_User u')
                ->leftJoin('u.CustomProfileAlias p');

        try {
            $q->execute();
            $this->pass();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
        
    }
}

spl_autoload_register(array('Ticket_1121_Autoloader', 'autoload'));

class Ticket_1121_Autoloader
{
    public static function autoload($className)
    {
        // You will see the above query is parsing the from parts of the query
        if ($className == 'CustomProfileAlias' || $className == 'CustomUserAlias') {
            throw new Exception('DQL callbacks being called for CustomProfileAlias and CustomUserAlias. These are the relationship aliases, not the class name in this case');
        }
    }
}

class Ticket_1121_User extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('username', 'string', 255);
        $this->hasColumn('password', 'string', 255);
        $this->hasColumn('profile_id', 'integer');
    }

    public function setUp()
    {
        $this->hasOne('Ticket_1121_Profile as CustomProfileAlias', array('local'   => 'profile_id',
                                                              'foreign' => 'id'));
    }
}

class Ticket_1121_Profile extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 255);
        $this->hasColumn('about', 'string', 2000);
        $this->hasColumn('active', 'integer');
        $this->addListener(new Ticket_1121_Profile_Listener());
    }

    public function setUp()
    {
        $this->hasOne('Ticket_1121_User as CustomUserAlias', array('local'   => 'id',
                                                        'foreign' => 'profile_id'));
    }
}

class Ticket_1121_Profile_Listener extends Doctrine_Record_Listener
{
    public function preDqlSelect(Doctrine_Event $event)
    {
        $params = $event->getParams();
        $field = $params['alias'] . '.active';

        $event->getQuery()->addWhere($field . ' = ?', 1);
    }
}