<?php
require_once("Access.class.php");
/**
 * Doctrine_Query
 *
 * @package     Doctrine ORM
 * @url         www.phpdoctrine.com
 * @license     LGPL
 * @version     1.0 alpha
 */
class Doctrine_Query extends Doctrine_Access {
    /**
     * @var array $fetchmodes       an array containing all fetchmodes
     */
    private $fetchModes  = array();
    /**
     * @var array $tables           an array containing all the tables used in the query
     */
    private $tables      = array();
    /**
     * @var array $collections      an array containing all collections this parser has created/will create
     */
    private $collections = array();

    private $joined      = array();
    
    private $joins       = array();
    /**
     * @var array $data             fetched data
     */
    private $data        = array();
    /**
     * @var Doctrine_Session $session     Doctrine_Session object
     */
    private $session;

    private $inheritanceApplied = false;

    private $aggregate  = false;
    /**
     * @var array $connectors       component connectors
     */
    private $connectors  = array();
    /**
     * @var array $dql              DQL query string parts
     */
    protected $dql = array(
        "columns"   => array(),
        "from"      => array(),
        "join"      => array(),
        "where"     => array(),
        "group"     => array(),
        "having"    => array(),
        "orderby"   => array(),
        "limit"     => false,
        "offset"    => false,
        );
    /**
     * @var array $parts            SQL query string parts
     */
    protected $parts = array(
        "columns"   => array(),
        "from"      => array(),
        "join"      => array(),
        "where"     => array(),
        "group"     => array(),
        "having"    => array(),
        "orderby"   => array(),
        "limit"     => false,
        "offset"    => false,
        );
    /**
     * constructor
     *
     * @param Doctrine_Session $session
     */
    public function __construct(Doctrine_Session $session) {
        $this->session = $session;
    }
    /**
     * clear
     * resets all the variables
     * 
     * @return void
     */
    private function clear() {
        $this->fetchModes   = array();
        $this->tables       = array();

        $this->parts = array(
                  "columns"   => array(),
                  "from"      => array(),
                  "join"      => array(),
                  "where"     => array(),
                  "group"     => array(),
                  "having"    => array(),
                  "orderby"   => array(),
                  "limit"     => false,
                  "offset"    => false,
                );
        $this->inheritanceApplied = false;
        $this->aggregate    = false;
        $this->data         = array();
        $this->connectors   = array();
        $this->collections  = array();
        $this->joined       = array();
        $this->joins        = array();
    }
    /**
     * loadFields      
     * loads fields for a given table and
     * constructs a little bit of sql for every field
     *
     * fields of the tables become: [tablename].[fieldname] as [tablename]__[fieldname]
     *
     * @access private
     * @param object Doctrine_Table $table       a Doctrine_Table object
     * @param integer $fetchmode                 fetchmode the table is using eg. Doctrine::FETCH_LAZY
     * @return void
     */
    private function loadFields(Doctrine_Table $table,$fetchmode) {
        $name = $table->getComponentName();

        switch($fetchmode):
            case Doctrine::FETCH_OFFSET:
                $this->limit = $table->getAttribute(Doctrine::ATTR_COLL_LIMIT);
            case Doctrine::FETCH_IMMEDIATE:
                $names  = $table->getColumnNames();
            break;
            case Doctrine::FETCH_LAZY_OFFSET:
                $this->limit = $table->getAttribute(Doctrine::ATTR_COLL_LIMIT);
            case Doctrine::FETCH_LAZY:
            case Doctrine::FETCH_BATCH:
                $names = $table->getPrimaryKeys();
            break;
            default:
                throw new Doctrine_Exception("Unknown fetchmode.");
        endswitch;
        $cname          = $table->getComponentName();
        $this->fetchModes[$cname] = $fetchmode;
        $tablename      = $table->getTableName();

        $count = count($this->tables);
        foreach($names as $name) {
            if($count == 0) {
                $this->parts["columns"][] = $tablename.".".$name;
            } else {
                $this->parts["columns"][] = $tablename.".".$name." AS ".$cname."__".$name;
            }
        }
    }
    /** 
     * sets a query part
     *
     * @param string $name
     * @param array $args
     * @return void
     */
    public function __call($name, $args) {
        $name = strtolower($name);
        if(isset($this->parts[$name])) {
            $method = "parse".ucwords($name);
            switch($name):
                case "where":
                    $this->parts[$name] = array($this->$method($args[0]));
                break;
                case "limit":
                case "offset":
                    if($args[0] == null)
                        $args[0] = false;

                    $this->parts[$name] = $args[0];
                break;
                case "from":
                    $this->parts['columns'] = array();
                    $this->joins            = array();
                default:
                    $this->parts[$name] = array();
                    $this->$method($args[0]);
            endswitch;
        }

        return $this;
    }
    /**
     * returns a query part
     *
     * @param $name         query part name
     * @return mixed
     */
    public function get($name) {
        if( ! isset($this->parts[$name]))
            return false;

        return $this->parts[$name];
    }
    /**
     * sets a query part
     *
     * @param $name         query part name
     * @param $value        query part value
     * @return boolean
     */
    public function set($name, $value) {

        if(isset($this->parts[$name])) {
            $method = "parse".ucwords($name);
            switch($name):
                case "where":
                    $this->parts[$name] = array($this->$method($value));
                break;
                case "limit":
                case "offset": 
                    if($value == null)
                        $value = false;

                    $this->parts[$name] = $value;
                break;
                case "from":
                    $this->parts['columns'] = array();
                    $this->joins            = array();
                default:
                    $this->parts[$name] = array();
                    $this->$method($value);
            endswitch;
            
            return true;
        }
        return false;
    }
    /**
     * returns the built sql query
     *
     * @return string
     */
    final public function getQuery() {
        if(empty($this->parts["columns"]) || empty($this->parts["from"]))
            return false;

        // build the basic query
        $q = "SELECT ".implode(", ",$this->parts["columns"]).
             " FROM ";
        
        foreach($this->parts["from"] as $tname => $bool) {
            $a[] = $tname;
        }
        $q .= implode(", ",$a);
        
        if( ! empty($this->parts['join']))
            $q .= " ".implode(' ', $this->parts["join"]);

        $this->applyInheritance();
        if( ! empty($this->parts["where"]))
            $q .= " WHERE ".implode(" ",$this->parts["where"]);

        if( ! empty($this->parts["orderby"]))
            $q .= " ORDER BY ".implode(", ",$this->parts["orderby"]);

        if( ! empty($this->parts["limit"]) || ! empty($this->offset))
            $q = $this->session->modifyLimitQuery($q,$this->parts["limit"],$this->offset);

        return $q;
    }
    /**
     * sql delete for mysql
     */
    final public function buildDelete() {
        if(empty($this->parts["columns"]) || empty($this->parts["from"]))
            return false;    
        
        $a = array_merge(array_keys($this->parts["from"]),$this->joined);
        $q = "DELETE ".implode(", ",$a)." FROM ";
        $a = array();

        foreach($this->parts["from"] as $tname => $bool) {
            $str = $tname;
            if(isset($this->parts["join"][$tname]))
                $str .= " ".$this->parts["join"][$tname];

            $a[] = $str;
        }

        $q .= implode(", ",$a);
        $this->applyInheritance();
        if( ! empty($this->parts["where"]))
            $q .= " WHERE ".implode(" ",$this->parts["where"]);

        if( ! empty($this->parts["orderby"]))
            $q .= " ORDER BY ".implode(", ",$this->parts["orderby"]);

        if( ! empty($this->parts["limit"]) && ! empty($this->offset))
            $q = $this->session->modifyLimitQuery($q,$this->parts["limit"],$this->offset);

        return $q;
    }
    /**
     * applyInheritance
     * applies column aggregation inheritance to DQL query
     *
     * @return boolean
     */
    final public function applyInheritance() {
        if($this->inheritanceApplied) 
            return false;

        // get the inheritance maps
        $array = array();

        foreach($this->tables as $objTable):
            $tname = $objTable->getTableName();
            $array[$tname][] = $objTable->getInheritanceMap();
        endforeach;

        // apply inheritance maps
        $str = "";
        $c = array();

        foreach($array as $tname => $maps) {
            $a = array();
            foreach($maps as $map) {
                $b = array();
                foreach($map as $field=>$value) {
                    $b[] = $tname.".$field = $value";
                }
                if( ! empty($b)) $a[] = implode(" AND ",$b);
            }
            if( ! empty($a)) $c[] = implode(" || ",$a);
        }

        $str .= implode(" || ",$c);

        $this->addWhere($str);
        $this->inheritanceApplied = true;
        return true;
    }
    /**
     * @param string $where
     * @return boolean
     */
    final public function addWhere($where) {
        if(empty($where))
            return false;

        if($this->parts["where"]) {
            $this->parts["where"][] = "AND (".$where.")";
        } else {
            $this->parts["where"][] = "(".$where.")";
        }
        return true;
    }
    /**
     * getData
     * @param $key                      the component name
     * @return array                    the data row for the specified component
     */
    final public function getData($key) {
        if(isset($this->data[$key]) && is_array($this->data[$key]))
            return $this->data[$key];

        return array();
    }
    /**
     * execute
     * executes the dql query and populates all collections
     *
     * @param string $params
     * @return Doctrine_Collection            the root collection
     */
    public function execute($params = array()) {
        $this->data = array();
        $this->collections = array();

        switch(count($this->tables)):
            case 0:
                throw new DQLException();
            break;
            case 1:
                $query = $this->getQuery();

                $keys  = array_keys($this->tables);

                $name  = $this->tables[$keys[0]]->getComponentName();
                $stmt  = $this->session->execute($query,$params);

                while($data = $stmt->fetch(PDO::FETCH_ASSOC)):
                    foreach($data as $key => $value):
                        $e = explode("__",$key);
                        if(count($e) > 1) {
                            $data[$e[1]] = $value;
                        } else {
                            $data[$e[0]] = $value;
                        }
                        unset($data[$key]);
                    endforeach;
                    $this->data[$name][] = $data;
                endwhile;

                return $this->getCollection($keys[0]);
            break;
            default:
                $query = $this->getQuery();

                $keys  = array_keys($this->tables);
                $root  = $keys[0];
                $stmt  = $this->session->execute($query,$params);
                
                $previd = array();

                $coll        = $this->getCollection($root);
                $prev[$root] = $coll;

                $array = $this->parseData($stmt);

                $colls = array();

                foreach($array as $data) {
                    /**
                     * remove duplicated data rows and map data into objects
                     */
                    foreach($data as $key => $row) {
                        if(empty($row))
                            continue;

                        $name = $this->tables[$key]->getComponentName();

                        if( ! isset($previd[$name]))
                            $previd[$name] = array();


                        if($previd[$name] !== $row) {
                            // set internal data
                            $this->tables[$name]->setData($row);

                            // initialize a new record
                            $record = $this->tables[$name]->getRecord();

                            if($name == $root) {
                                // add record into root collection
                                $coll->add($record);
                            } else {
                                $pointer = $this->joins[$name];
                                
                                $last = $prev[$pointer]->getLast();

                                if( ! $last->hasReference($name)) {
                                    $prev[$name] = $this->getCollection($name);
                                    $last->initReference($prev[$name],$this->connectors[$name]);
                                }
                                $last->addReference($record);
                            }
                        }

                        $previd[$name] = $row;
                    }
                }

                return $coll;
        endswitch;
    }
    /**
     * parseData
     * parses the data returned by PDOStatement
     *
     * @return array
     */
    public function parseData(PDOStatement $stmt) {
        $array = array();
        $keys  = array();
        foreach(array_keys($this->tables) as $key) {
            $k = strtolower($key);
            $keys[$k] = $key;
        }
        while($data = $stmt->fetch(PDO::FETCH_ASSOC)):
            /**
             * parse the data into two-dimensional array
             */
            foreach($data as $key => $value):
                $e = explode("__",$key);

                if(count($e) > 1) {
                    $data[$keys[$e[0]]][$e[1]] = $value;
                } else {
                    $data[0][$e[0]] = $value;
                }
                unset($data[$key]);
            endforeach;
            $array[] = $data;
        endwhile;
        $stmt->closeCursor();
        return $array;
    }
    /**
     * returns a Doctrine_Table for given name
     *
     * @param string $name              component name
     * @return Doctrine_Table
     */
    public function getTable($name) {
        return $this->tables[$name];
    }
    /**
     * getCollection
     *
     * @parma string $name              component name
     * @param integer $index
     */
    private function getCollection($name) {
        $table = $this->session->getTable($name);
        switch($this->fetchModes[$name]):
            case Doctrine::FETCH_BATCH:
                $coll = new Doctrine_Collection_Batch($table);
            break;
            case Doctrine::FETCH_LAZY:
                $coll = new Doctrine_Collection_Lazy($table);
            break;
            case Doctrine::FETCH_OFFSET:
                $coll = new Doctrine_Collection_Offset($table);
            break;
            case Doctrine::FETCH_IMMEDIATE:
                $coll = new Doctrine_Collection_Immediate($table);
            break;
            case Doctrine::FETCH_LAZY_OFFSET:
                $coll = new Doctrine_Collection_LazyOffset($table);
            break;
        endswitch;

        $coll->populate($this);
        return $coll;
    }
    /**
     * query the database with DQL (Doctrine Query Language)
     *
     * @param string $query                 DQL query
     * @param array $params                 parameters
     */
    public function query($query,$params = array()) {
        $this->parseQuery($query);

        if($this->aggregate) {
            $keys  = array_keys($this->tables);
            $query = $this->getQuery();
            $stmt  = $this->tables[$keys[0]]->getSession()->select($query,$this->parts["limit"],$this->offset);
            $data  = $stmt->fetch(PDO::FETCH_ASSOC);
            if(count($data) == 1) {
                return current($data);
            } else {
                return $data;
            }
        } else {
            return $this->execute($params);
        }
    }
    /**
     * DQL PARSER
     *
     * @param string $query         DQL query
     * @return void
     */
    final public function parseQuery($query) {
        $this->clear();
        $e = self::bracketExplode($query," ","(",")");
            
        $parts = array();
        foreach($e as $k=>$part):
            switch(strtolower($part)):
                case "select":
                case "from":
                case "where":
                case "limit":
                case "offset":
                    $p = $part;
                    $parts[$part] = array();
                break;
                case "order":
                    $p = $part;
                    $i = $k+1;
                    if(isset($e[$i]) && strtolower($e[$i]) == "by") {
                        $parts[$part] = array();
                    }
                break;
                case "by":
                    continue;
                default:
                    $parts[$p][] = $part;
            endswitch;
        endforeach;

        foreach($parts as $k => $part) {
            $part = implode(" ",$part);
            switch(strtoupper($k)):
                case "SELECT":
                    $this->parseSelect($part);
                break;
                case "FROM":
                    $this->parseFrom($part);
                break;
                case "WHERE":
                    $this->addWhere($this->parseWhere($part));
                break;
                case "ORDER":
                    $this->parseOrderBy($part);
                break;  
                case "LIMIT":
                    $this->parts["limit"] = trim($part);
                break;
                case "OFFSET":
                    $this->offset = trim($part);
                break;
            endswitch;
        }
    }
    /**
     * DQL ORDER BY PARSER
     * parses the order by part of the query string
     *
     * @param string $str
     * @return void
     */
    private function parseOrderBy($str) {
        foreach(explode(",",trim($str)) as $r) {
            $r = trim($r);
            $e = explode(" ",$r);
            $a = explode(".",$e[0]);
    
            if(count($a) > 1) {
                $field     = array_pop($a);
                $reference = implode(".",$a);
                $name      = end($a);

                $this->load($reference);
                $tname     = $this->tables[$name]->getTableName();

                $r = $tname.".".$field;
                if(isset($e[1])) 
                    $r .= " ".$e[1];
            }
            $this->parts["orderby"][] = $r;
        }
    }
    /**
     * DQL SELECT PARSER
     * parses the select part of the query string
     *
     * @param string $str
     * @return void
     */
    private function parseSelect($str) {
        $this->aggregate = true;
        foreach(explode(",",trim($str)) as $reference) {

            $e = explode(" AS ",trim($reference));

            $f = explode("(",$e[0]);
            $a = explode(".",$f[1]);
            $field = substr(array_pop($a),0,-1);

            $reference = trim(implode(".",$a));

            $objTable = $this->load($reference);
            if(isset($e[1]))
                $s = " AS $e[1]";

            $this->parts["columns"][] = $f[0]."(".$objTable->getTableName().".$field)$s";

        }
    }
    /**
     * DQL FROM PARSER
     * parses the from part of the query string

     * @param string $str
     * @return void
     */
    private function parseFrom($str) {
        foreach(explode(",",trim($str)) as $reference) {
            $reference = trim($reference);
            $e = explode("-",$reference);
            $reference = $e[0];
            $table = $this->load($reference);

            if(isset($e[1])) {
                switch(strtolower($e[1])):
                    case "i":
                    case "immediate":
                        $fetchmode = Doctrine::FETCH_IMMEDIATE;
                    break;
                    case "b":
                    case "batch":
                        $fetchmode = Doctrine::FETCH_BATCH;
                    break;
                    case "l":
                    case "lazy":
                        $fetchmode = Doctrine::FETCH_LAZY;
                    break;
                    case "o":
                    case "offset":
                        $fetchmode = Doctrine::FETCH_OFFSET;
                    break;
                    case "lo":
                    case "lazyoffset":
                        $fetchmode = Doctrine::FETCH_LAZYOFFSET;
                    default:
                        throw new DQLException("Unknown fetchmode '$e[1]'. The availible fetchmodes are 'i', 'b' and 'l'.");
                endswitch;
            } else
                $fetchmode = $table->getAttribute(Doctrine::ATTR_FETCHMODE);

            if( ! $this->aggregate) {
                $this->loadFields($table,$fetchmode);
            }
        }
    }
    /**
     * DQL WHERE PARSER
     * parses the where part of the query string
     *
     *
     * @param string $str
     * @return string
     */
    private function parseWhere($str) {
        $tmp = trim($str);
        $str = self::bracketTrim($tmp,"(",")");
        
        $brackets = false;
        while($tmp != $str) {
            $brackets = true;
            $tmp = $str;
            $str = self::bracketTrim($str,"(",")");
        }

        $parts = self::bracketExplode($str," && ","(",")");
        if(count($parts) > 1) {
            $ret = array();
            foreach($parts as $part) {
                $ret[] = $this->parseWhere($part);
            }
            $r = implode(" AND ",$ret);
        } else {
            $parts = self::bracketExplode($str," || ","(",")");
            if(count($parts) > 1) {
                $ret = array();
                foreach($parts as $part) {
                    $ret[] = $this->parseWhere($part);
                }
                $r = implode(" OR ",$ret);
            } else {
                return $this->loadWhere($parts[0]);
            }
        }
        if($brackets)
            return "(".$r.")";
        else
            return $r;
    }
    /**
     * trims brackets
     *
     * @param string $str
     * @param string $e1        the first bracket, usually '('
     * @param string $e2        the second bracket, usually ')'
     */
    public static function bracketTrim($str,$e1,$e2) {
        if(substr($str,0,1) == $e1 && substr($str,-1) == $e2)
            return substr($str,1,-1);
        else
            return $str;
    }
    /**
     * bracketExplode
     * usage:
     * $str = (age < 20 AND age > 18) AND email LIKE 'John@example.com'
     * now exploding $str with parameters $d = ' AND ', $e1 = '(' and $e2 = ')'
     * would return an array:
     * array("(age < 20 AND age > 18)", "email LIKE 'John@example.com'")
     *
     * @param string $str
     * @param string $d         the delimeter which explodes the string
     * @param string $e1        the first bracket, usually '('
     * @param string $e2        the second bracket, usually ')'
     *
     */
    public static function bracketExplode($str,$d,$e1,$e2) {
        $str = explode("$d",$str);
        $i = 0;
        $term = array();
        foreach($str as $key=>$val) {
            if (empty($term[$i])) {
                $term[$i] = trim($val);
                $s1 = substr_count($term[$i],"$e1");
                $s2 = substr_count($term[$i],"$e2");
                    if($s1 == $s2) $i++;
            } else {
                $term[$i] .= "$d".trim($val);
                $c1 = substr_count($term[$i],"$e1");
                $c2 = substr_count($term[$i],"$e2");
                    if($c1 == $c2) $i++;
            }
        }
        return $term;
    }
    /**
     * loadWhere
     *
     * @param string $where
     */
    private function loadWhere($where) {
        $e = explode(" ",$where);
        $r = array_shift($e);
        $a = explode(".",$r);

        if(count($a) > 1) {
            $field     = array_pop($a);
            $operator  = array_shift($e);
            $value     = implode(" ",$e);
            $reference = implode(".",$a);

            if(count($a) > 1)
                $objTable = $this->tables[$a[0]]->getForeignKey(end($a))->getTable();
            else
                $objTable = $this->session->getTable(end($a));

            $where     = $objTable->getTableName().".".$field." ".$operator." ".$value;

            if(count($a) > 1 && isset($a[1])) {
                $root = $a[0];
                $fk = $this->tables[$root]->getForeignKey($a[1]);
                if($fk instanceof Doctrine_Association) {
                    $asf = $fk->getAssociationFactory();

                    switch($fk->getType()):
                        case Doctrine_Relation::ONE_AGGREGATE:
                        case Doctrine_Relation::ONE_COMPOSITE:

                        break;
                        case Doctrine_Relation::MANY_AGGREGATE:
                        case Doctrine_Relation::MANY_COMPOSITE:
                            
                            // subquery needed

                            $b = $fk->getTable()->getComponentName();

                            $graph = new Doctrine_Query($this->session);
                            $graph->parseQuery("FROM $b-l WHERE $where");
                            $where = $this->tables[$root]->getTableName().".".$this->tables[$root]->getIdentifier()." IN (SELECT ".$fk->getLocal()." FROM ".$asf->getTableName()." WHERE ".$fk->getForeign()." IN (".$graph->getQuery()."))";
                        break;
                    endswitch;
                } else
                    $this->load($reference);

            } else
                $this->load($reference);
        }
        return $where;
    }
    /**
     * @param string $path              the path of the loadable component
     * @param integer $fetchmode        optional fetchmode, if not set the components default fetchmode will be used
     * @throws DQLException
     */
    final public function load($path, $fetchmode = Doctrine::FETCH_LAZY) {
        $e = preg_split("/[.:]/",$path);
        $index = 0;
        foreach($e as $key => $name) {


            try {
                if($key == 0) {

                    $table = $this->session->getTable($name);
                    if(count($e) == 1) {
                        $tname = $table->getTableName();
                        $this->parts["from"][$tname] = true;
                    }
                } else {

                    $index += strlen($e[($key - 1)]) + 1;
                    // the mark here is either '.' or ':'
                    $mark  = substr($path,($index - 1),1);
                       	

                    $fk     = $table->getForeignKey($name);
                    $name   = $fk->getTable()->getComponentName();

                    $tname  = $table->getTableName();

                    $tname2 = $fk->getTable()->getTableName();

                    $this->connectors[$name] = $fk;

                    if($fk instanceof Doctrine_ForeignKey ||
                       $fk instanceof Doctrine_LocalKey) {

                        switch($mark):
                            case ":":
                                $this->parts["join"][$tname]  = "INNER JOIN ".$tname2." ON ".$tname.".".$fk->getLocal()." = ".$tname2.".".$fk->getForeign();
                            break;
                            case ".":
                                $this->parts["join"][$tname]  = "LEFT JOIN ".$tname2." ON ".$tname.".".$fk->getLocal()." = ".$tname2.".".$fk->getForeign();
                            break;
                        endswitch;

                        $c = $table->getComponentName();
                        $this->joins[$name] = $c;
                    } elseif($fk instanceof Doctrine_Association) {
                        $asf = $fk->getAssociationFactory();

                        switch($fk->getType()):
                            case Doctrine_Relation::ONE_AGGREGATE:
                            case Doctrine_Relation::ONE_COMPOSITE:

                            break;
                            case Doctrine_Relation::MANY_AGGREGATE:
                            case Doctrine_Relation::MANY_COMPOSITE:

                                //$this->addWhere("SELECT ".$fk->getLocal()." FROM ".$asf->getTableName()." WHERE ".$fk->getForeign()." IN (SELECT ".$fk->getTable()->getComponentName().")");
                                $this->parts["from"][$tname]  = true;
                            break;
                        endswitch;
                    }

                    $table = $fk->getTable();
                }

                if( ! isset($this->tables[$name])) {
                    $this->tables[$name] = $table;
                }

            } catch(Exception $e) {

                throw new DQLException($e->getMessage(),$e->getCode());
            }
        }
        return $table;
    }
}

?>
