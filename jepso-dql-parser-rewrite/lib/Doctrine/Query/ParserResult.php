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
 * Doctrine_Query_ParserResult
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
class Doctrine_Query_ParserResult
{
    /**
     * @var array $_queryComponents
     *
     * Two dimensional array containing the map for query aliases. Main keys are component aliases.
     *
     * table    Table object associated with given alias.
     * relation Relation object owned by the parent.
     * parent   Alias of the parent.
     * agg      Aggregates of this component.
     * map      Name of the column / aggregate value this component is mapped to a collection.
     */
    protected $_queryComponents = array();

    /**
     * @var array Table alias map. Keys are SQL aliases and values DQL aliases.
     */
    protected $_tableAliasMap = array();


    /**
     * setQueryComponents
     *
     * Defines the mapping components.
     *
     * @param array $queryComponents Query components.
     */
    public function setQueryComponents(array $queryComponents)
    {
        $this->_queryComponents = $queryComponents;
    }


    /**
     * setQueryComponent
     *
     * Sets the declaration for given component alias.
     *
     * @param string $componentAlias The component alias to set the declaration to.
     * @param string $queryComponent Alias declaration.
     */
    public function setQueryComponent($componentAlias, array $queryComponent)
    {
        $this->_queryComponents[$componentAlias] = $queryComponent;
    }


    /**
     * getQueryComponents
     *
     * Gets the mapping components.
     *
     * @return array Query components.
     */
    public function getQueryComponents()
    {
        return $this->_queryComponents;
    }


    /**
     * getQueryComponent
     *
     * Get the declaration for given component alias.
     *
     * @param string $componentAlias The component alias the retrieve the declaration from.
     * @return array Alias declaration.
     */
    public function getQueryComponent($componentAlias)
    {
        if ( ! isset($this->_queryComponents[$componentAlias])) {
            throw new Doctrine_Query_Exception('Unknown query component ' . $componentAlias);
        }

        return $this->_queryComponents[$componentAlias];
    }


    /**
     * hasQueryComponent
     *
     * Whether or not this object has a declaration for given component alias.
     *
     * @param string $componentAlias Component alias the retrieve the declaration from.
     * @return boolean True if this object has given alias, otherwise false.
     */
    public function hasQueryComponent($componentAlias)
    {
        return isset($this->_queryComponents[$componentAlias]);
    }


    /**
     * setTableAliasMap
     *
     * Defines the table aliases.
     *
     * @param array $tableAliasMap Table aliases.
     */
    public function setTableAliasMap(array $tableAliasMap)
    {
        $this->_tableAliasMap = $tableAliasMap;
    }


    /**
     * setTableAlias
     *
     * Adds an SQL table alias and associates it a component alias
     *
     * @param string $tableAlias Table alias to be added.
     * @param string $componentAlias Alias for the query component associated with given tableAlias.
     */
    public function setTableAlias($tableAlias, $componentAlias)
    {
        $this->_tableAliasMap[$tableAlias] = $componentAlias;
    }


    /**
     * getTableAliasMap
     *
     * Returns all table aliases.
     *
     * @return array Table aliases as an array.
     */
    public function getTableAliasMap()
    {
        return $this->_tableAliasMap;
    }


    /**
     * getTableAlias
     *
     * Get component alias associated with given table alias.
     *
     * @param string $tableAlias SQL table alias that identifies the component alias
     * @return string Component alias
     */
    public function getTableAlias($tableAlias)
    {
        if ( ! isset($this->_tableAliasMap[$tableAlias])) {
            throw new Doctrine_Query_Exception('Unknown table alias ' . $tableAlias);
        }

        return $this->_tableAliasMap[$tableAlias];
    }


    /**
     * hasTableAlias
     *
     * Whether or not this object has given tableAlias.
     *
     * @param string $tableAlias Table alias to be checked.
     * @return boolean True if this object has given alias, otherwise false.
     */
    public function hasTableAlias($tableAlias)
    {
        return (isset($this->_tableAliasMap[$tableAlias]));
    }

}
