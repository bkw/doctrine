<?php
require_once('bootstrap.php');

$daniels = new User();
$daniels->username = 'Jack Daniels';

$brandy = new User();
$brandy->username = 'John Brandy';

$koskenkorva = new User();
$koskenkorva->username = 'Mikko Koskenkorva';

$beer = new User();
$beer->username = 'Stefan Beer';

$daniels->Friends[0] = $brandy;

$koskenkorva->Friends[0] = $daniels;
$koskenkorva->Friends[1] = $brandy;
$koskenkorva->Friends[2] = $beer;

$conn->flush();

$beer->free();
unset($beer);
$user = Doctrine::getTable('User')->findOneByUsername('Stefan Beer');

print_r($user->Friends->toArray());