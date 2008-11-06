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
 * Doctrine_Cache_Apc_TestCase
 *
 * @package     Doctrine
 * @subpackage  Doctrine_Cache
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Cache_Apc_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables = array('User');
        parent::prepareTables();
    }
    
    public function prepareData()
    {
        $user = new User();
        $user->name = 'Hans';
        $user->save();
    }
    
    public function testApcAsResultCache()
    {
        if (!extension_loaded("apc")) {
            return;
        }
        
        // clear user cache to make sure we always get the same behavior:
        // 1st iteration cache miss, subsequent iterations cache hit.
        apc_clear_cache("user");
        
        $cacheDriver = new Doctrine_Cache_Apc();
        $this->conn->setAttribute(Doctrine::ATTR_RESULT_CACHE, $cacheDriver);
        
        $queryCountBefore = $this->conn->count();
        
        for ($i = 0; $i < 10; $i++) {
            $u = Doctrine_Query::create()
                ->from('User u')
                ->addWhere('u.name = ?', array('Hans'))
                ->useResultCache()
                ->execute();
            $this->assertEqual(1, count($u));
            $this->assertEqual("Hans", $u[0]->name);
        }
        
        // Just 1 query should be run
        $this->assertEqual($queryCountBefore + 1, $this->conn->count());
    }
}
