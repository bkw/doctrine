<?php
class VersioningTest extends Doctrine_Record 
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string');
        $this->hasColumn('version', 'integer');
    }
    public function setUp()
    {
        $this->actAs('Versionable');
    }
}

class VersioningTest2 extends Doctrine_Record 
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string');
        $this->hasColumn('version', 'integer');
    }
    public function setUp()
    {
        $this->actAs('Versionable', array('auditLog' => false));
    }
}
