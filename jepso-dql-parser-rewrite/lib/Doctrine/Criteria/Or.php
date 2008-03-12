<?php

/*
 *  $Id: Or.php 1393 2008-03-06 17:49:16Z guilhermeblanco $
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
 * Doctrine_Criteria_Or
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
class Doctrine_Criteria_Or extends Doctrine_Criteria
{
    /**
     * @var array $_dqlParts An array containing all DQL query parts.
     */
    protected $_dqlParts = array();


    /**
     * Constructor
     *
     * Initializes the object with items.
     *
     * @param mixed $expr1 Criteria expression
     * @param mixed $expr2 Criteria expression
     * @return Doctrine_Criteria_Or
     */
    public function __construct($expr1, $expr2)
    {
        $args = func_get_args();
        $this->_dqlParts = $args;
    }


    /**
     * add
     *
     * Add new Criteria expressions to be added.
     *
     * @param mixed $expr Criteria expression
     * @param boolean $override Optional argument. If true, overrides the
     *                          already defined expressions; if false, append.
     * @return Doctrine_Criteria_Or
     */
    public function add($expr, $override = false)
    {
        if ($override) {
            $this->_dqlParts = array();
        }

        $this->_dqlParts[] = $expr;

        return $this;
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
        return '( ' . implode(' OR ', array_map(array(&$this, '_processDqlPart'), $this->_dqlParts)) . ' )';
    }


    /**
     * getParams
     *
     * Retrieves the processed params array related to this criteria expression.
     *
     * @return array An array of parameters
     */
    public function getParams()
    {
        $params = array();

        for ($i = 0, $l = count($this->_dqlParts); $i < $l; $i++) {
            $params = array_merge($params, $this->_dqlParts[$i]->getParams());
        }

        return $params;
    }


    /**
     * @nodoc
     */
    protected function _processDqlPart($value)
    {
        return  ($value instanceof Doctrine_Criteria) ? $value->getDql() : $value;
    }
}
