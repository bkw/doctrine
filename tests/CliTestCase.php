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
 * Doctrine_Cli_TestCase
 *
 * @package     Doctrine
 * @author      Dan Bettles <danbettles@yahoo.co.uk>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Cli_TestCase extends Doctrine_UnitTestCase 
{
    public function setUp()
    {
        
    }

    public function tearDown()
    {
    }

    public function testTheNameOfTheTaskBaseClassNameIsStoredInAClassConstant()
    {
        $this->assertFalse(is_null(constant('Doctrine_Cli::TASK_BASE_CLASS')));
    }

    public function testClassistaskReturnsTrueIfTheSpecifiedClassIsATask()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();
        $this->assertTrue($cli->classIsTask('Doctrine_Cli_TestCase_EmptyTask'));
        $this->assertFalse($cli->classIsTask('Doctrine_Cli_TestCase_TestTask003'));
    }

    public function testLoadandregistertaskLoadsAndRegistersTheTaskClassInTheSpecifiedFile()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();

        /*
         * Load any task-class from a file.  Neither the file nor the class follow Doctrine naming conventions because
         * the method mustn't care about that.
         */
        $cli->loadAndRegisterTask(
            dirname(__FILE__) . '/CliTestCase/foo.php',
            'Doctrine_Cli_TestCase_TestTask004',
            'TestTask004'
        );

        $aLoadedClassTask = $cli->getRegisteredTasks();
        $this->assertTrue(isset($aLoadedClassTask['Doctrine_Cli_TestCase_TestTask004']));
        $this->assertEqual('TestTask004', $aLoadedClassTask['Doctrine_Cli_TestCase_TestTask004']);
    }

    public function testLoadandregistertaskThrowsAnExceptionIfTheSpecifiedFileDoesNotExist()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();

        try {
            $filename = dirname(__FILE__) . '/CliTestCase/bar.php';
            $cli->loadAndRegisterTask($filename, 'anything', 'anything');
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() == "The task file \"{$filename}\" does not exist") {
                $this->pass();
                return;
            }
        }

        $this->fail();
    }

    public function testLoadandregistertaskThrowsAnExceptionIfTheSpecifiedClassDoesNotExist()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();

        try {
            $className = 'MissingClass';
            $cli->loadAndRegisterTask(dirname(__FILE__) . '/CliTestCase/baz.php', $className, 'anything');
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() == "The task class \"{$className}\" does not exist") {
                $this->pass();
                return;
            }
        }

        $this->fail();
    }

    public function testLoadandregistertaskThrowsAnExceptionIfTheSpecifiedClassIsNotATask()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();

        try
        {
            $className = 'Doctrine_Cli_TestCase_NotATask';
            $cli->loadAndRegisterTask(dirname(__FILE__) . '/CliTestCase/bip.php', $className, 'anything');
        } catch (DomainException $e) {
            if ($e->getMessage() == "The class \"{$className}\" is not a Doctrine Task") {
                $this->pass();
                return;
            }
        }

        $this->fail();
    }

    public function testLoadandregistertaskThrowsAnExceptionIfTheSpecifiedTaskNameIsBlank()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();

        try {
            $cli->loadAndRegisterTask(dirname(__FILE__) . '/CliTestCase/foo.php', 'Doctrine_Cli_TestCase_TestTask004', '');
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() == "The task-name is blank") {
                $this->pass();
                return;
            }
        }

        $this->fail();
    }

    public function testRegistertaskRegistersTheSpecifiedTaskClass()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();
        $cli->setRegisteredTasks(array());
        $cli->registerTask('Doctrine_Cli_TestCase_EmptyTask', 'anything');
        $this->assertEqual(array('Doctrine_Cli_TestCase_EmptyTask' => 'anything'), $cli->getRegisteredTasks());
    }

    public function testRegistertaskThrowsAnExceptionIfTheSpecifiedClassDoesNotExist()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();

        try {
            $className = 'MissingClass';
            $cli->registerTask($className, 'anything');
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() == "The task class \"{$className}\" does not exist") {
                $this->pass();
                return;
            }
        }

        $this->fail();
    }

    public function testRegistertaskThrowsAnExceptionIfTheSpecifiedClassIsNotATask()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();

        try {
            $className = 'Doctrine_Cli_TestCase_TestTask003';
            $cli->registerTask($className, 'anything');
        } catch (DomainException $e) {
            if ($e->getMessage() == "The class \"{$className}\" is not a Doctrine Task") {
                $this->pass();
                return;
            }
        }

        $this->fail();
    }

    public function testRegistertaskThrowsAnExceptionIfTheSpecifiedTaskNameIsBlank()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();

        try {
            $cli->registerTask('Doctrine_Cli_TestCase_EmptyTask', '');
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() == "The task-name is blank") {
                $this->pass();
                return;
            }
        }

        $this->fail();
    }

    public function testGetregisteredtasksReturnsTheArrayOfTasksSetWithSetregisteredtasks()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();
        $task = array('Doctrine_Task_DoSomething' => 'do-something');
        $cli->setRegisteredTasks($task);
        $this->assertEqual($task, $cli->getRegisteredTasks());
    }

    public function testListloadedtaskclassesReturnsAnArrayContainingTheNamesOfAllLoadedTaskClasses()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();
        $loadedTaskClassName = $cli->getLoadedTaskClasses();

        $this->assertTrue(is_array($loadedTaskClassName));

        $expectedTaskClassName = array('Doctrine_Cli_TestCase_EmptyTask', 'Doctrine_Task_TestTask004');

        /*
         * We can't be exactly sure of what's _already_ loaded, so all we can do is make sure that the classes defined
         * at the bottom of this file have been loaded - or not, as the case may be
         */
        $this->assertEqual($expectedTaskClassName, array_intersect($expectedTaskClassName, $loadedTaskClassName));

        //Make sure the list doesn't contain anything _un_expected
        $this->assertFalse(in_array('Doctrine_Cli_TestCase_TestTask003', $loadedTaskClassName));
    }

    public function testDerivedoctrinetasknameReturnsTheNameOfADoctrineStyleTaskFromItsClassName()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();
        $this->assertEqual('migrate', $cli->deriveDoctrineTaskName('Doctrine_Task_Migrate'));
        $this->assertEqual('create-db', $cli->deriveDoctrineTaskName('Doctrine_Task_CreateDb'));
        $this->assertEqual('generate-models-db', $cli->deriveDoctrineTaskName('Doctrine_Task_GenerateModelsDb'));
    }

    public function testListloadeddoctrinetasksReturnsAnArrayContainingTheNamesOfLoadedDoctrineStyleTasks()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();
        $loadedTaskName = $cli->getLoadedDoctrineTasks();
    
        $this->assertTrue(is_array($loadedTaskName));
    
        $expectedTaskName = array('Doctrine_Task_TestTask004' => 'test-task004');

        $this->assertEqual($expectedTaskName, array_intersect_assoc($expectedTaskName, $loadedTaskName));
    
        $this->assertFalse(isset($loadedTaskName['Doctrine_Cli_TestCase_EmptyTask']));
    }

    public function testTaskisregisteredReturnsTrueIfTheSpecifiedTaskIsRegistered()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();

        $cli->setRegisteredTasks(array());
        $found = $cli->taskIsRegistered('do-something', $className);
        $this->assertFalse($found);
        $this->assertIdentical(null, $className);

        $cli->setRegisteredTasks(array('DoSomething' => 'do-something'));
        $found = $cli->taskIsRegistered('do-something', $className);
        $this->assertTrue($found);
        $this->assertEqual('DoSomething', $className);
    }

    public function testLoadsDoctrineStyleTasksOnConstruction()
    {
        $cli = new Doctrine_Cli();
        $registeredTask = $cli->getRegisteredTasks();
        $expectedTaskName = array('Doctrine_Task_TestTask004' => 'test-task004');
        $this->assertEqual($expectedTaskName, array_intersect_assoc($expectedTaskName, $registeredTask));
        $this->assertFalse(isset($registeredTask['Doctrine_Cli_TestCase_EmptyTask']));
    }

    public function testLoadtasksLoadsCustomDoctrineStyleTasksFromTheSpecifiedDirectory () {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();

        $this->assertEqual(array(), $cli->getRegisteredTasks());

        $loadedTaskName = $cli->loadTasks(dirname(__FILE__) . '/CliTestCase');
        
        $expectedTaskName = array('custom-doctrine-style-task' => 'custom-doctrine-style-task');
        $this->assertEqual($expectedTaskName, array_intersect_assoc($expectedTaskName, $loadedTaskName));
        
        $registeredTask = $cli->getRegisteredTasks();
        $expectedTaskName = array('Doctrine_Task_CustomDoctrineStyleTask' => 'custom-doctrine-style-task');
        $this->assertEqual($expectedTaskName, array_intersect_assoc($expectedTaskName, $registeredTask));
        //Invalid name:
        $this->assertFalse(isset($registeredTask['Doctrine_Cli_TestCase_TestTask004']));
        //Doctrine_Cli::loadTasks() must ignore .inc files:
        $this->assertFalse(isset($registeredTask['Doctrine_Task_TaskDeclaredInAnIncFile']));
    }

    /*
     * Exists only to ensure the structure of the array returned by Doctrine_Cli::loadTasks() is the same as it was
     * before refactoring
     */
    public function testLoadtasksReturnsAnArrayOfTaskNames()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();
        $loadedTaskName = $cli->loadTasks();
        $expectedTaskName = array('test-task004' => 'test-task004');
        $this->assertEqual($expectedTaskName, array_intersect_assoc($expectedTaskName, $loadedTaskName));
    }

    /*
     * Exists only to ensure the structure of the array returned by Doctrine_Cli::getLoadedTasks() is the same as it was
     * before refactoring
     */
    public function testGetloadedtasksReturnsAnArrayOfTaskNames()
    {
        $cli = new Doctrine_Cli_TestCase_MinimalCli();
        $loadedTaskName = $cli->getLoadedTasks();
        $expectedTaskName = array('test-task004' => 'test-task004');
        $this->assertEqual($expectedTaskName, array_intersect_assoc($expectedTaskName, $loadedTaskName));
    }

    /*
     * Exists only to ensure the method behaves the same as it did before refactoring
     */
    public function test_gettaskclassfromargsReturnsTheNameOfTheClassAssociatedWithTheSpecifiedTask()
    {
        $cli = new Doctrine_Cli_TestCase_TestCli002();
        $this->assertEqual('Doctrine_Task_TaskName', $cli->_getTaskClassFromArgs(array('scriptName', 'task-name')));
    }
}

class Doctrine_Cli_TestCase_EmptyTask extends Doctrine_Task
{
    public function execute()
    {
    }
}

//_Not_ a task on purpose
class Doctrine_Cli_TestCase_TestTask003
{
}

//This _must_ follow the normal Doctrine Task naming convention
class Doctrine_Task_TestTask004 extends Doctrine_Task
{
    public function execute()
    {
    }
}

class Doctrine_Cli_TestCase_MinimalCli extends Doctrine_Cli
{
    public function __construct()
    {
    }
}

class Doctrine_Cli_TestCase_TestCli002 extends Doctrine_Cli
{
    public function _getTaskClassFromArgs(array $args)
    {
        return parent::_getTaskClassFromArgs($args);
    }
}