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
 * Doctrine_Ticket_1868_TestCase
 *
 * This test case reproduces a problem with the way Doctrine implements
 * offset and limit in mysql databases.  The test will always pass in
 * normal testing; to make it fail you need to uncomment the lines which
 * connect it to a real mysql database, filling in your own database
 * information.
 *
 * The summary of the problem: if you define a limit and use an offset which
 * is past the end of the result set, you get back ALL results instead of NO
 * results.
 *
 * @package     Doctrine
 * @author      David Brewer
 */
class Doctrine_Ticket_1868_TestCase extends Doctrine_UnitTestCase
{

    /**
     * Uncomment this method and fill in your own database information to
     * test against mysql.
     */
    public function useMysql()
    {
        //$dsn = 'mysql:dbname=test;host=127.0.0.1';
        //$user = 'test';
        //$password = 'test';
        //
        //$this->dbh = new PDO($dsn, $user, $password);
        //$this->conn = Doctrine_Manager::connection($this->dbh);
    }

    public function prepareTables() {
        $this->useMysql();
        $this->tables = array('stuff', 'substuff');
        parent::prepareTables();
    }

    public function prepareData()
    {
      // we're going to make twenty items.  The content doesn't matter.
      for ($i = 0; $i < 20; $i++) {
        $s = new Stuff();
        $s->save();

        $s['SubStuff'][0]['content'] = 'test 1';
        $s['SubStuff'][1]['content'] = 'test 2';
        $s['SubStuff'][1]['content'] = 'test 3';
        $s['SubStuff']->save();
      }
    }

    /**
     * If we retrieve all results from the table, we should get the same count,
     * no matter how we count it.
     */
    public function testRetrieveAll()
    {
      $results = Doctrine_Query::create()
          ->from('Stuff s, s.SubStuff sub')->execute();
      $this->verifyResultCount($results, 20);
    }

    /**
     * If we use limit and offset where the offset falls within the
     * total result count, we should get a count equal to the limit.
     */
    public function testOffsetWithinResultSet()
    {
      $limit = 10;
      $offset = 10;

      $results = Doctrine_Query::create()
          ->from('Stuff s, s.SubStuff sub')
          ->limit($limit)
          ->offset($offset)->execute();
      $this->verifyResultCount($results, $limit);
    }

    /**
     * If we have twenty records, and we offset by 15, we should get back
     * 5 results.
     */
    public function testOffsetNearResultSetEnd()
    {
      $limit = 10;
      $offset = 15;

      $results = Doctrine_Query::create()
          ->from('Stuff s, s.SubStuff sub')
          ->limit($limit)
          ->offset($offset)->execute();
      $this->verifyResultCount($results, 5);
    }

    /**
     * If we have twenty records, and we offset by 20, we should get back
     * 0 results
     */
    public function testOffsetAtResultSetEnd()
    {
      $limit = 10;
      $offset = 20;

      $results = Doctrine_Query::create()
          ->from('Stuff s, s.SubStuff sub')
          ->limit($limit)
          ->offset($offset)->execute();
      $this->verifyResultCount($results, 0);
    }

    /**
     * If we have twenty records, and we offset by 25, we should get back
     * 0 results
     */
    public function testOffsetPastResultSetEnd()
    {
      $limit = 10;
      $offset = 25;

      $results = Doctrine_Query::create()
          ->from('Stuff s, s.SubStuff sub')
          ->limit($limit)
          ->offset($offset)->execute();
      $this->verifyResultCount($results, 0);
    }

    /**
     * Helper method used by all tests: given a result set and an expected
     * count, verify the count three different ways.
     *
     * @param Doctrine_Collection $results
     * @param integer $expected_count
     */
    public function verifyResultCount($results, $expected_count) {
      $this->assertEqual($expected_count, $results->count());
      $this->assertEqual($expected_count, count($results));

      // do a manual count
      $count = 0;
      foreach ($results as $r) {
        $count++;
      }

      $this->assertEqual($expected_count, $count);
    }
}

class Stuff extends Doctrine_Record
{
    public function setTableDefinition()
    {
      $this->hasColumn('data', 'string', 50);
    }

  public function setUp()
  {
    $this->hasMany('SubStuff', array('local' => 'id',
                                        'foreign' => 'stuff_id'));
  }
}

class SubStuff extends Doctrine_Record
{
    public function setTableDefinition()
    {
      $this->hasColumn('stuff_id', 'integer', null, array('type' => 'integer'));
      $this->hasColumn('content', 'string', 10);
    }
}
