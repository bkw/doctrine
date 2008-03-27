<?php
/*
 *  $Id$
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
Doctrine::autoload('Doctrine_Expression_Driver');
/**
 * Doctrine_Expression_Mysql
 *
 * @package     Doctrine
 * @subpackage  Expression
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_Expression_Mysql extends Doctrine_Expression_Driver
{
    /**
     * returns the regular expression operator
     *
     * @return string
     */
    public function regexp()
    {
        return 'RLIKE';
    }

    /**
     * return string to call a function to get random value inside an SQL statement
     *
     * @return string to generate float between 0 and 1
     */
    public function random()
    {
        return 'RAND()';
    }

    /**
     * build a pattern matching string
     *
     * EXPERIMENTAL
     *
     * WARNING: this function is experimental and may change signature at
     * any time until labelled as non-experimental
     *
     * @access public
     *
     * @param array $pattern even keys are strings, odd are patterns (% and _)
     * @param string $operator optional pattern operator (LIKE, ILIKE and maybe others in the future)
     * @param string $field optional field name that is being matched against
     *                  (might be required when emulating ILIKE)
     *
     * @return string SQL pattern
     */
    public function matchPattern($pattern, $operator = null, $field = null)
    {
        $match = '';
        if ( ! is_null($operator)) {
            $field = is_null($field) ? '' : $field.' ';
            $operator = strtoupper($operator);
            switch ($operator) {
                // case insensitive
                case 'ILIKE':
                    $match = $field.'LIKE ';
                    break;
                // case sensitive
                case 'LIKE':
                    $match = $field.'LIKE BINARY ';
                    break;
                default:
                    throw new Doctrine_Expression_Mysql_Exception('not a supported operator type:'. $operator);
            }
        }
        $match.= "'";
        foreach ($pattern as $key => $value) {
            if ($key % 2) {
                $match .= $value;
            } else {
                $match .= $this->conn->escapePattern($this->conn->escape($value));
            }
        }
        $match.= "'";
        $match.= $this->patternEscapeString();
        return $match;
    }

    /**
     * Returns global unique identifier
     *
     * @return string to get global unique identifier
     */
    public function guid()
    {
        return 'UUID()';
    }

    /**
     * Returns a series of strings concatinated
     *
     * concat() accepts an arbitrary number of parameters. Each parameter
     * must contain an expression or an array with expressions.
     *
     * @param string|array(string) strings that will be concatinated.
     */
    public function concat()
    {
        $args = func_get_args();

        return 'CONCAT(' . join(', ', (array) $args) . ')';
    }
}
