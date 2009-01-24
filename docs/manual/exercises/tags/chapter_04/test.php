<?php
require_once('bootstrap.php');

$conn->export->createTable('test', array('name' => array('type' => 'string')));
$conn->execute('INSERT INTO test (name) VALUES (?)', array('jwage'));

$stmt = $conn->prepare('SELECT * FROM test');
$stmt->execute();
$results = $stmt->fetchAll();
print_r($results);