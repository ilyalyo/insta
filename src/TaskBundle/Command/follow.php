<?php
function follow_by_username(){
    global $inst;
    global $task;
    var_dump('start searching');
    $users = $inst->get_followers($task['tags'], $task['count'] );
    $errors = 0;
    foreach ($users as $user)
    {
        var_dump($user);
        $result = $inst->follow($user['user_id']);
        if(isset($result) && $result->meta->code == 200){
            $errors = 0;
            $inst->add_row($user['username']);
        }
        else
            $errors++;

        sleep(sleepTime($task['speed']));

        if ($inst->get_task_status() == 3 || $errors > 5){
            $inst->set_task_status(3);
            break;
        }
    }
    if ($inst->get_task_status() == 2)
        $inst->set_task_status(1);
}

function unfollowing(){
    global $inst;
    global $task;
    var_dump('start unfollowing');
    $users = $inst->get_followers_revers($task['count'] );
    $errors = 0;
    foreach ($users as $user)
    {
        var_dump($user);
        $result = $inst->follow($user['user_id']);
        if(isset($result) && $result->meta->code == 200){
            $errors = 0;
            $inst->add_row($user['username']);
        }
        else
            $errors++;

        sleep(sleepTime($task['speed']));

        if ($inst->get_task_status() == 3 || $errors > 5){
            $inst->set_task_status(3);
            break;
        }
    }
    if ($inst->get_task_status() == 2)
        $inst->set_task_status(1);
}


include_once("instagram.php");

$TASK_ID = $_SERVER['argv'][1];

$inst = new Instagram($TASK_ID);
$task = $inst->get_task();

try{
//0 - following by username
//10 - following by tags
//1 - liking by username
//11 - liking by tags
//3 - un following

    $inst->set_task_status(2);
    switch ($task['type']) {
        case 0:
            follow_by_username();
            break;
        case 10:
            follow_by_tags();
            break;
        case 1:
            liking_by_username();
            break;
        case 11:
            liking_by_tags();
            break;
        case 3:
            unfollowing();
            break;
    };



    if ($task['byUsername']==1)
    {

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

}
catch (Exception $e){

    $m=substr($e->getMessage(),0,200);
    $task=$_SERVER['argv'][1];

    mysql_query("INSERT INTO errors (task_id,message) VALUES ($task,'$m')")
    or die(mysql_error());
    $qr_result = mysql_query("UPDATE tasks SET status=4 WHERE id=$task")
    or die(mysql_error());
}


function follow_by_tags(){

}
function liking_by_username(){}
function liking_by_tags(){}

function sleepTime($interval_id){
    switch ($interval_id) {
        case 0:
            return rand(20, 30);
        case 1:
            return rand(30, 45);
        default:
            return rand(60, 90);
    }
}

