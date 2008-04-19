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
 * Doctrine_Ticket_963_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_963_TestCase extends Doctrine_UnitTestCase 
{
  public function testExportSql()
  {
    $sql = Doctrine::generateSqlFromArray(array('Ticket_963_User', 'Ticket_963_Email'));
    $this->assertTrue(count($sql) > 2);
  }
}

class Ticket_963_User extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->hasColumn('username', 'string', 255);
    $this->hasColumn('password', 'string', 255);
  }

  public function setUp()
  {
    $this->hasOne('Ticket_963_Email as Email', array('local' => 'id',
                                 'foreign' => 'user_id'));
  }
}

class Ticket_963_Email extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->hasColumn('user_id', 'integer', 4, array('primary' => true));
    $this->hasColumn('address2', 'string', 255);
  }

  public function setUp()
  {
    $this->hasOne('Ticket_963_User as User', array('local' => 'user_id',
                                'foreign' => 'id',
                                'onDelete' => 'CASCADE'));
  }
}