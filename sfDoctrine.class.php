<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony.runtime.addon
 * @version    SVN: $Id: sfDoctrineAutoload.php 1507 2006-06-22 05:59:58Z fabien $
 */

class sfDoctrine
{

  static public function session($connection = null)
  {
    return connection($connection);
  }

  static public function connection($connection = null)
  {
    // load doctrine config
    require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_config_dir_name').'/doctrine.yml'));

    if($connection === null)
    {
      $connection = $defaultConnection;
      if($defaultConnection === null)
      {
        $error = 'Either specify a Doctrine connection or set a default in doctrine.yml.';
        throw new sfDatabaseException($error);
      }
    }

    $connection = sfContext::getInstance()->getDatabaseConnection($connection);
    foreach($attributes as $k => $v)
    {
      $connection->setAttribute(constant('Doctrine::'.$k), $v);
    }
    return $connection;
  }
}
