<?php
    $TASK_ID=$_SERVER['argv'][1];
    connect();
    $follows=get_followed($TASK_ID);
    $token=get_token($TASK_ID);

    foreach($follows as $follow){
        unFollow($follow, $token);
}

function debug($message){
    file_put_contents('/home/c/cc25673/instellar_s/src/TaskBundle/data',"|". json_encode($message). "|",FILE_APPEND);
}


function get_followed($task_id){
    $qr_result = mysql_query("SELECT distinct target_user_id FROM actions WHERE task_id=$task_id")
		or die(mysql_error());
    $result=array();
    while ($row = mysql_fetch_array($qr_result))
        $result[]=$row['target_user_id'];
    return $result;
}

function get_token($task_id){
    $qr_result = mysql_query("SELECT distinct token FROM tasks t LEFT JOIN accounts a ON t.account_id=a.id WHERE t.id=$task_id")
		or die(mysql_error());

    $row = mysql_fetch_array($qr_result);
    return $row['token'];
}


function  unFollow($follow,$token){
    $url="https://api.instagram.com/v1/users/$follow/relationship";
  
    $params = array(
        "access_token" =>  $token,
        "action" =>  'unfollow'
    );
    $result= json_decode(httpPost($url, $params));
}

function httpPost($url,$params)
{
  $postData = '';
   //create name value pairs seperated by &
   foreach($params as $k => $v) 
   { 
      $postData .= $k . '='.$v.'&'; 
   }
   rtrim($postData, '&');
 
    $ch = curl_init();  
 
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    
 
    $output=curl_exec($ch);
 
    curl_close($ch);
    return $output;
 }

function connect(){
    $connection = mysql_connect('localhost', 'cc25673_calc', '0000');
    if (!$connection){
        die("Database Connection Failed" . mysql_error());
    }
    $select_db = mysql_select_db('cc25673_calc');
    if (!$select_db){
        die("Database Selection Failed" . mysql_error());
    }
}