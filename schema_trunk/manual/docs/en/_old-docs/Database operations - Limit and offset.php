
<code type="php">
$sess = Doctrine_Manager::getInstance()->openConnection(new PDO("dsn","username","password"));

// select first ten rows starting from the row 20

$sess->select("select * from user",10,20);
</code>
