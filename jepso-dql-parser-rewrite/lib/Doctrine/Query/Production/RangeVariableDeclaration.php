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
 * RangeVariableDeclaration = identifier {"." identifier} [["AS"] IdentificationVariable]
 *
 * @package     Doctrine
 * @subpackage  Query
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Query_Production_RangeVariableDeclaration extends Doctrine_Query_Production
{
    protected $_identifiers = array();

    protected $_identificationVariable;


    public function syntax($paramHolder)
    {
        $this->_parser->match(Doctrine_Query_Token::T_IDENTIFIER);
        $this->_identifiers[] = $this->_parser->token['value'];

        while ($this->_isNextToken('.')) {
            $this->_parser->match('.');
            $this->_parser->match(Doctrine_Query_Token::T_IDENTIFIER);

            $this->_identifiers[] = $this->_parser->token['value'];
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_AS)) {
            $this->_parser->match(Doctrine_Query_Token::T_AS);
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_IDENTIFIER)) {
            $paramHolder->set('componentName', implode('.', $this->_identifiers));

            // Will return an identifier, with the semantical check already applied
            $this->_identificationVariable = $this->IdentificationVariable($paramHolder);

            $paramHolder->remove('componentName');
        }
    }


    public function semantical($paramHolder)
    {
    }


    public function buildSql()
    {}


    /*public function execute(array $params = array())
    {
        // RangeVariableDeclaration = identifier {"." identifier} [["AS"] IdentificationVariable]
        $path = '';

        $parserResult = $this->_parser->getParserResult();
        $connection = $this->_parser->getConnection();

        if ($this->_parser->match(Doctrine_Query_Token::T_IDENTIFIER)) {
            $component = $this->_parser->token['value'];
            $path = $component;

            if ($parserResult->hasQueryComponent($component)) {

                $queryComponent = $parserResult->getQueryComponent($component);
                $metadata = $queryComponent['metadata'];

            } else {

                // get the connection for the component
                $manager = Doctrine_Manager::getInstance();
                if ($manager->hasConnectionForComponent($component)) {
                    $this->_parser->setConnection($manager->getConnectionForComponent($component));
                }

                $conn = $this->_parser->getConnection();

                try {
                    $metadata = $conn->getMetadata($component);
                    $mapper = $conn->getMapper($component);
                } catch (Doctrine_Exception $e) {
                    $this->_parser->semanticalError($e->getMessage());
                }

                $queryComponent = array(
                    'metadata'  => $metadata,
                    'mapper'    => $mapper,
                    'map'       => null
                );
            }

            $parent = $path;
        }

        while ($this->_isNextToken('.')) {
            $this->_parser->match('.');

            if ( ! $parserResult->hasQueryComponent($path)) {
                $parserResult->setQueryComponent($path, $queryComponent);
            }

            if ($this->_parser->match(Doctrine_Query_Token::T_IDENTIFIER)) {
                $component = $this->_parser->token['value'];
                $path .= '.' . $component;

                if ( ! isset($table)) {
                    continue;
                }

                $relation = $table->getRelation($component);
                $metadata = $relation->getTable();

                $queryComponent = array(
                        'metadata' => $metadata,
                        'mapper'   => $this->_parser->getConnection()->getMapper($relation->getForeignComponentName()),
                        'parent'   => $parent,
                        'relation' => $relation,
                        'map'      => null
                );

                $parent = $path;
            }
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_AS)) {

            $this->_parser->match(Doctrine_Query_Token::T_AS);
            $alias = $this->IdentificationVariable();

        } elseif ($this->_isNextToken(Doctrine_Query_Token::T_IDENTIFIER)) {

            $alias = $this->IdentificationVariable();

        } else {
            $alias = $path;
        }

        $parserResult->setQueryComponent($alias, $queryComponent);

        return $alias;
    }*/
}
