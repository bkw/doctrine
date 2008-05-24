<?php
class BookmarkUser extends Doctrine_Entity
{
    public static function initMetadata($class)
    {
        $class->setColumn('name', 'string', 30);
    	$class->hasMany('Bookmark as Bookmarks',
                        array('local' => 'id',
                              'foreign' => 'user_id'));
    }
}
