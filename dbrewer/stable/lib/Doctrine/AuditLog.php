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
 * Doctrine_AuditLog
 *
 * @package     Doctrine
 * @subpackage  AuditLog
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.com
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_AuditLog extends Doctrine_Plugin
{
    protected $_options = array(
                            'className'     => '%CLASS%Version',
                            'versionColumn' => 'version',
                            'generateFiles' => false,
                            'table'         => false,
                            'pluginTable'   => false,
                            );

    public function __construct($options)
    {
        $this->_options = array_merge($this->_options, $options);
    }

    public function getVersion(Doctrine_Record $record, $version)
    {           
        $className = $this->_options['className'];

        $q = new Doctrine_Query();

        $values = array();
        foreach ((array) $this->_options['table']->getIdentifier() as $id) {
            $conditions[] = $className . '.' . $id . ' = ?';
            $values[] = $record->get($id);
        }
        $where = implode(' AND ', $conditions) . ' AND ' . $className . '.' . $this->_options['versionColumn'] . ' = ?';
        
        $values[] = $version;

        $q->from($className)
          ->where($where);

        return $q->execute($values, Doctrine::HYDRATE_ARRAY);
    }
    public function buildDefinition(Doctrine_Table $table)
    {
        $this->_options['className'] = str_replace('%CLASS%', 
                                                   $this->_options['table']->getComponentName(),
                                                   $this->_options['className']);

        $name = $table->getComponentName();

        $className = $name . 'Version';

        // check that class doesn't exist (otherwise we cannot create it)
        if (class_exists($className)) {
            return false;
        }

        $columns = $table->getColumns();

        // remove all sequence, autoincrement and unique constraint definitions
        foreach ($columns as $column => $definition) {
            unset($columns[$column]['autoincrement']);
            unset($columns[$column]['sequence']);
            unset($columns[$column]['unique']);
        }

        // the version column should be part of the primary key definition
        $columns[$this->_options['versionColumn']] = array('type' => 'integer',
                                                           'length' => 8,
                                                           'primary' => true);

        $id = $table->getIdentifier();

        $options = array('className' => $className);
        
        $relations = array($name => array('local' => $id,
                                          'foreign' => $id, 
                                          'onDelete' => 'CASCADE',
                                          'onUpdate' => 'CASCADE'));

        $this->generateClass($options, $columns, array());
        
        $this->_options['pluginTable'] = $table->getConnection()->getTable($this->_options['className']);

        return true;
    }
}
