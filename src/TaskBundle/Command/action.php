<?php

$TASK_ID=$_SERVER['argv'][1];
debug("started: " . $TASK_ID);
connect();
$task=get_task($TASK_ID);

for ($i = 0; $i <= $task['count'] - 1 ; $i++) {

    $token = $task["token"];

    $tags = explode('#', $task['tags']);

    $rand_key = array_rand($tags);

    $tag = $tags[$rand_key];

    $media=getUsernameAndIdsbyTag($tag, $token);

    if($task['type']==0)
        sendLike($task, $media);
    else
         follow($task, $media);

    sleep(rand(30,50));

    if(is_stopped($TASK_ID))
         break;
}
if(!is_stopped($TASK_ID))
	done_task($TASK_ID);

debug("closed: " . $TASK_ID);

function debug($message){
    $file = __DIR__ . "/data";
    file_put_contents("$file","|". json_encode($message). "|",FILE_APPEND);
}

function is_stopped($id){

    $qr_result = mysql_query("SELECT id FROM tasks WHERE status=3 AND id=$id")
		or die(mysql_error());
   $row = mysql_fetch_array($qr_result);

  if(!empty($row['id'])){
    	return true;
  }
  	return false;
}


function done_task($id){
    $qr_result = mysql_query("UPDATE tasks SET status=1 WHERE id=$id")
		or die(mysql_error());
}


function add_row($task_id,$user_id,$username,$resource_id,$responce){
        $qr_result = mysql_query("INSERT INTO actions (task_id,target_user_id,username,resource_id,responce) VALUES ($task_id,'$user_id','$username','$resource_id','$responce')")
		or die(mysql_error());

}


function  getUsernameAndIdsbyTag($tag,$token){

    $url = "https://api.instagram.com/v1/tags/$tag/media/recent?" . "access_token=$token" . "&count=1";
  
  	$response =json_decode( file_get_contents($url));
  	$result['id']=$response->data[0]->id;
    $result['username']=$response->data[0]->user->username;
    $result['user_id']=$response->data[0]->user->id;
  	
  return $result;
}


function get_task($task_id){
    $qr_result = mysql_query("SELECT t.*,a.token FROM tasks t INNER JOIN accounts a ON t.account_id=a.id WHERE t.id=$task_id AND status=0")
		or die(mysql_error());
  $row = mysql_fetch_array($qr_result);
 
  $result=array(
    'id' => $task_id,
    'count' => $row['count'],
    'tags' => $row['tags'],
    'type' => $row['type'],
    'token' => $row['token'] );

   mysql_query("UPDATE tasks SET status=2 WHERE id=$task_id")
  	or die(mysql_error());  
    return $result;
}



function  sendLike($task,$media){

    $media_id=$media['id'];
    $token=$task["token"];
  	$url="https://api.instagram.com/v1/media/$media_id/likes";
  
  $params = array(
   "access_token" =>  $token
   );
  
  $result= json_decode(httpPost($url, $params));

   add_row($task['id'],$media['user_id'],$media['username'],$media['id'],$result->meta->code);  
}

function  follow($task,$media){

  	$target_id=$media['user_id'];
    $token=$task["token"];
  	$url="https://api.instagram.com/v1/users/$target_id/relationship";
    
  
  $params = array(
   "access_token" =>  $token,
   "action" =>  'follow'
   );
  $result= json_decode(httpPost($url, $params));

   add_row($task['id'],$media['user_id'],$media['username'],$media['id'],$result->meta->code);  
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