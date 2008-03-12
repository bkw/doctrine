<?php

/*
 *  $Id: NotIn.php 1393 2008-03-06 17:49:16Z guilhermeblanco $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

Doctrine::autoload('Doctrine_Criteria');

/**
 * Doctrine_Criteria_NotIn
 *
 * @package     Doctrine
 * @subpackage  Criteria
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.com
 * @since       1.0
 * @version     $Revision: 1393 $
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @todo        See {@link Doctrine_Criteria}
 */
class Doctrine_Criteria_NotIn extends Doctrine_Criteria
{
    /**
     * @var string $_expr The operand of the NOT IN
     */
    protected $_expr;


    /**
     * @var string $_dqlPart The compiled dqlPart to be applied inside NOT IN ()
     */
    protected $_dqlPart;


    /**
     * @var array $params An array of parameters
     */
    protected $_params;


    /**
     * Constructor
     *
     * Creates NOT IN condition to the query criteria.
     *
     * @param string $expr The operand of the IN
     * @param mixed $params An array of parameters or a simple scalar
     * @return Doctrine_Criteria_In
     */
    public function __construct($expr, $params = array())
    {
        $params = (array) $params;

        // Must have at least one param, otherwise we'll get an empty IN () => invalid SQL
        if ( ! count($params)) {
            throw new Doctrine_Criteria_Exception(
                'Cannot create NOT IN() criteria object with an empty params argument.'
            );
        }

        list($dqlPart, $params) = $this->_processWhereInParams($params);

        $this->_expr = $expr;
        $this->_dqlPart = $dqlPart;
        $this->_params = $params;
    }


    /**
     * getDql
     * returns the DQL query that is represented by this criteria object.
     *
     * the query is built from $_dqlParts
     *
     * @return string The DQL query criteria
     */
    public function getDql()
    {
        return '( ' . $this->_expr . ' NOT IN (' . $this->_dqlPart . ') )';
    }


    /**
     * getParams
     *
     * Retrieves the processed params array related to this criteria expression
     *
     * @return array An array of parameters
     */
    public function getParams()
    {
        return $this->_params;
    }


    /**
     * _processWhereInParams
     *
     * Processes the WHERE IN () parameters and return an indexed array containing
     * the sqlPart to be placed in SQL statement and the new parameters (that will be
     * bound in SQL execution)
     *
     * @param array $params Parameters to be processed
     * @return array
     */
    protected function _processWhereInParams($params = array())
    {
        return array(
            // [0] => dqlPart
            implode(', ', array_map(array(&$this, '_processWhereInDqlPart'), $params)),
            // [1] => params
            array_filter($params, array(&$this, '_processWhereInParamItem')),
        );
    }


    /**
     * @nodoc
     */
    protected function _processWhereInDqlPart($value)
    {
        // [TODO] Add support to imbricated query (must deliver the hardest effort to Parser)
        return  ($value instanceof Doctrine_Expression) ? $value->getSql() : '?';
    }


    /**
     * @nodoc
     */
    protected function _processWhereInParamItem($value)
    {
        // [TODO] Add support to imbricated query (must deliver the hardest effort to Parser)
        return ( ! ($value instanceof Doctrine_Expression));
    }
}
