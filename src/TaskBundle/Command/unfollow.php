<?php
    $TASK_ID =$_SERVER['argv'][1];
    connect();
    $data=get_tokenUserIdCount($TASK_ID);
    $token=$data[0];
    $count=$data[1];
    $account_id=$data[2];

    $users=getUserFollowers($account_id,$count,$token);
    $counter=0;

    foreach($users as $user){
        unFollow($user, $token);
        //die();
        sleep(rand(10,15));
        if($counter++>$count)
            break;
    }

function getUserFollowers($user_id,$count,$token )
{
    $next="";
    $result=array();
    $counter=0;
    $about_count=$count/50;

    $all=getFollowedBy($user_id,$token)/50;

    do {
        $counter++;
        $url = "https://api.instagram.com/v1/users/$user_id/follows?" . "access_token=$token" . "&cursor=$next";
        $response = json_decode(file_get_contents($url));

        $data = $response->data;
        $next = $response->pagination->next_cursor;
        if($all-$counter<=$about_count)
            foreach ($data as $d) {
                $result[] = $d->id;
            }

    }while(isset($next));
    return $result;
}

function  getFollowedBy($id,$token){
    $url = "https://api.instagram.com/v1/users/$id?access_token=$token";
    $response =json_decode( file_get_contents($url));
    return $response->data->counts->follows;
}

function get_tokenUserIdCount($task_id){
    $qr_result = mysql_query("SELECT token,count,a.account_id as account_id FROM tasks t LEFT JOIN accounts a ON t.account_id=a.id WHERE t.id=$task_id")
		or die(mysql_error());

    $row = mysql_fetch_array($qr_result);
    return [$row['token'],$row['count'],$row['account_id']];
}

function  unFollow($follow,$token){
    $url="https://api.instagram.com/v1/users/$follow/relationship";
    $params = array(
        "access_token" =>  $token,
        "action" =>  'unfollow'
    );
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
    $connection = mysql_connect('localhost', 'root', '');
    if (!$connection){
        die("Database Connection Failed" . mysql_error());
    }
    $select_db = mysql_select_db('symfony');
    if (!$select_db){
        die("Database Selection Failed" . mysql_error());
    }
}