<?php


class InstFollow
{
    private $TASK_ID;
    private $PROXY;
    private $PROXY_TIME=10;

    public function __construct ($task_id){
        $this->TASK_ID = $task_id;
        $this->connect();
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
                    $result[] = $d->username;
                }
            }

        } while ($task['count'] - 1 > count($result) && isset($next));

        return $result;
    }

    function  getUsernameByTag($tag, $token)
    {
        $url = "https://api.instagram.com/v1/tags/$tag/media/recent?" . "access_token=$token" . "&count=10";
        do {
            $response = ($this->httpGet($url, 0));

            $data = $response->data;
            foreach ($data as $d) {
                if ($this->checkUser($d->user->id, $token)) {
                    return $d->user->username;
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
        $mysql = mysql_query("SELECT t.*,a.token, a.proxy FROM tasks t INNER JOIN accounts a ON t.account_id=a.id WHERE t.id=$task_id");// AND status=0
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

        $this->PROXY = $this->get_proxy($row['proxy']);

        return $result;
    }

    function get_proxy($id)
    {
        $sql="SELECT * FROM  proxy WHERE id =$id";
        $mysql = mysql_query($sql);
          if(!$mysql)
              throw new Exception(mysql_error());
        $row = mysql_fetch_array($mysql);

        $result =$row['ip'] . ":" . $row['port'];

        return $result;
    }

    function httpPost($url, $params)
    {
        $postData = '';
        foreach ($params as $k => $v) {
            $postData .= $k . '=' . $v . '&';
        }
        rtrim($postData, '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->PROXY_TIME);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PROXY, $this->PROXY);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);


        $output = curl_exec($ch);
        $result=json_decode($output);

        if(FALSE === $output){
            curl_close($ch);
            $this->close(curl_error($ch) . curl_errno($ch) . substr($output,0,200));
        }
        elseif($result->meta->code!=200){
            curl_close($ch);
            $this->close($result->meta->code . substr($output,0,200) );
        }
        else
            curl_close($ch);
        return $result;
    }

    function httpGet($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->PROXY_TIME);
        curl_setopt($ch, CURLOPT_PROXY, $this->PROXY);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);

        $result=json_decode($output);

        if(FALSE === $output){
            $this->close(curl_error($ch));
        }
        elseif($result->meta->code!=200){
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