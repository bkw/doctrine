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
     * The Sql Builder object.
     *
     * @var Doctrine_Query_SqlBuilder
     */
    protected $_sqlbuilder;

    /**
     * A scanner object.
     *
     * @var Doctrine_Query_Scanner
     */
    protected $_scanner;

    /**
     * The Parser Result object.
     *
     * @var Doctrine_Query_ParserResult
     */
    protected $_parserResult;

    /**
     * Keyword symbol table
     *
     * @var Doctrine_Query_Token
     */
    protected $_keywordTable;

    /**
     * A query printer object used to print a parse tree from the input string
     * for debugging purposes.
     *
     * @var Doctrine_Query_Printer
     */
    protected $_printer;


    // Scanner Stuff

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
     * Array containing errors detected in the query string during parsing process.
     *
     * @var array
     */
    protected $_errors;

    /**
     * @var int The number of tokens read since last error in the input string.
     */
    protected $_errorDistance;

    // End of Error management stuff


    /**
     * Creates a new query parser object.
     *
     * @param string $dql DQL to be parsed.
     */
    public function __construct($dql, $connection = null)
    {
        $this->_scanner = new Doctrine_Query_Scanner($dql);
        $this->_parserResult = new Doctrine_Query_ParserResult();
        $this->_sqlBuilder = Doctrine_Query_SqlBuilder::fromConnection($connection);
        $this->_keywordTable = new Doctrine_Query_Token();

        $this->free(true);

        // Used for debug purposes. Remove it later!
        $this->_printer = new Doctrine_Query_Printer(true);
    }


    /**
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

            $this->next();
        } else {
            // No definition for value checking.
            $this->syntaxError($this->_keywordTable->getLiteral($token));
        }

        return $isMatch;
    }


    /**
     * @todo [TODO] Document these!
     */
    public function next()
    {
        $this->token = $this->lookahead;
        $this->lookahead = $this->_scanner->next();
        $this->_errorDistance++;
    }


    public function free($deep = false)
    {
        // WARNING! Use this method with care. It resets the scanner!
        $this->_scanner->resetPosition();

        // Deep = true cleans peek and also any previously defined errors
        if ($deep) {
            $this->_scanner->resetPeek();
            $this->_errors = array();
        }

        $this->token = null;
        $this->lookahead = null;

        $this->_errorDistance = self::MIN_ERROR_DISTANCE;
    }


    /**
     * Parses a query string.
     */
    public function parse()
    {
        $this->lookahead = $this->_scanner->next();

        // Building the Abstract Syntax Tree
        $AST = $this->getProduction('QueryLanguage')->execute();

        // Check for end of string
        if ($this->lookahead !== null) {
            $this->syntaxError('end of string');
        }

        // Check for semantical errors
        if (count($this->_errors) > 0) {
            throw new Doctrine_Query_Parser_Exception(implode("\r\n", $this->_errors));
        }

        // Assign the SQL in parser result
        $this->_parserResult->setSql($AST->buildSql());

        return $this->_parserResult;
    }


    /**
     * Retrieves the assocated Doctrine_Query_SqlBuilder to this object.
     *
     * @return Doctrine_Query_SqlBuilder
     */
    public function getSqlBuilder()
    {
        return $this->_sqlBuilder;
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
     * Returns the parser result associated with this object.
     *
     * @return Doctrine_Query_ParserResult
     */
    public function getParserResult()
    {
        return $this->_parserResult;
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
     * Returns a production object with the given name.
     *
     * @param string $name production name
     * @return Doctrine_Query_Production
     */
    public function getProduction($name)
    {
        $class = 'Doctrine_Query_Production_' . $name;

        return new $class($this);
    }


    /**
     * Generates a new syntax error.
     *
     * @param string $expected Optional expected string.
     * @param array $token Optional token.
     */
    public function syntaxError($expected = '', $token = null)
    {
        if ($token === null) {
            $token = $this->lookahead;
        }

        // Formatting message
        $message = 'line 0, col ' . $token['position'] . ': Error: ';

        if ($expected !== '') {
            $message .= "Expected '$expected', got ";
        } else {
            $message .= 'Unexpected ';
        }

        if ($this->lookahead === null) {
            $message .= 'end of string.';
        } else {
            $message .= "'{$this->lookahead['value']}'";
        }

        throw new Doctrine_Query_Parser_Exception($message);
    }


    /**
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

}
