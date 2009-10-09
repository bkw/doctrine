<?php
/**
 * Script to test the output from Doctrine_Cli
 * 
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/lib/Doctrine.php');
spl_autoload_register(array('Doctrine', 'autoload'));

$oCli = new Doctrine_Cli(array());
$oCli->loadTasks(dirname(__FILE__));
$oCli->run($_SERVER['argv']);