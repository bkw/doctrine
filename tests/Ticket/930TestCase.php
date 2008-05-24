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
 * Doctrine_Ticket_930_TestCase
 *
 * @package     Doctrine
 * @author      David Stendardi <david.stendardi@adenclassifieds.com>
 * @category    Hydration
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_930_TestCase extends Doctrine_UnitTestCase {

  /**
   * prepareData
   */

    public function prepareData()
    {
	    $oPerson = new T930_Person;
	    $oPerson->name = 'David';
	    $oPerson->Country->code = 'fr';
	    $oPerson->Country->Translation['fr']->name = 'France';
	    $oPerson->Country->Translation['fr']->people = 'Francais';
	    $oPerson->JobPositions[0]->name = 'Webdeveloper';
	    $oPerson->JobPositions[0]->Category->code = '1234';
	    $oPerson->JobPositions[0]->Category->Translation['fr']->name = 'Développeur Web';
	    $oPerson->JobPositions[0]->Category->Translation['en']->name = 'Webdeveloper';

	    $oPerson->JobPositions[1]->name = 'Webmaster';
      $oPerson->JobPositions[1]->Category->code = '1234';
      $oPerson->JobPositions[1]->Category->Translation['fr']->name = 'Webmaster';
      $oPerson->JobPositions[1]->Category->Translation['en']->name = 'Webmaster';
      $oPerson->save();

      $oPerson = new T930_Person;
      $oPerson->name = 'Jonathan';
      $oPerson->Country->code = 'us';
      $oPerson->Country->Translation['fr']->name   = 'Etats Unis';
      $oPerson->Country->Translation['fr']->people = 'Américains';
      $oPerson->Country->Translation['en']->name   = 'United states';
      $oPerson->Country->Translation['fr']->people = 'Americans';

	    $oPerson->save();

    }

    /**
     * prepareTables
     */

    public function prepareTables()
    {
    	$this->tables = array();
    	$this->tables[] = 'T930_Person';
    	$this->tables[] = 'T930_Country';
    	$this->tables[] = 'T930_JobPosition';
    	$this->tables[] = 'T930_JobCategory';

    	parent :: prepareTables();
    }


    /**
     * Test the existence expected indexes
     */

    public function testTicket()
    {
        try {
        $q = new Doctrine_Query();
        $r = $q
          ->select('P.id, Ct.id, T1.name, T1.people, J.name, C.code, T2.name')
          ->from('T930_Person P')
          ->leftJoin('P.Country Ct')
          ->leftJoin('Ct.Translation T1 WITH T1.lang = ?', 'fr')
          ->leftJoin('P.JobPositions J')
          ->leftJoin('J.Category C')
          ->leftJoin('C.Translation T2 WITH T2.lang = ?', 'fr')
          //->where('P.name = ?', 'Jonathan')
          ->fetchArray();
        } catch (Exception $e) {
          $this->fail($e->getMessage());
        }

        $this->assertTrue(isset($r[0]['Country']['Translation']['fr']['name']));
    }
}

class T930_Person extends Doctrine_Record
{
  public function setTableDefinition()
  {
      $this->setTableName('T930_person');
      $this->hasColumn('country_id', 'integer');
      $this->hasColumn('name', 'string', 200);
  }

  public function setUp()
  {
    parent :: setUp();
    $this->hasOne('T930_Country as Country', array(
      'local' => 'country_id',
      'foreign' => 'id',
      'onDelete' => 'CASCADE'
      ));

    $this->hasMany('T930_JobPosition as JobPositions', array(
      'local' => 'id',
      'foreign' => 'person_id',
      'onDelete' => 'CASCADE'
      ));
  }
}

class T930_Country extends Doctrine_Record
{
  public function setTableDefinition()
  {
      $this->setTableName('T930_country');
      $this->hasColumn('name', 'string', 200);
      $this->hasColumn('people', 'string', 200);
      $this->hasColumn('code', 'string', 200);
  }

  public function setUp()
  {
    parent :: setUp();
    $this->hasMany('T930_Person as Persons', array(
      'local' => 'id',
      'foreign' => 'country_id',
      'onDelete' => 'CASCADE'
      ));

    $this->actAs('I18n', array('fields' => array('name', 'people')));
  }
}



class T930_JobPosition extends Doctrine_Record
{
  public function setTableDefinition()
  {
      $this->setTableName('T930_address');
      $this->hasColumn('name', 'string', 200);
      $this->hasColumn('person_id', 'integer');
      $this->hasColumn('job_category_id', 'integer');
  }

  public function setUp()
  {
    parent :: setUp();
    $this->hasOne('T930_Person as Person', array(
      'local' => 'person_id',
      'foreign' => 'id',
      'onDelete' => 'CASCADE'
      ));

    $this->hasOne('T930_JobCategory as Category', array(
      'local' => 'job_category_id',
      'foreign' => 'id',
      'onDelete' => 'CASCADE'
      ));
  }
}

class T930_JobCategory extends Doctrine_Record
{
  public function setTableDefinition()
  {
      $this->setTableName('job_category');
      $this->hasColumn('code', 'integer', 4);
      $this->hasColumn('name', 'string', 200);
  }

  public function setUp()
  {
    parent :: setUp();
    $this->hasMany('T930_JobPosition as Positions', array(
      'local' => 'id',
      'foreign' => 'job_category_id',
      'onDelete' => 'CASCADE'
      ));

    $this->actAs('I18n', array('fields' => array('name')));
  }
}
