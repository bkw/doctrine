<?php 

class Doctrine_Mapper_SingleTable extends Doctrine_Mapper_Abstract
{
    
    public function getDiscriminatorColumn($domainClassName)
    {
        $inheritanceMap = $this->_table->getOption('inheritanceMap');
        return isset($inheritanceMap[$domainClassName]) ? $inheritanceMap[$domainClassName] : array();
    }
    
    public function getCustomQueryCriteria($domainClassName)
    {
        return $this->getDiscriminatorColumn($domainClassName);
    }
    
}

