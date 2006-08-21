<?php
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDoctrineUniqueValidator validates that the value does not already exists
 *
 * <b>Required parameters:</b>
 *
 * # <b>connection</b>    - [none]               - Doctrine db connection name.
 * # <b>class</b>         - [none]               - Doctrine class name.
 * # <b>column</b>        - [none]               - Doctrine column name.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>unique_error</b>  - [Uniqueness error]   - An error message to use when
 *                                                the value for this column already
 *                                                exists in the database.
 *
 * @package    symfony
 * @subpackage validator
 * @version
 */
class sfDoctrineUniqueValidator extends sfValidator
{
  public function execute (&$value, &$error)
  {

    $className  = ucfirst($this->getParameter('class'));
    $columnName = $className.'.'.$this->getParameter('column');

    //get connection
    $session = sfContext::getInstance()
      ->getDatabaseConnection($this->getParameter('connection'));


    $primaryKeys =  $session->getTable($className)->getPrimaryKeys();
    foreach($primaryKeys as $primaryKey)
    {
        if(is_null($primaryKeyValue = $this->getContext()
      ->getRequest()
      ->getParameter($primaryKey)));
        break;
    }


    $query = new Doctrine_Query($session);
    $query->from($className);

    if($primaryKeyValue === null)
    {
        $query->where($columnName.' = ?');
        $res = $query->execute(array($value));
    }
    else
    {
        $query->where($columnName.' = ? AND '.$primaryKey.' != ?');
        $res = $query->execute(array($value, $primaryKeyValue));

    }

    if(sizeof($res))
    {
      $error = $this->getParameterHolder()->get('unique_error');
      return false;
    }

    return true;


  }

  /**
   * Initialize this validator.
   *
   * @param sfContext The current application context.
   * @param array   An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   */
  public function initialize ($context, $parameters = null)
  {
    // initialize parent
    parent::initialize($context);

    // set defaults
    $this->setParameter('unique_error', 'Uniqueness error');

    $this->getParameterHolder()->add($parameters);

    // check parameters
    if (!$this->getParameter('connection'))
    {
      throw new sfValidatorException('The "connection" parameter is mandatory for the sfDoctrineUniqueValidator validator.');
    }

    // check parameters
    if (!$this->getParameter('class'))
    {
      throw new sfValidatorException('The "class" parameter is mandatory for the sfDoctrineUniqueValidator validator.');
    }

    if (!$this->getParameter('column'))
    {
      throw new sfValidatorException('The "column" parameter is mandatory for the sfDoctrineUniqueValidator validator.');
    }

    return true;
  }
}
