<?php
require_once(dirname(__FILE__) . '/../bootstrap.php');

Doctrine::generateYamlFromModels('schema.yml', 'models');