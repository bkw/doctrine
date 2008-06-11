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
 * Doctrine_Import_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Import_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables() 
    { }
    public function prepareData() 
    { }

    public function testImport()
    {
        $this->dbh = new PDO('sqlite::memory:');

        $this->dbh->exec('CREATE TABLE import_test_user (id INTEGER PRIMARY KEY, name TEXT)');

        $this->conn = Doctrine_Manager::connection($this->dbh, 'tmp123');

        $this->conn->import->importSchema('Import/_files', array('tmp123'));

        $this->assertTrue(file_exists('Import/_files/ImportTestUser.php'));
        $this->assertTrue(file_exists('Import/_files/generated/BaseImportTestUser.php'));
        Doctrine_Lib::removeDirectories('Import/_files');
    }

    public function testImportSingularizeOn()
    {
        $this->dbh = new PDO('sqlite::memory:');

        $this->dbh->exec('CREATE TABLE imports_tests_users (id INTEGER PRIMARY KEY, name TEXT)');

        $this->conn = Doctrine_Manager::connection($this->dbh, 'tmp1234');
        $this->conn->setAttribute(Doctrine::ATTR_SINGULARIZE_IMPORT, true);
        $this->conn->import->importSchema('Import/_files', array('tmp1234'));

        $this->assertTrue(file_exists('Import/_files/ImportTestUser.php'));
        $this->assertTrue(file_exists('Import/_files/generated/BaseImportTestUser.php'));
        Doctrine_Lib::removeDirectories('Import/_files');
    }

    public function testImportSingularizeOff()
    {
        $this->dbh = new PDO('sqlite::memory:');

        $this->dbh->exec('CREATE TABLE imports_tests_users (id INTEGER PRIMARY KEY, name TEXT)');

        $this->conn = Doctrine_Manager::connection($this->dbh, 'tmp1235');
        $this->conn->setAttribute(Doctrine::ATTR_SINGULARIZE_IMPORT, false);
        $this->conn->import->importSchema('Import/_files', array('tmp1235'));

        $this->assertTrue(file_exists('Import/_files/ImportsTestsUsers.php'));
        $this->assertTrue(file_exists('Import/_files/generated/BaseImportsTestsUsers.php'));
        Doctrine_Lib::removeDirectories('Import/_files');
    }
}