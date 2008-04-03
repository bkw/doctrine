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
 * SelectStatement = [SelectClause] FromClause [WhereClause] [GroupByClause]
 *     [HavingClause] [OrderByClause] [LimitClause]
 *
 * @package     Doctrine
 * @subpackage  Query
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Query_Production_SelectStatement extends Doctrine_Query_Production
{
    public function execute(array $params = array())
    {
        // We need to populate the symbol table first (Objects aliases)
        // Otherwise the SELECT fields will fail gracefully.
        // We'll move to FROM statement, process it and then go back.
        // After that, process the DQL again, now do not considering semantical
        // check in FROM statement (it was already done in the first pass).

        $position = $this->_moveCursorToFromStatement();
        $this->FromClause();
        $this->_parser->free(false, $position); // Cannot be deep, it would clean already processed errors

        // End of symbol table population

        if ($this->_isNextToken(Doctrine_Query_Token::T_SELECT)) {
            $this->SelectClause();
        }

        $this->FromClause(array('semanticalCheck' => false));

        if ($this->_isNextToken(Doctrine_Query_Token::T_WHERE)) {
            $this->WhereClause();
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_GROUP)) {
            $this->GroupByClause();
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_HAVING)) {
            $this->HavingClause();
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_ORDER)) {
            $this->OrderByClause();
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_LIMIT)) {
            $this->LimitClause();
        }
    }


    protected function _moveCursorToFromStatement()
    {
        $position = $this->_parser->token['position'];

        while ( ! $this->_isNextToken(Doctrine_Query_Token::T_FROM) || $this->_parser->lookahead !== null) {
            // Move to the next token
            $this->_parser->next();
        }

        if ($this->_parser->lookahead === null) {
            $this->syntaxError('FROM');
        }

        return $position;
    }
}
