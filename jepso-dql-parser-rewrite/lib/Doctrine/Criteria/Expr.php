<?php

/*
 *  $Id: Expr.php 1393 2008-03-06 17:49:16Z guilhermeblanco $
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
 * Doctrine_Criteria_Expr
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
class Doctrine_Criteria_Expr extends Doctrine_Criteria
{
    /**
     * @var string $_expr The operand expression
     */
    protected $_expr;


    /**
     * @var array $params An array of parameters
     */
    protected $_params;


    /**
     * Constructor
     *
     * Creates expression to the query criteria.
     *
     * @param string $expr The operand expression
     * @param mixed $params An array of parameters or a simple scalar
     * @return Doctrine_Criteria_Expr
     */
    public function __construct($expr, $params = array())
    {
        $this->_expr = $expr;
        $this->_params = (is_array($params) ? $params : array($params));
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
        return '( ' . $this->_expr . ' )';
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
        return $this->_params;
    }
}
