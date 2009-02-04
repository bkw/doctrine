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
 * Doctrine_Ticket_1480_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_1480_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'Foo';
        $this->tables[] = 'Bar';
        parent::prepareTables();
    }

    public function testSubQueryWithSoftDeleteTestCase()
    {
        Doctrine_Manager::getInstance()->setAttribute('use_dql_callbacks', true);
        $q = Doctrine_Query::create()
            ->from('Foo f')
            ->addWhere('f.id IN (SELECT user_id FROM Bar b)');
        $this->assertEqual($q->getSql(), 'SELECT f.id AS f__id, f.name AS f__name, f.password AS f__password, f.deleted AS f__deleted FROM foo f WHERE f.id IN (SELECT f.user_id AS f__user_id FROM bar b) AND (f.deleted = 0 OR f.deleted IS NULL)');
        $this->assertEqual(count($q->getParams()), 0);
        Doctrine_Manager::getInstance()->setAttribute('use_dql_callbacks', false);
    }
}

class Foo extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('foo');
        $this->hasColumn('id', 'integer');
        $this->hasColumn('name', 'string');
        $this->hasColumn('password', 'string');
        $this->hasColumn('deleted', 'boolean');
    }

    public function setUp()
    {
        $this->actAs('SoftDelete');
    }
}

class Bar extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('bar');
        $this->hasColumn('user_id', 'integer');
    }
}