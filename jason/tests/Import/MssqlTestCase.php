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
 * Doctrine_Import_Mssql_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Import_Mssql_TestCase extends Doctrine_UnitTestCase 
{
    public function testListSequencesExecutesSql() 
    {
        $this->import->listSequences('table');
        
        $this->assertEqual($this->adapter->pop(), "SELECT name FROM sysobjects WHERE xtype = 'U'");
    }
    public function testListTableColumnsExecutesSql()
    {
        $this->conn->setAttribute(Doctrine::ATTR_QUOTE_IDENTIFIER, false);
        $this->import->listTableColumns('table');
        
        $this->assertEqual($this->adapter->pop(), "EXEC sp_columns @table_name = table");
    }
    public function testListTablesExecutesSql()
    {
        $this->import->listTables();
        
        $this->assertEqual($this->adapter->pop(), "SELECT name FROM sysobjects WHERE type = 'U' AND name <> 'dtproperties' ORDER BY name");
    }
    public function testListTriggersExecutesSql()
    {
        $this->import->listTriggers();
        
        $this->assertEqual($this->adapter->pop(), "SELECT name FROM sysobjects WHERE xtype = 'TR'");
    }
    public function testListTableTriggersExecutesSql()
    {
        $this->import->listTableTriggers('table');
        
        $this->assertEqual($this->adapter->pop(), "SELECT name FROM sysobjects WHERE xtype = 'TR' AND object_name(parent_obj) = 'table'");
    }
    public function testListViewsExecutesSql()
    {
        $this->import->listViews();
        
        $this->assertEqual($this->adapter->pop(), "SELECT name FROM sysobjects WHERE xtype = 'V'");
    }
}
