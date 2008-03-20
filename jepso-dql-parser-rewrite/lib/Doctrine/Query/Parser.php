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

/**
 * An LL(k) parser for the context-free grammar of Doctrine Query Language.
 * Parses a DQL query, reports any errors in it, and generates the corresponding
 * SQL.
 *
 * @package     Doctrine
 * @subpackage  Query
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Query_Parser
{
    /**
     * The minimum number of tokens read after last detected error before
     * another error can be reported.
     *
     * @var int
     */
    const MIN_ERROR_DISTANCE = 2;


    /**
     * A scanner object.
     *
     * @var Doctrine_Query_Scanner
     */
    protected $_scanner;

    /**
     * The builder object.
     *
     * @var Doctrine_Query_Builder
     */
    protected $_builder;

    /**
     * A query printer object used to print a parse tree from the input string
     * for debugging purposes.
     *
     * @var Doctrine_Query_Printer
     */
    protected $_printer;


    // Scanner Stuff

    /**
     * @var array An array of production objects with their names as keys.
     */
    protected $_productions = array();

    /**
     * @var array The next token in the query string.
     */
    public $lookahead;

    /**
     * @var array The last matched token.
     */
    public $token;

    // End of Scanner Stuff


    // Error management stuff

    /**
     * Array containing syntax and semantical errors detected in the query
     * string during parsing process.
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * @var int Number of semantical errors
     */
    protected $_semanticalErrorCount = 0;

    /**
     * @var int Number of syntatical errors
     */
    protected $_syntaxErrorCount = 0;

    /**
     * @var int The number of tokens read since last error in the input string.
     */
    protected $_errorDistance = self::MIN_ERROR_DISTANCE;

    // End of Error management stuff


    /**
     * constructor
     *
     * Creates a new query parser object.
     *
     * @param string $dql DQL to be parsed.
     */
    public function __construct($dql)
    {
        $this->_scanner = new Doctrine_Query_Scanner($dql);
        $this->_builder = new Doctrine_Query_Builder();

        // Used for debug purposes. Remove it later!
        $this->_printer = new Doctrine_Query_Printer(true);
    }


    /**
     * match
     *
     * Attempts to match the given token with the current lookahead token.
     *
     * If they match, updates the lookahead token; otherwise raises a syntax
     * error.
     *
     * @param int|string token type or value
     * @return bool True, if tokens match; false otherwise.
     */
    public function match($token)
    {
        if (is_string($token)) {
            $isMatch = ($this->lookahead['value'] === $token);
        } else {
            $isMatch = ($this->lookahead['type'] === $token);
        }

        if ($isMatch) {
            $this->_printer->println($this->lookahead['value']);

            $this->token = $this->lookahead;
            $this->lookahead = $this->_scanner->next();
            $this->_errorDistance++;
        } else {
            $this->syntaxError();
        }

        return $isMatch;
    }


    /**
     * parse
     *
     * Parses a query string.
     */
    public function parse()
    {
        $this->lookahead = $this->_scanner->next();

        $this->getProduction('QueryLanguage')->execute();

        if ($this->lookahead !== null) {
            $this->syntaxError('end of string');
        }

        return $this->_builder->getParserResult();
    }


    /**
     * Returns the scanner object associated with this object.
     *
     * @return Doctrine_Query_Scanner
     */
    public function getScanner()
    {
        return $this->_scanner;
    }


    /**
     * Returns the sql builder object associated with this object.
     *
     * @return Doctrine_Query_Builder
     */
    public function getBuilder()
    {
        return $this->_builder;
    }


    /**
     * Returns the parse tree printer object associated with this object.
     *
     * @return Doctrine_Query_Printer
     */
    public function getPrinter()
    {
        return $this->_printer;
    }


    /**
     * getProduction
     *
     * Returns a production object with the given name.
     *
     * @param string $name production name
     * @return Doctrine_Query_Production
     */
    public function getProduction($name)
    {
        if ( ! isset($this->_productions[$name])) {
            $class = 'Doctrine_Query_Production_' . $name;
            $this->_productions[$name] = new $class($this);
        }

        return $this->_productions[$name];
    }


    /**
     * syntaxError
     *
     * Generates a new syntatical error.
     *
     * @param string $expected Optional expected string.
     * @param array $token Optional token.
     */
    public function syntaxError($expected = '', $token = null)
    {
        if ($token === null) {
            $token = $this->lookahead;
        }

        if ($expected !== '') {
            $message = "Expected '$expected', got ";
        } else {
            $message = 'Unexpected ';
        }

        if ($this->lookahead === null) {
            $message .= 'end of string.';
        } else {
            $message .= "'{$this->lookahead['value']}'";
        }

        $this->_syntaxErrorCount++;

        $this->_logError('Error: ' . $message, $token);
    }


    /**
     * semanticalError
     *
     * Generates a new semantical error.
     *
     * @param string $message Optional message.
     * @param array $token Optional token.
     */
    public function semanticalError($message = '', $token = null)
    {
        $this->_semanticalErrorCount++;

        if ($token === null) {
            $token = $this->token;
        }

        $this->_logError('Warning: ' . $message, $token);
    }


    /**
     * _logError
     *
     * Logs new error entry.
     *
     * @param string $message Message to log.
     * @param array $token Token that it was processing.
     */
    protected function _logError($message = '', $token)
    {
        if ($this->_errorDistance >= self::MIN_ERROR_DISTANCE) {
            $message = 'line 0, col ' . $token['position'] . ': ' . $message;
            $this->_errors[] = $message;
        }

        $this->_errorDistance = 0;
    }


    /**
     * hasErrors
     *
     * Checks if the current processment has errors or not.
     *
     * @return bool If there're errors or not.
     */
    public function hasErrors()
    {
        return $this->getErrorCount() > 0;
    }


    /**
     * getErrorCount
     *
     * Return the number of errors occured.
     *
     * @return int Number of errors occurred.
     */
    public function getErrorCount()
    {
        return count($this->_errors);
    }


    /**
     * getSyntaxErrorCount
     *
     * Return the number of syntax errors occured.
     *
     * @return int Number of errors occurred.
     */
    public function getSyntaxErrorCount()
    {
        return $this->_syntaxErrorCount;
    }


    /**
     * getSemanticalErrorCount
     *
     * Return the number of semantical errors occured.
     *
     * @return int Number of errors occurred.
     */
    public function getSemanticalErrorCount()
    {
        return $this->_semanticalErrorCount;
    }


    /**
     * getErrors
     *
     * Return the errors occured.
     *
     * @return array Errors occured.
     */
    public function getErrors()
    {
        return $this->_errors;
    }

}
