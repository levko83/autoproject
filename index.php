<?php
/*
$realm = 'forbidden';

$users = array('admin' => 'mypass', 'guest' => 'guest', 'test' => 'test');


if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized'); 
    header('WWW-Authenticate: Digest realm="'.$realm.
           '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');

    die('Текст, отправляемый в том случае, если пользователь нажал кнопку Cancel');
}


// анализируем переменную PHP_AUTH_DIGEST
if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) ||
    !isset($users[$data['username']]))
    die('Неправильные данные!');


// генерируем корректный ответ
$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

if ($data['response'] != $valid_response)
    die('Неправильные данные!');

// ok, логин и пароль верны
// echo 'Вы вошли как: ' . $data['username'];


// функция разбора заголовка http auth
function http_digest_parse($txt)
{
    // защита от отсутствующих данных
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();
    $keys = implode('|', array_keys($needed_parts));

    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
        unset($needed_parts[$m[1]]);
    }

    return $needed_parts ? false : $data;
}*/

date_default_timezone_set('Europe/Berlin');
// die($_SERVER['HTTP_HOST']);
header("Content-Type: text/html; charset=UTF-8");
define('debug',false);

if (!debug){
	error_reporting(0);
} else {
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 'On');
}

try {
	require('application/config/init.php');
	$controller = new Dispatcher();
	$controller->process();
} catch (Exception $exception) {
	if (debug){
		error404($exception);
	}
	else {
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		$controller = new Dispatcher();
		$controller->process('/error404');
		exit();
	}
}

$arr = get_defined_vars();

// print $b
// print("<pre>");
// print_r($accountData);


?>