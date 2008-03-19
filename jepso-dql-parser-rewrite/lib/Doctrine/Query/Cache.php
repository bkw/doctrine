<?php

/*
 *  $Id: Cache.php 3938 2008-03-06 19:36:50Z romanb $
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
 * Doctrine_Query_Cache
 *
 * @package     Doctrine
 * @subpackage  Query
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.com
 * @since       1.0
 * @version     $Revision: 1393 $
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 *
 * @todo        [TODO] Turn this class Serializable.
 */
class Doctrine_Query_Cache
{
    /**
     * @var mixed $_data The actual data to be stored. Can be an array, a string or an integer.
     */
    protected $_data;

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
    protected $_queryComponents;

    /**
     * @var array Table alias map. Keys are SQL aliases and values DQL aliases.
     */
    protected $_tableAliasMap;


    /**
     * constructor
     *
     * Cannot be called directly, factory methods handle this job.
     *
     * @param mixed $data Data to be stored.
     * @param array $queryComponents Query components.
     * @param array $tableAliasMap Table aliases.
     * @return Doctrine_Query_Cache
     */
    protected function __construct($data, $queryComponents, $tableAliasMap)
    {
        $this->_data = $data;
        $this->_queryComponents = $queryComponents;
        $this->_tableAliasMap = $tableAliasMap;
    }


    /**
     * fromResultSet
     *
     * Static factory method. Receives a Doctrine_Query object and generates
     * the object after processing queryComponents. Table aliases are retrieved
     * directly from Doctrine_Query_Parser.
     *
     * @param Doctrine_Query $query Doctrine_Query_Object related to this cache item.
     * @param mixed $result Data to be stored.
     */
    public static function fromResultSet($query, $result = false)
    {
        $parser = $query->getParser();
        $componentInfo = array();

        foreach ($parser->getQueryComponents() as $alias => $components) {
            if ( ! isset($components['parent'])) {
                $componentInfo[$alias][] = $components['mapper']->getComponentName();
                //$componentInfo[$alias][] = $components['mapper']->getComponentName();
            } else {
                $componentInfo[$alias][] = $components['parent'] . '.' . $components['relation']->getAlias();
            }
            if (isset($components['agg'])) {
                $componentInfo[$alias][] = $components['agg'];
            }
            if (isset($components['map'])) {
                $componentInfo[$alias][] = $components['map'];
            }
        }

        return new self($result, $componentInfo, $parser->getTableAliasMap());
    }


    /**
     * fromResultSet
     *
     * Static factory method. Receives a Doctrine_Query object and a cached data.
     * It handles the cache and generates the object after processing queryComponents.
     * Table aliases are retrieved from cache.
     *
     * @param Doctrine_Query $query Doctrine_Query_Object related to this cache item.
     * @param mixed $cached Cached data.
     */
    public static function fromCachedForm($query, $cached = false)
    {
        $cached = unserialize($cached);
        $cachedComponents = $cached[1];

        $queryComponents = array();

        foreach ($cachedComponents as $alias => $components) {
            $e = explode('.', $components[0]);

            if (count($e) === 1) {
                $queryComponents[$alias]['mapper'] = $query->getConnection()->getMapper($e[0]);
                $queryComponents[$alias]['table'] = $queryComponents[$alias]['mapper']->getTable();
            } else {
                $queryComponents[$alias]['parent'] = $e[0];
                $queryComponents[$alias]['relation'] = $queryComponents[$e[0]]['table']->getRelation($e[1]);
                $queryComponents[$alias]['mapper'] = $query->getConnection()->getMapper($queryComponents[$alias]['relation']->getForeignComponentName());
                $queryComponents[$alias]['table'] = $queryComponents[$alias]['mapper']->getTable();
            }

            if (isset($v[1])) {
                $queryComponents[$alias]['agg'] = $components[1];
            }

            if (isset($v[2])) {
                $queryComponents[$alias]['map'] = $components[2];
            }
        }

        return new self($cached[0], $queryComponents, $cached[2]);
    }


    /**
     * toCachedForm
     *
     * Returns this object in serialized format, revertable using fromCachedForm.
     *
     * @return string Serialized cache item.
     */
    public function toCachedForm()
    {
        return serialize(array(
            $this->getData(),
            $this->getQueryComponents(),
            $this->getTableAliasMap()
        ));
    }


    /**
     * getData
     *
     * Returns the stored data.
     *
     * @return mixed Stored data.
     */
    public function getData()
    {
        return $this->_data;
    }


    /**
     * getQueryComponents
     *
     * Returns the query components.
     *
     * @return mixed Query components.
     */
    public function getQueryComponents()
    {
        return $this->_queryComponents;
    }


    /**
     * getTableAliasMap
     *
     * Returns the table aliases.
     *
     * @return array Table aliases.
     */
    public function getTableAliasMap()
    {
        return $this->_tableAliasMap;
    }

}