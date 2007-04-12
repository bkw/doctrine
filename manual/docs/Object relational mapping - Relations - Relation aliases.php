Doctrine supports relation aliases through 'as' keyword.

<code type="php">
class Forum_Board extends Doctrine_Record { 
    public function setTableDefinition() {
        $this->hasColumn('name', 'string', 100);
        $this->hasColumn('description', 'string', 5000);
    }
    public function setUp() {
        // notice the 'as' keyword here
        $this->ownsMany('Forum_Thread as Threads',  'Forum_Thread.board_id');
    }
}

class Forum_Thread extends Doctrine_Record {
    public function setTableDefinition() {
        $this->hasColumn('board_id', 'integer', 10);
        $this->hasColumn('updated', 'integer', 10);
        $this->hasColumn('closed', 'integer', 1);
    }
    public function setUp() {
        // notice the 'as' keyword here
        $this->hasOne('Forum_Board as Board', 'Forum_Thread.board_id');
    }
}
$board = new Board();
$board->Threads[0]->updated = time();
</code>
