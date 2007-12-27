<?php 

class Doctrine_Mapper_Joined extends Doctrine_Mapper
{
    
    /**
     * inserts a record into database
     *
     * @param Doctrine_Record $record   record to be inserted
     * @return boolean
     * @todo Move to Doctrine_Table (which will become Doctrine_Mapper).
     */
    public function insert(Doctrine_Record $record)
    {
        $table = $this;
        if (count($table->getOption('joinedParents')) > 0) {
            $dataSet = $this->formatDataSet($record);
            $component = $table->getComponentName();
            $classes = $table->getOption('joinedParents');
            $classes[] = $component;

            foreach ($classes as $k => $parent) {
                if ($k === 0) {
                    $rootRecord = new $parent();
                    $rootRecord->merge($dataSet[$parent]);
                    parent::insert($rootRecord);
                } else {
                    foreach ((array) $rootRecord->identifier() as $id => $value) {
                        $dataSet[$parent][$id] = $value;
                    }
                    $this->_conn->insert($this->_conn->getTable($parent), $dataSet[$parent]);
                }
            }
        } else {
            $this->_conn->processSingleInsert($record);
        }

        return true;
    }
    
    /**
     * CLASS TABLE INHERITANCE SPECIFIC
     * @todo DESCRIBE WHAT THIS METHOD DOES, PLEASE!
     */
    public function formatDataSet(Doctrine_Record $record)
    {
        $table = $this;

        $dataSet = array();
    
        $component = $table->getComponentName();
    
        $array = $record->getPrepared();
    
        foreach ($table->getColumns() as $columnName => $definition) {
            $fieldName = $table->getFieldName($columnName);
            if (isset($definition['primary']) && $definition['primary']) {
                continue;
            }
    
            if (isset($definition['owner'])) {
                $dataSet[$definition['owner']][$fieldName] = $array[$fieldName];
            } else {
                $dataSet[$component][$fieldName] = $array[$fieldName];
            }
        }    
        
        return $dataSet;
    }
}

