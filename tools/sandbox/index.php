<?php
require_once('config.php');

Doctrine::loadModels('models');

Doctrine::generateMigrationsFromDiff('migrations', 'schema/schema1.yml', 'schema/schema2.yml');