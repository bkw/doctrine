<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Orm_Component_Query_AllTests::main');
}

require_once 'lib/DoctrineTestInit.php';

require_once 'IdentifierRecognitionTest.php';
require_once 'ScannerTest.php';
require_once 'LanguageRecognitionTest.php';

class Orm_Component_Query_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new Doctrine_TestSuite('Doctrine Orm Component Query');

        $suite->addTestSuite('Orm_Component_Query_ScannerTest');
        $suite->addTestSuite('Orm_Component_Query_LanguageRecognitionTest');
        $suite->addTestSuite('Orm_Component_Query_IdentifierRecognitionTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Orm_Component_Query_AllTests::main') {
    Orm_Component_Query_AllTests::main();
}
