<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Ticket_AllTests::main');
}

require_once 'lib/DoctrineTestInit.php';

// Tests
require_once 'Dbal/Ticket/1Test.php';

class Dbal_Ticket_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new Doctrine_TestSuite('Doctrine Orm');

        $suite->addTestSuite('Dbal_Ticket_1Test');
        
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Ticket_AllTests::main') {
    Ticket_AllTests::main();
}