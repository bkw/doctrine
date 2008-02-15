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
 * IdentificationVariableDeclaration = RangeVariableDeclaration {Join}
 *
 * @package     Doctrine
 * @subpackage  Query
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Query_Production_IdentificationVariableDeclaration extends Doctrine_Query_Production
{
    private function _checkIndexBy($table, $column)
    {
        if ($table instanceof Doctrine_Table && ! $table->hasField($column)) {
            $this->_parser->semanticalError(
                "Cannot use key mapping. Column " . $column . " does not exist.",
                $this->_parser->token
            );
        }
    }

    public function execute(array $params = array())
    {
        $builder = $this->_parser->getSqlBuilder();

        $alias = $this->RangeVariableDeclaration();

        if ($this->_isNextToken(Doctrine_Query_Token::T_INDEX)) {
            $column = $this->IndexBy();

            $aliasDeclaration = $builder->getAliasDeclaration($alias);

            $this->_checkIndexBy($aliasDeclaration['table'], $alias);
            $aliasDeclaration['map'] = $column;

            $builder->setAliasDeclaration($alias, $aliasDeclaration);
        }

        while ($this->_isNextToken(Doctrine_Query_Token::T_LEFT) ||
               $this->_isNextToken(Doctrine_Query_Token::T_INNER) ||
               $this->_isNextToken(Doctrine_Query_Token::T_JOIN)) {

            $this->Join();

            if ($this->_isNextToken(Doctrine_Query_Token::T_INDEX)) {
                $column = $this->IndexBy();

                $aliasDeclaration = $builder->getAliasDeclaration($alias);

                $this->_checkIndexBy($aliasDeclaration['table'], $alias);
                $aliasDeclaration['map'] = $column;

                $builder->setAliasDeclaration($alias, $aliasDeclaration);
            }
        }
    }
}
