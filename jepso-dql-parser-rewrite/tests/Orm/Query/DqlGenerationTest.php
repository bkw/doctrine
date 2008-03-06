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
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Orm_Query_DqlGenerationTest extends Doctrine_OrmTestCase
{
    const QueryClass = 'Doctrine_Query2';

    public function testSimpleSelectGeneration()
    {
        $class = self::QueryClass;
        $q = new $class();

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

        $q->select()->from('User u')->where('u.id = ?', 1);
        $this->assertEquals('SELECT * FROM User u WHERE u.id = ?', $q->getDql());
        $this->assertEquals(array(1), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->where('u.id = ?', 1);
        $this->assertEquals('SELECT u.name FROM User u WHERE u.id = ?', $q->getDql());
        $this->assertEquals(array(1), $q->getParams());
        $q->free();

        $q->select('u.name AS myCustomName')->from('User u')->where('u.id = ?', 1);
        $this->assertEquals('SELECT u.name AS myCustomName FROM User u WHERE u.id = ?', $q->getDql());
        $this->assertEquals(array(1), $q->getParams());
        $q->free();

        $q->select('u.name')->from('User u')->whereIn('u.id', array(1, 2, 3, 4, 5));
        $this->assertEquals('SELECT u.name FROM User u WHERE u.id IN (?, ?, ?, ?, ?)', $q->getDql());
        $this->assertEquals(array(1, 2, 3, 4, 5), $q->getParams());
        $q->free();

        $q->select('u.name')->distinct()->from('User u');
        $this->assertEquals('SELECT DISTINCT u.name FROM User u', $q->getDql());
        $q->free();

        $q->select('DISTINCT u.name')->from('User u');
        $this->assertEquals('SELECT DISTINCT u.name FROM User u', $q->getDql());
        $q->free();
    }

}
