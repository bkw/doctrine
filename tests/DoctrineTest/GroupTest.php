<?php
class GroupTest extends UnitTestCase
{
    protected $_testCases = array();
    protected $_name;
    protected $_title;

    public function __construct($title, $name)
    {
        $this->_title = $title;
        $this->_name =  $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function addTestCase(UnitTestCase $testCase)
    {
        if ($testCase instanceOf GroupTest) {
            $this->_testCases = array_merge($this->_testCases, $testCase->getTestCases());
         } else {
            $this->_testCases[get_class($testCase)] = $testCase;
         }
    }

    public function shouldBeRun($testCase, $filter)
    {
        if ( ! is_array($filter)) {
            return true;
        }
        foreach($filter as $subFilter) {
            $name = strtolower(get_class($testCase));
            $pos = strpos($name, strtolower($subFilter));
            //it can be 0 so we have to use === to see if false
            if ($pos === false) {
                return false;
            }
        }
        return true;
    }
    public function run(DoctrineTest_Reporter $reporter = null, $filter = null)
    {
        set_time_limit(900);

        $reporter->paintHeader($this->_title);
        foreach ($this->_testCases as $k => $testCase) {
            $this->_messages = array();
            if ( ! $this->shouldBeRun($testCase, $filter)) {
                continue;
            }
            try {
                $testCase->run();
            } catch (Exception $e) {
                $this->_failed += 1;
                $this->_messages[] = 'Unexpected ' . get_class($e) . ' thrown in [' . get_class($testCase) . '] with message [' . $e->getMessage() . '] in ' . $e->getFile() . ' on line ' . $e->getLine() . "\n\nTrace\n-------------\n\n" . $e->getTraceAsString();
            }

            $failed = $testCase->getFailCount() ? true:false;
            $this->_passed += $testCase->getPassCount();
            $this->_failed += $testCase->getFailCount();
            $this->_messages = array_merge($this->_messages, $testCase->getMessages());

            $this->_testCases[$k] = null;
            $formatter = new Doctrine_Cli_AnsiColorFormatter();

            $max = 80;
            $class = get_class($testCase);
            $strRepeatLength = $max - strlen($class);
            
            echo $class.str_repeat('.', $strRepeatLength).$formatter->format($failed ? 'failed':'passed', $failed ? 'ERROR':'INFO')."\n";
            if (! empty($this->_messages)) {
                echo "\n";
                echo "\n";
                foreach ($this->_messages as $message) {
                    echo $formatter->format($message, 'ERROR') . "\n\n";
                }
                echo "\n";
            }
        }
        $reporter->setTestCase($this);
        
        $this->cachePassesAndFails();

        $reporter->paintFooter();

        return $this->_failed ? false : true;
    }


    public function getTestCaseCount()
    {
        return count($this->_testCases);
    }

    public function getTestCases()
    {
        return $this->_testCases;
    }
}