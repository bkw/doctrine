<?php
require_once(dirname(__FILE__) . '/../bootstrap.php');

$conn = Doctrine_Manager::connection('mysql://username:password@localhost/test', 'connection 1');

$conn2 = Doctrine_Manager::connection();

if ($conn === $conn2) {
    echo 'Doctrine_Manager::connection() returns the current connection';
}