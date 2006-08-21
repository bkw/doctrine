<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDoctrineDatabase provides connectivity for the Doctrine.
 *
 * @package    symfony
 * @subpackage database
 * @author     Maarten den Braber <mdb@twister.cx>
 * @version    SVN: $Id$
 */
class sfDoctrineDatabase extends sfDatabase
{
  /**
   * Connect to the database.
   *
   * @throws <b>sfDatabaseException</b> If a connection could not be created.
   */
  public function connect ()
  {
    // determine how to get our parameters
    $method = $this->getParameter('method', 'dsn');

    // get parameters
    switch ($method)
    {
      case 'dsn':
        $dsn = $this->getParameter('dsn');

        if ($dsn == null)
        {
          // missing required dsn parameter
          $error = 'Database configuration specifies method "dsn", but is missing dsn parameter';

          throw new sfDatabaseException($error);
        }
        break;
    }

    if(!class_exists('Doctrine'))
    {
        // missing required dsn parameter
          $error = 'Doctrine could not be found. Make sure that Doctrine.php is in your path.';
          throw new sfDatabaseException($error);
    }


    try
    {
      $pdo_username = $this->getParameter('username');
      $pdo_password = $this->getParameter('password');
      $manager = Doctrine_Manager::getInstance();
      $this->connection = $manager->openSession(new PDO($dsn, $pdo_username, $pdo_password));

    }
    catch (PDOException $e)
    {
      throw new sfDatabaseException($e->getMessage());
    }

  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   *
   * @throws <b>sfDatabaseException</b> If an error occurs while shutting down this database.
   */
  public function shutdown ()
  {
    if ($this->connection !== null)
    {
      @$this->connection = null;
    }
  }
}
