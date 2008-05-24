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
 * Doctrine_Query_QueryResult
 *
 * @package     Doctrine
 * @subpackage  Query
 * @author      Roman Borschel <roman@code-factory.org>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       2.0
 * @version     $Revision$
 */
abstract class Doctrine_Query_SqlExecutor_Abstract implements Serializable
{
    // [TODO] Remove me later!
    public $AST;

    protected $_sqlStatements;

    public function __construct(Doctrine_Query_Production $AST)
    {
        // [TODO] Remove me later!
        $this->AST = $AST;
    }


    /**
     * Gets the SQL statements that are executed by the executor.
     *
     * @return array  All the SQL update statements.
     */
    public function getSqlStatements()
    {
        return $this->_sqlStatements;
    }


    /**
     * Executes all sql statements.
     *
     * @param Doctrine_Connection $conn  The database connection that is used to execute the queries.
     * @param array $params  The parameters.
     */
    abstract public function execute(Doctrine_Connection $conn, array $params);


    /**
     * Factory method.
     * Creates an appropriate sql executor for the given AST.
     *
     * @param Doctrine_Query_Production $AST  The root node of the AST.
     * @return Doctrine_Query_SqlExecutor_Abstract  The executor that is suitable for the given AST.
     */
    public static function create(Doctrine_Query_Production $AST)
    {
        $isDeleteStatement = $AST instanceof Doctrine_Query_Production_DeleteStatement;
        $isUpdateStatement = $AST instanceof Doctrine_Query_Production_UpdateStatement;

        if ($isUpdateStatement || $isDeleteStatement) {
            // TODO: Inspect the $AST and create the proper executor like so (pseudo-code):
            /*
            if (primaryClassInFromClause->isMultiTable()) {
                   if ($isDeleteStatement) {
                       return new Doctrine_Query_SqlExecutor_MultiTableDelete($AST);
                   } else {
                       return new Doctrine_Query_SqlExecutor_MultiTableUpdate($AST);
                   }
            } else ...
            */
            return new Doctrine_Query_SqlExecutor_SingleTableDeleteUpdate($AST);
        } else {
            return new Doctrine_Query_SqlExecutor_SingleSelect($AST);
        }
    }


    /**
     * Serializes the sql statements of the executor.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->_sqlStatements);
    }


    /**
     * Reconstructs the executor with it's sql statements.
     */
    public function unserialize($serialized)
    {
        $this->_sqlStatements = unserialize($serialized);
    }
}