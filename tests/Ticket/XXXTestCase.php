<?php


class Doctrine_Ticket_XXX_TestCase extends Doctrine_UnitTestCase
{

    public function prepareData() 
    { }

    public function prepareTables()
    {
        $this->tables = array('Moo', 'Cow');
        parent::prepareTables();
    }
  public function testTicket()
  {
    $moo = new Moo();
    $moo->amount = 1000;
    $cow = new Cow();

    $moo->Cows[] = $cow;
    $moo->save();
    $this->assertEqual($moo->amount, 0);
  }

}



class Moo extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->hasColumn('id', 'integer', 4, array (
      'primary' => true,
      'autoincrement' => true,
      'notnull' => true,
    ));

    $this->hasColumn('amount', 'integer');
  }

  public function setUp()
  {
    $this->hasMany('Cow as Cows', array('local' => 'id', 'foreign' => 'moo_id'));
  }
}

class Cow extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->hasColumn('id', 'integer', 4, array (
      'primary' => true,
      'autoincrement' => true,
      'notnull' => true,
    ));

    $this->hasColumn('moo_id', 'integer');
  }

  public function setUp()
  {
    $this->hasOne('Moo', array('local' => 'moo_id', 'foreign' => 'id'));
  }

  public function preInsert($e)
  {
    
    $this->Moo->amount = 0;
    echo $this->Moo->amount. "\n";
    $this->Moo->save();
    $this->Moo->refresh();
    echo $this->Moo->amount. "\n";
  }
}


