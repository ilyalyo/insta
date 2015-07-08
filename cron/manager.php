<?php
connect();

$tasks = get_tasks();
if(count($tasks)>0)
    copy_and_run_tasks($tasks);

function copy_and_run_tasks($tasks_ids){

    $tasks_str = implode(',',$tasks_ids);

    $qr_result = mysql_query("
        INSERT INTO tasks(account_id,tags,status,type,count,createdAt,speed,parsingStatus,optionAddLike,optionCheckUserFromDB,optionFollowClosed,optionHasAvatar,optionLastActivity,optionStopPhrases,optionGeo,optionFollowersFrom,optionFollowersTo,optionFollowFrom,optionFollowTo)
        SELECT
        o.account_id,o.tags, 0,o.type,o.count,'" . date("Y-m-d H:i:s") . "',o.speed,o.parsingStatus,o.optionAddLike,o.optionCheckUserFromDB,o.optionFollowClosed,o.optionHasAvatar,o.optionLastActivity,o.optionStopPhrases,o.optionGeo,o.optionFollowersFrom,o.optionFollowersTo,o.optionFollowFrom,o.optionFollowTo
        FROM tasks o
        WHERE o.id IN ($tasks_str)
        ")
    or die(mysql_error());

    $first_id = mysql_insert_id();
    for($i = 0; $i < count($tasks_ids); $i++ )
        shell_exec("php /var/www/instastellar/src/TaskBundle/Command/follow.php '" . ($i + $first_id) . "' > /dev/null &");
}

function get_tasks(){
    $date = new DateTime('now');
    $m = $date->format('i');
    $rest_d = $date->format("Y-m-d H");

    $dtTo = new DateTime($rest_d .':' . floor($m / 15) * 15 . ':00' );
    $dtFrom = new DateTime($rest_d .':' . floor($m / 15) * 15 . ':00' );
    $dtFrom->sub(new DateInterval('PT15M'));

    $d1 = $dtFrom->format("Y-m-d H:i:s");
    $d2 = $dtTo->format("Y-m-d H:i:s");

    $qr_result = mysql_query("
        SELECT outerT.id
        FROM schedule_tasks st
        INNER JOIN tasks outerT
        ON outerT.id = st.task_id
        INNER JOIN accounts ac
                         ON ac.id = outerT.account_id
         INNER JOIN fos_user u
         ON u.id = ac.user
         WHERE
          NOT EXISTS
             (
             SELECT id
             FROM tasks innerT
             WHERE status in (0,2) AND innerT.id = outerT.id
             )
          AND
             u.validUntil > NOW()
          AND
             st.runAt > '$d1' AND st.runAt <= '$d2'")
    or die(mysql_error());

    $result = [];
    while ($row = mysql_fetch_array($qr_result))
        $result[]=$row['id'];
    return $result;
}


function connect(){
    $connection = mysql_connect('localhost', 'root', 'bycnfcntkkfh,fpf');
    if (!$connection){
        die("Database Connection Failed" . mysql_error());
    }
    $select_db = mysql_select_db('symfony');
    if (!$select_db){
        die("Database Selection Failed" . mysql_error());
    }
    mysql_query("SET NAMES 'utf8'");
    mysql_query("SET CHARACTER SET utf8 ");
}