<?php
class GzipTest extends Doctrine_Record {
    public static function initMetadata($class) {
        $class->setColumn('gzip', 'gzip', 100000);
    }
}
