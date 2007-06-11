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
 *
 * Doctrine_Manager is the base component of all doctrine based projects.
 * It opens and keeps track of all connections (database connections).
 *
 * @package     Doctrine
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.com
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_Manager extends Doctrine_Configurable implements Countable, IteratorAggregate
{
    /**
     * @var array $connections      an array containing all the opened connections
     */
    protected $_connections   = array();
    /**
     * @var array $bound            an array containing all components that have a bound connection
     */
    protected $_bound          = array();
    /**
     * @var integer $index          the incremented index
     */
    protected $_index      = 0;
    /**
     * @var integer $currIndex      the current connection index
     */
    protected $_currIndex  = 0;
    /**
     * @var string $root            root directory
     */
    protected $_root;
    /**
     * @var Doctrine_Null $null     Doctrine_Null object, used for extremely fast null value checking
     */
    protected $_null;
    /**
     * @var array $driverMap
     */
    protected $_driverMap        = array('oracle'     => 'oci8',
                                         'postgres'   => 'pgsql',
                                         'oci'        => 'oci8',
                                         'sqlite2'    => 'sqlite',
                                         'sqlite3'    => 'sqlite');
    /**
     * constructor
     *
     * this is private constructor (use getInstance to get an instance of this class)
     */
    private function __construct()
    {
        $this->_root = dirname(__FILE__);
        $this->_null = new Doctrine_Null; 

        Doctrine_Record_Iterator::initNullObject($this->_null);
        Doctrine_Validator::initNullObject($this->_null);
        Doctrine_Object::initNullObject($this->_null);
    }
    /**
     * @return Doctrine_Null
     */
    final public function getNullObject()
    {
        return $this->_null;
    }
    /**
     * setDefaultAttributes
     * sets default attributes
     *
     * @return boolean
     */
    public function setDefaultAttributes()
    {
        static $init = false;
        if ( ! $init) {
            $init = true;
            $attributes = array(
                        Doctrine::ATTR_DQL_PARSER_CACHE => null,
                        Doctrine::ATTR_DQL_CACHE        => null,
                        Doctrine::ATTR_SQL_CACHE        => null,
                        Doctrine::ATTR_LOAD_REFERENCES  => true,
                        Doctrine::ATTR_LISTENER         => new Doctrine_EventListener(),
                        Doctrine::ATTR_LOCKMODE         => 1,
                        Doctrine::ATTR_VLD              => false,
                        Doctrine::ATTR_AUTO_LENGTH_VLD  => true,
                        Doctrine::ATTR_AUTO_TYPE_VLD    => true,
                        Doctrine::ATTR_QUERY_LIMIT      => Doctrine::LIMIT_RECORDS,
                        Doctrine::ATTR_IDXNAME_FORMAT   => "%s_idx",
                        Doctrine::ATTR_SEQNAME_FORMAT   => "%s_seq",
                        Doctrine::ATTR_QUOTE_IDENTIFIER => false,
                        Doctrine::ATTR_SEQCOL_NAME      => 'id',
                        Doctrine::ATTR_PORTABILITY      => Doctrine::PORTABILITY_ALL,
                        Doctrine::ATTR_EXPORT           => Doctrine::EXPORT_NONE,
                        Doctrine::ATTR_DECIMAL_PLACES   => 2,
                        );
            foreach ($attributes as $attribute => $value) {
                $old = $this->getAttribute($attribute);
                if ($old === null) {
                    $this->setAttribute($attribute,$value);
                }
            }
            return true;
        }
        return false;
    }
    /**
     * returns the root directory of Doctrine
     *
     * @return string
     */
    final public function getRoot()
    {
        return $this->_root;
    }
    /**
     * getInstance
     * returns an instance of this class
     * (this class uses the singleton pattern)
     *
     * @return Doctrine_Manager
     */
    public static function getInstance()
    {
        static $instance;
        if ( ! isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }
    /**
     * connection
     *
     * if the adapter parameter is set this method acts as
     * a short cut for Doctrine_Manager::getInstance()->openConnection($adapter, $name);
     *
     * if the adapter paramater is not set this method acts as
     * a short cut for Doctrine_Manager::getInstance()->getCurrentConnection()
     *
     * @param PDO|Doctrine_Adapter_Interface $adapter   database driver
     * @param string $name                              name of the connection, if empty numeric key is used
     * @throws Doctrine_Manager_Exception               if trying to bind a connection with an existing name
     * @return Doctrine_Connection
     */
    public static function connection($adapter = null, $name = null)
    {
        if ($adapter == null) {
            return Doctrine_Manager::getInstance()->getCurrentConnection();
        } else {
            return Doctrine_Manager::getInstance()->openConnection($adapter, $name);
        }
    }
    /**
     * openConnection
     * opens a new connection and saves it to Doctrine_Manager->connections
     *
     * @param PDO|Doctrine_Adapter_Interface $adapter   database driver
     * @param string $name                              name of the connection, if empty numeric key is used
     * @throws Doctrine_Manager_Exception               if trying to bind a connection with an existing name
     * @throws Doctrine_Manager_Exception               if trying to open connection for unknown driver
     * @return Doctrine_Connection
     */
    public function openConnection($adapter, $name = null, $setCurrent = true)
    {
        if ( ! ($adapter instanceof PDO) && ! in_array('Doctrine_Adapter_Interface', class_implements($adapter))) {
            throw new Doctrine_Manager_Exception("First argument should be an instance of PDO or implement Doctrine_Adapter_Interface");
        }

        if ($adapter instanceof Doctrine_Db) {
            $adapter->setName($name);
        }

        // initialize the default attributes
        $this->setDefaultAttributes();

        if ($name !== null) {
            $name = (string) $name;
            if (isset($this->_connections[$name])) {
                return $this->_connections[$name];
            }
        } else {
            $name = $this->_index;
            $this->_index++;
        }

        switch ($adapter->getAttribute(Doctrine::ATTR_DRIVER_NAME)) {
            case 'mysql':
                $this->_connections[$name] = new Doctrine_Connection_Mysql($this, $adapter);
                break;
            case 'sqlite':
                $this->_connections[$name] = new Doctrine_Connection_Sqlite($this, $adapter);
                break;
            case 'pgsql':
                $this->_connections[$name] = new Doctrine_Connection_Pgsql($this, $adapter);
                break;
            case 'oci':
            case 'oci8':
            case 'oracle':
                $this->_connections[$name] = new Doctrine_Connection_Oracle($this, $adapter);
                break;
            case 'mssql':
            case 'dblib':
                $this->_connections[$name] = new Doctrine_Connection_Mssql($this, $adapter);
                break;
            case 'firebird':
                $this->_connections[$name] = new Doctrine_Connection_Firebird($this, $adapter);
                break;
            case 'informix':
                $this->_connections[$name] = new Doctrine_Connection_Informix($this, $adapter);
                break;
            case 'mock':
                $this->_connections[$name] = new Doctrine_Connection_Mock($this, $adapter);
                break;
            default:
                throw new Doctrine_Manager_Exception('Unknown connection driver '. $adapter->getAttribute(Doctrine::ATTR_DRIVER_NAME));
        };

        if ($setCurrent) {
            $this->_currIndex = $name;
        }
        return $this->_connections[$name];
    }
    /**
     * getConnection
     * @param integer $index
     * @return object Doctrine_Connection
     * @throws Doctrine_Manager_Exception   if trying to get a non-existent connection
     */
    public function getConnection($name)
    {
        if ( ! isset($this->_connections[$name])) {
            throw new Doctrine_Manager_Exception('Unknown connection: ' . $name);
        }

        return $this->_connections[$name];
    }
    /**
     * getComponentAlias
     * retrieves the alias for given component name
     * if the alias couldn't be found, this method returns the given
     * component name
     *
     * @param string $componentName
     * @return string                   the component alias
     */
    public function getComponentAlias($componentName)
    {
        if (isset($this->componentAliases[$componentName])) {
            return $this->componentAliases[$componentName];
        }

        return $componentName;
    }
    /**
     * sets an alias for given component name
     * very useful when building a large framework with a possibility
     * to override any given class
     *
     * @param string $componentName         the name of the component
     * @param string $alias
     * @return Doctrine_Manager
     */
    public function setComponentAlias($componentName, $alias)
    {
        $this->componentAliases[$componentName] = $alias;

        return $this;
    }
    /**
     * getConnectionName
     *
     * @param Doctrine_Connection $conn     connection object to be searched for
     * @return string                       the name of the connection
     */
    public function getConnectionName(Doctrine_Connection $conn)
    {
        return array_search($conn, $this->_connections, true);
    }
    /**
     * bindComponent
     * binds given component to given connection
     * this means that when ever the given component uses a connection
     * it will be using the bound connection instead of the current connection
     *
     * @param string $componentName
     * @param string $connectionName
     * @return boolean
     */
    public function bindComponent($componentName, $connectionName)
    {
        $this->_bound[$componentName] = $connectionName;
    }
    /**
     * getConnectionForComponent
     *
     * @param string $componentName
     * @return Doctrine_Connection
     */
    public function getConnectionForComponent($componentName = null)
    {
        if (isset($this->_bound[$componentName])) {
            return $this->getConnection($this->_bound[$componentName]);
        }
        return $this->getCurrentConnection();
    }
    /**
     * getTable
     * this is the same as Doctrine_Connection::getTable() except
     * that it works seamlessly in multi-server/connection environment
     *
     * @see Doctrine_Connection::getTable()
     * @param string $componentName
     * @return Doctrine_Table
     */
    public function getTable($componentName)
    {
        return $this->getConnectionForComponent($componentName)->getTable($componentName);
    }
    /**
     * closes the connection
     *
     * @param Doctrine_Connection $connection
     * @return void
     */
    public function closeConnection(Doctrine_Connection $connection)
    {
        $connection->close();

        $key = array_search($connection, $this->_connections, true);

        if ($key !== false) {
            unset($this->_connections[$key]);
        }
        $this->_currIndex = key($this->_connections);

        unset($connection);
    }
    /**
     * getConnections
     * returns all opened connections
     *
     * @return array
     */
    public function getConnections()
    {
        return $this->_connections;
    }
    /**
     * setCurrentConnection
     * sets the current connection to $key
     *
     * @param mixed $key                        the connection key
     * @throws InvalidKeyException
     * @return void
     */
    public function setCurrentConnection($key)
    {
        $key = (string) $key;
        if ( ! isset($this->_connections[$key])) {
            throw new InvalidKeyException();
        }
        $this->_currIndex = $key;
    }
    /**
     * contains
     * whether or not the manager contains specified connection
     *
     * @param mixed $key                        the connection key
     * @return boolean
     */
    public function contains($key)
    {
        return isset($this->_connections[$key]);
    }
    /**
     * count
     * returns the number of opened connections
     *
     * @return integer
     */
    public function count()
    {
        return count($this->_connections);
    }
    /**
     * getIterator
     * returns an ArrayIterator that iterates through all connections
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_connections);
    }
    /**
     * getCurrentConnection
     * returns the current connection
     *
     * @throws Doctrine_Connection_Exception       if there are no open connections
     * @return Doctrine_Connection
     */
    public function getCurrentConnection()
    {
        $i = $this->_currIndex;
        if ( ! isset($this->_connections[$i])) {
            throw new Doctrine_Connection_Exception();
        }
        return $this->_connections[$i];
    }
    /**
     * __toString
     * returns a string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        $r[] = "<pre>";
        $r[] = "Doctrine_Manager";
        $r[] = "Connections : ".count($this->_connections);
        $r[] = "</pre>";
        return implode("\n",$r);
    }
}
