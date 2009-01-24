<?php

/**
 * Bootstrap Doctrine.php, register autoloader specify
 * configuration attributes and load models.
 */

require_once(dirname(__FILE__) . '/lib/vendor/doctrine/Doctrine.php');
spl_autoload_register(array('Doctrine', 'autoload'));
$manager = Doctrine_Manager::getInstance();