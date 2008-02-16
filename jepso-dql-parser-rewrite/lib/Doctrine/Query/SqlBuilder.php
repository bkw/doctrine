<?php

class Doctrine_Query_SqlBuilder
{
    private $_aliasDeclarations = array();

    public function getAliasDeclarations()
    {
        return $this->_aliasDeclarations;
    }

    public function setAliasDeclaration($alias, array $declaration)
    {
        return $this->_aliasDeclarations[$alias] = $declaration;
    }

    public function getAliasDeclaration($alias)
    {
        return $this->_aliasDeclarations[$alias];
    }

    public function hasAliasDeclaration($alias)
    {
        return isset($this->_aliasDeclarations['$alias']);
    }
}
