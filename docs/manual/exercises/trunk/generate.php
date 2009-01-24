<?php
require_once('bootstrap.php');

Doctrine::dropDatabases();
Doctrine::createDatabases();
Doctrine::generateModelsFromYaml('schema.yml', 'models');
Doctrine::createTablesFromModels('models');