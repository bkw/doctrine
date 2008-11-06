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
 * Doctrine_Ticket_1535_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_1535_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'Ticket_1535_User';
        $this->tables[] = 'Ticket_1535_Profile';
        $this->tables[] = 'Ticket_1535_Group';
        $this->tables[] = 'Ticket_1535_UserGroup';
        $this->tables[] = 'Ticket_1535_Phonenumber';
        parent::prepareTables();
    }

    public function testTest()
    {
        try {
            $q = Doctrine_Query::create()
                ->from('Ticket_1535_UserVersion u')
                ->leftJoin('u.Profile p')
                ->leftJoin('u.Phonenumbers p2')
                ->leftJoin('u.Groups g');
            $this->assertEqual($q->getSql(), 'SELECT t.id AS t__id, t.username AS t__username, t.profile_id AS t__profile_id, t.version AS t__version, t2.id AS t2__id, t2.name AS t2__name, t2.email_address AS t2__email_address, t3.id AS t3__id, t3.phonenumber AS t3__phonenumber, t3.user_id AS t3__user_id, t4.id AS t4__id, t4.name AS t4__name FROM ticket_1535__user_version t LEFT JOIN ticket_1535__profile t2 ON t.profile_id = t2.id LEFT JOIN ticket_1535__phonenumber t3 ON t.id = t3.user_id LEFT JOIN ticket_1535__user_group t5 ON t.id = t5.user_id LEFT JOIN ticket_1535__group t4 ON t4.id = t5.group_id');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}

class Ticket_1535_User extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('username', 'string', 255);
        $this->hasColumn('profile_id', 'integer');
    }

    public function setUp()
    {
        $this->hasMany('Ticket_1535_Group as Groups', array('local'    => 'user_id',
                                                            'foreign'  => 'group_id',
                                                            'refClass' => 'Ticket_1535_UserGroup'));

        $this->hasOne('Ticket_1535_Profile as Profile', array('local'   => 'profile_id',
                                                              'foreign' => 'id'));

        $this->hasMany('Ticket_1535_Phonenumber as Phonenumbers', array('local'   => 'id',
                                                                        'foreign' => 'user_id'));

        $this->actAs('Versionable');
    }
}

class Ticket_1535_Phonenumber extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('phonenumber', 'string', 255);
        $this->hasColumn('user_id', 'integer');
    }

    public function setUp()
    {
        $this->hasOne('Ticket_1535_User as User', array('local'   => 'user_id',
                                                        'foreign' => 'id'));
    }
}

class Ticket_1535_Profile extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 255);
        $this->hasColumn('email_address', 'string', 255);
    }

    public function setUp()
    {
        $this->hasOne('Ticket_1535_User as User', array('local'   => 'id',
                                                        'foreign' => 'user_id'));
    }
}

class Ticket_1535_Group extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 255);
    }

    public function setUp()
    {
        $this->hasMany('Ticket_1535_User as Users', array('local'    => 'group_id',
                                                          'foreign'  => 'user_id',
                                                          'refClass' => 'Ticket_1535_UserGroup'));
    }
}

class Ticket_1535_UserGroup extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('user_id', 'integer');
        $this->hasColumn('group_id', 'integer');
    }
}