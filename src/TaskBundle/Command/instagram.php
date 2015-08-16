<?php
class Instagram
{
    public $PROXY;
    private $TASK_ID;
    // массив токенов вида : [client,token,id]
    public $TOKEN_ARRAY;
    public $TAGS;
    public $TAGS_ARRAY;
    public $OPTIONS;
    public $TOKEN_INDEX;
    private $PROXY_TIME=10;
    private $ACCOUNT_ID;
    private $ACCOUNT_ID_INST;
    private $LOGIN;
    private $PASSWORD;
    private $DB_USERS;

    // устанавливаем соединение с БД, выгружаем все токены
    // находим рабочий токен
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

        for($i = 0; $i<7;$i++) {
            $r = $this->check_token('1800392910', $this->TOKEN_ARRAY[$this->TOKEN_INDEX]['token']);
            if($r = 200)
                break;
        }
    }

    // фоловим указанного юзера
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

    // лайкаем указанный медиа
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
    //анфолов
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

    // получаем подписчиков указанного пользователя
    public function get_followers($username, $count){
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];

        $url = "https://api.instagram.com/v1/users/search?q=$username" . "&access_token=$token";

        // находим пользователя, подписчиков которого нужно спарсить
        $response = $this->httpGet($url);
        $user_id = $response->data[0]->id;

        // вычисляем количество юзеров, после нахождения которого надо будет обновить статус парсинга( каждые 10%)
        $block = $count / 10;
        $next = "";
        $result = array();
        do {
            // на каждом шаге получаем новый блок подписчиков
            $url = "https://api.instagram.com/v1/users/$user_id/followed-by?" .  "cursor=$next" . "&access_token=$token";
            $response = ($this->httpGet($url));

            $data = $response->data;
            $next = $response->pagination->next_cursor;
            $this->debug($next);
            foreach ($data as $d) {
                if ($count - 1 < count($result))
                    return $result;

                if ($this->checkUserOptions($d->id, $token, $d->username)) {
                    $user['username'] = $d->username;
                    $user['user_id'] = $d->id;
                    $result[] = $user;
                    // каждые 10% обновляем парсинг статус в базе
                    $p_count = count($result);
                    if($p_count % $block == 0)
                        $this->set_parsing_status($p_count);
                }
            }
            // работаем до тех пор пока у пользователя есть подписчики и количество спарсенных подписчиков меньше необходимого
        } while ($count - 1 > count($result) && isset($next));
        $this->debug('parsed: ' . count($result));
        return $result;
    }

    // парсинг подписчиков с конца для отписки
    // зная сколько всего подписчиков и сколько нужно спарсить
    // двигаемся по подписчикам с начала в конец до тех пор пока до конца не останется необходимое количество подписчиков
    // ( округленное в большую сторону, тк мы двигаемся фиксированными блоками по 50)
    // с этого момента начинаем запоминать подписчиков и дойдя до конца округляем их количество до заданного
    // и возвращаем массив с этими пользователями
    public function get_followers_revers($count){
        $next="";
        $result=array();
        $counter=0;
        $block = $count / 10;
        $about_count=$count/50;
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];
        $account_id = $this->ACCOUNT_ID_INST;

        // количество подписчиков
        $all = $this->get_followed_by($account_id)/50;

        do {
            $counter++;
            $url = "https://api.instagram.com/v1/users/$account_id/follows?count=50" . "&cursor=$next" . "&access_token=$token" ;
            $response = ($this->httpGet($url));

            $data = $response->data;
            $next = $response->pagination->next_cursor;
            $this->debug($next);
            if($all-$counter<=$about_count)
                foreach ($data as $d) {
                    $user['username'] = $d->username;
                    $user['user_id'] = $d->id;
                    $result[] = $user;
                    $p_count = count($result);
                    if($p_count % $block == 0)
                        $this->set_parsing_status($p_count);
                }

        }while(isset($next));
        $this->debug('parsed: ' . count($result));
        return array_slice($result, 0,  $count);
    }

    // вытаскиваем количество подписчиков
    function  get_followed_by($id){
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];
        $url = "https://api.instagram.com/v1/users/$id?access_token=$token";

        $response = $this->httpGet($url);
        return $response->data->counts->follows;
    }

    // выбираем недавно загруженное медиа по заданным тэгам
    // фоловим пользователей загрузивших эти медиа
    // тк тэгов несколько, набираем по каждому их них равно количество пользователей( примерно)
    public function get_followers_by_tags($tags_str, $count)
    {
        $next="";
        $result=array();
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];

        $tags = explode('#', $tags_str);
        $part_size = round($count / count($tags), 0, PHP_ROUND_HALF_UP);
        $block = $count / 10;

        foreach($tags as $index => $tag){
            $url = "https://api.instagram.com/v1/tags/$tag/media/recent?count=50" . "&next_max_tag_id=$next" . "&access_token=$token";
            do {
                $response = $this->httpGet($url);

                $data = $response->data;
                $next = $response->pagination->next_max_tag_id;

                foreach ($data as $d) {
                    if(count($result) < $part_size * ($index + 1) && count($result) < $count) {
                        if ($this->checkUserOptions($d->user->id, $token, $d->user->username)) {
                            $user['username'] = $d->user->username;
                            $user['user_id'] = $d->user->id;
                            $user['resource_id'] = $d->id;
                            $user['link'] = $d->link;
                            $result[] = $user;
                            $p_count = count($result);
                            if ($p_count % $block == 0)
                                $this->set_parsing_status($p_count);
                        }
                    }
                    else
                        break;
                }
                $url = $response->pagination->next_url;
            }while(isset($next)  && count($result) < $part_size * ($index + 1) && count($result) < $count);
        }
        $this->debug('parsed: ' . count($result));
        return $result;
    }


    // выбираем недавно загруженное медиа по заданным тэгам
        // тк тэгов несколько, набираем по каждому их них равно количество пользователей( примерно)
    public function get_media_by_tags($tags_str, $count)
    {
        $next="";
        $result=array();
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];

        $tags = explode('#', $tags_str);
        $part_size = round($count / count($tags), 0, PHP_ROUND_HALF_UP);
        $block = $count / 10;

        foreach($tags as $index => $tag){
            $url = "https://api.instagram.com/v1/tags/$tag/media/recent?count=50" . "&next_max_tag_id=$next" . "&access_token=$token";
            do {
                $response = $this->httpGet($url);

                $data = $response->data;
                $next = $response->pagination->next_max_tag_id;

                foreach ($data as $d) {
                    //если идет сбор людей на фоловинг, и мы находим человека, которого уже добавляли в ходе поиска в массив, пропускаем его
                    if(array_search($d->user->id, array_column($result, 'user_id')) && in_array($this->OPTIONS['type'],[0,10,20,30]))
                        continue;
                    if(count($result) < $part_size * ($index + 1) && count($result) < $count) {
                        if ($this->checkMediaOptions($d->id, $token) && $this->checkUserOptions($d->user->id, $token, $d->user->username)) {
                            $user['username'] = $d->user->username;
                            $user['user_id'] = $d->user->id;
                            $user['resource_id'] = $d->id;
                            $user['link'] = $d->link;
                            $result[] = $user;
                            $p_count = count($result);
                            if ($p_count % $block == 0)
                                $this->set_parsing_status($p_count);
                        }
                    }
                    else
                        break;
                }
                $url = $response->pagination->next_url;
            }while(isset($next)  && count($result) < $part_size * ($index + 1) && count($result) < $count);
        }
        $this->debug('parsed: ' . count($result));
        return $result;
    }


    // парсим медиа загруженно в указанной области
    // по указанным координатам и радиусу получаем список мест
    // начиная перебирать все места получаем недавние медиа загруженные с привязкой к ним
    public function get_media_by_geo($lat_lng_radius_str, $count)
    {
        if(count($this->OPTIONS['optionGeo']) > 0)
            $this->TAGS_ARRAY = explode('#', $this->TAGS);

        $next="";
        $result=array();
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];

        $lat_lng_radius = explode(';', $lat_lng_radius_str);
        var_dump('start get media');

        $locations_url = "https://api.instagram.com/v1/locations/search?lat=" . $lat_lng_radius[0] . "&lng=" . $lat_lng_radius[1] . "&DISTANCE=" . $lat_lng_radius[2] . "&access_token=$token";
        $response = $this->httpGet($locations_url);
        $locations = $response->data;
        $block = $count / 10;

        foreach($locations as $location){
            $id = $location->id;
            $url = "https://api.instagram.com/v1/locations/$id/media/recent?count=50&access_token=$token";
            do {
                $response = $this->httpGet($url);
                $data = $response->data;

                foreach ($data as $d) {
                    if(count($result) < $count) {
                        //если идет сбор людей на фоловинг, и мы находим человека, которого уже добавляли в ходе поиска в массив, пропускаем его
                            if(array_search($d->user->id, array_column($result, 'user_id')) && in_array($this->OPTIONS['type'],[0,10,20,30]))
                                continue;
                            if ($this->checkMediaOptions($d->id, $token) && $this->checkUserOptions($d->user->id, $token, $d->user->username)) {
                            $user['username'] = $d->user->username;
                            $user['user_id'] = $d->user->id;
                            $user['resource_id'] = $d->id;
                            $user['link'] = $d->link;
                            $result[] = $user;
                            $p_count = count($result);
                            if ($p_count % $block == 0)
                                $this->set_parsing_status($p_count);
                            }
                    }
                    else
                        break;
                }
                $url = $response->pagination->next_url;
            }while(isset($url) && count($result) < $count);
        }
        $this->debug('parsed: ' . count($result));
        return $result;
    }

    //исключаем из списка загруженного пользователем
    // всех юзеров, которые лиоб не существуют, либо не подходят под выбранные опции(см. $this->checkUserOptions)
    public function get_followers_by_list(){
        $result = [];
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];

        $usernames = $this->load_users();
        $block = count($usernames)/ 10;
        foreach($usernames as $username){
            $url = "https://api.instagram.com/v1/users/search?q=$username" . "&access_token=$token";
            $response = $this->httpGet($url);
            $d = $response->data[0];
            if ($this->checkUserOptions($d->id, $token, $d->username) ) {
                $user['username'] = $d->username;
                $user['user_id'] = $d->id;
                $result[] = $user;
                $p_count = count($result);
                if($p_count % $block == 0)
                    $this->set_parsing_status($p_count);
            }
        }
        $this->debug('parsed: ' . count($result));
        return $result;
    }

    //загружаем список, загруженный пользоватем, из базы
    private function load_users(){
        $task_id = $this->TASK_ID;
        $qr_result = mysql_query("
          SELECT list FROM lists WHERE id = $task_id")
        or die(mysql_error());
        $row = mysql_fetch_array($qr_result);
        return explode("\r\n", $row['list']);
    }

    // загружаем всех пользователей, которых уже добавлял/анфоловил пользователь,
    // что бы не добавлять их еще раз, тк он мог их добавить, отписать, и опять добавить и опять отписать и тд
    private function load_users_from_db(){
        $account_id = $this->ACCOUNT_ID;
        $qr_result = mysql_query("
          SELECT distinct resource_id  FROM actions a
          INNER JOIN tasks t ON  t.id = a.task_id
          INNER JOIN accounts ac ON ac.id = t.account_id
          WHERE ac.id = $account_id")
        or die(mysql_error());
        while ($row = mysql_fetch_array($qr_result))
            $this->DB_USERS[] = $row['resource_id'];
    }


    // проверяем подходит ли заданный пользователь под наши критерии
    function checkUserOptions($user_id, $token, $username = null)
    {

        if(in_array($this->OPTIONS['type'],[0,10,20,30]) || !$this->OPTIONS['optionFollowClosed'])
        {
            // добавлялся ли ранее
            if(!$this->OPTIONS['optionCheckUserFromDB'])
                if(in_array($username, $this->DB_USERS))
                    return false;

            $url = "https://api.instagram.com/v1/users/$user_id/relationship?" . "access_token=$token";
            $response = $this->httpGet($url);

            // закрыта ли страница
            if(!$this->OPTIONS['optionFollowClosed'])
                if($response->data->target_user_is_private)
                    return false;

            if (!$response->data->outgoing_status == 'none')
                return false;
        }

        if($this->OPTIONS['optionHasAvatar']               || count($this->OPTIONS['optionStopPhrases']) > 0
        || isset($this->OPTIONS['optionFollowersFrom'])    || isset($this->OPTIONS['optionFollowersTo'])
        || isset($this->OPTIONS['optionFollowFrom'])       || isset($this->OPTIONS['optionFollowTo']))
        {
            $url = "https://api.instagram.com/v1/users/$user_id?" . "access_token=$token";
            $response = $this->httpGet($url);
            var_dump($response->data->profile_picture );

            if($this->OPTIONS['optionHasAvatar'])
                if($response->data->profile_picture == 'https://instagramimages-a.akamaihd.net/profiles/anonymousUser.jpg')
                    return false;

            var_dump($response->data->bio );
            if(count($this->OPTIONS['optionStopPhrases']) > 0){
                foreach($this->OPTIONS['optionStopPhrases'] as $word)
                    if(strpos(strtolower($response->data->bio), $word ))
                        return false;
            }

            if(isset($this->OPTIONS['optionFollowersFrom']))
                if($response->data->counts->followed_by < $this->OPTIONS['optionFollowersFrom'])
                    return false;

            if(isset($this->OPTIONS['optionFollowersTo']))
                if($response->data->counts->followed_by > $this->OPTIONS['optionFollowersTo'])
                    return false;

            if(isset($this->OPTIONS['optionFollowFrom']))
                if($response->data->counts->follows < $this->OPTIONS['optionFollowFrom'])
                    return false;

            if(isset($this->OPTIONS['optionFollowTo']))
                if($response->data->counts->follows > $this->OPTIONS['optionFollowTo'])
                    return false;
        }
        return true;
    }

    // проверяем не лайкали ли этот объект ранее + разные опции
    function checkMediaOptions($media_id, $token)
    {
        $url = "https://api.instagram.com/v1/media/$media_id?" . "access_token=$token";
        $response = $this->httpGet($url);

        // проверяем есть ли у заданного медиа интересующие нас тэги
        if(count($this->OPTIONS['optionGeo']) > 0)
            if (count(array_intersect($response->data->tags,$this->TAGS_ARRAY)) == 0)
                return false;

        if ($response->data->user_has_liked == false)
            return true;

        return false;
    }

    public function get_media(){

    }

    public function get_last_user_media($user_id){
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index]['token'];
        $url = "https://api.instagram.com/v1/users/$user_id/media/recent?count=1&access_token=$token";
        return $this->httpGet($url);
    }

    // загружем из базы все необходимое по заданной задаче
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
            'optionAddLike' => $row['optionAddLike'],
            );

        $this->TAGS = $row['tags'];
        $this->PROXY = $row['ip'] . ':' . $row['port'];
        $this->LOGIN = $row['instLogin'];
        $this->PASSWORD = $row['instPass'];
        $this->ACCOUNT_ID = $row['account_id'];
        $this->ACCOUNT_ID_INST = $row['account_id_inst'];

        $this->OPTIONS['optionCheckUserFromDB'] = $row['optionCheckUserFromDB'];
        $this->OPTIONS['optionFollowClosed'] = $row['optionFollowClosed'];
        $this->OPTIONS['optionAddLike'] = $row['optionAddLike'];
        $this->OPTIONS['optionLastActivity'] = $row['optionLastActivity'];
        if(isset($row['optionStopPhrases'])){
            $tmp = explode(',', $row['optionStopPhrases']);
            $tmp = array_map('strtolower', $tmp);
        }
        else
            $tmp = "";
        $this->OPTIONS['optionStopPhrases'] = $tmp;
        $this->OPTIONS['optionFollowClosed'] = $row['optionFollowClosed'];
        $this->OPTIONS['optionHasAvatar'] = $row['optionHasAvatar'];
        $this->OPTIONS['optionGeo'] = $row['optionGeo'];
        $this->OPTIONS['optionFollowersFrom'] = $row['optionFollowersFrom'];
        $this->OPTIONS['optionFollowersTo'] = $row['optionFollowersTo'];
        $this->OPTIONS['optionFollowFrom'] = $row['optionFollowFrom'];
        $this->OPTIONS['optionFollowTo'] = $row['optionFollowTo'];
        $this->OPTIONS['type'] = $row['type'];

        if(!$this->OPTIONS['optionCheckUserFromDB'])
           $this->load_users_from_db();

        if($this->OPTIONS['optionAddLike'])
            $result['count'] = $result['count'] / 2;

        return $result;
    }

    private function check_token($account_id, $token){
        $url = "https://api.instagram.com/v1/users/$account_id?access_token=$token";
        $code ="";
        $json = $this->httpGet($url);
        $code = $json->meta->code;

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

    public function set_parsing_status($status){
        $id = $this->TASK_ID;
        $qr_result = mysql_query("UPDATE tasks SET parsingStatus=$status WHERE id=$id")
        or die(mysql_error());
    }

    // меняем индекс текущего токена
    public function change_token(){
        $this->debug($this->TOKEN_ARRAY[$this->TOKEN_INDEX]);
        $this->TOKEN_INDEX = ($this->TOKEN_INDEX + 1) % count($this->TOKEN_ARRAY);
    }

    // если выпала капча, или токен просто устарел, обновляем его через каспер
    public function update_token(){
        $index = $this->TOKEN_INDEX;
        $token = $this->TOKEN_ARRAY[$index];
        $file = __DIR__ . "/Casper/auth.js";
        $file2 = __DIR__ . "/Casper/get_token.js";
        shell_exec("casperjs --web-security=no $file '" . $this->LOGIN . "' '" . $this->PASSWORD ."' '" .  $token['client'] ."' '" .  $this->ACCOUNT_ID . "' --proxy" . $this->PROXY . "= --proxy-type=socks5");
        $output = shell_exec("casperjs --web-security=no $file2 '" . $this->LOGIN . "' '" . $this->PASSWORD ."' '" . $token['client'] . "' --proxy" . $this->PROXY . " --proxy-type=socks5");
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
            $output = $this->httpPostReal($url, $params);
            $this->debug($output);
            $json = json_decode($output);
            if(!isset($json)){
                $this->debug('json is null - httpPost');
                $this->debug($params['url']);
                return null;
            }
            if($output === FALSE){
                $this->debug('json is false - httpPost');
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
        return null;
    }

    function httpGet($url){
            $output = $this->httpGetReal($url);
            $json = json_decode($output);
            if(!isset($json)){
                $this->debug('json is null - httpGet');
                return null;
            }
            if($output === FALSE){
                $this->debug('json is false -httpGet');
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
            $this->debug($output);
            $this->change_token();
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

    function httpGetReal($url)
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

//        $result=json_decode($output);

        curl_close($ch);

        return $output;
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


    public function close_task(){
        $id = $this->TASK_ID;
        $date = new DateTime('now');
        $d = $date->format("Y-m-d H:i:s");
        $qr_result = mysql_query("UPDATE tasks SET closedAt = '$d' WHERE id=$id")
            or die(mysql_error());
    }

    private function debug($message)
    {
        $filename = $this->TASK_ID;
        $file = "/var/www/instastellar/tasks/$filename";
        var_dump($message);
        file_put_contents("$file", "|" . json_encode($message) . "\n", FILE_APPEND);
    }
}