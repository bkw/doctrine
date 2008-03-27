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
 * Doctrine_Ticket_710_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_710_TestCase extends Doctrine_UnitTestCase 
{
    public function testResourcesFreeSimple()
    {
        echo "Free Simple (BEFORE):" . (memory_get_usage() / 1024) . "<br/>";

        for ($i = 0; $i < 5000; $i++) {
            $author = new Author();
            $author->name = 'bar';
            $author->free(false);
        }

        echo "Free Simple (AFTER):" . (memory_get_usage() / 1024) . "<br/>";
    }


    public function testResourcesFreeRecursive()
    {
        echo "Free Recursive (BEFORE):" . (memory_get_usage() / 1024) . "<br/>";

        for ($i = 0; $i < 5000; $i++) {
            $book = new Book();
            $book->name = 'foo';
            $author = new Author();
            $author->name = 'bar';
            $book->Author[] = $author;
            $book->free(true);
        }

        echo "Free Recursive (AFTER):" . (memory_get_usage() / 1024) . "<br/>";
    }


    public function testResourcesFreeNonRecursive()
    {
        echo "Free Non-Recursive (BEFORE):" . (memory_get_usage() / 1024) . "<br/>";

        for ($i = 0; $i < 5000; $i++) {
            $book = new Book();
            $book->name = 'foo';
            $author = new Author();
            $author->name = 'bar';
            $book->Author[] = $author;
            $book->free(false);
        }

        echo "Free Non-Recursive (AFTER):" . (memory_get_usage() / 1024) . "<br/>";
    }


    public function testResourcesFreeWithoutRecursive()
    {
        echo "Without Free (BEFORE):" . (memory_get_usage() / 1024) . "<br/>";

        for ($i = 0; $i < 5000; $i++) {
            $book = new Book();
            $book->name = 'foo';
            $author = new Author();
            $author->name = 'bar';
            $book->Author[] = $author;
        }

        echo "Without Free (AFTER):" . (memory_get_usage() / 1024) . "<br/>";
    }
}


/*
class Book extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('user');
        $this->hasColumn('id', 'integer', 4, array (
            'primary' => true,
            'autoincrement' => true,
        ));
        $this->hasColumn('author_id', 'integer', 4, array (
            'notnull' => true,
        ));
        $this->hasColumn('title', 'string', 255);
    }


    public function setUp()
    {
        $this->hasOne('User as Author', array('local' => 'author_id',
                                   'foreign' => 'id'));
    }
}
*/