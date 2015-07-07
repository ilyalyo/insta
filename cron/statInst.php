<?php

connect();
$accounts=get_accounts();

$token = get_token();
$sleep=3000/count($accounts);
$sleep=$sleep>50 ? 50 : $sleep;

foreach($accounts as $key=>$account_id){
    $result = getData($account_id,$token);
    if(isset($result))
        add_row($key,$result["followed_by"],$result["follows"] );
    else
        echo 'FAIL' . $key;


    sleep($sleep);
}

function add_row($account_id,$followed_by,$follows){
    $qr_result = mysql_query("INSERT INTO history (account_id,followed_by,follows) VALUES ($account_id,$followed_by,$follows)");
}

function  getData($id,$token){
    $url = "https://api.instagram.com/v1/users/$id?access_token=$token";
    $response =json_decode( file_get_contents($url));
    if($response->meta->code == 200){
        $d = $response->data;

        $result["followed_by"] = $d->counts->followed_by;
        $result["follows"] = $d->counts->follows;
        return $result;
    }
    $new_t = get_token_by_id($id);

    $url = "https://api.instagram.com/v1/users/$id?access_token=$new_t";
    $response =json_decode( file_get_contents($url));

    if($response->meta->code == 200){
        $d = $response->data;

        $result["followed_by"] = $d->counts->followed_by;
        $result["follows"] = $d->counts->follows;
        return $result;
    }
    return null;
}

function get_token(){
    $try = 0;
    while($try<10){
        $token=get_token_db($try++);
        $url = "https://api.instagram.com/v1/users/1803201938?access_token=$token";
        $response =json_decode( file_get_contents($url));
        if ($response->meta->code == '200')
            return $token;
    }

    return false;
}

function get_token_by_id($id){
    if(!isset($id))
        return null;
    $qr_result = mysql_query("SELECT t.token from tokens t INNER JOIN accounts a on t.account = a.id WHERE a.account_id =$id AND client ='easytogo'")
    or die(mysql_error());

    $row = mysql_fetch_array($qr_result);
    $result = $row['token'];

    return $result;
}

function get_token_db($offset){
    $qr_result = mysql_query("SELECT token FROM accounts LIMIT $offset,1")
    or die(mysql_error());

    $row = mysql_fetch_array($qr_result);
    $result = $row['token'];

    return $result;
}

function get_accounts(){
    $qr_result = mysql_query("SELECT account_id,id FROM accounts")
    or die(mysql_error());

    while ($row = mysql_fetch_array($qr_result))
        $result[$row['id']]=$row['account_id'];

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
    mysql_query("SET NAMES 'utf8'");
    mysql_query("SET CHARACTER SET utf8 ");
}