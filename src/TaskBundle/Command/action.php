<?php
include_once("InstFollow.php");

$TASK_ID = $_SERVER['argv'][1];

$inst = new InstFollow($TASK_ID);

$task = $inst->get_task($TASK_ID);

$token = $task["token"];

if ($task['byUsername']==1)
{
    $users = $inst->getUserFollowers($task);
    foreach ($users as $user)
    {
        $inst->follow($task, $user);

        sleep(rand(20, 30));

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

