<?php 
class BlogTag extends Doctrine_Record
{
    public static function initMetadata($class)
    {
        $class->setColumn('name', 'string', 100);
        $class->setColumn('description', 'string');
        $class->hasOne('Blog', array('onDelete' => 'CASCADE'));
    }
}
