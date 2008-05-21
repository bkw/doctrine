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

Doctrine::autoload('Doctrine_Query_AbstractResult');

/**
 * Doctrine_Query_ParserResult
 *
 * @package     Doctrine
 * @subpackage  Query
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       2.0
 * @version     $Revision$
 */
class Doctrine_Query_ParserResult extends Doctrine_Query_AbstractResult
{
    /**
     * A simple array keys representing table aliases and values table alias
     * seeds. The seeds are used for generating short table aliases.
     *
     * @var array $_tableAliasSeeds
     */
    protected $_tableAliasSeeds = array();


    /**
     * @nodoc
     */
    public function setSqlExecutor(Doctrine_Query_SqlExecutor_Abstract $executor)
    {
        $this->_data = $executor;
    }


    /**
     * @nodoc
     */
    public function getSqlExecutor()
    {
        return $this->_data;
    }


    /**
     * Generates a table alias from given table name and associates 
     * it with given component alias
     *
     * @param string $componentName Component name to be associated with generated table alias
     * @return string               Generated table alias
     */
    public function generateTableAlias($componentName)
    {
        $baseAlias = strtolower(preg_replace('/[^A-Z]/', '\\1', $componentName));

        $alias = $baseAlias;

        if ( ! isset($this->_tableAliasSeeds[$baseAlias])) {
            $this->_tableAliasSeeds[$baseAlias] = 1;
        } else {
            $alias .= $this->_tableAliasSeeds[$baseAlias]++;
        }

        return $alias;
    }
}
