<?php
include_once("InstFollow.php");

$TASK_ID = $_SERVER['argv'][1];

$inst = new InstFollow($TASK_ID);

$task = $inst->get_task($TASK_ID);

$token = $task["token"];

try{

if ($task['byUsername']==1)
{
    $users = $inst->getUserFollowers($task);
    foreach ($users as $user)
    {
        $inst->follow($task, $user);

        sleep(rand(30, 50));

        if ($inst->is_stopped($TASK_ID))
            break;
    }
}
else
for ($i = 0; $i <= $task['count'] - 1; $i++) {

    $tags = explode('#', $task['tags']);

    $rand_key = array_rand($tags);

    $tag = $tags[$rand_key];

    $media = $inst->getUsernameAndIdsbyTag($tag, $token);

    if ($task['type'] == 0)
        $inst->follow($task, $media);
    else
        $inst->sendLike($task, $media);

    sleep(rand(30, 50));

    if ($inst->is_stopped($TASK_ID))
        break;
}
if (!$inst->is_stopped($TASK_ID))
    $inst->done_task($TASK_ID);
}
catch (Exception $e){

    $m=substr($e->getMessage(),0,200);
    $task=$_SERVER['argv'][1];

    mysql_query("INSERT INTO errors (task_id,message) VALUES ($task,'$m')")
         or die(mysql_error());
    $qr_result = mysql_query("UPDATE tasks SET status=4 WHERE id=$task")
         or die(mysql_error());
}

