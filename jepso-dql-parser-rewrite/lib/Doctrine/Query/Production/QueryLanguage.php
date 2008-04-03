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
 * QueryLanguage = SelectStatement | UpdateStatement | DeleteStatement
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
class Doctrine_Query_Production_QueryLanguage extends Doctrine_Query_Production
{
    protected $_queryStatement;


    protected function _syntax($params = array())
    {
        switch ($this->_parser->lookahead['type']) {
            case Doctrine_Query_Token::T_SELECT:
            case Doctrine_Query_Token::T_FROM:
                $this->_queryStatement = $this->SelectStatement();
            break;

            case Doctrine_Query_Token::T_UPDATE:
                $this->_queryStatement = $this->UpdateStatement();
            break;

            case Doctrine_Query_Token::T_DELETE:
                $this->_queryStatement = $this->DeleteStatement();
            break;

            default:
                $this->_parser->syntaxError('SELECT, FROM, UPDATE or DELETE');
                $this->_queryStatement = null;
            break;
        }
    }


    protected function _semantical($params = array())
    {
    }


    public function buildSql()
    {
        return $this->_queryStatement->buildSql();
    }
}
