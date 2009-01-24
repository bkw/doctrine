<?php
require_once(dirname(__FILE__) . '/../bootstrap.php');

$user = new User();
$user->username = 'jwage';
$user->password = 'changeme';

echo $user->password; // outputs md5 hash and not changeme