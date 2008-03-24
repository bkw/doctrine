<?php

/*
 *  $Id: Query.php 3938 2008-03-06 19:36:50Z romanb $
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

Doctrine::autoload('Doctrine_Query_Abstract');

/**
 * Doctrine_Query
 *
 * A Doctrine_Query object represents a DQL query. It is used to query databases for
 * data in an object-oriented fashion. A DQL query understands relations and inheritance
 * and is dbms independant.
 *
 * @package     Doctrine
 * @subpackage  Query
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision: 3938 $
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @todo        Proposal: This class does far too much. It should have only 1 task: Collecting
 *              the DQL query parts and the query parameters (the query state and caching options/methods
 *              can remain here, too).
 *              The actual SQL construction could be done by a separate object (Doctrine_Query_SqlBuilder?)
 *              whose task it is to convert DQL into SQL.
 *              Furthermore the SqlBuilder? can then use other objects (Doctrine_Query_Tokenizer?),
 *              (Doctrine_Query_Parser(s)?) to accomplish his work. Doctrine_Query does not need
 *              to know the tokenizer/parsers. There could be extending
 *              implementations of SqlBuilder? that cover the specific SQL dialects.
 *              This would release Doctrine_Connection and the Doctrine_Connection_xxx classes
 *              from this tedious task.
 *              This would also largely reduce the currently huge interface of Doctrine_Query(_Abstract)
 *              and better hide all these transformation internals from the public Query API.
 *
 * @internal    The lifecycle of a Query object is the following:
 *              After construction the query object is empty. Through using the fluent
 *              query interface the user fills the query object with DQL parts and query parameters.
 *              These get collected in {@link $_dqlParts} and {@link $_params}, respectively.
 *              When the query is executed the first time, or when {@link getSqlQuery()}
 *              is called the first time, the collected DQL parts get parsed and the resulting
 *              connection-driver specific SQL is generated. The generated SQL parts are
 *              stored in {@link $_sqlParts} and the final resulting SQL query is stored in
 *              {@link $_sql}.
 */
class Doctrine_Query extends Doctrine_Query_Abstract
{
    /**
     * @var Doctrine_Connection The connection used by this query object.
     */
    protected $_connection;

    /**
     * @var Doctrine_Hydrator   The hydrator object used to hydrate query results.
     */
    protected $_hydrator;

    /**
     * @var Doctrine_Query_ParserResult  The parser result that holds DQL => SQL information.
     */
    protected $_parserResult;

    /**
     * @var string $_sql Cached SQL query.
     */
    protected $_sql = null;


    // Caching Stuff

    /**
     * @var Doctrine_Cache_Interface  The cache driver used for caching result sets.
     */
    protected $_resultCache;

    /**
     * @var boolean Boolean value that indicates whether or not expire the result cache.
     */
    protected $_expireResultCache = false;

    /**
     * @var int Result Cache lifetime.
     */
    protected $_resultCacheTTL;


    /**
     * @var Doctrine_Cache_Interface  The cache driver used for caching queries.
     */
    protected $_queryCache;

    /**
     * @var boolean Boolean value that indicates whether or not expire the query cache.
     */
    protected $_expireQueryCache = false;

    /**
     * @var int Query Cache lifetime.
     */
    protected $_queryCacheTTL;

    // End of Caching Stuff


    public function __construct(Doctrine_Connection $conn = null, Doctrine_Hydrator_Abstract $hydrator = null)
    {
        $this->setConnection($conn);

        if ($hydrator === null) {
            $hydrator = new Doctrine_Hydrator();
        }

        $this->_hydrator = $hydrator;

        $this->free();
    }


    /**
     * create
     *
     * Returns a new Doctrine_Query object
     *
     * @param Doctrine_Connection $conn     optional connection parameter
     * @return Doctrine_Query
     */
    public static function create($conn = null)
    {
        return new self($conn);
    }


    /**
     * getConnection
     *
     * Retrieves the assocated Doctrine_Connection to this Doctrine_Query
     *
     * @return Doctrine_Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }


    /**
     * setConnection
     *
     * Defines an assocated Doctrine_Connection to this Doctrine_Query
     *
     * @param Doctrine_Connection $conn A valid Doctrine_Connection
     * @return void
     */
    public function setConnection(Doctrine_Connection $conn = null)
    {
        if ($conn === null) {
            $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
        }

        $this->_connection = $conn;
    }


    /**
     * getHydrator
     *
     * Returns the hydrator associated with this query object
     *
     * @return Doctrine_Hydrator The hydrator associated with this query object
     */
    public function getHydrator()
    {
        return $this->_hydrator;
    }


    /**
     * fetchArray
     *
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
     *
     * Convenience method to execute the query and return the first item
     * of the collection.
     *
     * @param string $params Parameters
     * @param int $hydrationMode Hydration mode
     * @return mixed Array or Doctrine_Collection or false if no result.
     */
    public function fetchOne($params = array(), $hydrationMode = null)
    {
        $collection = $this->execute($params, $hydrationMode);

        if (count($collection) === 0) {
            return false;
        }

        if ($collection instanceof Doctrine_Collection) {
            return $collection->getFirst();
        } else if (is_array($collection)) {
            return array_shift($collection);
        }

        return false;
    }


    /**
     * query
     *
     * Query the database with DQL (Doctrine Query Language).
     *
     * @param string $query      DQL query
     * @param array $params      prepared statement parameters
     * @param int $hydrationMode Doctrine::FETCH_ARRAY or Doctrine::FETCH_RECORD
     * @see Doctrine::FETCH_* constants
     * @return mixed
     */
    public function query($query, $params = array(), $hydrationMode = null)
    {
        $this->setDql($query);
        return $this->execute($params, $hydrationMode);
    }


    /**
     * getSql
     *
     * Builds the sql query from the given parameters and applies things such as
     * column aggregation inheritance and limit subqueries if needed
     *
     * @return string The built sql query
     */
    public function getSql()
    {
        if ($this->_state === self::STATE_DIRTY) {
            $parser = new Doctrine_Query_Parser($this->getDql());
            $this->_parserResult = $parser->parse();

            $this->_state = self::STATE_CLEAN;
            $this->_sql = $this->_parserResult->getSql();
        }

        return $this->_sql;
    }


    /**
     * execute
     *
     * Executes the query and populates the data set.
     *
     * @param string $params Parameters to be sent to query.
     * @param integer $hydrationMode Doctrine processing mode to be used during hydration process.
     *                               One of the Doctrine::HYDRATE_* constants.
     * @return Doctrine_Collection The root collection
     */
    public function execute($params = array(), $hydrationMode = null)
    {
        $params = $this->getParams($params);

        // If there is a CacheDriver associated to cache resultsets...
        if ($this->_resultCache && $this->_type === self::SELECT) { // Only executes if "SELECT"
            $cacheDriver = $this->getResultCacheDriver();

            // Calculate hash for dql query.
            $hash = md5($this->getDql() . var_export($params, true));
            $cached = ($this->_expireResultCache) ? false : $cacheDriver->fetch($hash);

            if ($cached === false) {
                // Cache does not exist, we have to create it.
                $result = $this->_execute($params, Doctrine::HYDRATE_ARRAY);

                $queryResult = Doctrine_Query_CacheHandler::fromResultSet($this, $result);
                $cacheDriver->save($hash, $queryResult->toCachedForm(), $this->_resultCacheTTL);

                return $result;
            } else {
                // Cache exists, recover it and return the results.
                $queryResult = Doctrine_Query_CacheHandler::fromCachedResult($this, $cached);

                return $queryResult->getResultSet();
            }
        }

        return $this->_execute($params, $hydrationMode);
    }


    /**
     * _execute
     *
     * @param string $params Parameters to be sent to query.
     * @param int $hydrationMode Method of hydration to be used.
     * @return Doctrine_Collection The root collection
     */
    protected function _execute($params, $hydrationMode)
    {
        // preQuery invoking
        $this->preQuery();

        // Query execution
        $stmt = $this->_execute2($params);

        // postQuery invoking
        $this->postQuery();

        if (is_integer($stmt)) {
            return $stmt;
        }

        return $this->_hydrator->hydrateResultSet($stmt, $hydrationMode);
    }


    /**
     * _execute2
     *
     * @param array $params
     * @return PDOStatement  The executed PDOStatement.
     */
    protected function _execute2($params)
    {
        // If there is a CacheDriver associated to cache queries...
        if ($this->_queryCache || $this->_connection->getAttribute(Doctrine::ATTR_QUERY_CACHE)) {
            $queryCacheDriver = $this->getQueryCacheDriver();

            // Calculate hash for dql query.
            $hash = md5($this->getDql() . 'DOCTRINE_QUERY_CACHE_SALT');
            $cached = ($this->_expireQueryCache) ? false : $queryCacheDriver->fetch($hash);

            if ($cached === false) {
                // Cache does not exist, we have to create it.
                $query = $this->getSql();

                // To-be cached item is parserResult
                $cacheDriver->save($hash, $this->_parserResult->toCachedForm(), $this->_queryCacheTTL);
            } else {
                // Cache exists, recover it and return the results.
                $this->_parserResult = Doctrine_Query_CacheHandler::fromCachedQuery($this, $cached);

                $query = $this->_parserResult->getSql();
            }
        } else {
            $query = $this->getSql();
        }

        // Assignments for Hydrator and Enums
        $this->_hydrator->setQueryComponents($this->_parserResult->getQueryComponents());
        $this->_hydrator->setTableAliasMap($this->_parserResult->getTableAliasMap());
        $this->_setEnumParams($this->_parserResult->getEnumParams());

        // Converting parameters
        $params = $this->_prepareParams($params);

        // Double the params if we are using limit-subquery algorithm
        // We always have an instance of Doctrine_Query_ParserResult on hands...
        if ($this->_parserResult->isLimitSubqueryUsed() &&
            $this->_connection->getAttribute(Doctrine::ATTR_DRIVER_NAME) !== 'mysql') {
            $params = array_merge($params, $params);
        }

        // Executing the query and assigning PDOStatement
        if ($this->_type !== self::SELECT) {
            return $this->_connection->exec($query, $params);
        }

        return $this->_connection->execute($query, $params);
    }


    /**
     * @nodoc
     */
    protected function _prepareParams(array $params)
    {
        // Convert boolean params
        $params = $this->_connection->convertBooleans($params);

        // Convert enum params
        return $this->convertEnums($params);
    }


    /**
     * setResultCache
     *
     * Defines a cache driver to be used for caching result sets.
     *
     * @param Doctrine_Cache_Interface|null $driver Cache driver
     * @return Doctrine_Query
     */
    public function setResultCache($resultCache)
    {
        if ($resultCache !== null && ! ($resultCache instanceof Doctrine_Cache_Interface)) {
            throw new Doctrine_Query_Exception(
                'Method setResultCache() accepts only an instance of Doctrine_Cache_Interface or null.'
            );
        }

        $this->_resultCache = $resultCache;

        return $this;
    }


    /**
     * getResultCache
     *
     * Returns the cache driver used for caching result sets.
     *
     * @return Doctrine_Cache_Interface Cache driver
     */
    public function getResultCache()
    {
        if ($this->_resultCache instanceof Doctrine_Cache_Interface) {
            return $this->_resultCache;
        } else {
            return $this->_connection->getResultCacheDriver();
        }
    }


    /**
     * setResultCacheLifetime
     *
     * Defines how long the result cache will be active before expire.
     *
     * @param integer $timeToLive How long the cache entry is valid
     * @return Doctrine_Query
     */
    public function setResultCacheLifetime($timeToLive)
    {
        if ($timeToLive !== null) {
            $timeToLive = (int) $timeToLive;
        }

        $this->_resultCacheTTL = $timeToLive;

        return $this;
    }


    /**
     * getResultCacheLifetime
     *
     * Retrieves the lifetime of resultset cache.
     *
     * @return int
     */
    public function getResultCacheLifetime()
    {
        return $this->_resultCacheTTL;
    }


    /**
     * setExpireResultCache
     *
     * Defines if the resultset cache is active or not.
     *
     * @param boolean $expire Whether or not to force resultset cache expiration.
     * @return Doctrine_Query
     */
    public function setExpireResultCache($expire = true)
    {
        $this->_expireResultCache = (bool) $expire;

        return $this;
    }


    /**
     * getExpireResultCache
     *
     * Retrieves if the resultset cache is active or not.
     *
     * @return bool
     */
    public function getExpireResultCache()
    {
        return $this->_expireResultCache;
    }


    /**
     * setQueryCache
     *
     * Defines a cache driver to be used for caching queries.
     *
     * @param Doctrine_Cache_Interface|null $driver Cache driver
     * @return Doctrine_Query
     */
    public function setQueryCache($queryCache)
    {
        if ($queryCache !== null && ! ($queryCache instanceof Doctrine_Cache_Interface)) {
            throw new Doctrine_Query_Exception(
                'Method setResultCache() accepts only an instance of Doctrine_Cache_Interface or null.'
            );
        }

        $this->_queryCache = $queryCache;

        return $this;
    }


    /**
     * getQueryCache
     *
     * Returns the cache driver used for caching queries.
     *
     * @return Doctrine_Cache_Interface Cache driver
     */
    public function getQueryCache()
    {
        if ($this->_queryCache instanceof Doctrine_Cache_Interface) {
            return $this->_queryCache;
        } else {
            return $this->_connection->getQueryCacheDriver();
        }
    }


    /**
     * setQueryCacheLifetime
     *
     * Defines how long the query cache will be active before expire.
     *
     * @param integer $timeToLive How long the cache entry is valid
     * @return Doctrine_Query
     */
    public function setQueryCacheLifetime($timeToLive)
    {
        if ($timeToLive !== null) {
            $timeToLive = (int) $timeToLive;
        }

        $this->_queryCacheTTL = $timeToLive;

        return $this;
    }


    /**
     * getQueryCacheLifetime
     *
     * Retrieves the lifetime of resultset cache.
     *
     * @return int
     */
    public function getQueryCacheLifetime()
    {
        return $this->_queryCacheTTL;
    }


    /**
     * setExpireQueryCache
     *
     * Defines if the query cache is active or not.
     *
     * @param boolean $expire Whether or not to force query cache expiration.
     * @return Doctrine_Query
     */
    public function setExpireQueryCache($expire = true)
    {
        $this->_expireQueryCache = (bool) $expire;

        return $this;
    }


    /**
     * getExpireQueryCache
     *
     * Retrieves if the query cache is active or not.
     *
     * @return bool
     */
    public function getExpireQueryCache()
    {
        return $this->_expireQueryCache;
    }


    /**
     * setHydrationMode
     *
     * Defines the processing mode to be used during hydration process.
     *
     * @param integer $hydrationMode Doctrine processing mode to be used during hydration process.
     *                               One of the Doctrine::HYDRATE_* constants.
     * @return Doctrine_Query
     */
    public function setHydrationMode($hydrationMode)
    {
        $this->_hydrator->setHydrationMode($hydrationMode);

        return $this;
    }


    /**
     * preQuery
     *
     * Empty template method to provide Query subclasses with the possibility
     * to hook into the query building procedure, doing any custom / specialized
     * query building procedures that are neccessary.
     *
     * @return void
     */
    public function preQuery()
    {

    }

    /**
     * postQuery
     *
     * Empty template method to provide Query subclasses with the possibility
     * to hook into the query building procedure, doing any custom / specialized
     * post query procedures (for example logging) that are neccessary.
     *
     * @return void
     */
    public function postQuery()
    {

    }


    /**
     * serialize
     *
     * This method is automatically called when this Doctrine_Hydrate is serialized.
     *
     * @return array An array of serialized properties
     */
    public function serialize()
    {
        $vars = get_object_vars($this);
    }

    /**
     * unseralize
     *
     * This method is automatically called everytime a Doctrine_Hydrate object is unserialized.
     *
     * @param string $serialized Doctrine_Record as serialized string
     * @return void
     */
    public function unserialize($serialized)
    {

    }
}
