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
 * DeleteStatement = DeleteClause [WhereClause] [OrderByClause] [LimitClause] [OffsetClause]
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
class Doctrine_Query_Production_DeleteStatement extends Doctrine_Query_Production
{
    protected $_deleteClause;

    protected $_whereClause;

    protected $_orderByClause;

    protected $_limitClause;

    protected $_offsetClause;


    public function execute(array $params = array())
    {
        // DeleteStatement = DeleteClause [WhereClause] [OrderByClause] [LimitClause] [OffsetClause]
        $this->_deleteClause = $this->DeleteClause();

        if ($this->_isNextToken(Doctrine_Query_Token::T_WHERE)) {
            $this->_whereClause = $this->WhereClause();
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_ORDER)) {
            $this->_orderByClause = $this->OrderByClause();
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_LIMIT)) {
            $this->_limitClause = $this->LimitClause();
        }

        if ($this->_isNextToken(Doctrine_Query_Token::T_OFFSET)) {
            $this->_offsetClause = $this->OffsetClause();
        }

        return $this;
    }


    public function buildSql()
    {
        $str = $this->_deleteClause->buildSql()
             . (($this->_whereClause !== null) ? $this->_whereClause->buildSql() : '')
             . (($this->_orderByClause !== null) ? $this->_orderByClause->buildSql() : '')
             . (($this->_limitClause !== null) ? $this->_limitClause->buildSql() : '')
             . (($this->_offsetClause !== null) ? $this->_offsetClause->buildSql() : '');

        return $str;
    }
}
