<?php


class InstFollow
{
    private $TASK_ID;
    private $PROXY;
    private $PROXY_ID;
    private $PROXY_USED_ID;
    private $PROXY_TIME=10;

    public function __construct ($task_id){
        $this->PROXY_USED_ID=array();
        $this->TASK_ID = $task_id;
        $this->connect();
       // $this->PROXY = $this->get_proxy();
    }

    function getUserFollowers($task)
    {
        $user_name = $task['tags'];
        $token = $task['token'];
        $url = "https://api.instagram.com/v1/users/search?q=$user_name" . "&access_token=$token";
        $response = ($this->httpGet($url,0));
        $user_id = $response->data[0]->id;

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
        /*$filename=$this->TASK_ID;
        $file = "var/www/Debug/$filename";
        $fp = fopen($file, 'w');
        fwrite($fp, json_encode($message));
        fclose($fp);
        chmod($file, 0777);  //changed to add the zero
        */
        //$filename=$this->TASK_ID;
        //$file = "var/www/Debug/$filename";
        //file_put_contents("$file", "|" . json_encode($message) . '\n', FILE_APPEND);
    }

    function is_stopped($id)
    {
        $mysql = mysql_query("SELECT id FROM tasks WHERE status=3 AND id=$id");
        if(!$mysql)
            throw new Exception(mysql_error());
        $row = mysql_fetch_array($mysql);

        if (!empty($row['id'])) {
            return true;
        }
        return false;
    }


    function done_task($id)
    {
        $mysql = mysql_query("UPDATE tasks SET status=1 WHERE id=$id");
        if(!$mysql)
            throw new Exception(mysql_error());
    }


    function add_row($task_id, $user_id, $username, $resource_id, $responce)
    {
        $mysql = mysql_query("INSERT INTO actions (task_id,target_user_id,username,resource_id,responce) VALUES ($task_id,'$user_id','$username','$resource_id','$responce')");
          if(!$mysql)
              throw new Exception(mysql_error());
    }


    function  getUsernameAndIdsbyTag($tag, $token)
    {
        $url = "https://api.instagram.com/v1/tags/$tag/media/recent?" . "access_token=$token" . "&count=10";
        do {
            $response = ($this->httpGet($url, 0));

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
            $url = $response->pagination->next_url;
        }while(isset($url));
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
        $mysql = mysql_query("SELECT t.*,a.token, a.proxy FROM tasks t INNER JOIN accounts a ON t.account_id=a.id WHERE t.id=$task_id AND status=0");
        if(!$mysql)
           throw new Exception(mysql_error());
        $row = mysql_fetch_array($mysql);

        $result = array(
            'id' => $task_id,
            'count' => $row['count'],
            'tags' => $row['tags'],
            'type' => $row['type'],
            'token' => $row['token'],
            'byUsername' => $row['byUsername']);

        $this->PROXY_ID =  $row['proxy'];
        $this->PROXY = $this->get_proxy();
        $mysql = mysql_query("UPDATE tasks SET status=2 WHERE id=$task_id");
          if(!$mysql)
              throw new Exception(mysql_error());
        return $result;
    }


    function get_proxy()
    {
        $proxy_id=$this->PROXY_ID;
        $sql="SELECT * FROM  proxy WHERE id =$proxy_id";
        $mysql = mysql_query($sql);
          if(!$mysql)
              throw new Exception(mysql_error());
        $row = mysql_fetch_array($mysql);

        $result = array(
            'id' => $row['id'],
            'ip' => $row['ip'],
            'port' => $row['port']
        );

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

        $result = ($this->httpPost($url, $params,0));

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

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->PROXY_TIME);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);


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
                $this->close(curl_error($ch) . curl_errno($ch) . substr($output,0,200));
            }
        }
        elseif($result->meta->code!=200){
            curl_close($ch);
            $this->close($result->meta->code . substr($output,0,200) );
        }
        else
            curl_close($ch);
        return $result;
    }

    function httpGet($url,$try)
    {
        $ch = curl_init();

        $proxy = $this->PROXY['ip'] . ":" . $this->PROXY['port'];

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->PROXY_TIME);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $output = curl_exec($ch);
        $result=json_decode($output);

        if(FALSE === $output){
            //curl_close($ch);
            var_dump(curl_error($ch));
            var_dump(curl_errno($ch));
            $this->close(curl_error($ch));
        }
        elseif($result->meta->code!=200){
            //curl_close($ch);
            $this->close($result->meta->code . substr($output,0,200));
        }
        else
            curl_close($ch);

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
        $task=$this->TASK_ID;
        $mysql = mysql_query("INSERT INTO errors (task_id,message) VALUES ($task,'$message')");
        if(!$mysql)
            throw new Exception(mysql_error());
        $mysql = mysql_query("UPDATE tasks SET status=4 WHERE id=$task");
        if(!$mysql)
            throw new Exception(mysql_error());
        exit();
    }
}