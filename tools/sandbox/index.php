<?php

require_once('config.php');

$models = Doctrine_Core::loadModels('models');

$q = Doctrine_Core::getTable('Article')->createQuery();

echo $q->getSqlQuery();
exit;
$comments = $q->execute();