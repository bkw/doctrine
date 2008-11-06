<?php
abstract class CTII18nTestAbstract extends Doctrine_Record
{}

class CTII18nTestParent extends CTII18nTestAbstract
{
    public function setTableDefinition()
    {
        $this->hasColumn('parent_name', 'string', 200);
    }
}

class CTII18nTest extends CTII18nTestParent
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 200);
        $this->hasColumn('title', 'string', 200);
    }
    public function setUp()
    {
        $this->actAs('I18n', array('fields' => array('name', 'title')));
    }
}
