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
    public function testDeleteWithoutWhere()
    {
        $q = new Doctrine_Query();

        // NO WhereClause
        $q->setDql('DELETE CmsUser u');
        $this->assertEquals('DELETE FROM cms_user cu WHERE 1 = 1', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u');
        $this->assertEquals('DELETE FROM cms_user cu WHERE 1 = 1', $q->getSql());
        $q->free();
    }


    public function testDeleteWithWhere()
    {
        $q = new Doctrine_Query();

        // "WHERE" ConditionalExpression
        // ConditionalExpression = ConditionalTerm {"OR" ConditionalTerm}
        // ConditionalTerm       = ConditionalFactor {"AND" ConditionalFactor}
        // ConditionalFactor     = ["NOT"] ConditionalPrimary
        // ConditionalPrimary    = SimpleConditionalExpression | "(" ConditionalExpression ")"
        // SimpleConditionalExpression
        //                       = Expression (ComparisonExpression | BetweenExpression | LikeExpression
        //                       | InExpression | NullComparisonExpression) | ExistsExpression

        // If this one test fail, all others will fail too. That's the simplest case possible
        $q->delete()->from('CmsUser u')->where('id = ?', 1);
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id = ?', $q->getSql());
        $q->free();
    }


    public function testDeleteWithConditionalExpressions()
    {
        $q = new Doctrine_Query();

        $q->delete()->from('CmsUser u')->where('u.username = ?', 'gblanco')
          ->orWhere('u.name = ?', 'Guilherme');
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.username = ? OR cu.name = ?', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('u.id = ?', 2)
          ->orWhere('( u.username = ? OR u.name = ? )', array('gblanco', 'Guilherme'));
        $this->assertEquals(
            'DELETE FROM cms_user cu WHERE cu.id = ? OR (cu.username = ? OR cu.name = ?)',
            $q->getSql()
        );
        $q->free();
    }


    public function testDeleteWithConditionalTerms()
    {
        $q = new Doctrine_Query();

        $q->delete()->from('CmsUser u')->where('u.username = ?', 'gblanco')->andWhere('u.name = ?', 'Guilherme');
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.username = ? AND cu.name = ?', $q->getSql());
        $q->free();
    }


    public function testDeleteWithConditionalFactors()
    {
        $q = new Doctrine_Query();

        $q->delete()->from('CmsUser u')->where('NOT id != ?', 1);
        $this->assertEquals('DELETE FROM cms_user cu WHERE NOT cu.id <> ?', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('NOT ( id != ? )', 1);
        $this->assertEquals('DELETE FROM cms_user cu WHERE NOT (cu.id <> ?)', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('NOT ( id != ? AND username = ? )', array(1, 'gblanco'));
        $this->assertEquals('DELETE FROM cms_user cu WHERE NOT (cu.id <> ? AND cu.username = ?)', $q->getSql());
        $q->free();
    }


    // ConditionalPrimary was already tested (see testDeleteWithWhere() and testDeleteWithConditionalFactors())


    public function testDeleteWithExprAndComparison()
    {
        $q = new Doctrine_Query();

        // id = ? was already tested (see testDeleteWithWhere())

        $q->delete()->from('CmsUser u')->where('id > ?', 1);
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id > ?', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('id >= ?', 1);
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id >= ?', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('id < ?', 1);
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id < ?', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('id <= ?', 1);
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id <= ?', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('id <> ?', 1);
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id <> ?', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('id != ?', 1);
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id <> ?', $q->getSql());
        $q->free();
    }


    public function testDeleteWithExprAndBetween()
    {
        $q = new Doctrine_Query();

        // "WHERE" Expression BetweenExpression
        $q->delete()->from('CmsUser u')->where('u.id NOT BETWEEN ? AND ?', array(1, 10));
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id NOT BETWEEN ? AND ?', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('u.id BETWEEN ? AND ?', array(1, 10))
          ->andWhere('u.username != ?', 'admin');
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id BETWEEN ? AND ? AND cu.username <> ?', $q->getSql());
        $q->free();
    }


    public function testDeleteWithExprAndLike()
    {
        $q = new Doctrine_Query();

        // "WHERE" Expression LikeExpression
        $q->delete()->from('CmsUser u')->where('u.username NOT LIKE ?', 'gblanco');
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.username NOT LIKE ?', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where("u.username LIKE ? ESCAPE '\\'", 'gblanco');
        $this->assertEquals("DELETE FROM cms_user cu WHERE cu.username LIKE ? ESCAPE '\\'", $q->getSql());
        $q->free();
    }


    public function testDeleteWithExprAndIn()
    {
        $q = new Doctrine_Query();

        // "WHERE" Expression InExpression
        $q->delete()->from('CmsUser u')->whereIn('u.id', array(1, 3, 7, 10));
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id IN (?, ?, ?, ?)', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->whereNotIn('u.id', array(1, 10));
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.id NOT IN (?, ?)', $q->getSql());
        $q->free();
    }


    public function testDeleteWithExprAndNull()
    {
        $q = new Doctrine_Query();

        // "WHERE" Expression NullComparisonExpression
        $q->delete()->from('CmsUser u')->where('u.name IS NULL');
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.name IS NULL', $q->getSql());
        $q->free();

        $q->delete()->from('CmsUser u')->where('u.name IS NOT NULL');
        $this->assertEquals('DELETE FROM cms_user cu WHERE cu.name IS NOT NULL', $q->getSql());
        $q->free();
    }


    // All previously defined tests used Primary as PathExpression. No need to check it again.

    public function testDeleteWithPrimaryAsAtom()
    {
        $q = new Doctrine_Query();

        // Atom = string | integer | float | boolean | input_parameter
        $q->delete()->from('CmsUser u')->where('1 = 1');
        $this->assertEquals('DELETE FROM cms_user cu WHERE 1 = 1', $q->getSql());
        $q->free();
    }
}
