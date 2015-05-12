<?php
include_once("InstFollow.php");

$TASK_ID = $_SERVER['argv'][1];

$inst = new InstFollow($TASK_ID);

$task = $inst->get_task($TASK_ID);

$token = $task["token"];

$usernames='';

if ($task['byUsername']==1)
{
    $arr = $inst->getUserFollowers($task);
    $usernames= implode(",",$arr) ;
}
else
for ($i = 0; $i <= $task['count'] - 1; $i++) {

    $tags = explode('#', $task['tags']);

    $rand_key = array_rand($tags);

    $tag = $tags[$rand_key];

    $usernames .= $inst->getUsernameByTag($tag, $token);
}

var_dump($usernames);
$file = fopen('/var/www/instastellar/tasks/' . $TASK_ID, "w") or die("Unable to open file!");
fwrite($file, $usernames);
fclose($file);
$wait=10000;
$data=$inst->get_login_pass($task['account_id']);
shell_exec("casperjs $file '" . $data['login'] . "' '" . $data['pass'] ."' '" . $TASK_ID . "' '" . $wait . "' > /dev/null &");