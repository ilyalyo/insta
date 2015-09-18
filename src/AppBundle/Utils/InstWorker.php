<?php
/**
 * Created by PhpStorm.
 * User: Ilya
 * Date: 12.09.2015
 * Time: 20:51
 */
namespace AppBundle\Utils;
require __DIR__ . '/RuCaptcha.php';

use PHPHtmlParser\Dom;
class InstWorker {

    const api_key = '9b5a393207b21e19b979059cf970639e';
    const cookie_folder = '/var/www/instastellar/tasks/';
    const connection_max_time = 30;

    private $login;
    private $pass;
    private $proxy;
    private $cookie_file;
    private $last_csrf;
    public $apps = array(
        'easytogo' => array('id' => '6e336200a7f446a78b125602b90989cc', 'redirect_uri' => 'http://instastellar.su/get_token'),
        'stapico' => array('id' =>  'e77306665eb54866ae0a8185c4028604', 'redirect_uri' => 'http://stapico.ru/accounts/auth/complete'),
        'collecto' => array('id' => '2b5a0c10371c4784935b03e5619e94ca', 'redirect_uri' => 'http://collec.to/login'),
        'ink361' => array('id' =>   '2e7067c2b22a4cbdb6b2e0e89cbd6537', 'redirect_uri' => 'https://instagram.com/accounts/login/?force_classic_login=&next=/oauth/authorize/%3Fclient_id%3D2e7067c2b22a4cbdb6b2e0e89cbd6537%26response_type%3Dcode%26redirect_uri%3Dhttp%3A//data.ink361.com/v1/auth%3Finstagram%3D1%26scope%3Dbasic%2Bcomments%2Brelationships%2Blikes'),
        'pictacular' => array('id' =>   'b7b6584eadc84911b211d3e9045ed917', 'redirect_uri' => 'https://instagram.com/oauth/authorize/?client_id=b7b6584eadc84911b211d3e9045ed917&redirect_uri=http://www.pictacular.co/&scope=comments+likes+relationships&response_type=token'),
        'websta' => array('id' =>   '9d836570317f4c18bca0db6d2ac38e29', 'redirect_uri' => 'https://instagram.com/oauth/authorize/?client_id=9d836570317f4c18bca0db6d2ac38e29&redirect_uri=http://websta.me/&response_type=code&scope=comments+relationships+likes'),
        );

    //account_id нужен для именования файлов, тк может случиться что юзер изменит логин
    public function __construct($login, $pass, $account_id, $proxy){
        $this->login = $login;
        $this->pass = $pass;
        $this->proxy = $proxy;
        $this->cookie_file = self::cookie_folder . $account_id . ".txt";
    }

    public function Login(){
        //получаем кукисы на странице логина
        $login_url = 'https://instagram.com/accounts/login/';

        $ch = curl_init($login_url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::connection_max_time);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0;');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        $result = curl_exec($ch);
        $header = substr($result, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        curl_close ($ch);

        //вытаскиваем кукисы из хеадера
        preg_match_all("/Set-Cookie: (.*?)=(.*?);/i", $header, $res);

        $this->last_csrf = $res[2][0];

        //получаем кукисы сессии
        $login_url = 'https://instagram.com/accounts/login/ajax/';

        $ch = curl_init($login_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::connection_max_time);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0;');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Instagram-AJAX: 1',
            "X-CSRFToken: " . $this->last_csrf,
            'X-Requested-With: XMLHttpRequest'
        ));
        curl_setopt($ch, CURLOPT_REFERER, 'https://instagram.com/accounts/login/');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "username=" . $this->login . "&password=" . $this->pass );

        $result = curl_exec($ch);
        curl_close ($ch);

        $json = json_decode($result);
        if($json !== FALSE && isset($json->authenticated) && $json->authenticated == 'true')
            return true;
        else
            return false;
    }

    public function InstallApp($app_name){
        //получаем код для регистрации приложения
        $client_id = $this->apps[$app_name]['id'];
        $redirect_uri = $this->apps[$app_name]['redirect_uri'];
        $url = "https://instagram.com/oauth/authorize/?client_id=$client_id&redirect_uri=$redirect_uri&response_type=code&scope=likes+comments+relationships";

        //обновляем кукисы
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::connection_max_time);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0;');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        $result = curl_exec($ch);
        $header = substr($result, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        //вытаскиваем кукисы из хеадера
        preg_match_all("/Set-Cookie: (.*?)=(.*?);/i", $header, $res);

        $this->last_csrf = $res[2][0];

        curl_close ($ch);


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::connection_max_time);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0;');
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "csrfmiddlewaretoken=" . $this->last_csrf . "&allow=Authorize");
        $result = curl_exec($ch);
        $header = substr($result, 0, curl_getinfo($ch,CURLINFO_HEADER_SIZE));
        curl_close ($ch);

        var_dump($header);

        preg_match('/Location: .*/',$header,$matches);
        $location= substr($matches[0], 10);

        //отправляем код
        $ch = curl_init($location);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0;');
        curl_exec($ch);
        curl_close ($ch);
    }

    public function GetToken($app_name){
        $url = 'https://instagram.com/accounts/manage_access/';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::connection_max_time);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0;');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        $result = curl_exec($ch);
        curl_close ($ch);
        $dom = new Dom;
        $dom->load($result);
        $a = $dom->find('#client_' . $app_name);
        $a = count($a) > 0 ? $a->find('form')->find('input')->value :  null;
        return $a;
    }

    public function CheckCaptcha(){
        //берем капчу
        $url = 'https://instagram.com/integrity/checkpoint/';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::connection_max_time);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0;');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        $result = curl_exec($ch);
        curl_close ($ch);

        $dom = new Dom;
        $dom->load($result);
        $a = $dom->find('#recaptcha_challenge_image');
        $a = count($a) > 0 ? $a->src : null;

        if($a !== null){
            $text = recognize($a, self::api_key, 3, 100);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::connection_max_time);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0;');
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_REFERER, $url);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "recaptcha_response_field=" . $text);
            $result = curl_exec($ch);
            curl_close ($ch);
        }

        return $result;
    }

    public function removeCookie(){
        unlink($this->cookie_file);
    }

}