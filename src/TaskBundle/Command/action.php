<?php

$TASK_ID = $_SERVER['argv'][1];

$inst = new Inst($TASK_ID);

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


class Inst
{
    private $TASK_ID;
    private $PROXY;
    private $PROXY_USED_ID;
    private $PROXY_TIME=10;

    public function __construct ($task_id){
        $this->PROXY_USED_ID=array();
        $this->TASK_ID = $task_id;
        $this->connect();
        $this->PROXY = $this->get_proxy();
    }

    function getUserFollowers($task)
    {
        $user_name = $task['tags'];
        $token = $task['token'];
        $url = "https://api.instagram.com/v1/users/search?q=$user_name" . "&access_token=$token";
        $response = ($this->httpGet($url,0));
        $user_id = $response->data[0]->id;
        if (!isset($user_id))
            return 1;

        $next = "";
        $result = array();
        do {
            $url = "https://api.instagram.com/v1/users/$user_id/followed-by?" . "access_token=$token" . "&cursor=$next";
            $response = ($this->httpGet($url,0));

            $data = $response->data;
            $next = $response->pagination->next_cursor;


            foreach ($data as $d) {
                if ($task['count'] - 1 < count($result))
                    break;

                if ($this->checkUser($d->id, $token)) {
                    $user['username'] = $d->username;
                    $user['user_id'] = $d->id;
                    $user['link'] = '';
                    $result[] = $user;
                }
            }

        } while ($task['count'] - 1 > count($result) && isset($next));

        return $result;
    }


    function debug($message)
    {
        $filename=$this->TASK_ID;
        $file = "var/www/Debug/$filename";
        file_put_contents("$file", "|" . json_encode($message) . '\n', FILE_APPEND);
    }

    function is_stopped($id)
    {
        $qr_result = mysql_query("SELECT id FROM tasks WHERE status=3 AND id=$id")
            or die(mysql_error());
        $row = mysql_fetch_array($qr_result);

        if (!empty($row['id'])) {
            return true;
        }
        return false;
    }


    function done_task($id)
    {
        $qr_result = mysql_query("UPDATE tasks SET status=1 WHERE id=$id")
        or die(mysql_error());
    }


    function add_row($task_id, $user_id, $username, $resource_id, $responce)
    {
        $qr_result = mysql_query("INSERT INTO actions (task_id,target_user_id,username,resource_id,responce) VALUES ($task_id,'$user_id','$username','$resource_id','$responce')")
            or die(mysql_error());
    }


    function  getUsernameAndIdsbyTag($tag, $token)
    {
        $url = "https://api.instagram.com/v1/tags/$tag/media/recent?" . "access_token=$token" . "&count=5";
        $response = ($this->httpGet($url,0));

        $data = $response->data;
        foreach ($data as $d) {
            if ($this->checkUser($d->user->id, $token)) {
                $result['id'] = $d->id;
                $result['username'] = $d->user->username;
                $result['user_id'] = $d->user->id;
                $result['link'] = $d->link;
                return $result;
            }
        }
    }

    function checkUser($user_id, $token)
    {
        $url = "https://api.instagram.com/v1/users/$user_id/relationship?" . "access_token=$token";
        $response = ($this->httpGet($url,0));

        if ($response->data->outgoing_status == 'none' && $response->data->target_user_is_private == false)
            return true;
        return false;
    }

    function get_task($task_id)
    {
        $qr_result = mysql_query("SELECT t.*,a.token FROM tasks t INNER JOIN accounts a ON t.account_id=a.id WHERE t.id=$task_id AND status=0")
            or die(mysql_error());
        $row = mysql_fetch_array($qr_result);

        $result = array(
            'id' => $task_id,
            'count' => $row['count'],
            'tags' => $row['tags'],
            'type' => $row['type'],
            'token' => $row['token'],
            'byUsername' => $row['byUsername']);

        mysql_query("UPDATE tasks SET status=2 WHERE id=$task_id")
            or die(mysql_error());
        return $result;
    }


    function get_proxy()
    {
        $blocked_proxy="";
        if(count($this->PROXY_USED_ID) > 0 ) {
            $blocked_proxy = " WHERE id NOT IN (";
            foreach ($this->PROXY_USED_ID as $id) {
                $blocked_proxy .= $id . ',';
            }
            $blocked_proxy = rtrim($blocked_proxy, ',');
            $blocked_proxy .= ')';
        }
        $sql="SELECT * FROM  proxy ". $blocked_proxy ."
             ORDER BY  proxy.use ASC LIMIT 0 , 1";
        $qr_result = mysql_query($sql)
            or die(mysql_error());
        $row = mysql_fetch_array($qr_result);

        $id = $row['id'];
        $use = $row['use'] + 1;
        $result = array(
            'id' => $row['id'],
            'ip' => $row['ip'],
            'port' => $row['port']
        );
        $this->PROXY_USED_ID[] = $id;

        mysql_query("UPDATE proxy SET proxy.use=$use WHERE id=$id")
            or die(mysql_error());
        return $result;
    }

    function  sendLike($task, $media)
    {
        $media_id = $media['id'];
        $token = $task["token"];
        $url = "https://api.instagram.com/v1/media/$media_id/likes";

        $params = array(
            "access_token" => $token
        );

        $result = (httpPost($url, $params,0));

        $this->add_row($task['id'], $media['user_id'], $media['username'], $media['link'], $result->meta->code);
    }

    function  follow($task, $media)
    {
        $target_id = $media['user_id'];
        $token = $task["token"];
        $url = "https://api.instagram.com/v1/users/$target_id/relationship";


        $params = array(
            "access_token" => $token,
            "action" => 'follow'
        );
        $result = ( $this->httpPost($url, $params,0));

        $this->add_row($task['id'], $media['user_id'], $media['username'], $media['link'], $result->meta->code);
    }


    function httpPost($url, $params,$try)
    {
        $postData = '';
        foreach ($params as $k => $v) {
            $postData .= $k . '=' . $v . '&';
        }
        rtrim($postData, '&');

        $ch = curl_init();
        $proxy = $this->PROXY['ip'] . ":" . $this->PROXY['port'];

        $this->debug($proxy . "|" . $try );

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->PROXY_TIME);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $output = curl_exec($ch);
        $result=json_decode($output);

        if(FALSE === $output){
            if($try++ < 5) {
                curl_close($ch);
                $this->PROXY = $this->get_proxy();
                $result = $this->httpPost($url, $params, $try);
            }
            else{
                curl_close($ch);
                $this->close(curl_error($ch) . curl_errno($ch));
            }
        }
        elseif($result->meta->code!=200){
            curl_close($ch);
            $this->close($result->meta->code);
        }
        else
            curl_close($ch);
        $this->debug( "stop post|"  );
        return $result;
    }

    function httpGet($url,$try)
    {
        $ch = curl_init();

        $proxy = $this->PROXY['ip'] . ":" . $this->PROXY['port'];

        $this->debug("\n" . $proxy . "|" . $try );

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->PROXY_TIME);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($ch);
        $result=json_decode($output);

        if(FALSE === $output){
            //throw new Exception(curl_error($ch), curl_errno($ch));
            if($try++<5) {
                curl_close($ch);
                $this->PROXY = $this->get_proxy();

                $result = $this->httpGet($url, $try);
            }
            else{
                $this->debug( "!full stop!"  );

                curl_close($ch);
                $this->close(curl_error($ch) . curl_errno($ch));
            }
        }
        elseif($result->meta->code!=200){
            curl_close($ch);
            $this->close($result->meta->code);
        }
        else
            curl_close($ch);
        $this->debug( "stop get|"  );

        return $result;
     }

    function connect()
    {
        $connection = mysql_connect('localhost', 'root', 'bycnfcntkkfh,fpf');//bycnfcntkkfh,fpf
        if (!$connection) {
            die("Database Connection Failed" . mysql_error());
        }
        $select_db = mysql_select_db('symfony');
        if (!$select_db) {
            die("Database Selection Failed" . mysql_error());
        }
        mysql_query("SET NAMES 'utf8'");
        mysql_query("SET CHARACTER SET utf8 ");
    }

    public function close($message){
        mysql_query("INSERT INTO errors (task_id,message) VALUES ($this->TASK_ID,$message)")
            or die(mysql_error());
        $qr_result = mysql_query("UPDATE tasks SET status=4 WHERE id=$this->TASK_ID")
         or die(mysql_error());
        exit();
    }

}