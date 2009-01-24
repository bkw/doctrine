<?php
require_once(dirname(__FILE__) . '/../bootstrap.php');

Doctrine::generateModelsFromDb(dirname(__FILE__) . '/../models', array('doctrine'), array('generateTableClasses' => true));