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
 * IndexBy = "INDEX" "BY" identifier
 *
 * @package     Doctrine
 * @subpackage  Query
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Query_Production_IndexBy extends Doctrine_Query_Production
{
    public function syntax($paramHolder)
    {
        $this->_parser->match(Doctrine_Query_Token::T_INDEX);
        $this->_parser->match(Doctrine_Query_Token::T_BY);
        $this->_parser->match(Doctrine_Query_Token::T_IDENTIFIER);

        $this->_processIndexBy($paramHolder['alias'], $this->_parser->token['value']);
    }


    public function buildSql()
    {}


    private function _processIndexBy($alias, $column)
    {
        $parserResult = $this->_parser->getParserResult();
        $queryComponent = $parserResult->getQueryComponent($alias);
        $metadata = $queryComponent['metadata'];

        if ($metadata instanceof Doctrine_ClassMetadata && ! $metadata->hasField($column)) {
            $this->_parser->semanticalError(
                "Cannot use key mapping. Column " . $column . " does not exist.",
                $this->_parser->token
            );
        }

        $queryComponent['map'] = $column;
        $parserResult->setQueryComponent($alias, $queryComponent);
    }
}
