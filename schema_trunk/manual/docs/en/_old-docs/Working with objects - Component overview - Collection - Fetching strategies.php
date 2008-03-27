Whenever you fetch records with eg. Doctrine_Table::findAll or Doctrine_Connection::query methods an instance of
Doctrine_Collection is returned. There are many types of collections in Doctrine and it is crucial to understand
the differences of these collections. Remember choosing the right fetching strategy (collection type) is one of the most 
influental things when it comes to boosting application performance.



* Immediate Collection
Fetches all records and all record data immediately into collection memory. Use this collection only if you really need to show all that data
in web page.



Example query:

SELECT id, name, type, created FROM user



* Batch Collection
Fetches all record primary keys into colletion memory. When individual collection elements are accessed this collection initializes proxy objects.
When the non-primary-key-property of a proxy object is accessed that object sends request to Batch collection which loads the data
for that specific proxy object as well as other objects close to that proxy object.



Example queries:

SELECT id FROM user

SELECT id, name, type, created FROM user WHERE id IN (1,2,3,4,5)

SELECT id, name, type, created FROM user WHERE id IN (6,7,8,9,10)

[ ... ]


* Lazy Collection
Lazy collection is exactly same as Batch collection with batch size preset to one.



Example queries:

SELECT id FROM user

SELECT id, name, type, created FROM user WHERE id = 1

SELECT id, name, type, created FROM user WHERE id = 2

SELECT id, name, type, created FROM user WHERE id = 3

[ ... ]


* Offset Collection
Offset collection is the same as immediate collection with the difference that it uses database provided limiting of queries.



Example queries:

SELECT id, name, type, created FROM user LIMIT 5

SELECT id, name, type, created FROM user LIMIT 5 OFFSET 5

SELECT id, name, type, created FROM user LIMIT 5 OFFSET 10

[ ... ]



<code type="php">
$table = $conn->getTable("User");

$table->setAttribute(Doctrine::ATTR_FETCHMODE, Doctrine::FETCH_IMMEDIATE);

$users = $table->findAll();

// or

$users = $conn->query("FROM User-I"); // immediate collection

foreach($users as $user) {
    print $user->name;
}


$table->setAttribute(Doctrine::ATTR_FETCHMODE, Doctrine::FETCH_LAZY);

$users = $table->findAll();

// or

$users = $conn->query("FROM User-L"); // lazy collection

foreach($users as $user) {
    print $user->name;
}

$table->setAttribute(Doctrine::ATTR_FETCHMODE, Doctrine::FETCH_BATCH);

$users = $table->findAll();

// or

$users = $conn->query("FROM User-B"); // batch collection

foreach($users as $user) {
    print $user->name;
}

$table->setAttribute(Doctrine::ATTR_FETCHMODE, Doctrine::FETCH_OFFSET);

$users = $table->findAll();

// or

$users = $conn->query("FROM User-O"); // offset collection

foreach($users as $user) {
    print $user->name;
}
</code>
