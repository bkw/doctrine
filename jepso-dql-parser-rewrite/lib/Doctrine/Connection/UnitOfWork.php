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
Doctrine::autoload('Doctrine_Connection_Module');
/**
 * Doctrine_Connection_UnitOfWork
 *
 * @package     Doctrine
 * @subpackage  Connection
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Roman Borschel <roman@code-factory.org>
 * @todo package:orm. Figure out a useful implementation.
 */
class Doctrine_Connection_UnitOfWork extends Doctrine_Connection_Module
{
    /**
     * A map of all currently managed entities.
     *
     * @var array
     */
    protected $_managedEntities = array();
    
    /**
     * The identity map that holds references to all managed entities that have
     * an identity. The entities are grouped by their class name.
     */
    protected $_identityMap = array();
    
    /**
     * Boolean flag that indicates whether the unit of work immediately executes any
     * database operations or whether these operations are postponed until the
     * unit of work is flushed/committed.
     *
     * @var boolean
     */
    protected $_autoflush = true;
    
    /**
     * A list of all postponed inserts.
     */
    protected $_inserts = array();
    
    /**
     * A list of all postponed updates.
     */
    protected $_updates = array();
    
    /**
     * A list of all postponed deletes.
     */
    protected $_deletes = array();
    
    /**
     * The dbal connection used by the unit of work.
     *
     * @var Doctrine_Connection
     * @todo Allow multiple connections for transparent master-slave replication.
     */
    protected $_conn;
    
    /**
     * Flushes the unit of work, executing all operations that have been postponed
     * up to this point.
     *
     */
    public function flush()
    {
        // get the flush tree
        $tree = $this->buildFlushTree($this->conn->getMappers());
        
        $tree = array_combine($tree, array_fill(0, count($tree), array()));
        
        foreach ($this->_managedEntities as $oid => $entity) {
            $className = $entity->getClassName();
            $tree[$className][] = $entity;
        }
        
        // save all records
        foreach ($tree as $className => $entities) {
            $mapper = $this->conn->getMapper($className);
            foreach ($entities as $entity) {
                $mapper->saveSingleRecord($entity);
            }
        }
        
        // save all associations
        foreach ($tree as $className => $entities) {
            $mapper = $this->conn->getMapper($className);
            foreach ($entities as $entity) {
                $mapper->saveAssociations($entity);
            }
        }
    }
    
    public function addInsert()
    {
        
    }
    
    public function addUpdate()
    {
        
    }
    
    public function addDelete()
    {
        
    }
    
    
    /**
     * buildFlushTree
     * builds a flush tree that is used in transactions
     *
     * The returned array has all the initialized components in
     * 'correct' order. Basically this means that the records of those
     * components can be saved safely in the order specified by the returned array.
     *
     * @param array $tables     an array of Doctrine_Table objects or component names
     * @return array            an array of component names in flushing order
     */
    public function buildFlushTree(array $mappers)
    {
        $tree = array();
        foreach ($mappers as $k => $mapper) {
            if ( ! ($mapper instanceof Doctrine_Mapper)) {
                $mapper = $this->conn->getMapper($mapper);
            }
            $nm = $mapper->getComponentName();

            $index = array_search($nm, $tree);

            if ($index === false) {
                $tree[] = $nm;
                $index  = max(array_keys($tree));
            }

            $rels = $mapper->getTable()->getRelations();

            // group relations

            foreach ($rels as $key => $rel) {
                if ($rel instanceof Doctrine_Relation_ForeignKey) {
                    unset($rels[$key]);
                    array_unshift($rels, $rel);
                }
            }

            foreach ($rels as $rel) {
                $name   = $rel->getTable()->getComponentName();
                $index2 = array_search($name, $tree);
                $type   = $rel->getType();

                // skip self-referenced relations
                if ($name === $nm) {
                    continue;
                }

                if ($rel instanceof Doctrine_Relation_ForeignKey) {
                    if ($index2 !== false) {
                        if ($index2 >= $index)
                            continue;

                        unset($tree[$index]);
                        array_splice($tree,$index2,0,$nm);
                        $index = $index2;
                    } else {
                        $tree[] = $name;
                    }
                } else if ($rel instanceof Doctrine_Relation_LocalKey) {
                    if ($index2 !== false) {
                        if ($index2 <= $index)
                            continue;

                        unset($tree[$index2]);
                        array_splice($tree, $index, 0, $name);
                    } else {
                        array_unshift($tree,$name);
                        $index++;
                    }
                } else if ($rel instanceof Doctrine_Relation_Association) {
                    $t = $rel->getAssociationFactory();
                    $n = $t->getComponentName();

                    if ($index2 !== false) {
                        unset($tree[$index2]);
                    }

                    array_splice($tree, $index, 0, $name);
                    $index++;

                    $index3 = array_search($n, $tree);

                    if ($index3 !== false) {
                        if ($index3 >= $index)
                            continue;

                        unset($tree[$index]);
                        array_splice($tree, $index3, 0, $n);
                        $index = $index2;
                    } else {
                        $tree[] = $n;
                    }
                }
            }
        }
        
        return $tree;
    }
    
    /**
     * saveAll
     * persists all the pending records from all tables
     *
     * @throws PDOException         if something went wrong at database level
     * @return void
     * @deprecated
     */
    public function saveAll()
    {
        return $this->flush();
    }
    
    /**
     * Adds an entity to the pool of managed entities.
     *
     */
    public function manage(Doctrine_Record $entity)
    {
        $oid = $entity->getOid();
        if ( ! isset($this->_managedEntities[$oid])) {
            $this->_managedEntities[$oid] = $entity;
            return true;
        }
        return false;
    }
    
    /**
     * Gets a managed entity by it's object id (oid).
     *
     * @param integer $oid  The object id.
     * @throws Doctrine_Table_Repository_Exception
     */
    public function getByOid($oid)
    {
        if ( ! isset($this->_managedEntities[$oid])) {
            throw new Doctrine_Connection_Exception("Unknown object identifier '$oid'.");
        }
        return $this->_managedEntities[$oid];
    }
    
    /**
     * @param integer $oid                  object identifier
     * @return boolean                      whether ot not the operation was successful
     */
    public function detach(Doctrine_Record $entity)
    {
        $oid = $entity->getOid();
        if ( ! isset($this->_managedEntities[$oid])) {
            return false;
        }
        unset($this->_managedEntities[$oid]);
        return true;
    }
    
    /**
     * Detaches all currently managed entities.
     *
     * @return integer   The number of detached entities.
     */
    public function detachAll()
    {
        $numDetached = count($this->_managedEntities);
        $this->_managedEntities = array();
        return $numDetached;
    }
    
    /**
     * Checks whether an entity is managed.
     * 
     * @param Doctrine_Record $entity  The entity to check.
     * @return boolean  TRUE if the entity is currently managed by doctrine, FALSE otherwise.
     */
    public function isManaged(Doctrine_Record $entity)
    {
        return isset($this->_managedEntities[$entity->getOid()]);
    }
    
    /**
     * Registers an entity in the identity map.
     * 
     * @return boolean  TRUE if the registration was successful, FALSE if the identity of
     *                  the entity in question is already managed.
     * @throws Doctrine_Connection_Exception  If the entity has no (database) identity.
     */
    public function registerIdentity(Doctrine_Record $entity)
    {
        $id = implode(' ', $entity->identifier());
        if ( ! $id) {
            throw new Doctrine_Connection_Exception("Entity with oid '" . $entity->getOid()
                    . "' has no database identity and therefore can't be added to the identity map.");
        }
        $className = $entity->getClassMetadata()->getRootClassName();
        if (isset($this->_identityMap[$className][$id])) {
            return false;
        }
        $this->_identityMap[$className][$id] = $entity;
        return true;
    }
    
    public function unregisterIdentity(Doctrine_Record $entity)
    {
        $id = implode(' ', $entity->identifier());
        if ( ! $id) {
            throw new Doctrine_Connection_Exception("Entity with oid '" . $entity->getOid()
                    . "' has no database identity and therefore can't be removed from the identity map.");
        }
        $className = $entity->getClassMetadata()->getRootClassName();
        if (isset($this->_identityMap[$className][$id])) {
            unset($this->_identityMap[$className][$id]);
            return true;
        }

        return false;
    }
    
    public function containsIdentity(Doctrine_Record $entity)
    {
        
    }
    
}
