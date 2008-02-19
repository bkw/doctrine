<?php
/*
 *  $Id: Builder.php 2939 2007-10-19 14:23:42Z Jonathan.Wage $
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
 * <http://www.phpdoctrine.com>.
 */

/**
 * Doctrine_Migration_Builder
 *
 * @package     Doctrine
 * @subpackage  Migration
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Jonathan H. Wage <jwage@mac.com>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.com
 * @since       1.0
 * @version     $Revision: 2939 $
 */
class Doctrine_Migration_Builder
{
    /**
     * migrationsPath
     * 
     * The path to your migration classes directory
     *
     * @var string
     */
    private $migrationsPath = '';

    /**
     * suffix
     * 
     * File suffix to use when writing class definitions
     *
     * @var string $suffix
     */
    private $suffix = '.class.php';

    /**
     * tpl
     *
     * Class template used for writing classes
     *
     * @var $tpl
     */
    private static $tpl;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct($migrationsPath = null)
    {
        if ($migrationsPath) {
            $this->setMigrationsPath($migrationsPath);
        }
        
        $this->loadTemplate();
    }

    /**
     * setMigrationsPath
     *
     * @param string path   the path where migration classes are stored and being generated
     * @return
     */
    public function setMigrationsPath($path)
    {
        if ( ! file_exists($path)) {
            mkdir($path, 0777);
        }

        $this->migrationsPath = $path;
    }

    /**
     * getMigrationsPath
     *
     * @return string       the path where migration classes are stored and being generated
     */
    public function getMigrationsPath()
    {
        return $this->migrationsPath;
    }

    /**
     * loadTemplate
     * 
     * Loads the class template used for generating classes
     *
     * @return void
     */
    protected function loadTemplate() 
    {
        if (isset(self::$tpl)) {
            return;
        }

        self::$tpl =<<<END
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class %s extends %s
{
	public function up()
	{
%s
	}

	public function down()
	{
%s
	}
}
END;
    }

    /**
     * generateMigrationsFromDb
     *
     * @return void
     */
    public function generateMigrationsFromDb()
    {
        $directory = '/tmp/tmp_doctrine_models';

        Doctrine::generateModelsFromDb($directory);
        
        $result = $this->generateMigrationsFromModels($directory);
        
        Doctrine::removeDirectories($directory);
        
        return $result;
    }

    /**
     * generateMigrationsFromModels
     *
     * @param string $modelsPath 
     * @return void
     */
    public function generateMigrationsFromModels($modelsPath = null)
    {
        if ($modelsPath) {
            $models = Doctrine::loadModels($modelsPath);
        } else {
            $models = Doctrine::getLoadedModels();
        }
        
        $foreignKeys = array();
        
        foreach ($models as $model) {
            $export = Doctrine::getTable($model)->getExportableFormat();
            
            $foreignKeys[$export['tableName']] = $export['options']['foreignKeys'];
            
            $up = $this->buildCreateTable($export);
            $down = $this->buildDropTable($export);
            
            $className = 'Add'.Doctrine::classify($export['tableName']);
            
            $this->generateMigrationClass($className, array(), $up, $down);
        }
        
        $className = 'ApplyForeignKeyConstraints';
        
        $up = '';
        $down = '';
        foreach ($foreignKeys as $tableName => $definitions)    {
            $tableForeignKeyNames[$tableName] = array();
            
            foreach ($definitions as $definition) {
                $definition['name'] = $tableName . '_' . $definition['foreignTable'] . '_' . $definition['local'] . '_' . $definition['foreign'];
                
                $up .= $this->buildCreateForeignKey($tableName, $definition);
                $down .= $this->buildDropForeignKey($tableName, $definition);
            }
        }
        
        $this->generateMigrationClass($className, array(), $up, $down);
        
        return true;
    }

    /**
     * buildCreateForeignKey
     *
     * @param string $tableName 
     * @param string $definition 
     * @return void
     */
    public function buildCreateForeignKey($tableName, $definition)
    {
        return "\t\t\$this->createForeignKey('" . $tableName . "', " . $this->dataToPhpCode($definition) . ");";
    }

    /**
     * buildDropForeignKey
     *
     * @param string $tableName 
     * @param string $definition 
     * @return void
     */
    public function buildDropForeignKey($tableName, $definition)
    {
        return "\t\t\$this->dropForeignKey('" . $tableName . "', '" . $definition['name'] . "');\n";
    }

    /**
     * buildCreateTable
     *
     * @param string $tableData 
     * @return void
     */
    public function buildCreateTable($tableData)
    {
        $code  = "\t\t\$this->createTable('" . $tableData['tableName'] . "', ";
        
        $code .= $this->dataToPhpCode($tableData['columns']) . ", ";
        
        $code .= $this->dataToPhpCode(array('indexes' => $tableData['options']['indexes'], 'primary' => $tableData['options']['primary']));
        
        $code .= ");";
        
        return $code;
    }

    /**
     * buildDropTable
     *
     * @param string $tableData 
     * @return string
     */
    public function buildDropTable($tableData)
    {
        return "\t\t\$this->dropTable('" . $tableData['tableName'] . "');";
    }

    /**
     * dataToPhpCode
     *
     * @param string $data 
     * @return string
     */
    public function dataToPhpCode($data)
    {
        ob_start();
        var_export($data);
        $results = ob_get_contents();
        ob_end_clean();
        
        return $results;
    }

    /**
     * generateMigrationClass
     *
     * @return void
     */
    public function generateMigrationClass($className, $options = array(), $up = null, $down = null, $return = false)
    {
        if ($return || !$this->getMigrationsPath()) {
            return $this->buildMigrationClass($className, null, $options, $up, $down);
        } else {
            if ( ! $this->getMigrationsPath()) {
                throw new Doctrine_Migration_Exception('You must specify the path to your migrations.');
            }
            
            $migration = new Doctrine_Migration($this->getMigrationsPath());
            $next = (string) $migration->getNextVersion();
            
            $fileName = str_repeat('0', (3 - strlen($next))) . $next . '_' . Doctrine::tableize($className) . $this->suffix;
            
            $class = $this->buildMigrationClass($className, $fileName, $options, $up, $down);
            
            $path = $this->getMigrationsPath() . DIRECTORY_SEPARATOR . $fileName;
            
            file_put_contents($path, $class);
        }
    }

    /**
     * buildMigrationClass
     *
     * @return string
     */
    public function buildMigrationClass($className, $fileName = null, $options = array(), $up = null, $down = null)
    {
        $extends = isset($options['extends']) ? $options['extends']:'Doctrine_Migration';
        
        $content  = '<?php' . PHP_EOL;
        
        $content .= sprintf(self::$tpl, $className,
                                       $extends,
                                       $up,
                                       $down);
        
        
        return $content;
    }
}