<?php

namespace Doctrine\Tests\DBAL\Functional;

use Doctrine\Tests\DBAL\Functional;

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Dbal_Functional_AllTests::main');
}

require_once __DIR__ . '/../../TestInit.php';

class AllTests
{
    public static function main()
    {
        \PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new \Doctrine\Tests\DbalFunctionalTestSuite('Doctrine Dbal Functional');

        $suite->addTestSuite('Doctrine\Tests\DBAL\Functional\Schema\SqliteSchemaManagerTest');
        $suite->addTestSuite('Doctrine\Tests\DBAL\Functional\Schema\MySqlSchemaManagerTest');
        $suite->addTestSuite('Doctrine\Tests\DBAL\Functional\Schema\PostgreSqlSchemaManagerTest');
        $suite->addTestSuite('Doctrine\Tests\DBAL\Functional\Schema\OracleSchemaManagerTest');
        $suite->addTestSuite('Doctrine\Tests\DBAL\Functional\Schema\Db2SchemaManagerTest');
        $suite->addTestSuite('Doctrine\Tests\DBAL\Functional\ConnectionTest');
        $suite->addTestSuite('Doctrine\Tests\DBAL\Functional\DataAccessTest');
        $suite->addTestSuite('Doctrine\Tests\DBAL\Functional\WriteTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Dbal_Functional_AllTests::main') {
    AllTests::main();
}
