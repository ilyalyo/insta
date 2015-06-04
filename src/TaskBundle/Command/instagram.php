<?php
class Instagram
{
    public $PROXY;
    private $TASK_ID;
    public $TOKEN_ARRAY;
    public $TOKEN_INDEX;
    private $PROXY_TIME=10;
    private $ACCOUNT_ID;
    private $ACCOUNT_ID_INST;
    private $LOGIN;
    private $PASSWORD;

    public function __construct ($task_id){
        $this->TASK_ID = $task_id;
        $this->connect();

        $qr_result = mysql_query("
          SELECT token, client,id FROM tokens WHERE account = (
          SELECT account_id FROM tasks WHERE id =$task_id)")
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

    function  like($resource_id)
    {
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];
        $url = "https://api.instagram.com/v1/media/$resource_id/likes";

        $params = array(
            "access_token" => $token
        );
        return $this->httpPost($url, $params);
    }

    function  unfollow($user_id){
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];
        $url="https://api.instagram.com/v1/users/$user_id/relationship";

        $params = array(
            "access_token" =>  $token,
            "action" =>  'unfollow'
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
            $url = "https://api.instagram.com/v1/users/$user_id/followed-by?" .  "cursor=$next" . "&access_token=$token";
            $response = ($this->httpGet($url));

            $data = $response->data;
            $next = $response->pagination->next_cursor;
            $this->debug($next);
            foreach ($data as $d) {
                if ($count - 1 < count($result))
                    break;

                if ($this->checkUser($d->id, $token)) {
                    $user['username'] = $d->username;
                    $user['user_id'] = $d->id;
                    $result[] = $user;
                }
            }

        } while ($count - 1 > count($result) && isset($next));

        return $result;
    }

    public function get_followers_revers($count){
        $next="";
        $result=array();
        $counter=0;
        $about_count=$count/50;
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];
        $account_id = $this->ACCOUNT_ID_INST;

        $all = $this->get_followed_by($account_id)/50;

        do {
            $counter++;
            $url = "https://api.instagram.com/v1/users/$account_id/follows?count=50" . "&cursor=$next" . "&access_token=$token" ;
            $response = ($this->httpGet($url));

            $data = $response->data;
            $next = $response->pagination->next_cursor;
            if($all-$counter<=$about_count)
                foreach ($data as $d) {
                    $user['username'] = $d->username;
                    $user['user_id'] = $d->id;
                    $result[] = $user;
                }

        }while(isset($next));
        return $result;
    }

    function  get_followed_by($id){
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];
        $url = "https://api.instagram.com/v1/users/$id?access_token=$token";

        $response = $this->httpGet($url);
        return $response->data->counts->follows;
    }

    public function get_followers_by_tags($tags_str, $count)
    {
        $next="";
        $result=array();
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];

        $tags = explode('#', $tags_str);
        $part_size = round($count / count($tags), 0, PHP_ROUND_HALF_UP);

        foreach($tags as $index => $tag){
            $url = "https://api.instagram.com/v1/tags/$tag/media/recent?count=50" . "&next_max_tag_id=$next" . "&access_token=$token";
            do {
                $response = $this->httpGet($url);

                $data = $response->data;
                $next = $response->pagination->next_max_tag_id;

                foreach ($data as $d) {
                    if(count($result) < $part_size * ($index + 1))
                        if ($this->checkUser($d->user->id, $token) ) {
                            $user['username'] = $d->user->username;
                            $user['user_id'] = $d->user->id;
                            $user['resource_id'] = $d->id;
                            $user['link'] = $d->link;
                            $result[] = $user;
                        }
                    else
                        break;
                }
                $url = $response->pagination->next_url;
            }while(isset($next)  && count($result) < $part_size * ($index + 1) );
        }

        return $result;
    }

    public function get_followers_by_list(){
        $result = [];
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];

        $usernames = $this->load_users();
        foreach($usernames as $username){
            $url = "https://api.instagram.com/v1/users/search?q=$username" . "&access_token=$token";
            $response = $this->httpGet($url);
            $d = $response->data[0];
            if ($this->checkUser($d->id, $token) ) {
                $user['username'] = $d->username;
                $user['user_id'] = $d->id;
                $result[] = $user;
            }
        }
        return $result;
    }

    private function load_users(){
        $task_id = $this->TASK_ID;
        $qr_result = mysql_query("
          SELECT list FROM lists WHERE id = $task_id")
        or die(mysql_error());
        $row = mysql_fetch_array($qr_result);
        return explode("\r\n", $row['list']);
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
          SELECT t.*,a.token, a.instLogin, a.instPass, p.ip, p.port, a.id as account_id, a.account_id as account_id_inst
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
            );

        $this->PROXY = $row['ip'] . ':' . $row['port'];
        $this->LOGIN = $row['instLogin'];
        $this->PASSWORD = $row['instPass'];
        $this->ACCOUNT_ID = $row['account_id'];
        $this->ACCOUNT_ID_INST = $row['account_id_inst'];

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
            $this->debug($e);
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
        $this->debug($this->TOKEN_ARRAY[$this->TOKEN_INDEX]);
        $this->TOKEN_INDEX = ($this->TOKEN_INDEX + 1) % count($this->TOKEN_ARRAY);
    }

    public function update_token(){
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index];
        $file = __DIR__ . "/Casper/auth.js";
        $file2 = __DIR__ . "/Casper/get_token.js";
        shell_exec("casperjs $file '" . $this->LOGIN . "' '" . $this->PASSWORD ."' '" .  $token['client'] ."' '" .  $this->ACCOUNT_ID . "' ");
        $output = shell_exec("casperjs $file2 '" . $this->LOGIN . "' '" . $this->PASSWORD ."' '" . $token['client'] . "' ");
        $output = trim($output);
        $this->debug($token['client']);
        $this->debug($output);
        if( isset($output) && strpos($output, $this->ACCOUNT_ID_INST) !== false && $output != $token['token']){
            $this->debug('success update');
            $this->TOKEN_ARRAY[$index]['token']=$output;
            $token_id=$token['id'];
            $qr_result = mysql_query("UPDATE tokens SET token='$output' WHERE id=$token_id")
                or die(mysql_error());
            return true;
        }
        return false;
    }


    function httpPost($url, $params){
        try {
            $output = $this->httpPostReal($url, $params);
            $this->debug($output);
            $json = json_decode($output);
            if(!isset($json)){
                $this->debug('json is null');
                return null;
            }
            if($output === FALSE){
                $this->debug('json is false');
                return null;
            }
            if($json->meta->code == 200)
                return $json;
            if($json->meta->code == 429){
                $this->change_token();
                return null;
            }
            if($json->meta->code == 400){
                if($json->meta->error_type == '"APINotAllowedError')
                    return null;
                if(!$this->update_token())
                    $this->change_token();
                return null;
            }
            $this->debug('un tracked error');
            $this->change_token();
        }
        catch(Exception $e){
            $this->debug($e);
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


    private function debug($message)
    {
        $filename = $this->TASK_ID;
        $file = "/var/www/instastellar/tasks/$filename";
        file_put_contents("$file", "|" . json_encode($message) . "\n", FILE_APPEND);
    }
}