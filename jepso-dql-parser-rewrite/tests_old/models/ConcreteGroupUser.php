<?php
class ConcreteGroupUser extends Doctrine_Entity
{
    public static function initMetadata($class)
    {
        $class->loadTemplate('GroupUserTemplate');
    }
}
