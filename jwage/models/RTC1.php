<?php
class RTC1 extends Doctrine_Record {
    public function setTableDefinition() { 
        $this->hasColumn('name', 'string', 200);
    }
    public function setUp() {
        $this->hasMany('M2MTest as RTC1', 'JC1.c2_id');
    }
}

