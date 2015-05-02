<?php
include_once("InstUnfollow.php");

    $TASK_ID =$_SERVER['argv'][1];
    $inst = new InstUnfollow($TASK_ID);

    $data = $inst->get_tokenUserIdCount();
    $token=$data[0];
    $count=$data[1];
    $account_id = $data[2];

    $users=$inst->getUserFollowers($account_id,$count,$token);
    $counter=0;

    foreach($users as $user){
        $inst->unFollow($user, $token);
        sleep(rand(20,40));
        if($counter++>$count)
            break;
        if ($inst->is_stopped($TASK_ID))
            break;
    }
$inst->done_task($TASK_ID);

