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
 * Join = ["LEFT" | "INNER"] "JOIN" RangeVariableDeclaration [("ON" | "WITH") ConditionalExpression]
 *
 * @package     Doctrine
 * @subpackage  Query
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Query_Production_Join extends Doctrine_Query_Production
{
    protected $_joinType;

    protected $_rangeVariableDeclaration;

    protected $_whereType;

    protected $_conditionalExpression;


    public function syntax($paramHolder)
    {
        $this->_joinType = 'INNER';
        $this->_whereType = 'WITH';

        if ($this->_isNextToken(Doctrine_Query_Token::T_LEFT)) {
            $this->_parser->match(Doctrine_Query_Token::T_LEFT);

            $this->_joinType = 'LEFT';
        } elseif ($this->_isNextToken(Doctrine_Query_Token::T_INNER)) {
            $this->_parser->match(Doctrine_Query_Token::T_INNER);
        }

        $this->_parser->match(Doctrine_Query_Token::T_JOIN);

        $this->_rangeVariableDeclaration = $this->AST('RangeVariableDeclaration', $paramHolder);

        if ($this->_isNextToken(Doctrine_Query_Token::T_ON)) {
            $this->_parser->match(Doctrine_Query_Token::T_ON);

            $this->_whereType = 'ON';

            $this->_conditionalExpression = $this->AST('ConditionalExpression', $paramHolder);
        } elseif ($this->_isNextToken(Doctrine_Query_Token::T_WITH)) {
            $this->_parser->match(Doctrine_Query_Token::T_WITH);

            $this->_conditionalExpression = $this->AST('ConditionalExpression', $paramHolder);
        }
    }


    public function buildSql()
    {
        return '';
    }
}
