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
 * VariableDeclaration = identifier [["AS"] IdentificationVariable]
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
class Doctrine_Query_Production_VariableDeclaration extends Doctrine_Query_Production
{
    protected $_componentName;

    protected $_componentAlias;


    protected function _syntax($params = array())
    {
        // VariableDeclaration = identifier [["AS"] IdentificationVariable]
        if ($this->_parser->match(Doctrine_Query_Token::T_IDENTIFIER)) {
            // identifier
            $this->_componentName = $this->_parser->token['value'];

            // IdentificationVariable (identifier if alias not defined)
            $this->_componentAlias = $this->_componentName;
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_AS)) {
            $this->_parser->match(Doctrine_Query_Token::T_AS);
        }

        if ($this->_parser->match(Doctrine_Query_Token::T_IDENTIFIER)) {
            $this->_componentAlias = $this->_parser->token['value'];
        }
    }


    protected function _semantical($params = array())
    {
        $parserResult = $this->_parser->getParserResult();

        if ($parserResult->hasQueryComponent($this->_componentAlias)) {
            // We should throw semantical error if there's already a component for this alias
            $queryComponent = $parserResult->getQueryComponent($this->_componentAlias);
            $componentName = $queryComponent['metadata']->getClassName();

            $message  = "Cannot re-declare component alias '{$this->_componentAlias}'"
                      . "for '{$this->_componentName}'. It was already declared for '"
                      . "component '{$componentName}'.";

            $this->_parser->semanticalError($message);
        } elseif ($parserResult->hasQueryComponent($this->_componentName)) {
            // Since name != alias, we can try to bring the queryComponent from name (already processed)
            $queryComponent = $parserResult->getQueryComponent($this->_componentName);
            $metadata = $queryComponent['metadata'];
        } else {
            // No queryComponent was found. We will have to build it for the first time

            // Get the connection for the component
            $conn = $this->_parser->getSqlBuilder()->getConnection();
            $manager = Doctrine_Manager::getInstance();

            if ($manager->hasConnectionForComponent($this->_componentName)) {
                $conn = $manager->getConnectionForComponent($this->_componentName);
            }

            // Retrieving ClassMetadata and Mapper
            try {
                $metadata = $conn->getMetadata($this->_componentName);
                $mapper = $conn->getMapper($this->_componentName);
            } catch (Doctrine_Exception $e) {
                $this->_parser->semanticalError($e->getMessage());
            }

            // Building queryComponent
            $queryComponent = array(
                'metadata'  => $metadata,
                'mapper'    => $mapper,
                'map'       => null
            );
        }

        // Define ParserResult assertions for later usage
        $tableAlias = $this->_parser->getParserResult()->generateTableAlias($this->_componentName);

        $parserResult->setQueryComponent($this->_componentAlias, $queryComponent);
        $parserResult->setTableAlias($tableAlias, $this->_componentAlias);
    }


    public function buildSql()
    {
        // Basic handy variables
        $parserResult = $this->_parser->getParserResult();
        $queryComponent = $parserResult->getQueryComponent($this->_componentAlias);

        // Retrieving connection
        $conn = $this->_parser->getSqlBuilder()->getConnection();
        $manager = Doctrine_Manager::getInstance();

        if ($manager->hasConnectionForComponent($this->_componentName)) {
            $conn = $manager->getConnectionForComponent($this->_componentName);
        }

        return $conn->quoteIdentifier($queryComponent['metadata']->getTableName()) . ' '
             . $conn->quoteIdentifier($parserResult->getTableAliasFromComponentAlias($this->_componentAlias));
    }
}
