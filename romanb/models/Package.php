<?php
class Package extends Doctrine_Record {
    public static function initMetadata($class) {
        $class->setColumn('description', 'string', 255);
        $class->hasMany('PackageVersion as Version', 'PackageVersion.package_id');
    }
}
