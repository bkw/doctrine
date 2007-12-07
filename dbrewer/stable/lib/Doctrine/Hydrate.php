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
 * <http://www.phpdoctrine.com>.
 */

/**
 * Doctrine_Hydrate is a base class for Doctrine_RawSql and Doctrine_Query.
 * Its purpose is to populate object graphs.
 *
 *
 * @package     Doctrine
 * @subpackage  Hydrate
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.com
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_Hydrate extends Doctrine_Locator_Injectable implements Serializable
{
    /**
     * QUERY TYPE CONSTANTS
     */

    /**
     * constant for SELECT queries
     */
    const SELECT = 0;

    /**
     * constant for DELETE queries
     */
    const DELETE = 1;

    /**
     * constant for UPDATE queries
     */
    const UPDATE = 2;

    /**
     * constant for INSERT queries
     */
    const INSERT = 3;

    /**
     * constant for CREATE queries
     */
    const CREATE = 4;

    /**
     * @var array $params                       query input parameters
     */
    protected $_params      = array('where' => array(),
                                    'set' => array(),
                                    'having' => array());

    /**
     * @var Doctrine_Connection $conn           Doctrine_Connection object
     */
    protected $_conn;

    /**
     * @var Doctrine_View $_view                Doctrine_View object, when set this object will use the
     *                                          the query given by the view object for object population
     */
    protected $_view;

    /**
     * @var array $_aliasMap                    two dimensional array containing the map for query aliases
     *      Main keys are component aliases
     *
     *          table               table object associated with given alias
     *
     *          relation            the relation object owned by the parent
     *
     *          parent              the alias of the parent
     *
     *          agg                 the aggregates of this component
     *
     *          map                 the name of the column / aggregate value this
     *                              component is mapped to a collection
     */
    protected $_aliasMap         = array();

    /**
     *
     */
    protected $pendingAggregates = array();

    /**
     * @var array $aggregateMap             an array containing all aggregate aliases, keys as dql aliases
     *                                      and values as sql aliases
     */
    protected $aggregateMap      = array();

    /**
     * @var array $_options                 an array of options
     */
    protected $_options    = array(
                            'fetchMode'      => Doctrine::FETCH_RECORD,
                            'parserCache'    => false,
                            'resultSetCache' => false,
                            );

    /**
     * @var string $_sql            cached SQL query
     */
    protected $_sql;

    /**
     * @var array $parts            SQL query string parts
     */
    protected $parts = array(
        'select'    => array(),
        'distinct'  => false,
        'forUpdate' => false,
        'from'      => array(),
        'set'       => array(),
        'join'      => array(),
        'where'     => array(),
        'groupby'   => array(),
        'having'    => array(),
        'orderby'   => array(),
        'limit'     => false,
        'offset'    => false,
        );

    /**
     * @var integer $type                   the query type
     *
     * @see Doctrine_Query::* constants
     */
    protected $type            = self::SELECT;

    /**
     * @var array
     */
    protected $_cache;

    /**
     * The current hydration mode.
     */
    protected $_hydrationMode = Doctrine::HYDRATE_RECORD;

    /**
     * @var boolean $_expireCache           a boolean value that indicates whether or not to force cache expiration
     */
    protected $_expireCache     = false;

    protected $_timeToLive;

    protected $_tableAliases    = array();

    /**
     * @var array $_tableAliasSeeds         A simple array keys representing table aliases and values
     *                                      as table alias seeds. The seeds are used for generating short table
     *                                      aliases.
     */
    protected $_tableAliasSeeds = array();

    /**
     * constructor
     *
     * @param Doctrine_Connection|null $connection
     */
    public function __construct($connection = null)
    {
        if ( ! ($connection instanceof Doctrine_Connection)) {
            $connection = Doctrine_Manager::getInstance()->getCurrentConnection();
        }
        $this->_conn = $connection;
    }

    /**
     * getRootAlias
     * returns the alias of the the root component
     *
     * @return array
     */
    public function getRootAlias()
    {
        if ( ! $this->_aliasMap) {
          $this->getSql();
        }
        
        reset($this->_aliasMap);

        return key($this->_aliasMap);
    }

    /**
     * getRootDeclaration
     * returns the root declaration
     *
     * @return array
     */
    public function getRootDeclaration()
    {
        $map = reset($this->_aliasMap);

        return $map;
    }

    /**
     * getRoot
     * returns the root component for this object
     *
     * @return Doctrine_Table       root components table
     */
    public function getRoot()
    {
        $map = reset($this->_aliasMap);

        if ( ! isset($map['table'])) {
            throw new Doctrine_Hydrate_Exception('Root component not initialized.');
        }

        return $map['table'];
    }

    /**
     * getSql
     * return the sql associated with this object
     *
     * @return string   sql query string
     */
    public function getSql()
    {
        return $this->getQuery();
    }

    /**
     * useCache
     *
     * @param Doctrine_Cache_Interface|bool $driver      cache driver
     * @param integer $timeToLive                        how long the cache entry is valid
     * @return Doctrine_Hydrate         this object
     */
    public function useCache($driver = true, $timeToLive = null)
    {
        if ($driver !== null) {
            if ($driver !== true) {
                if ( ! ($driver instanceof Doctrine_Cache_Interface)) {
                    $msg = 'First argument should be instance of Doctrine_Cache_Interface or null.';

                    throw new Doctrine_Hydrate_Exception($msg);
                }
            }
        }
        $this->_cache = $driver;

        return $this->setCacheLifeSpan($timeToLive);
    }

    /**
     * expireCache
     *
     * @param boolean $expire       whether or not to force cache expiration
     * @return Doctrine_Hydrate     this object
     */
    public function expireCache($expire = true)
    {
        $this->_expireCache = true;

        return $this;
    }

    /**
     * setCacheLifeSpan
     *
     * @param integer $timeToLive   how long the cache entry is valid
     * @return Doctrine_Hydrate     this object
     */
    public function setCacheLifeSpan($timeToLive)
    {
        if ($timeToLive !== null) {
            $timeToLive = (int) $timeToLive;
        }
        $this->_timeToLive = $timeToLive;

        return $this;
    }

    /**
     * getCacheDriver
     * returns the cache driver associated with this object
     *
     * @return Doctrine_Cache_Interface|boolean|null    cache driver
     */
    public function getCacheDriver()
    {
        if ($this->_cache instanceof Doctrine_Cache_Interface) {
            return $this->_cache;
        } else {
            return $this->_conn->getCacheDriver();
        }
    }

    /**
     * Sets the fetchmode.
     *
     * @param integer $fetchmode  One of the Doctrine::HYDRATE_* constants.
     */
    public function setHydrationMode($hydrationMode)
    {
        $this->_hydrationMode = $hydrationMode;
        return $this;
    }

    /**
     * serialize
     * this method is automatically called when this Doctrine_Hydrate is serialized
     *
     * @return array    an array of serialized properties
     */
    public function serialize()
    {
        $vars = get_object_vars($this);

    }

    /**
     * unseralize
     * this method is automatically called everytime a Doctrine_Hydrate object is unserialized
     *
     * @param string $serialized                Doctrine_Record as serialized string
     * @return void
     */
    public function unserialize($serialized)
    {

    }

    /**
     * generateNewTableAlias
     * generates a new alias from given table alias
     *
     * @param string $tableAlias    table alias from which to generate the new alias from
     * @return string               the created table alias
     */
    public function generateNewTableAlias($tableAlias)
    {
        if (isset($this->_tableAliases[$tableAlias])) {
            // generate a new alias
            $name = substr($tableAlias, 0, 1);
            $i    = ((int) substr($tableAlias, 1));

            if ($i == 0) {
                $i = 1;
            }

            $newIndex  = ($this->_tableAliasSeeds[$name] + $i);

            return $name . $newIndex;
        }

        return $tableAlias;
    }

    /**
     * hasTableAlias
     * whether or not this object has given tableAlias
     *
     * @param string $tableAlias    the table alias to be checked
     * @return boolean              true if this object has given alias, otherwise false
     */
    public function hasTableAlias($tableAlias)
    {
        return (isset($this->_tableAliases[$tableAlias]));
    }

    /**
     * getComponentAlias
     * get component alias associated with given table alias
     *
     * @param string $tableAlias    the table alias that identifies the component alias
     * @return string               component alias
     */
    public function getComponentAlias($tableAlias)
    {
        if ( ! isset($this->_tableAliases[$tableAlias])) {
            throw new Doctrine_Hydrate_Exception('Unknown table alias ' . $tableAlias);
        }
        return $this->_tableAliases[$tableAlias];
    }

    /**
     * getTableAliasSeed
     * returns the alias seed for given table alias
     *
     * @param string $tableAlias    table alias that identifies the alias seed
     * @return integer              table alias seed
     */
    public function getTableAliasSeed($tableAlias)
    {
        if ( ! isset($this->_tableAliasSeeds[$tableAlias])) {
            return 0;
        }
        return $this->_tableAliasSeeds[$tableAlias];
    }

    /**
     * generateTableAlias
     * generates a table alias from given table name and associates 
     * it with given component alias
     *
     * @param string $componentAlias    the component alias to be associated with generated table alias
     * @param string $tableName         the table name from which to generate the table alias
     * @return string                   the generated table alias
     */
    public function generateTableAlias($componentAlias, $tableName)
    {
        $char   = strtolower(substr($tableName, 0, 1));

        $alias  = $char;

        if ( ! isset($this->_tableAliasSeeds[$alias])) {
            $this->_tableAliasSeeds[$alias] = 1;
        }

        while (isset($this->_tableAliases[$alias])) {
            if ( ! isset($this->_tableAliasSeeds[$alias])) {
                $this->_tableAliasSeeds[$alias] = 1;
            }
            $alias = $char . ++$this->_tableAliasSeeds[$alias];
        }

        $this->_tableAliases[$alias] = $componentAlias;

        return $alias;
    }

    /**
     * getTableAliases
     * returns all table aliases
     *
     * @return array        table aliases as an array
     */
    public function getTableAliases()
    {
        return $this->_tableAliases;
    }

    /** 
     * addTableAlias
     * adds an alias for table and associates it with given component alias
     *
     * @param string $componentAlias    the alias for the query component associated with given tableAlias
     * @param string $tableAlias        the table alias to be added
     * @return Doctrine_Hydrate
     */
    public function addTableAlias($tableAlias, $componentAlias)
    {
        $this->_tableAliases[$tableAlias] = $componentAlias;

        return $this;
    }

    /**
     * getTableAlias
     * some database such as Oracle need the identifier lengths to be < ~30 chars
     * hence Doctrine creates as short identifier aliases as possible
     *
     * this method is used for the creation of short table aliases, its also
     * smart enough to check if an alias already exists for given component (componentAlias)
     *
     * @param string $componentAlias    the alias for the query component to search table alias for
     * @param string $tableName         the table name from which the table alias is being created
     * @return string                   the generated / fetched short alias
     */
    public function getTableAlias($componentAlias, $tableName = null)
    {
        $alias = array_search($componentAlias, $this->_tableAliases);

        if ($alias !== false) {
            return $alias;
        }

        if ($tableName === null) {
            throw new Doctrine_Hydrate_Exception("Couldn't get short alias for " . $componentAlias);
        }

        return $this->generateTableAlias($componentAlias, $tableName);
    }

    /**
     * addQueryPart
     * adds a query part in the query part array
     *
     * @param string $name          the name of the query part to be added
     * @param string $part          query part string
     * @throws Doctrine_Hydrate_Exception   if trying to add unknown query part
     * @return Doctrine_Hydrate     this object
     */
    public function addQueryPart($name, $part)
    {
        if ( ! isset($this->parts[$name])) {
            throw new Doctrine_Hydrate_Exception('Unknown query part ' . $name);
        }
        if (is_array($part)) {
            $this->parts[$name] = array_merge($this->parts[$name], $part);
        } else {
            $this->parts[$name][] = $part;
        }
        return $this;
    }

    /**
     * setQueryPart
     * sets a query part in the query part array
     *
     * @param string $name          the name of the query part to be set
     * @param string $part          query part string
     * @throws Doctrine_Hydrate_Exception   if trying to set unknown query part
     * @return Doctrine_Hydrate     this object
     */
    public function getQueryPart($part)
    {
        if ( ! isset($this->parts[$part])) {
            throw new Doctrine_Hydrate_Exception('Unknown query part ' . $part);
        }

        return $this->parts[$part];
    }

    /**
     * removeQueryPart
     * removes a query part from the query part array
     *
     * @param string $name          the name of the query part to be removed
     * @throws Doctrine_Hydrate_Exception   if trying to remove unknown query part
     * @return Doctrine_Hydrate     this object
     */
    public function removeQueryPart($name)
    {
        if (isset($this->parts[$name])) {
            if ($name == 'limit' || $name == 'offset') {
                $this->parts[$name] = false;
            } else {
                $this->parts[$name] = array();
            }
        } else {
            throw new Doctrine_Hydrate_Exception('Unknown query part ' . $name);
        }
        return $this;
    }

    /**
     * setQueryPart
     * sets a query part in the query part array
     *
     * @param string $name          the name of the query part to be set
     * @param string $part          query part string
     * @throws Doctrine_Hydrate_Exception   if trying to set unknown query part
     * @return Doctrine_Hydrate     this object
     */
    public function setQueryPart($name, $part)
    {
        if ( ! isset($this->parts[$name])) {
            throw new Doctrine_Hydrate_Exception('Unknown query part ' . $name);
        }

        if ($name !== 'limit' && $name !== 'offset') {
            if (is_array($part)) {
                $this->parts[$name] = $part;
            } else {
                $this->parts[$name] = array($part);
            }
        } else {
            $this->parts[$name] = $part;
        }

        return $this;
    }

    /**
     * hasAliasDeclaration
     * whether or not this object has a declaration for given component alias
     *
     * @param string $componentAlias    the component alias the retrieve the declaration from
     * @return boolean
     */
    public function hasAliasDeclaration($componentAlias)
    {
        return isset($this->_aliasMap[$componentAlias]);
    }

    /**
     * getAliasDeclaration
     * get the declaration for given component alias
     *
     * @param string $componentAlias    the component alias the retrieve the declaration from
     * @return array                    the alias declaration
     */
    public function getAliasDeclaration($componentAlias)
    {
        if ( ! isset($this->_aliasMap[$componentAlias])) {
            throw new Doctrine_Hydrate_Exception('Unknown component alias ' . $componentAlias);
        }

        return $this->_aliasMap[$componentAlias];
    }

    /**
     * copyAliases
     * copy aliases from another Hydrate object
     *
     * this method is needed by DQL subqueries which need the aliases
     * of the parent query
     *
     * @param Doctrine_Hydrate $query   the query object from which the
     *                                  aliases are copied from
     * @return Doctrine_Hydrate         this object
     */
    public function copyAliases(Doctrine_Hydrate $query)
    {
        $this->_tableAliases = $query->_tableAliases;
        $this->_aliasMap     = $query->_aliasMap;
        $this->_tableAliasSeeds = $query->_tableAliasSeeds;
        return $this;
    }

    /**
     * createSubquery
     * creates a subquery
     *
     * @return Doctrine_Hydrate
     */
    public function createSubquery()
    {
        $class = get_class($this);
        $obj   = new $class();

        // copy the aliases to the subquery
        $obj->copyAliases($this);

        // this prevents the 'id' being selected, re ticket #307
        $obj->isSubquery(true);

        return $obj;
    }

    /**
     * limitSubqueryUsed
     * whether or not limit subquery was used
     *
     * @return boolean
     */
    public function isLimitSubqueryUsed()
    {
        return false;
    }

    /**
     * clear
     * resets all the variables
     *
     * @return void
     */
    protected function clear()
    {
        $this->parts = array(
                    'select'    => array(),
                    'distinct'  => false,
                    'forUpdate' => false,
                    'from'      => array(),
                    'set'       => array(),
                    'join'      => array(),
                    'where'     => array(),
                    'groupby'   => array(),
                    'having'    => array(),
                    'orderby'   => array(),
                    'limit'     => false,
                    'offset'    => false,
                    );
        $this->inheritanceApplied = false;
    }

    /**
     * getConnection
     *
     * @return Doctrine_Connection
     */
    public function getConnection()
    {
        return $this->_conn;
    }

    /**
     * setView
     * sets a database view this query object uses
     * this method should only be called internally by doctrine
     *
     * @param Doctrine_View $view       database view
     * @return void
     */
    public function setView(Doctrine_View $view)
    {
        $this->_view = $view;
    }

    /**
     * getView
     * returns the view associated with this query object (if any)
     *
     * @return Doctrine_View        the view associated with this query object
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * getParams
     *
     * @return array
     */
    public function getParams()
    {
        return array_merge($this->_params['set'], $this->_params['where'], $this->_params['having']);
    }

    /**
     * setParams
     *
     * @param array $params
     */
    public function setParams(array $params = array()) {
        $this->_params = $params;
    }
    public function convertEnums($params)
    {
        return $params;
    }

    /**
     * setAliasMap
     * sets the whole component alias map
     *
     * @param array $map            alias map
     * @return Doctrine_Hydrate     this object
     */
    public function setAliasMap(array $map)
    {
        $this->_aliasMap = $map;

        return $this;
    }

    /**
     * getAliasMap
     * returns the component alias map
     *
     * @return array    component alias map
     */
    public function getAliasMap()
    {
        return $this->_aliasMap;
    }

    /**
     * getCachedForm
     * returns the cached form of this query for given resultSet
     *
     * @param array $resultSet
     * @return string           serialized string representation of this query
     */
    public function getCachedForm(array $resultSet)
    {
        $map = '';

        foreach ($this->getAliasMap() as $k => $v) {
            if ( ! isset($v['parent'])) {
                $map[$k][] = $v['table']->getComponentName();
            } else {
                $map[$k][] = $v['parent'] . '.' . $v['relation']->getAlias();
            }
            if (isset($v['agg'])) {
                $map[$k][] = $v['agg'];
            }
        }

        return serialize(array($resultSet, $map, $this->getTableAliases()));
    }
    public function _execute($params)
    {
        $params = $this->_conn->convertBooleans($params);

        if ( ! $this->_view) {
            $query = $this->getQuery($params);
        } else {
            $query = $this->_view->getSelectSql();
        }

        $params = $this->convertEnums($params);

        if ($this->isLimitSubqueryUsed() &&
            $this->_conn->getAttribute(Doctrine::ATTR_DRIVER_NAME) !== 'mysql') {

            $params = array_merge($params, $params);
        }

        if ($this->type !== self::SELECT) {
            return $this->_conn->exec($query, $params);
        }

        $stmt = $this->_conn->execute($query, $params);
        return $stmt;
    }

    /**
     * execute
     * executes the query and populates the data set
     *
     * @param string $params
     * @return Doctrine_Collection            the root collection
     */
    public function execute($params = array(), $hydrationMode = null)
    {
        $params = array_merge($this->_params['set'], 
                              $this->_params['where'],
                              $this->_params['having'], 
                              $params);
        if ($this->_cache) {
            $cacheDriver = $this->getCacheDriver();

            $dql  = $this->getDql();
            // calculate hash for dql query
            $hash = md5($dql . var_export($params, true));

            $cached = ($this->_expireCache) ? false : $cacheDriver->fetch($hash);


            if ($cached === false) {
                // cache miss
                $stmt = $this->_execute($params);
                $array = $this->parseData2($stmt, Doctrine::HYDRATE_ARRAY);

                $cached = $this->getCachedForm($array);

                $cacheDriver->save($hash, $cached, $this->_timeToLive);
            } else {
                $cached = unserialize($cached);
                $this->_tableAliases = $cached[2];
                $array = $cached[0];

                $map   = array();
                foreach ($cached[1] as $k => $v) {
                    $e = explode('.', $v[0]);
                    if (count($e) === 1) {
                        $map[$k]['table'] = $this->_conn->getTable($e[0]);
                    } else {
                        $map[$k]['parent']   = $e[0];
                        $map[$k]['relation'] = $map[$e[0]]['table']->getRelation($e[1]);
                        $map[$k]['table']    = $map[$k]['relation']->getTable();
                    }
                    if (isset($v[1])) {
                        $map[$k]['agg'] = $v[1];
                    }
                }
                $this->_aliasMap = $map;
            }
        } else {
            $stmt = $this->_execute($params);

            if (is_integer($stmt)) {
                return $stmt;
            }
            
            $array = $this->parseData2($stmt, $hydrationMode);
        }
        return $array;
    }

    /**
     * getType
     *
     * returns the type of this query object
     * by default the type is Doctrine_Hydrate::SELECT but if update() or delete()
     * are being called the type is Doctrine_Hydrate::UPDATE and Doctrine_Hydrate::DELETE,
     * respectively
     *
     * @see Doctrine_Hydrate::SELECT
     * @see Doctrine_Hydrate::UPDATE
     * @see Doctrine_Hydrate::DELETE
     *
     * @return integer      return the query type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * applyInheritance
     * applies column aggregation inheritance to DQL / SQL query
     *
     * @return string
     */
    public function applyInheritance()
    {
        // get the inheritance maps
        $array = array();

        foreach ($this->_aliasMap as $componentAlias => $data) {
            $tableAlias = $this->getTableAlias($componentAlias);
            $array[$tableAlias][] = $data['table']->inheritanceMap;
        }

        // apply inheritance maps
        $str = '';
        $c = array();

        $index = 0;
        foreach ($array as $tableAlias => $maps) {
            $a = array();

            // don't use table aliases if the query isn't a select query
            if ($this->type !== Doctrine_Query::SELECT) {
                $tableAlias = '';
            } else {
                $tableAlias .= '.';
            }

            foreach ($maps as $map) {
                $b = array();
                foreach ($map as $field => $value) {
                    $identifier = $this->_conn->quoteIdentifier($tableAlias . $field);

                    if ($index > 0) {
                        $b[] = '(' . $identifier . ' = ' . $this->_conn->quote($value)
                             . ' OR ' . $identifier . ' IS NULL)';
                    } else {
                        $b[] = $identifier . ' = ' . $this->_conn->quote($value);
                    }
                }

                if ( ! empty($b)) {
                    $a[] = implode(' AND ', $b);
                }
            }

            if ( ! empty($a)) {
                $c[] = implode(' AND ', $a);
            }
            $index++;
        }

        $str .= implode(' AND ', $c);

        return $str;
    }

    /**
     * fetchArray
     * Convenience method to execute using array fetching as hydration mode.
     *
     * @param string $params
     * @return array
     */
    public function fetchArray($params = array()) {
        return $this->execute($params, Doctrine::HYDRATE_ARRAY);
    }

    /**
     * fetchOne
     * Convenience method to execute the query and return the first item
     * of the collection.
     *
     * @param string $params Parameters
     * @param int $hydrationMode Hydration mode
     * @return mixed Array or Doctrine_Collection or false if no result.
     */
    public function fetchOne($params = array(), $hydrationMode = null)
    {
        if (is_null($hydrationMode)) {
            $hydrationMode = $this->_hydrationMode;
        }

        $collection = $this->execute($params, $hydrationMode);

        if (count($collection) === 0) {
            return false;
        }

        switch ($hydrationMode) {
            case Doctrine::HYDRATE_RECORD:
                return $collection->getFirst();
            case Doctrine::HYDRATE_ARRAY:
                return array_shift($collection);
        }

        return false;
    }

    /**
     * parseData
     * parses the data returned by statement object
     *
     * This is method defines the core of Doctrine object population algorithm
     * hence this method strives to be as fast as possible
     *
     * The key idea is the loop over the rowset only once doing all the needed operations
     * within this massive loop.
     *
     * @param mixed $stmt
     * @return array
     */
    public function parseData2($stmt, $hydrationMode)
    {
        if ($hydrationMode == Doctrine::HYDRATE_NONE) {
            return $stmt->fetchAll(PDO::FETCH_NUM);
        }
        
        $cache = array();
        $rootMap   = reset($this->_aliasMap);
        $rootAlias = key($this->_aliasMap);
        $componentName = $rootMap['table']->getComponentName();
        $isSimpleQuery = count($this->_aliasMap) <= 1;
        
        if ($hydrationMode === null) {
            $hydrationMode = $this->_hydrationMode;
        }

        if ($hydrationMode === Doctrine::HYDRATE_ARRAY) {
            $driver = new Doctrine_Hydrate_Array();
        } else {
            $driver = new Doctrine_Hydrate_Record();
        }

        $array = $driver->getElementCollection($componentName);

        if ($stmt === false || $stmt === 0) {
            return $array;
        }

        $event = new Doctrine_Event(null, Doctrine_Event::HYDRATE, null);

        // for every getRecordListener() there is a little bit 
        // logic behind it, hence calling it multiple times on
        // large result sets can be quite expensive.
        // So for efficiency we use little listener caching here
        foreach ($this->_aliasMap as $alias => $data) {
            $componentName = $data['table']->getComponentName();
            $listeners[$componentName] = $data['table']->getRecordListener();
            $identifierMap[$alias] = array();
            $currData[$alias] = array();
            $prev[$alias] = array();
            $id[$alias] = '';
        }

        while ($data = $stmt->fetch(Doctrine::FETCH_ASSOC)) {
            $currData  = array();
            $identifiable = array();

            foreach ($data as $key => $value) {

                // The following little cache solution ensures that field aliases are
                // parsed only once. This increases speed on large result sets by an order
                // of magnitude.
                if ( ! isset($cache[$key])) {
                    $e = explode('__', $key);
                    $cache[$key]['field'] = $field = strtolower(array_pop($e));
                    $cache[$key]['alias'] = $this->_tableAliases[strtolower(implode('__', $e))];
                }

                $map   = $this->_aliasMap[$cache[$key]['alias']];
                $table = $map['table'];
                $alias = $cache[$key]['alias'];
                $field = $cache[$key]['field'];

                if (isset($this->_aliasMap[$alias]['agg'][$field])) {
                    $field = $this->_aliasMap[$alias]['agg'][$field];
                }


                if ($table->isIdentifier($field)) {
                    $id[$alias] .= '|' . $value;
                }

                $currData[$alias][$field] = $table->prepareValue($field, $value);

                if ($value !== null) {
                    $identifiable[$alias] = true;
                }
            }

            // dealing with root component
            $table = $this->_aliasMap[$rootAlias]['table'];
            $componentName = $table->getComponentName();
            $event->set('data', $currData[$rootAlias]);
            $listeners[$componentName]->preHydrate($event);
            $element = $driver->getElement($currData[$rootAlias], $componentName);

            $oneToOne = false;

            if ($isSimpleQuery) {
                $index = false;
            } else {
                $index = isset($identifierMap[$rootAlias][$id[$rootAlias]]) ?
                         $identifierMap[$rootAlias][$id[$rootAlias]] : false;
            }

            if ($index === false) {
                $event->set('data', $element);
                $listeners[$componentName]->postHydrate($event);

                if (isset($this->_aliasMap[$rootAlias]['map'])) {
                    $key = $this->_aliasMap[$rootAlias]['map'];

                    if (isset($array[$key])) {
                        throw new Doctrine_Hydrate_Exception("Couldn't hydrate. Found non-unique key mapping.");
                    }

                    if ( ! isset($element[$key])) {
                        throw new Doctrine_Hydrate_Exception("Couldn't hydrate. Found a non-existent key.");
                    }

                    $array[$element[$key]] = $element;
                } else {
                    $array[] = $element;
                }

                $identifierMap[$rootAlias][$id[$rootAlias]] = $driver->getLastKey($array);
            }

            $this->_setLastElement($prev, $array, $index, $rootAlias, $oneToOne);
            unset($currData[$rootAlias]);

            foreach ($currData as $alias => $data) {
                $index = false;
                $map   = $this->_aliasMap[$alias];
                $table = $this->_aliasMap[$alias]['table'];
                $componentName = $table->getComponentName();
                $event->set('data', $data);
                $listeners[$componentName]->preHydrate($event);

                $element = $driver->getElement($data, $componentName);

                $parent   = $map['parent'];
                $relation = $map['relation'];
                $componentAlias = $map['relation']->getAlias();

                $path = $parent . '.' . $alias;

                if ( ! isset($prev[$parent])) {
                    break;
                }

                // check the type of the relation
                if ( ! $relation->isOneToOne()) {
                    // initialize the collection

                    if ($driver->initRelated($prev[$parent], $componentAlias)) {

                        // append element
                        if (isset($identifiable[$alias])) {
                            if ($isSimpleQuery) {
                                $index = false;
                            } else {
                                $index = isset($identifierMap[$path][$id[$parent]][$id[$alias]]) ?
                                         $identifierMap[$path][$id[$parent]][$id[$alias]] : false;
                            }

                            if ($index === false) {
                                $event->set('data', $element);
                                $listeners[$componentName]->postHydrate($event);

                                if (isset($map['map'])) {
                                    $key = $map['map'];
                                    if (isset($prev[$parent][$componentAlias][$key])) {
                                        throw new Doctrine_Hydrate_Exception("Couldn't hydrate. Found non-unique key mapping.");
                                    }
                                    if ( ! isset($element[$key])) {
                                        throw new Doctrine_Hydrate_Exception("Couldn't hydrate. Found a non-existent key.");
                                    }
                                    $prev[$parent][$componentAlias][$element[$key]] = $element;
                                } else {
                                    $prev[$parent][$componentAlias][] = $element;
                                }

                                $identifierMap[$path][$id[$parent]][$id[$alias]] = $driver->getLastKey($prev[$parent][$componentAlias]);
                            }
                        }
                        // register collection for later snapshots
                        $driver->registerCollection($prev[$parent][$componentAlias]);
                    }
                } else {
                    if ( ! isset($identifiable[$alias])) {
                        $prev[$parent][$componentAlias] = $driver->getNullPointer();
                    } else {
                        $prev[$parent][$componentAlias] = $element;
                    }
                    $oneToOne = true;
                }
                $coll =& $prev[$parent][$componentAlias];
                $this->_setLastElement($prev, $coll, $index, $alias, $oneToOne);
                $id[$alias] = '';
            }
            $id[$rootAlias] = '';
        }

        $driver->flush();

        $stmt->closeCursor();
        return $array;
    }

    /**
     * _setLastElement
     *
     * sets the last element of given data array / collection
     * as previous element
     *
     * @param boolean|integer $index
     * @return void
     */
    public function _setLastElement(&$prev, &$coll, $index, $alias, $oneToOne)
    {
        if ($coll === self::$_null) {
            return false;
        }
        if ($index !== false) {
            $prev[$alias] =& $coll[$index];
        } else {
            // first check the count (we do not want to get the last element
            // of an empty collection/array)
            if (count($coll) > 0) {
                if (is_array($coll)) {
                    if ($oneToOne) {
                        $prev[$alias] =& $coll;
                    } else {
                        end($coll);
                        $prev[$alias] =& $coll[key($coll)];
                    }
                } else {
                    $prev[$alias] = $coll->getLast();
                }
            } else {
                if (isset($prev[$alias])) {
                    unset($prev[$alias]);
                }
            }
        }
    }

    /**
     * @return string                   returns a string representation of this object
     */
    public function __toString()
    {
        return Doctrine_Lib::formatSql($this->getQuery());
    }

    /**
     * Return the parts
     *
     * @return array The parts
     */
    public function getParts()
    {
        return $this->parts;
    }
}
