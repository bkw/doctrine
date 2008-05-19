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

require_once 'lib/DoctrineTestInit.php';

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
class Orm_Query_IdentifierRecognitionTest extends Doctrine_OrmTestCase
{

    public function testSingleAliasDeclarationIsSupported()
    {
        $query = new Doctrine_Query;
        $query->setDql('SELECT u.* FROM CmsUser u');
        $parserResult = $query->parse();

        $decl = $parserResult->getQueryComponent('u');

        $this->assertTrue($decl['metadata'] instanceof Doctrine_ClassMetadata);
        $this->assertEquals(null, $decl['relation']);
        $this->assertEquals(null, $decl['parent']);
        $this->assertEquals(null, $decl['agg']);
        $this->assertEquals(null, $decl['map']);
    }

    public function testSingleAliasDeclarationWithIndexByIsSupported()
    {
        $query = new Doctrine_Query;
        $query->setDql('SELECT u.* FROM CmsUser u INDEX BY name');
        $parserResult = $query->parse();

        $decl = $parserResult->getQueryComponent('u');

        $this->assertTrue($decl['metadata'] instanceof Doctrine_ClassMetadata);
        $this->assertEquals(null, $decl['relation']);
        $this->assertEquals(null, $decl['parent']);
        $this->assertEquals(null, $decl['agg']);
        $this->assertEquals('name', $decl['map']);
    }

    public function testQueryParserSupportsMultipleAliasDeclarations()
    {
        $query = new Doctrine_Query;
        $query->setDql('SELECT u.* FROM CmsUser u INDEX BY name LEFT JOIN u.phonenumbers p');
        $parserResult = $query->parse();

        $decl = $parserResult->getQueryComponent('u');

        $this->assertTrue($decl['metadata'] instanceof Doctrine_ClassMetadata);
        $this->assertEquals(null, $decl['relation']);
        $this->assertEquals(null, $decl['parent']);
        $this->assertEquals(null, $decl['agg']);
        $this->assertEquals('name', $decl['map']);

        $decl = $parserResult->getQueryComponent('p');

        $this->assertTrue($decl['metadata'] instanceof Doctrine_ClassMetadata);
        $this->assertTrue($decl['relation'] instanceof Doctrine_Relation);
        $this->assertEquals('u', $decl['parent']);
        $this->assertEquals(null, $decl['agg']);
        $this->assertEquals(null, $decl['map']);
    }


    public function testQueryParserSupportsMultipleAliasDeclarationsWithIndexBy()
    {
        $query = new Doctrine_Query;
        $query->setDql('SELECT u.* FROM CmsUser u INDEX BY name LEFT JOIN u.articles a INNER JOIN u.phonenumbers pn INDEX BY phonenumber');
        $parserResult = $query->parse();

        $decl = $parserResult->getQueryComponent('u');

        $this->assertTrue($decl['metadata'] instanceof Doctrine_ClassMetadata);
        $this->assertEquals(null, $decl['relation']);
        $this->assertEquals(null, $decl['parent']);
        $this->assertEquals(null, $decl['agg']);
        $this->assertEquals('name', $decl['map']);

        $decl = $parserResult->getQueryComponent('a');

        $this->assertTrue($decl['metadata'] instanceof Doctrine_ClassMetadata);
        $this->assertTrue($decl['relation'] instanceof Doctrine_Relation);
        $this->assertEquals('u', $decl['parent']);
        $this->assertEquals(null, $decl['agg']);
        $this->assertEquals(null, $decl['map']);

        $decl = $parserResult->getQueryComponent('pn');

        $this->assertTrue($decl['metadata'] instanceof Doctrine_ClassMetadata);
        $this->assertTrue($decl['relation'] instanceof Doctrine_Relation);
        $this->assertEquals('u', $decl['parent']);
        $this->assertEquals(null, $decl['agg']);
        $this->assertEquals('phonenumber', $decl['map']);
    }
}
