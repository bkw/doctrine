<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Query_AllTests::main');
}

require_once 'IdentifierRecognitionTest.php';
require_once 'ScannerTest.php';
require_once 'LanguageRecognitionTest.php';

class Query_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new Doctrine_TestSuite('Doctrine Query Parser');

        $suite->addTestSuite('Doctrine_Query_ScannerTest');
        $suite->addTestSuite('Doctrine_Query_LanguageRecognitionTest');
        //$suite->addTestSuite('Doctrine_Query_IdentifierRecognitionTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Query_AllTests::main') {
    Query_AllTests::main();
}
