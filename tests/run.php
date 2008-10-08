<?php

// Debug Diagnosic process attacher sleep time needed to link process
// More info about that: http://bugs.php.net/bugs-generating-backtrace-win32.php
//sleep(10);

error_reporting(E_ALL | E_STRICT);
ini_set('max_execution_time', 900);
ini_set('date.timezone', 'GMT+0');

if ( ! defined('DOCTRINE_DIR')) {
    define('DOCTRINE_DIR', dirname(__FILE__) . '/../lib/');
}

require_once(DOCTRINE_DIR . 'Doctrine.php');

spl_autoload_register(array('Doctrine', 'autoload'));

require_once(dirname(__FILE__) . '/DoctrineTest.php');

spl_autoload_register(array('DoctrineTest','autoload'));

$test = new DoctrineTest();

// Ticket Tests
// If you write a ticket testcase add it to the bottom of the list, with the ticket number in it
$tickets = new GroupTest('Tickets Tests', 'tickets');
$tickets->addTestCase(new Doctrine_Ticket_Njero_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_Ayoub_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_381_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_384_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_424B_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_424C_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_428_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_438_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_480_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_486_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_565_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_574_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_576_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_583_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_587_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_626B_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_626C_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_626D_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_632_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_638_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_642_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_668_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_673_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_697_TestCase());
//$tickets->addTestCase(new Doctrine_Ticket_710_TestCase()); // Only uncomment if you want to check free() performance!
$tickets->addTestCase(new Doctrine_Ticket_736_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_741_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_749_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_786_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_832_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_838_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_867_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_876_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_889_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_894_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_904_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_912_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_915_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_923_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_927_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_929_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_930_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_932_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_935_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_941_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_950_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_952_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_963_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_966_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_969_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_973_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_982_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_987_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1015_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1028_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1044_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1071_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1072_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1077_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1099_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1106_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1113_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1116_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1118_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1121_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1125_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1123_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1124_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1131_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1133_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1134_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1160_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1175_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1192_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1195_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1205_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1206_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1211_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1213_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1215_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1225_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1228_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1230_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1236_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1244_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1250_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1251_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1253_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1254_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1257_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1277_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1280_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1289_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1296_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1304_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1305_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1323_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1323b2_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1325_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1326_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1335_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1351_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1365_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1372_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1380_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1381_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1383_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1390_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1395_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1400_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1419_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1436_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1441_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1452_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1454_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1461_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1465_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1480_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1483_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1488_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1507_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1513_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1520_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1523_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1527_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1537_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1540_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1543_TestCase());
$tickets->addTestCase(new Doctrine_Ticket_1545_TestCase());
$test->addTestCase($tickets);

// Connection Tests (not yet fully tested)
$driver = new GroupTest('Driver Tests', 'driver');
$driver->addTestCase(new Doctrine_Connection_Pgsql_TestCase());
$driver->addTestCase(new Doctrine_Connection_Oracle_TestCase());
$driver->addTestCase(new Doctrine_Connection_Sqlite_TestCase());
$driver->addTestCase(new Doctrine_Connection_Mssql_TestCase());
$driver->addTestCase(new Doctrine_Connection_Mysql_TestCase());
$driver->addTestCase(new Doctrine_Connection_Firebird_TestCase());
$driver->addTestCase(new Doctrine_Connection_Informix_TestCase());
$test->addTestCase($driver);

// Transaction Tests (FULLY TESTED)
$transaction = new GroupTest('Transaction Tests', 'transaction');
$transaction->addTestCase(new Doctrine_Transaction_TestCase());
$transaction->addTestCase(new Doctrine_Transaction_Firebird_TestCase());
$transaction->addTestCase(new Doctrine_Transaction_Informix_TestCase());
$transaction->addTestCase(new Doctrine_Transaction_Mysql_TestCase());
$transaction->addTestCase(new Doctrine_Transaction_Mssql_TestCase());
$transaction->addTestCase(new Doctrine_Transaction_Pgsql_TestCase());
$transaction->addTestCase(new Doctrine_Transaction_Oracle_TestCase());
$transaction->addTestCase(new Doctrine_Transaction_Sqlite_TestCase());
$test->addTestCase($transaction);

// DataDict Tests (FULLY TESTED)
$data_dict = new GroupTest('DataDict Tests', 'data_dict');
$data_dict->addTestCase(new Doctrine_DataDict_TestCase());
$data_dict->addTestCase(new Doctrine_DataDict_Firebird_TestCase());
$data_dict->addTestCase(new Doctrine_DataDict_Informix_TestCase());
$data_dict->addTestCase(new Doctrine_DataDict_Mysql_TestCase());
$data_dict->addTestCase(new Doctrine_DataDict_Mssql_TestCase());
$data_dict->addTestCase(new Doctrine_DataDict_Pgsql_TestCase());
$data_dict->addTestCase(new Doctrine_DataDict_Oracle_TestCase());
$data_dict->addTestCase(new Doctrine_DataDict_Sqlite_TestCase());
$test->addTestCase($data_dict);

// Sequence Tests (not yet fully tested)
$sequence = new GroupTest('Sequence Tests', 'sequence');
$sequence->addTestCase(new Doctrine_Sequence_TestCase());
$sequence->addTestCase(new Doctrine_Sequence_Firebird_TestCase());
$sequence->addTestCase(new Doctrine_Sequence_Informix_TestCase());
$sequence->addTestCase(new Doctrine_Sequence_Mysql_TestCase());
$sequence->addTestCase(new Doctrine_Sequence_Mssql_TestCase());
$sequence->addTestCase(new Doctrine_Sequence_Pgsql_TestCase());
$sequence->addTestCase(new Doctrine_Sequence_Oracle_TestCase());
$sequence->addTestCase(new Doctrine_Sequence_Sqlite_TestCase());
$test->addTestCase($sequence);

// Export Tests (not yet fully tested)
$export = new GroupTest('Export Tests', 'export');
$export->addTestCase(new Doctrine_Export_CheckConstraint_TestCase());
$export->addTestCase(new Doctrine_Export_Firebird_TestCase());
$export->addTestCase(new Doctrine_Export_Informix_TestCase());
$export->addTestCase(new Doctrine_Export_TestCase());
$export->addTestCase(new Doctrine_Export_Mssql_TestCase());
$export->addTestCase(new Doctrine_Export_Pgsql_TestCase());
$export->addTestCase(new Doctrine_Export_Oracle_TestCase());
$export->addTestCase(new Doctrine_Export_Record_TestCase());
$export->addTestCase(new Doctrine_Export_Mysql_TestCase());
$export->addTestCase(new Doctrine_Export_Sqlite_TestCase());
$export->addTestCase(new Doctrine_Export_Schema_TestCase());
$test->addTestCase($export);

// Import Tests (not yet fully tested)
$import = new GroupTest('Import Tests', 'import');
$import->addTestCase(new Doctrine_Import_TestCase());
$import->addTestCase(new Doctrine_Import_Firebird_TestCase());
$import->addTestCase(new Doctrine_Import_Informix_TestCase());
$import->addTestCase(new Doctrine_Import_Mysql_TestCase());
$import->addTestCase(new Doctrine_Import_Mssql_TestCase());
$import->addTestCase(new Doctrine_Import_Pgsql_TestCase());
$import->addTestCase(new Doctrine_Import_Oracle_TestCase());
$import->addTestCase(new Doctrine_Import_Sqlite_TestCase());
$import->addTestCase(new Doctrine_Import_Builder_TestCase());
$import->addTestCase(new Doctrine_Import_Schema_TestCase());
$import->addTestCase(new Doctrine_Import_PluginHierarchy_TestCase());
$test->addTestCase($import);

// Expression Tests (not yet fully tested)
$expression = new GroupTest('Expression Tests', 'expression');
$expression->addTestCase(new Doctrine_Expression_TestCase());
$expression->addTestCase(new Doctrine_Expression_Driver_TestCase());
$expression->addTestCase(new Doctrine_Expression_Firebird_TestCase());
$expression->addTestCase(new Doctrine_Expression_Informix_TestCase());
$expression->addTestCase(new Doctrine_Expression_Mysql_TestCase());
$expression->addTestCase(new Doctrine_Expression_Mssql_TestCase());
$expression->addTestCase(new Doctrine_Expression_Pgsql_TestCase());
$expression->addTestCase(new Doctrine_Expression_Oracle_TestCase());
$expression->addTestCase(new Doctrine_Expression_Sqlite_TestCase());
$test->addTestCase($expression);

// Core Tests
$core = new GroupTest('Core Test', 'core');
$core->addTestCase(new Doctrine_Base_TestCase());
$core->addTestCase(new Doctrine_Access_TestCase());
$core->addTestCase(new Doctrine_Configurable_TestCase());
$core->addTestCase(new Doctrine_Manager_TestCase());
$core->addTestCase(new Doctrine_Connection_TestCase());
$core->addTestCase(new Doctrine_Table_TestCase());
$core->addTestCase(new Doctrine_Table_RemoveColumn_TestCase());
$core->addTestCase(new Doctrine_Table_NamedQuery_TestCase());
$core->addTestCase(new Doctrine_UnitOfWork_TestCase());
$core->addTestCase(new Doctrine_Collection_TestCase());
$core->addTestCase(new Doctrine_Collection_Snapshot_TestCase());
$core->addTestCase(new Doctrine_Hydrate_FetchMode_TestCase());
$core->addTestCase(new Doctrine_Hydrate_CollectionInitialization_TestCase());
$core->addTestCase(new Doctrine_Tokenizer_TestCase());
$core->addTestCase(new Doctrine_BatchIterator_TestCase());
$core->addTestCase(new Doctrine_Hydrate_TestCase());
$test->addTestCase($core);

// Relation Tests
$relation = new GroupTest('Relation Tests', 'relation');
$relation->addTestCase(new Doctrine_TreeStructure_TestCase());
$relation->addTestCase(new Doctrine_Relation_TestCase());
$relation->addTestCase(new Doctrine_Relation_Access_TestCase());
$relation->addTestCase(new Doctrine_Relation_ManyToMany_TestCase());
$relation->addTestCase(new Doctrine_Relation_ManyToMany2_TestCase());
$relation->addTestCase(new Doctrine_Relation_OneToMany_TestCase());
$relation->addTestCase(new Doctrine_Relation_Nest_TestCase());
$relation->addTestCase(new Doctrine_Relation_OneToOne_TestCase());
$relation->addTestCase(new Doctrine_Relation_Parser_TestCase());
$test->addTestCase($relation);

// Data Types Tests
$data_types = new GroupTest('Data Types Tests', 'data_types');
$data_types->addTestCase(new Doctrine_DataType_Enum_TestCase());
$data_types->addTestCase(new Doctrine_DataType_Boolean_TestCase());
$test->addTestCase($data_types);

// Behaviors Testing
$behaviors = new GroupTest('Behaviors Tests', 'behaviors');
$behaviors->addTestCase(new Doctrine_Plugin_TestCase());
$behaviors->addTestCase(new Doctrine_View_TestCase());
$behaviors->addTestCase(new Doctrine_AuditLog_TestCase());
$behaviors->addTestCase(new Doctrine_Hook_TestCase());
$behaviors->addTestCase(new Doctrine_I18n_TestCase());
$behaviors->addTestCase(new Doctrine_Sluggable_TestCase());
$behaviors->addTestCase(new Doctrine_Record_Generator_TestCase());
$behaviors->addTestCase(new Doctrine_SoftDelete_TestCase());
$test->addTestCase($behaviors);

// Validator Testing
$validators = new GroupTest('Validators Testing', 'validators');
$validators->addTestCase(new Doctrine_Validator_TestCase());
$validators->addTestCase(new Doctrine_Validator_Future_TestCase());
$validators->addTestCase(new Doctrine_Validator_Past_TestCase());
$test->addTestCase($validators);

// Db Tests
$db = new GroupTest('Db Tests', 'db');
$db->addTestCase(new Doctrine_Db_TestCase());
$db->addTestCase(new Doctrine_Connection_Profiler_TestCase());
$test->addTestCase($db);

// Event Listener Tests
$event_listener = new GroupTest('EventListener Tests','event_listener');
$event_listener->addTestCase(new Doctrine_EventListener_TestCase());
$event_listener->addTestCase(new Doctrine_EventListener_Chain_TestCase());
$test->addTestCase($event_listener);

// Query Tests
$query_tests = new GroupTest('Query Tests','query');
$query_tests->addTestCase(new Doctrine_Query_Condition_TestCase());
$query_tests->addTestCase(new Doctrine_Query_MultiJoin_TestCase());
$query_tests->addTestCase(new Doctrine_Query_MultiJoin2_TestCase());
$query_tests->addTestCase(new Doctrine_Query_ReferenceModel_TestCase());
$query_tests->addTestCase(new Doctrine_Query_ComponentAlias_TestCase());
$query_tests->addTestCase(new Doctrine_Query_ShortAliases_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Expression_TestCase());
$query_tests->addTestCase(new Doctrine_Query_OneToOneFetching_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Check_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Limit_TestCase());
$query_tests->addTestCase(new Doctrine_Query_IdentifierQuoting_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Update_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Delete_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Join_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Having_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Orderby_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Subquery_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Driver_TestCase());
$query_tests->addTestCase(new Doctrine_Query_AggregateValue_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Where_TestCase());
$query_tests->addTestCase(new Doctrine_Query_From_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Select_TestCase());
$query_tests->addTestCase(new Doctrine_Query_JoinCondition_TestCase());
$query_tests->addTestCase(new Doctrine_Query_MultipleAggregateValue_TestCase());
$query_tests->addTestCase(new Doctrine_Query_TestCase());
$query_tests->addTestCase(new Doctrine_Query_MysqlSubquery_TestCase());
$query_tests->addTestCase(new Doctrine_Query_PgsqlSubquery_TestCase());
$query_tests->addTestCase(new Doctrine_Query_MysqlSubqueryHaving_TestCase());
$query_tests->addTestCase(new Doctrine_Query_SelectExpression_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Registry_TestCase());
$query_tests->addTestCase(new Doctrine_Query_RemoveQueryPart_TestCase());
$query_tests->addTestCase(new Doctrine_Query_Copy_TestCase());
$query_tests->addTestCase(new Doctrine_Query_HydrateNone_TestCase());
$test->addTestCase($query_tests);

// Record Tests
$record = new GroupTest('Record Tests', 'record');
$record->addTestCase(new Doctrine_Record_Hook_TestCase());
$record->addTestCase(new Doctrine_Record_CascadingDelete_TestCase());
$record->addTestCase(new Doctrine_Record_Filter_TestCase());
$record->addTestCase(new Doctrine_Record_TestCase());
$record->addTestCase(new Doctrine_Record_State_TestCase());
$record->addTestCase(new Doctrine_Record_SerializeUnserialize_TestCase());
$record->addTestCase(new Doctrine_Record_Lock_TestCase());
$record->addTestCase(new Doctrine_Record_ZeroValues_TestCase());
$record->addTestCase(new Doctrine_Record_SaveBlankRecord_TestCase());
$record->addTestCase(new Doctrine_Record_Inheritance_TestCase());
$record->addTestCase(new Doctrine_Record_Synchronize_TestCase());
$record->addTestCase(new Doctrine_Record_FromArray_TestCase());
$test->addTestCase($record);

// Inheritance Tests
$inheritance = new GroupTest('Inheritance Tests', 'inheritance');
$inheritance->addTestCase(new Doctrine_ConcreteInheritance_TestCase());
$inheritance->addTestCase(new Doctrine_CtiColumnAggregationInheritance_TestCase());
$inheritance->addTestCase(new Doctrine_ColumnAggregationInheritance_TestCase());
$inheritance->addTestCase(new Doctrine_ClassTableInheritance_TestCase());
$inheritance->addTestCase(new Doctrine_Query_ApplyInheritance_TestCase());
$test->addTestCase($inheritance);

// Search Tests
$search = new GroupTest('Search Tests', 'search');
$search->addTestCase(new Doctrine_Search_TestCase());
$search->addTestCase(new Doctrine_Search_Query_TestCase());
$search->addTestCase(new Doctrine_Search_File_TestCase());
$test->addTestCase($search);

// Cache Tests
$cache = new GroupTest('Cache Tests', 'cache');
$cache->addTestCase(new Doctrine_Query_Cache_TestCase());
$cache->addTestCase(new Doctrine_Cache_Apc_TestCase());
$cache->addTestCase(new Doctrine_Cache_Memcache_TestCase());
$cache->addTestCase(new Doctrine_Cache_Sqlite_TestCase());
$cache->addTestCase(new Doctrine_Cache_Query_Sqlite_TestCase());
//$cache->addTestCase(new Doctrine_Cache_File_TestCase());
$cache->addTestCase(new Doctrine_Cache_Sqlite_TestCase());
//$cache->addTestCase(new Doctrine_Cache_TestCase());
$test->addTestCase($cache);

// Migration Tests
$migration = new GroupTest('Migration Tests', 'migration');
$migration->addTestCase(new Doctrine_Migration_TestCase());
$test->addTestCase($migration);

// File Parser Tests
$parser = new GroupTest('Parser Tests', 'parser');
$parser->addTestCase(new Doctrine_Parser_TestCase());
$test->addTestCase($parser);

// Data Fixtures Tests
$data = new GroupTest('Data exporting/importing fixtures', 'data_fixtures');
$data->addTestCase(new Doctrine_Data_Import_TestCase());
$data->addTestCase(new Doctrine_Data_Export_TestCase());
$test->addTestCase($data);

// Unsorted Tests. These need to be sorted and placed in the appropriate group
$unsorted = new GroupTest('Unsorted Tests', 'unsorted');
$unsorted->addTestCase(new Doctrine_CustomPrimaryKey_TestCase());
$unsorted->addTestCase(new Doctrine_CustomResultSetOrder_TestCase());
$unsorted->addTestCase(new Doctrine_ColumnAlias_TestCase());
//$unsorted->addTestCase(new Doctrine_RawSql_TestCase());
$unsorted->addTestCase(new Doctrine_NewCore_TestCase());
$unsorted->addTestCase(new Doctrine_Template_TestCase());
$unsorted->addTestCase(new Doctrine_NestedSet_SingleRoot_TestCase());
$unsorted->addTestCase(new Doctrine_NestedSet_MultiRoot_TestCase());
$unsorted->addTestCase(new Doctrine_PessimisticLocking_TestCase());
$test->addTestCase($unsorted);

$test->run();