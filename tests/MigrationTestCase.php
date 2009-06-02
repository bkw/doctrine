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
 * Doctrine_Migration_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Migration_TestCase extends Doctrine_UnitTestCase 
{
    public function testMigration()
    {
        // New migration for the 'migration_classes' directory
        $migration = new Doctrine_Migration('migration_classes');

        // Make sure the current version is 0
        $this->assertEqual($migration->getCurrentVersion(), 0);

        // migrate to version latest version
        $migration->migrate($migration->getLatestVersion());
        // Make sure the current version is latest version
        $this->assertEqual($migration->getCurrentVersion(), $migration->getLatestVersion());

        // now migrate back to original version
        $migration->migrate(0);

        // Make sure the current version is 0
        $this->assertEqual($migration->getCurrentVersion(), 0);
    }
    
    public function testMigrationClassNameInflected()
    {
        $tests = array('test-class-Name',
                       'test_class_name',
                       'TestClassName',
                       'test:class:name',
                       'test(class)name',
                       'test*class*name',
                       'test class name',
                       'test&class&name');

        $builder = new Doctrine_Migration_Builder();

        foreach ($tests as $test) {
            $code = $builder->generateMigrationClass($test);
            $this->assertTrue(strpos($code, 'TestClassName'));
        }
    }
}