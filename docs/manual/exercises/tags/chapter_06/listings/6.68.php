<?php
require_once(dirname(__FILE__) . '/../bootstrap.php');

$conn = Doctrine_Manager::connection('mysql://username:password@localhost/test', 'connection 1');

$conn2 = Doctrine_Manager::connection('mysql://username2:password2@localhost/test2', 'connection 2');

foreach($manager as $conn) {
    echo $conn->getName() . "\n";
}