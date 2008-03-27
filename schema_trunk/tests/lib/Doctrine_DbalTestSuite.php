<?php 
/**
 * The outermost test suite for all dbal related testcases & suites.
 * 
 * Currently the dbal suite uses a normal connection object, too, just like the orm suite.
 * Upon separation of the DBAL and ORM package this suite should just use a DBAL
 * connection in the shared fixture.
 */
class Doctrine_DbalTestSuite extends Doctrine_TestSuite
{
    
    protected function setUp()
    {
        $this->sharedFixture['connection'] = Doctrine_TestUtil::getConnection();
    }
    
    protected function tearDown()
    {}    
}