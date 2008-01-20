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
 * SelectExpression = (PathExpressionEndingWithAsterisk | Expression | "(" Subselect ")")
 *                    [["AS"] IdentificationVariable]
 *
 * @package     Doctrine
 * @subpackage  Query
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Query_Production_SelectExpression extends Doctrine_Query_Production
{
    private function _isPathExpressionEndingWithAsterisk()
    {
        $token = $this->_parser->lookahead;
        $this->_parser->getScanner()->resetPeek();

        while (($token['type'] === Doctrine_Query_Token::T_IDENTIFIER) || ($token['value'] === '.')) {
            $token = $this->_parser->getScanner()->peek();
        }

        return $token['value'] === '*';
    }

    public function execute(array $params = array())
    {
        if ($this->_isPathExpressionEndingWithAsterisk()) {
            $this->PathExpressionEndingWithAsterisk();
        } elseif ($this->_isSubselect()) {
            $this->_parser->match('(');
            $this->Subselect();
            $this->_parser->match(')');
        } else {
            $this->Expression();
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_AS)) {
            $this->_parser->match(Doctrine_Query_Token::T_AS);
            $this->_parser->match(Doctrine_Query_Token::T_IDENTIFIER);
        } elseif ($this->_isNextToken(Doctrine_Query_Token::T_IDENTIFIER)) {
            $this->IdentificationVariable();
        }
    }
}
