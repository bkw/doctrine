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
class Doctrine_Query_IdentifierRecognitionTest extends Doctrine_OrmTestCase
{
    public function assertAliasDeclaration($alias, $declaration)
    {

    }

    public function testSingleAliasDeclarationIsSupported()
    {
        $q = new Doctrine_Query();

        $q->from('User u');

        $decl = $q->getHydrator()->getAliasDeclaration('u');

        $this->assertTrue($decl['table'] instanceof Doctrine_Table);
        $this->assertEqual($decl['relation'], null);
        $this->assertEqual($decl['parent'], null);
        $this->assertEqual($decl['agg'], null);
        $this->assertEqual($decl['map'], null);
    }

    public function testSingleAliasDeclarationWithIndexByIsSupported()
    {
        $q = new Doctrine_Query();

        $q->from('User u INDEX BY name');

        $decl = $q->getHydrator()->getAliasDeclaration('u');

        $this->assertTrue($decl['table'] instanceof Doctrine_Table);
        $this->assertEqual($decl['relation'], null);
        $this->assertEqual($decl['parent'], null);
        $this->assertEqual($decl['agg'], null);
        $this->assertEqual($decl['map'], 'name');
    }

    public function testQueryParserSupportsMultipleAliasDeclarations()
    {
        $q = new Doctrine_Query();

        $q->from('User u INDEX BY name LEFT JOIN u.Phonenumber p');

        $decl = $q->getHydrator()->getAliasDeclaration('u');

        $this->assertTrue($decl['table'] instanceof Doctrine_Table);
        $this->assertEqual($decl['relation'], null);
        $this->assertEqual($decl['parent'], null);
        $this->assertEqual($decl['agg'], null);
        $this->assertEqual($decl['map'], 'name');

        $decl = $q->getHydrator()->getAliasDeclaration('p');

        $this->assertTrue($decl['table'] instanceof Doctrine_Table);
        $this->assertTrue($decl['relation'] instanceof Doctrine_Relation);
        $this->assertEqual($decl['parent'], 'u');
        $this->assertEqual($decl['agg'], null);
        $this->assertEqual($decl['map'], 'name');
    }

    public function testQueryParserSupportsMultipleAliasDeclarationsWithIndexBy()
    {
        $q = new Doctrine_Query();

        $q->from('User u INDEX BY name LEFT JOIN u.UserGroup g INNER JOIN g.Phonenumber p INDEX BY name INDEX BY p.phonenumber');

        $decl = $q->getHydrator()->getAliasDeclaration('u');

        $this->assertTrue($decl['table'] instanceof Doctrine_Table);
        $this->assertEqual($decl['relation'], null);
        $this->assertEqual($decl['parent'], null);
        $this->assertEqual($decl['agg'], null);
        $this->assertEqual($decl['map'], 'name');

        $decl = $q->getHydrator()->getAliasDeclaration('g');

        $this->assertTrue($decl['table'] instanceof Doctrine_Table);
        $this->assertTrue($decl['relation'] instanceof Doctrine_Relation);
        $this->assertEqual($decl['parent'], 'u');
        $this->assertEqual($decl['agg'], null);
        $this->assertEqual($decl['map'], 'name');

        $decl = $q->getHydrator()->getAliasDeclaration('p');

        $this->assertTrue($decl['table'] instanceof Doctrine_Table);
        $this->assertTrue($decl['relation'] instanceof Doctrine_Relation);
        $this->assertEqual($decl['parent'], 'g');
        $this->assertEqual($decl['agg'], null);
        $this->assertEqual($decl['map'], 'phonenumber');
    }
}
