<?php
include_once("InstFollow.php");

    $TASK_ID =$_SERVER['argv'][1];
    $inst = new InstFollow($TASK_ID);

    $task= $inst->get_task($TASK_ID );
    $token=$task['token'];
    $count=$task['count'];
    $account_id = $task['account_id'];

try {
    $users=$inst->getUserFollowersFromBack($account_id,$count,$token);
    $counter=0;
    $usernames ='';

    foreach($users as $user){
        $usernames .=$user;
        if($counter++>$count)
            break;
        if ($inst->is_stopped($TASK_ID))
            break;
    }

    $file = fopen('/var/www/instastellar/tasks/' . $TASK_ID, "w") or die("Unable to open file!");
    fwrite($file, $usernames);
    fclose($file);
    $wait = $inst->SPEED;
    $data = $inst->get_login_pass($task['account_id']);
    $file_ex = __DIR__ . "/Casper/unfollow.js";
    $proxy=$inst->PROXY;
    $inst->start_task($TASK_ID);
    shell_exec("casperjs $file_ex '" . $data['login'] . "' '" . $data['pass'] . "' '" . $TASK_ID . "'  '--proxy=$proxy --proxy-type=socks5' > /dev/null");
    $inst->done_task($TASK_ID);

}catch (Exception $e){
    $inst->close($e);
}