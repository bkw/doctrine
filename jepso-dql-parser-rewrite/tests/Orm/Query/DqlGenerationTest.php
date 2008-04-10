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
 * Test case for testing the saving and referencing of query identifiers.
 *
 * @package     Doctrine
 * @subpackage  Query
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Orm_Query_DqlGenerationTest extends Doctrine_OrmTestCase
{
    const QueryClass = 'Doctrine_Query';

    public function testSelect()
    {
        $class = self::QueryClass;
        $q = new $class();

        // select and from
        $q->setDql('FROM User u');
        $this->assertEquals('FROM User u', $q->getDql()); // Internally we use SELECT * FROM User u to process the DQL
        $q->free();

        $q->select()->from('User u');
        $this->assertEquals('SELECT * FROM User u', $q->getDql());
        $q->free();

        $q->select('u.*')->from('User u');
        $this->assertEquals('SELECT u.* FROM User u', $q->getDql());
        $q->free();

        $q->select('u.id')->from('User u');
        $this->assertEquals('SELECT u.id FROM User u', $q->getDql());
        $q->free();

        $q->select('u.id, u.name')->from('User u');
        $this->assertEquals('SELECT u.id, u.name FROM User u', $q->getDql());
        $q->free();

        $q->select('u.name AS myCustomName')->from('User u');
        $this->assertEquals('SELECT u.name AS myCustomName FROM User u', $q->getDql());
        $q->free();

        $q->select('u.id')->select('u.name')->from('User u');
        $this->assertEquals('SELECT u.id, u.name FROM User u', $q->getDql());
        $q->free();
    }


    public function testSelectDistinct()
    {
        $class = self::QueryClass;
        $q = new $class();

        $q->select()->distinct()->from('User u');
        $this->assertEquals('SELECT DISTINCT * FROM User u', $q->getDql());
        $q->free();

        $q->select('u.name')->distinct(false)->from('User u');
        $this->assertEquals('SELECT u.name FROM User u', $q->getDql());
        $q->free();

        $q->select()->distinct(false)->from('User u');
        $this->assertEquals('SELECT * FROM User u', $q->getDql());
        $q->free();

        $q->select('u.name')->distinct()->from('User u');
        $this->assertEquals('SELECT DISTINCT u.name FROM User u', $q->getDql());
        $q->free();

        $q->select('u.name, u.email')->distinct()->from('User u');
        $this->assertEquals('SELECT DISTINCT u.name, u.email FROM User u', $q->getDql());
        $q->free();

        $q->select('u.name')->select('u.email')->distinct()->from('User u');
        $this->assertEquals('SELECT DISTINCT u.name, u.email FROM User u', $q->getDql());
        $q->free();

        $q->select('DISTINCT u.name')->from('User u');
        $this->assertEquals('SELECT DISTINCT u.name FROM User u', $q->getDql());
        $q->free();

        $q->select('DISTINCT u.name, u.email')->from('User u');
        $this->assertEquals('SELECT DISTINCT u.name, u.email FROM User u', $q->getDql());
        $q->free();

        $q->select('DISTINCT u.name')->select('u.email')->from('User u');
        $this->assertEquals('SELECT DISTINCT u.name, u.email FROM User u', $q->getDql());
        $q->free();
    }


    public function testSelectJoin()
    {
        $class = self::QueryClass;
        $q = new $class();

        $q->select('u.*')->from('User u')->join('u.Group g')->where('g.id = ?', 1);
        $this->assertEquals('SELECT u.* FROM User u INNER JOIN u.Group g WHERE g.id = ?', $q->getDql());
        $this->assertEquals(array(1), $q->getParams());
        $q->free();

        $q->select('u.*')->from('User u')->innerJoin('u.Group g')->where('g.id = ?', 1);
        $this->assertEquals('SELECT u.* FROM User u INNER JOIN u.Group g WHERE g.id = ?', $q->getDql());
        $this->assertEquals(array(1), $q->getParams());
        $q->free();

        $q->select('u.*')->from('User u')->leftJoin('u.Group g')->where('g.id IS NULL');
        $this->assertEquals('SELECT u.* FROM User u LEFT JOIN u.Group g WHERE g.id IS NULL', $q->getDql());
        $q->free();

        $q->select('u.*')->from('User u')->leftJoin('u.UserGroup ug')->leftJoin('ug.Group g')->where('g.name = ?', 'admin');
        $this->assertEquals('SELECT u.* FROM User u LEFT JOIN u.UserGroup ug LEFT JOIN ug.Group g WHERE g.name = ?', $q->getDql());
        $q->free();
    }


    public function testSelectWhere()
    {
        $class = self::QueryClass;
        $q = new $class();

        $q->select('u.name')->from('User u')->where('u.id = ?', 1);
        $this->assertEquals('SELECT u.name FROM User u WHERE u.id = ?', $q->getDql());
        $this->assertEquals(array(1), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->where('u.id = ? AND u.type != ?', array(1, 'admin'));
        $this->assertEquals('SELECT u.name FROM User u WHERE u.id = ? AND u.type != ?', $q->getDql());
        $this->assertEquals(array(1, 'admin'), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->where('u.id = ?', 1)->andWhere('u.type != ?', 'admin');
        $this->assertEquals('SELECT u.name FROM User u WHERE u.id = ? AND u.type != ?', $q->getDql());
        $this->assertEquals(array(1, 'admin'), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->where('( u.id = ?', 1)->andWhere('u.type != ? )', 'admin');
        $this->assertEquals('SELECT u.name FROM User u WHERE ( u.id = ? AND u.type != ? )', $q->getDql());
        $this->assertEquals(array(1, 'admin'), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->where('u.id = ? OR u.type != ?', array(1, 'admin'));
        $this->assertEquals('SELECT u.name FROM User u WHERE u.id = ? OR u.type != ?', $q->getDql());
        $this->assertEquals(array(1, 'admin'), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->where('u.id = ?', 1)->orWhere('u.type != ?', 'admin');
        $this->assertEquals('SELECT u.name FROM User u WHERE u.id = ? OR u.type != ?', $q->getDql());
        $this->assertEquals(array(1, 'admin'), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->andwhere('u.id = ?', 1)->andWhere('u.type != ?', 'admin')->orWhere('u.email = ?', 'admin@localhost');
        $this->assertEquals('SELECT u.name FROM User u WHERE u.id = ? AND u.type != ? OR u.email = ?', $q->getDql());
        $this->assertEquals(array(1, 'admin', 'admin@localhost'), $q->getParams());
        $q->free();
    }


    public function testSelectWhereIn()
    {
        $class = self::QueryClass;
        $q = new $class();

        $q->select('u.name')->from('User u')->whereIn('u.id', array(1, 2, 3, 4, 5));
        $this->assertEquals('SELECT u.name FROM User u WHERE u.id IN (?, ?, ?, ?, ?)', $q->getDql());
        $this->assertEquals(array(1, 2, 3, 4, 5), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->whereNotIn('u.id', array(1, 2, 3));
        $this->assertEquals('SELECT u.name FROM User u WHERE u.id NOT IN (?, ?, ?)', $q->getDql());
        $this->assertEquals(array(1, 2, 3), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->where('u.type = ?', 'admin')->andWhereIn('u.id', array(1, 2));
        $this->assertEquals('SELECT u.name FROM User u WHERE u.type = ? AND u.id IN (?, ?)', $q->getDql());
        $this->assertEquals(array('admin', 1, 2), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->where('u.type = ?', 'admin')->andWhereNotIn('u.id', array(1, 2));
        $this->assertEquals('SELECT u.name FROM User u WHERE u.type = ? AND u.id NOT IN (?, ?)', $q->getDql());
        $this->assertEquals(array('admin', 1, 2), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->whereIn('u.type', array('admin', 'moderator'))->andWhereNotIn('u.id', array(1, 2, 3, 4));
        $this->assertEquals('SELECT u.name FROM User u WHERE u.type IN (?, ?) AND u.id NOT IN (?, ?, ?, ?)', $q->getDql());
        $this->assertEquals(array('admin', 'moderator', 1, 2, 3, 4), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->whereIn('u.type', array('admin', 'moderator'))->orWhereIn('u.id', array(1, 2, 3, 4));
        $this->assertEquals('SELECT u.name FROM User u WHERE u.type IN (?, ?) OR u.id IN (?, ?, ?, ?)', $q->getDql());
        $this->assertEquals(array('admin', 'moderator', 1, 2, 3, 4), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->whereIn('u.type', array('admin', 'moderator'))->andWhereNotIn('u.id', array(1, 2))->orWhereNotIn('u.type', array('admin', 'moderator'))->andWhereNotIn('u.email', array('user@localhost', 'guest@localhost'));
        $this->assertEquals('SELECT u.name FROM User u WHERE u.type IN (?, ?) AND u.id NOT IN (?, ?) OR u.type NOT IN (?, ?) AND u.email NOT IN (?, ?)', $q->getDql());
        $this->assertEquals(array('admin', 'moderator', 1, 2, 'admin', 'moderator', 'user@localhost', 'guest@localhost'), $q->getParams());
        $q->free();
    }


    public function testDelete()
    {
        $class = self::QueryClass;
        $q = new $class();

        $q->setDql('DELETE CmsUser u');
        $this->assertEquals('DELETE CmsUser u', $q->getDql());
        $q->free();

        $q->delete()->from('CmsUser u');
        $this->assertEquals('DELETE FROM CmsUser u', $q->getDql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('u.id = ?', 1);
        $this->assertEquals('DELETE FROM CmsUser u WHERE u.id = ?', $q->getDql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('u.username = ?', 'gblanco')->orWhere('u.name = ?', 'Guilherme');
        $this->assertEquals('DELETE FROM CmsUser u WHERE u.username = ? OR u.name = ?', $q->getDql());
        $q->free();
    }

}
