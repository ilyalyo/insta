<?php
class Instagram
{
    public $PROXY;
    private $TASK_ID;
    public $TOKEN_ARRAY;
    public $TOKEN_INDEX;
    private $PROXY_TIME=10;
    private $ACCOUNT_ID;
    private $LOGIN;
    private $PASSWORD;

    public function __construct ($task_id){
        $this->TASK_ID = $task_id;
        $this->connect();

        $qr_result = mysql_query("SELECT token, client,id FROM tokens")
        or die(mysql_error());
        while ($row = mysql_fetch_array($qr_result))
            $this->TOKEN_ARRAY[] = array('client' => $row['client'], 'token' => $row['token'], 'id' => $row['id']);
        $this->TOKEN_INDEX = 0;
    }

    function  follow($user_id)
    {
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];
        $url = "https://api.instagram.com/v1/users/$user_id/relationship";

        $params = array(
            "access_token" => $token,
            "action" => 'follow'
        );
        return $this->httpPost($url, $params);

    }

    public function get_followers($username, $count){
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];

        $url = "https://api.instagram.com/v1/users/search?q=$username" . "&access_token=$token";

        $response = $this->httpGet($url);
        $user_id = $response->data[0]->id;

        $next = "";
        $result = array();
        do {
            $url = "https://api.instagram.com/v1/users/$user_id/followed-by?" . "access_token=$token" . "&cursor=$next";
            $response = ($this->httpGet($url));

            $data = $response->data;
            $next = $response->pagination->next_cursor;

            foreach ($data as $d) {
                if ($count - 1 < count($result))
                    break;

                if ($this->checkUser($d->id, $token)) {
                    $user['username'] = $d->username;
                    $user['user_id'] = $d->id;
                    $user['link'] = '';
                    $user['resource_id'] = '';
                    $result[] = $user;
                }
            }

        } while ($count - 1 > count($result) && isset($next));

        return $result;
    }

    public function get_followers_revers($user_id, $token, $count){
        $next="";
        $result=array();
        $counter=0;
        $about_count=$count/50;

        $all=$this->getFollowedBy($user_id,$token)/50;

        do {
            $counter++;
            $url = "https://api.instagram.com/v1/users/$user_id/follows?" . "access_token=$token" . "&cursor=$next";
            $response = ($this->httpGet($url));

            $data = $response->data;
            $next = $response->pagination->next_cursor;
            if($all-$counter<=$about_count)
                foreach ($data as $d) {
                    $result[]=$d->username;;
                }

        }while(isset($next));
        return $result;
    }

    public function  get_followers_by_tag($tag, $token)
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
        return null;
    }

    function checkUser($user_id, $token)
    {
        $url = "https://api.instagram.com/v1/users/$user_id/relationship?" . "access_token=$token";
        $response = ($this->httpGet($url));

        if ($response->data->outgoing_status == 'none' && $response->data->target_user_is_private == false)
            return true;
    }


    public function get_media(){

    }

    public function get_task(){

        $id= $this->TASK_ID;
        $mysql = mysql_query("
          SELECT t.*,a.token, a.instLogin, a.instPass, p.ip, p.port, a.id as account_id
          FROM tasks t
          INNER JOIN accounts a
          ON t.account_id=a.id
          INNER JOIN proxy p
          ON a.proxy=p.id
          WHERE t.id=$id");
        if(!$mysql)
            throw new Exception(mysql_error());

        $row = mysql_fetch_array($mysql);

        $result = array(
            'id' => $this->TASK_ID,
            'count' => $row['count'],
            'tags' => $row['tags'],
            'type' => $row['type'],
            'token' => $row['token'],
            'account_id' => $row['account_id'],
            'speed' => $row['speed'],
            'byUsername' => $row['byUsername']);

        $this->PROXY = $row['ip'] . ':' . $row['port'];
        $this->LOGIN = $row['instLogin'];
        $this->PASSWORD = $row['instPass'];
        $this->ACCOUNT_ID = $row['account_id'];

        return $result;
    }

    private function check_token($account_id, $token){
        $url = "https://api.instagram.com/v1/users/$account_id?access_token=$token";
        $code ="";
        try{
            $json = $this->httpGet($url);
            $code = $json->meta->code;
        }
        catch(Exception $e){
            var_dump($e);
        }
        return $code;
    }

    public function add_row( $resource_id)
    {
        $task_id = $this->TASK_ID;
        $mysql = mysql_query("INSERT INTO actions (task_id,resource_id) VALUES ($task_id,'$resource_id')");
        if(!$mysql)
            throw new Exception(mysql_error());
    }

    public function get_task_status(){
        $id = $this->TASK_ID;
        $mysql = mysql_query("SELECT status FROM tasks WHERE id=$id")
        or die(mysql_error());
        if(!$mysql)
            throw new Exception(mysql_error());

        $row = mysql_fetch_array($mysql);
        return $row['status'];
    }

    public function set_task_status($status){
        $id = $this->TASK_ID;
        $qr_result = mysql_query("UPDATE tasks SET status=$status WHERE id=$id")
            or die(mysql_error());
    }

    public function change_token(){
        $this->TOKEN_INDEX = ($this->TOKEN_INDEX + 1) % count($this->TOKEN_ARRAY);
    }

    public function update_token(){
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index];
        $file = __DIR__ . "/Casper/auth.js";
        $file2 = __DIR__ . "/Casper/get_token.js";
        shell_exec("casperjs $file '" . $this->LOGIN . "' '" . $this->PASSWORD ."' '" . $this->ACCOUNT_ID . "' ");
        $output = shell_exec("casperjs $file2 '" . $this->LOGIN . "' '" . $this->PASSWORD ."' '" . $token['client'] . "' ");
        if($output != $token['token'] && strlen($output) == 53 ){
            $this->TOKEN_ARRAY[$index]=$output;
            $token_id=$token['id'];
            $qr_result = mysql_query("UPDATE tokens SET token=$output WHERE id=$token_id")
                or die(mysql_error());
            return true;
        }
        return false;
    }


    function httpPost($url, $params){
        try {
            $output = $this->httpPostReal($url, $params);
            $json = json_decode($output);
            if(!isset($json)){
                return null;
            }
            if($json->meta->code == 200)
                return $json;
            if($output === FALSE){
                $this->httpPost($url, $params);
            }
            if($json->meta->code == 429){
                $this->change_token();
                $this->httpPost($url, $params);
            }
            if($json->meta->code == 400){
                if($this->update_token())
                    $this->change_token();
                $this->httpPost($url, $params);
            }
        }
        catch(Exception $e){
            var_dump($e);
        }
        return null;
    }

    function httpPostReal($url, $params)
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
        curl_close($ch);

        return $output;
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

        curl_close($ch);

        return $result;
    }


    private function connect()
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
}