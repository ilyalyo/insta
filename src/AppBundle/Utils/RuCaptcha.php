<?php
/*
$filename - Имя файла и локальный путь к нему. URL не поддерживается
$apikey   - Ваш API key
$rtimeout - таймаут проверки ответа
$mtimeout - максимальное время ожидания ответа

$is_verbose - false(commenting OFF),  true(commenting ON)

Дополнительные параметры капчи:
$is_phrase - 0 OR 1 - капча из двух или более слов
$is_regsense - 0 OR 1 - регистр ответа важен
$is_numeric -  0 OR 1 OR 2 OR 3 - 0 = параметр не задействован (значение по умолчанию) 1 = капча состоит только из цифр 2 = Капча состоит только из букв 3 = Капча состоит либо только из цифр, либо только из букв.
$min_len    -  0 если не ограничено, иначе обозначает минимальную длинну ответа
$max_len    -  0 если не ограничено, иначе обозначает минимальную длинну ответа
$language 	- 0 OR 1 OR 2  0 = параметр не задействован (значение по умолчанию) 1 = капча на кирилице 2 = капча на латинице

usage examples:
$text=recognize("/path/to/file/captcha.jpg","YOUR_KEY_HERE",true, "rucaptcha.com");

$text=recognize("/path/to/file/captcha.jpg","YOUR_KEY_HERE",false, "rucaptcha.com");

$text=recognize("/path/to/file/captcha.jpg","YOUR_KEY_HERE",false, "rucaptcha.com",1,0,0,5);

*/

function recognize(
    $filename,
    $apikey,
    $is_verbose = true,
    $domain="rucaptcha.com",
    $rtimeout = 5,
    $mtimeout = 120,
    $is_phrase = 0,
    $is_regsense = 0,
    $is_numeric = 0,
    $min_len = 0,
    $max_len = 0,
    $language = 0
)
{
    if (!file_exists($filename))
    {
        if ($is_verbose) echo "file $filename not found\n";
        return false;
    }
    $postdata = array(
        'method'    => 'post',
        'key'       => $apikey,
        'file'      => '@'.$filename,
        'phrase'	=> $is_phrase,
        'regsense'	=> $is_regsense,
        'numeric'	=> $is_numeric,
        'min_len'	=> $min_len,
        'max_len'	=> $max_len,
        'language'	=> $language

    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,             "http://$domain/in.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,     1);
    curl_setopt($ch, CURLOPT_TIMEOUT,             60);
    curl_setopt($ch, CURLOPT_POST,                 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,         $postdata);
    $result = curl_exec($ch);
    if (curl_errno($ch))
    {
        if ($is_verbose) echo "CURL returned error: ".curl_error($ch)."\n";
        return false;
    }
    curl_close($ch);
    if (strpos($result, "ERROR")!==false)
    {
        if ($is_verbose) echo "server returned error: $result\n";
        return false;
    }
    else
    {
        $ex = explode("|", $result);
        $captcha_id = $ex[1];
        if ($is_verbose) echo "captcha sent, got captcha ID $captcha_id\n";
        $waittime = 0;
        if ($is_verbose) echo "waiting for $rtimeout seconds\n";
        sleep($rtimeout);
        while(true)
        {
            $result = file_get_contents("http://$domain/res.php?key=".$apikey.'&action=get&id='.$captcha_id);
            if (strpos($result, 'ERROR')!==false)
            {
                if ($is_verbose) echo "server returned error: $result\n";
                return false;
            }
            if ($result=="CAPCHA_NOT_READY")
            {
                if ($is_verbose) echo "captcha is not ready yet\n";
                $waittime += $rtimeout;
                if ($waittime>$mtimeout)
                {
                    if ($is_verbose) echo "timelimit ($mtimeout) hit\n";
                    break;
                }
                if ($is_verbose) echo "waiting for $rtimeout seconds\n";
                sleep($rtimeout);
            }
            else
            {
                $ex = explode('|', $result);
                if (trim($ex[0])=='OK') return trim($ex[1]);
            }
        }

        return false;
    }
}
?>