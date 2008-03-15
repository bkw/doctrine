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

Doctrine::autoload('Doctrine_Query_Abstract2');

/**
 * Doctrine_Query2
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
class Doctrine_Query2 extends Doctrine_Query_Abstract2
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
     * @var Doctrine_Query_Parser  The parser that is used during the DQL convertion to SQL.
     */
    protected $_parser;


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

        $this->_parser = new Doctrine_Query_Parser($this);
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
     * getParser
     *
     * Returns the parser associated with this query object
     *
     * @return Doctrine_Query_Parser The parser associated with this query object
     */
    public function getParser()
    {
        return $this->_parser;
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


    public function getExpireQueryCache()
    {
        return $this->_expireQueryCache;
    }


    /**
     * setHydrationMode
     *
     * @params $hydrationMode Doctrine processing mode to be used during hydration process.
     * @return Doctrine_Query
     */
    public function setHydrationMode($hydrationMode)
    {
        $this->_hydrator->setHydrationMode($hydrationMode);

        return $this;
    }



    public function getSqlQuery($params = array())
    {
        return $this->getDql();
    }
}
