<?php
function follow_by_username(){
    global $inst;
    global $task;
    var_dump('start searching');
    $users = $inst->get_followers($task['tags'], $task['count'] );
    if ($inst->get_task_status() == 3){
        exit;
    }
    $inst->set_task_status(2);
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
/*
        if($task['optionAddLike'] == 1) {
            $media = $inst->get_last_user_media($user['user_id']);
            if (isset($media)) {
                $result = $inst->like($media->data[0]->id);
                if (isset($result) && $result->meta->code == 200)
                    $inst->add_row($media->data[0]->link);
            }
        }*/

        sleep(sleepTime($task['speed']));

        if ($inst->get_task_status() == 3){
            break;
        }
        if($errors > 5){
            $inst->set_task_status(4);
            break;
        }
    }
    if ($inst->get_task_status() == 2)
        $inst->set_task_status(1);
}

function follow_by_tags(){

    global $inst;
    global $task;

    $users = $inst->get_followers_by_tags($task['tags'],$task['count']);
    if ($inst->get_task_status() == 3)
        exit;
    $inst->set_task_status(2);

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

        if ($inst->get_task_status() == 3){
            break;
        }
        if($errors > 5){
            $inst->set_task_status(4);
            break;
        }
    }
    if ($inst->get_task_status() == 2)
        $inst->set_task_status(1);
}


function follow_by_list(){

    global $inst;
    global $task;

    $users = $inst->get_followers_by_list();
    if ($inst->get_task_status() == 3)
        exit;
    $inst->set_task_status(2);

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

        if ($inst->get_task_status() == 3){
            break;
        }
        if($errors > 5){
            $inst->set_task_status(4);
            break;
        }
    }
    if ($inst->get_task_status() == 2)
        $inst->set_task_status(1);
}

function liking_by_tags(){

    global $inst;
    global $task;

    $users = $inst->get_followers_by_tags($task['tags'],$task['count']);
    if ($inst->get_task_status() == 3)
        exit;
    $inst->set_task_status(2);

    $errors = 0;
    foreach ($users as $user)
    {
        var_dump($user);
        $result = $inst->like($user['resource_id']);
        if(isset($result) && $result->meta->code == 200){
            $errors = 0;
            $inst->add_row($user['link']);
        }
        else
            $errors++;

        sleep(sleepTime($task['speed']));

        if ($inst->get_task_status() == 3){
            break;
        }
        if($errors > 5){
            $inst->set_task_status(4);
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
    if ($inst->get_task_status() == 3)
        exit;
    $inst->set_task_status(2);

    $errors = 0;
    foreach ($users as $user)
    {
        var_dump($user);
        $result = $inst->unfollow($user['user_id']);
        if(isset($result) && $result->meta->code == 200){
            $errors = 0;
            $inst->add_row($user['username']);
        }
        else
            $errors++;

        sleep(sleepTime($task['speed']));

        if ($inst->get_task_status() == 3){
            break;
        }
        if($errors > 5){
            $inst->set_task_status(4);
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

    switch ($task['type']) {
        case 0:
            follow_by_username();
            break;
        case 10:
            follow_by_tags();
            break;
        case 20:
            follow_by_list();
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
}
catch (Exception $e){

    $m=substr($e->getMessage(),0,200);
    $task=$_SERVER['argv'][1];

    mysql_query("INSERT INTO errors (task_id,message) VALUES ($task,'$m')")
    or die(mysql_error());
    $qr_result = mysql_query("UPDATE tasks SET status=4 WHERE id=$task")
    or die(mysql_error());
}


function liking_by_username(){}


function sleepTime($interval_id){
    switch ($interval_id) {
        case 0:
            return rand(14, 35);
        case 1:
            return rand(30, 45);
        case 2:
            return rand(60, 90);
        case 3:
            return rand(35, 50);
        case 4:
            return rand(50, 80);
        default:
            return rand(70, 100);
    }
}

