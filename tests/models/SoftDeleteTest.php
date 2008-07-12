<?php
Doctrine_Manager::getInstance()->setAttribute('use_dql_callbacks', true);
class SoftDeleteTest extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', null, array('primary' => true));
        $this->hasColumn('something', 'string', '25', array('notnull' => true, 'unique' => true));
    }

    public function setUp()
    {
        $this->actAs('SoftDelete');
    }
}