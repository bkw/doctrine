<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ArrayHydrator
 *
 * @author robo
 */
class Doctrine_ORM_Internal_Hydration_ArrayHydrator extends Doctrine_ORM_Internal_Hydration_AbstractHydrator
{
    private $_rootAlias;
    private $_rootEntityName;
    private $_isSimpleQuery = false;
    private $_identifierMap = array();
    private $_resultPointers = array();
    private $_idTemplate = array();
    private $_resultCounter = 0;

    /** @override */
    protected function _prepare($parserResult)
    {
        parent::_prepare($parserResult);
        reset($this->_queryComponents);
        $this->_rootAlias = key($this->_queryComponents);
        $this->_rootEntityName = $this->_queryComponents[$this->_rootAlias]['metadata']->getClassName();
        $this->_isSimpleQuery = count($this->_queryComponents) <= 1;
        $this->_identifierMap = array();
        $this->_resultPointers = array();
        $this->_idTemplate = array();
        $this->_resultCounter = 0;
        foreach ($this->_queryComponents as $dqlAlias => $component) {
            $this->_identifierMap[$dqlAlias] = array();
            $this->_resultPointers[$dqlAlias] = array();
            $this->_idTemplate[$dqlAlias] = '';
        }
    }

    /** @override */
    protected function _hydrateAll($parserResult)
    {
        $s = microtime(true);

        $result = array();
        $cache = array();
        while ($data = $this->_stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->_hydrateRow($data, $cache, $result);
        }

        $e = microtime(true);
        echo 'Hydration took: ' . ($e - $s) . PHP_EOL;

        return $result;
    }

    /** @override */
    protected function _hydrateRow(array &$data, array &$cache, &$result)
    {
        // 1) Initialize
        $id = $this->_idTemplate; // initialize the id-memory
        $nonemptyComponents = array();
        $rowData = parent::_gatherRowData($data, $cache, $id, $nonemptyComponents);
        $rootAlias = $this->_rootAlias;

        // 2) Hydrate the data of the root entity from the current row
        // Check for an existing element
        $index = false;
        if ($this->_isSimpleQuery || ! isset($this->_identifierMap[$rootAlias][$id[$rootAlias]])) {
            $element = $rowData[$rootAlias];
            if ($field = $this->_getCustomIndexField($rootAlias)) {
                if ($this->_parserResult->isMixedQuery()) {
                    $result[] = array($element[$field] => $element);
                    ++$this->_resultCounter;
                } else {
                    $result[$element[$field]] = $element;
                }
            } else {
                if ($this->_parserResult->isMixedQuery()) {
                    $result[] = array($element);
                    ++$this->_resultCounter;
                } else {
                    $result[] = $element;
                }
            }
            end($result);
            $this->_identifierMap[$rootAlias][$id[$rootAlias]] = key($result);
        } else {
            $index = $this->_identifierMap[$rootAlias][$id[$rootAlias]];
        }
        $this->updateResultPointer($result, $index, $rootAlias, false);
        unset($rowData[$rootAlias]);
        // end of hydrate data of the root component for the current row

        // Extract scalar values. They're appended at the end.
        if (isset($rowData['scalars'])) {
            $scalars = $rowData['scalars'];
            unset($rowData['scalars']);
        }

        // 3) Now hydrate the rest of the data found in the current row, that
        // belongs to other (related) entities.
        foreach ($rowData as $dqlAlias => $data) {
            $index = false;
            $map = $this->_queryComponents[$dqlAlias];
            $parent = $map['parent'];
            $relationAlias = $map['relation']->getSourceFieldName();
            $path = $parent . '.' . $dqlAlias;

            // Get a reference to the right element in the result tree.
            // This element will get the associated element attached.
            if ($this->_parserResult->isMixedQuery() && $parent == $rootAlias) {
                $key = key(reset($this->_resultPointers));
                // TODO: Exception if $key === null ?
                $baseElement =& $this->_resultPointers[$parent][$key];
            } else if (isset($this->_resultPointers[$parent])) {
                $baseElement =& $this->_resultPointers[$parent];
            } else {
                unset($this->_resultPointers[$dqlAlias]); // Ticket #1228
                continue;
            }

            // Check the type of the relation (many or single-valued)
            if ( ! $map['relation']->isOneToOne()) {
                $oneToOne = false;
                if (isset($nonemptyComponents[$dqlAlias])) {
                    if ( ! isset($baseElement[$relationAlias])) {
                        $baseElement[$relationAlias] = array();
                    }
                    $indexExists = isset($this->_identifierMap[$path][$id[$parent]][$id[$dqlAlias]]);
                    $index = $indexExists ? $this->_identifierMap[$path][$id[$parent]][$id[$dqlAlias]] : false;
                    $indexIsValid = $index !== false ? isset($baseElement[$relationAlias][$index]) : false;
                    if ( ! $indexExists || ! $indexIsValid) {
                        $element = $data;
                        if ($field = $this->_getCustomIndexField($dqlAlias)) {
                            $baseElement[$relationAlias][$element[$field]] = $element;
                        } else {
                            $baseElement[$relationAlias][] = $element;
                        }
                        end($baseElement[$relationAlias]);
                        $this->_identifierMap[$path][$id[$parent]][$id[$dqlAlias]] =
                        key($baseElement[$relationAlias]);
                    }
                } else if ( ! isset($baseElement[$relationAlias])) {
                    $baseElement[$relationAlias] = array();
                }
            } else {
                $oneToOne = true;
                if ( ! isset($nonemptyComponents[$dqlAlias]) && ! isset($baseElement[$relationAlias])) {
                    $baseElement[$relationAlias] = null;
                } else if ( ! isset($baseElement[$relationAlias])) {
                    $baseElement[$relationAlias] = $data;
                }
            }

            $coll =& $baseElement[$relationAlias];

            if ($coll !== null) {
                $this->updateResultPointer($coll, $index, $dqlAlias, $oneToOne);
            }
        }

        // Append scalar values to mixed result sets
        if (isset($scalars)) {
            foreach ($scalars as $name => $value) {
                $result[$this->_resultCounter - 1][$name] = $value;
            }
        }
    }

    /**
     * Updates the result pointer for an Entity. The result pointers point to the
     * last seen instance of each Entity type. This is used for graph construction.
     *
     * @param array $coll  The element.
     * @param boolean|integer $index  Index of the element in the collection.
     * @param string $dqlAlias
     * @param boolean $oneToOne  Whether it is a single-valued association or not.
     */
    private function updateResultPointer(&$coll, $index, $dqlAlias, $oneToOne)
    {
        if ($coll === null) {
            unset($this->_resultPointers[$dqlAlias]); // Ticket #1228
            return;
        }
        if ($index !== false) {
            $this->_resultPointers[$dqlAlias] =& $coll[$index];
            return;
        } else {
            if ($coll) {
                if ($oneToOne) {
                    $this->_resultPointers[$dqlAlias] =& $coll;
                } else {
                    end($coll);
                    $this->_resultPointers[$dqlAlias] =& $coll[key($coll)];
                }
            }
        }
    }

    /** {@inheritdoc} */
    protected function _getRowContainer()
    {
        return array();
    }
}

