<?php
$username=isset($_POST['username']) ? $_POST['username'] : '';
$email=$_POST['email'];
$ip=get_client_ip();

$to      = $email;
$subject = 'instellar - coming soon';
$message = "Спасибо за участие! мы увидамим Вас когда наше приложение будет готово. По всем вопросам обращайтесь по адресу support@instastellar.su";
$headers = 'From: support@instastellar.su' . "\r\n" .
    'Reply-To: support@instastellar.su' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
mail($to, $subject, $message, $headers);

connect();
add_email($username,$email,$ip);

function add_email($username,$email,$ip){


    $qr_result = mysql_query("INSERT INTO beta_users(username, email,ip) VALUES ('$username', '$email','$ip')")
		or die(mysql_error());
}

function get_client_ip() {
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
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


?>