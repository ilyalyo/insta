<?php


class InstUnfollow
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


    function done_task($id){
        $qr_result = mysql_query("UPDATE tasks SET status=1 WHERE id=$id")
            or die(mysql_error());
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

    function getUserFollowers($user_id,$count,$token )
    {
        $next="";
        $result=array();
        $counter=0;
        $about_count=$count/50;

        $all=$this->getFollowedBy($user_id,$token)/50;

        do {
            $counter++;
            $url = "https://api.instagram.com/v1/users/$user_id/follows?" . "access_token=$token" . "&cursor=$next";
            $response = json_decode($this->httpGet($url,0));

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
        $response =json_decode( $this->httpGet($url,0));
        return $response->data->counts->follows;
    }

    function get_tokenUserIdCount(){
        $task_id=$this->TASK_ID;
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
        $result= json_decode($this->httpPost($url, $params,0));
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

    function connect(){
        $connection = mysql_connect('localhost', 'root', 'bycnfcntkkfh,fpf');
        if (!$connection){
            die("Database Connection Failed" . mysql_error());
        }
        $select_db = mysql_select_db('symfony');
        if (!$select_db){
            die("Database Selection Failed" . mysql_error());
        }
    }

    public function close($message){
        mysql_query("INSERT INTO errors (task_id,message) VALUES ($this->TASK_ID,$message)")
        or die(mysql_error());
        $qr_result = mysql_query("UPDATE tasks SET status=4 WHERE id=$this->TASK_ID")
        or die(mysql_error());
        exit();
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

    function debug($message)
    {
        //$filename=$this->TASK_ID;
        //$file = "var/www/Debug/$filename";
        //file_put_contents("$file", "|" . json_encode($message) . '\n', FILE_APPEND);
    }

}