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
 * Doctrine_Ticket_1885_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_1885_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'Ticket_1885_User';
        $this->tables[] = 'Ticket_1885_Contact';
        parent::prepareTables();
    }

    public function testTest()
    {
        $dbh = new Doctrine_Adapter_Mock('mysql');
        $conn = Doctrine_Manager::connection($dbh);
        $conn->setAttribute(Doctrine::ATTR_TBLNAME_FORMAT, 'pivip_%s');
        $sql = $conn->export->exportClassesSql(array('Ticket_1885_User', 'Ticket_1885_Contact'));
        $this->assertEqual($sql[0], 'CREATE TABLE pivip_ticket_1885__user (id BIGINT AUTO_INCREMENT, name VARCHAR(255), contact_id BIGINT, INDEX contact_id_idx (contact_id), PRIMARY KEY(id)) ENGINE = INNODB');
        $this->assertEqual($sql[1], 'CREATE TABLE pivip_ticket_1885__contact (id BIGINT AUTO_INCREMENT, name VARCHAR(255), PRIMARY KEY(id)) ENGINE = INNODB');
        $this->assertEqual($sql[2], 'ALTER TABLE pivip_ticket_1885__user ADD FOREIGN KEY (contact_id) REFERENCES pivip_ticket_1885__contact(id) ON DELETE CASCADE');
    }
}

class Ticket_1885_User extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 255);
        $this->hasColumn('contact_id', 'integer');
    }

    public function setUp()
    {
        $this->hasOne('Ticket_1885_Contact as Contact', array(
                'local' => 'contact_id',
                'foreign' => 'id',
                'onDelete' => 'CASCADE'
            )
        );
    }
}

class Ticket_1885_Contact extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 255);
    }
}