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
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Query_Production_RangeVariableDeclaration extends Doctrine_Query_Production
{
    public function execute(array $params = array())
    {
        $path = '';
        $queryObject = $this->_parser->getQueryObject();

        if ($this->_parser->match(Doctrine_Query_Token::T_IDENTIFIER)) {

            $component = $this->_parser->token['value'];
            $path = $component;

            if ($queryObject->hasQueryComponent($component)) {

                $queryComponent = $queryObject->getQueryComponent($component);
                $metadata = $queryComponent['metadata'];

            } else {

                // get the connection for the component
                $manager = Doctrine_Manager::getInstance();
                if ($manager->hasConnectionForComponent($component)) {
                    $queryObject->setConnection($manager->getConnectionForComponent($component));
                }

                $conn = $queryObject->getConnection();

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

            if ( ! $queryObject->hasQueryComponent($path)) {
                $queryObject->setQueryComponent($path, $queryComponent);
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
                        'mapper'   => $this->_conn->getMapper($relation->getForeignComponentName()),
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

        $queryObject->setQueryComponent($alias, $queryComponent);

        return $alias;
    }
}
