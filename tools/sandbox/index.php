<?php
require_once('config.php');

$models = Doctrine::loadModels('models');

$q = Doctrine_Query::create()
    ->select('u.id, u.username, p.*')
    ->from('User u')
    ->innerJoin('u.Phonenumbers p')
    ->limit(20);

echo $q->getSql();