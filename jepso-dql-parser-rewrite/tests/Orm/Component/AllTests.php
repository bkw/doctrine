<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Orm_Component_AllTests::main');
}

require_once 'lib/DoctrineTestInit.php';

// Tests
require_once 'Orm/Component/Query/AllTests.php';
require_once 'Orm/Component/TestTest.php';

class Orm_Component_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new Doctrine_TestSuite('Doctrine Orm Component');

        $suite->addTestSuite('Orm_Component_TestTest');
        $suite->addTest(Orm_Component_Query_AllTests::suite());

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Orm_Component_AllTests::main') {
    Orm_Component_AllTests::main();
}
