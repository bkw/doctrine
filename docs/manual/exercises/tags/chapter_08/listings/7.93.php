<?php
require_once(dirname(__FILE__) . '/../bootstrap.php');

$usersCreatedToday = Doctrine::getTable('User')->getCreatedToday();