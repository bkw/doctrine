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
class Orm_Query_SqlGenerationTest extends Doctrine_OrmTestCase
{
    const QueryClass = 'Doctrine_Query';

    public function testDelete()
    {
        $class = self::QueryClass;
        $q = new $class();

        $q->setDql('DELETE CmsUser u');
        $this->assertEquals('DELETE FROM cms_user cu WHERE 1=1', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u');
        $this->assertEquals('DELETE FROM cms_user cu WHERE 1=1', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('id = ?', 1);
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id = ?', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('u.id = ?', 1);
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id = ?', $q->getSql());
        $q->free();
    }
}
