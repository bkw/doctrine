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
 * Doctrine_Ticket_2300_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_2300_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        parent::prepareTables();
        $this->tables[] = 'Ticket_2300_User';
    }

    public function testTest()
    {
        $search = 'jwage';
        $q = new Doctrine_RawSql();
        $q->select('{u.*}')
          ->from('ticket_2300_user u')
          ->addComponent('u', 'Ticket_2300_User u')
          ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
          ->where("u.id LIKE ?",'%'.$search.'%')
          ->orWhere("u.username LIKE ?", '%'.$search.'%');
        $this->assertEqual($q->getSql(), 'SELECT u.id AS u__id, u.username AS u__username FROM ticket_2300_user u WHERE u.id LIKE ? OR u.username LIKE ?');
        $this->assertEqual($q->getCountQuery(), 'SELECT COUNT( DISTINCT u.id) as num_results FROM ticket_2300_user u WHERE u.id LIKE ? OR u.username LIKE ?');
    }
}

class Ticket_2300_User extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('username', 'string', 255);
    }
}