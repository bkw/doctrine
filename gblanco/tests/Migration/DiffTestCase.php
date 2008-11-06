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
 * Doctrine_Migration_Diff_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Migration_Diff_TestCase extends Doctrine_UnitTestCase 
{
    public function testTest()
    {
        $from = dirname(__FILE__) . '/Diff/schema/from.yml';
        $to = dirname(__FILE__) . '/Diff/schema/to.yml';
        $migrationsPath = dirname(__FILE__) . '/Diff/migrations';
        Doctrine_Lib::makeDirectories($migrationsPath);

        $diff = new Doctrine_Migration_Diff($from, $to, $migrationsPath);
        $changes = $diff->generateChanges();
        $this->assertEqual($changes['dropped_tables']['homepage']['tableName'], 'homepage');
        $this->assertEqual($changes['created_tables']['blog_post']['tableName'], 'blog_post');
        $this->assertEqual($changes['added_columns']['profile']['user_id'], array('type' => 'integer', 'length' => 8));
        $this->assertEqual($changes['removed_columns']['user']['homepage_id'], array('type' => 'integer', 'length' => 8));
        $this->assertEqual($changes['removed_columns']['user']['profile_id'], array('type' => 'integer', 'length' => 8));
        $this->assertEqual($changes['changed_columns']['user']['username'], array('type' => 'string', 'length' => 255, 'unique' => true, 'notnull' => true));
        $this->assertEqual($changes['created_fks']['profile']['profile_user_id_user_id_idx']['local'], 'user_id');
        $this->assertEqual($changes['created_fks']['blog_post']['blog_post_user_id_user_id_idx']['local'], 'user_id');
        $this->assertEqual($changes['dropped_fks']['user']['user_profile_id_profile_id_idx']['local'], 'profile_id');
        $this->assertEqual($changes['dropped_fks']['user']['user_homepage_id_homepage_id_idx']['local'], 'homepage_id');
        $this->assertEqual($changes['added_indexes']['profile']['profile_user_id_user_id'], array('fields' => array('user_id')));
        $this->assertEqual($changes['added_indexes']['profile']['profile_user_id_user_id'], array('fields' => array('user_id')));
        $this->assertEqual($changes['removed_indexes']['user']['is_active'], array('fields' => array('is_active')));
        $diff->generateMigrationClasses();

        $files = glob($migrationsPath . '/*.php');
        $this->assertEqual(count($files), 2);
        $this->assertTrue(strpos($files[0], '_version1.php'));
        $this->assertTrue(strpos($files[1], '_version2.php'));
        
        $code1 = file_get_contents($files[0]);
        $this->assertTrue(strpos($code1, 'this->dropTable'));
        $this->assertTrue(strpos($code1, 'this->createTable'));
        $this->assertTrue(strpos($code1, 'this->removeColumn'));
        $this->assertTrue(strpos($code1, 'this->addColumn'));
        $this->assertTrue(strpos($code1, 'this->changeColumn'));

        $code2 = file_get_contents($files[1]);
        $this->assertTrue(strpos($code2, 'this->dropForeignKey'));
        $this->assertTrue(strpos($code2, 'this->removeIndex'));
        $this->assertTrue(strpos($code2, 'this->addIndex'));
        $this->assertTrue(strpos($code2, 'this->createForeignKey'));

        Doctrine_Lib::removeDirectories($migrationsPath);
    }
}