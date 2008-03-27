<?php
/**
$str = "
The following examples should give a hint of how DQL is converted into SQL.
The classes used in here are the same as in chapter 14.1 (Users and groups are both entities etc).

DQL QUERY: FROM Email WHERE Email.address LIKE '%@example%'

SQL QUERY: SELECT email.id AS Email__id FROM email WHERE (email.address LIKE '%@example%')

DQL QUERY: FROM User(id) WHERE User.Phonenumber.phonenumber LIKE '%123%'

SQL QUERY: SELECT entity.id AS entity__id FROM entity LEFT JOIN phonenumber ON entity.id = phonenumber.entity_id WHERE phonenumber.phonenumber LIKE '%123%' AND (entity.type = 0)

DQL QUERY: FROM Forum_Board(id).Threads(id).Entries(id)

SQL QUERY: SELECT forum_board.id AS forum_board__id, forum_thread.id AS forum_thread__id, forum_entry.id AS forum_entry__id FROM forum_board LEFT JOIN forum_thread ON forum_board.id = forum_thread.board_id LEFT JOIN forum_entry ON forum_thread.id = forum_entry.thread_id

DQL QUERY: FROM User(id) WHERE User.Group.name = 'Action Actors'

SQL QUERY: SELECT entity.id AS entity__id FROM entity LEFT JOIN groupuser ON entity.id = groupuser.user_id LEFT JOIN entity AS entity2 ON entity2.id = groupuser.group_id WHERE entity2.name = 'Action Actors' AND (entity.type = 0 AND (entity2.type = 1 OR entity2.type IS NULL))

DQL QUERY: FROM User(id) WHERE User.Group.Phonenumber.phonenumber LIKE '123 123'

SQL QUERY: SELECT entity.id AS entity__id FROM entity LEFT JOIN groupuser ON entity.id = groupuser.user_id LEFT JOIN entity AS entity2 ON entity2.id = groupuser.group_id LEFT JOIN phonenumber ON entity2.id = phonenumber.entity_id WHERE phonenumber.phonenumber LIKE '123 123' AND (entity.type = 0 AND (entity2.type = 1 OR entity2.type IS NULL))
";

function renderQueries($str) {
$e = explode("\n",$str);
$color = "367FAC";

foreach($e as $line) {
    if(strpos($line, "SQL") !== false)
        $color = "A50A3D";
    elseif(strpos($line, "DQL") !== false)
        $color = "367FAC";

    $l = str_replace("SELECT","
<font color='$color'>**SELECT**</font>",$line);
    $l = str_replace("FROM","
<font color='$color'>**FROM**</font>",$l);
    $l = str_replace("LEFT JOIN","
<font color='$color'>**LEFT JOIN**</font>",$l);
    $l = str_replace("INNER JOIN","
<font color='$color'>**INNER JOIN**</font>",$l);
    $l = str_replace("WHERE","
<font color='$color'>**WHERE**</font>",$l);
    $l = str_replace("AS","<font color='$color'>**AS**</font>",$l);
    $l = str_replace("ON","<font color='$color'>**ON**</font>",$l);
    $l = str_replace("ORDER BY","<font color='$color'>**ORDER BY**</font>",$l);
    $l = str_replace("LIMIT","<font color='$color'>**LIMIT**</font>",$l);
    $l = str_replace("OFFSET","<font color='$color'>**OFFSET**</font>",$l);
    $l = str_replace("DISTINCT","<font color='$color'>**DISTINCT**</font>",$l);
    $l = str_replace("  ","<dd>",$l);

    print $l."<br>";
        if(substr($l,0,3) == "SQL") print "<hr valign='left' class='small'>";
}
}
renderQueries($str);
*/
?>

