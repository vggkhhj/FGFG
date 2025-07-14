<?php
// include './logger.php';
ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки

session_start();
ob_start();

if (function_exists('date_default_timezone_set'))
    date_default_timezone_set('Europe/Moscow');

if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])){
	$host = parse_url($_SERVER['HTTP_REFERER']);
    $host = $host['host'];
	if ($host != $_SERVER['HTTP_HOST']) {
		$time_cookie = time() + (86400 * 15);
		setcookie('httpref', $host, $time_cookie, '/');
	}
}

function __autoload($name)
{
	require 'classes/_'.$name.'.class.php';
}

$db = new db();
new router($db);
?><?php
/**
* Выдает нужный контролллер
*/
class router
{
	public $db;
	function __construct($db)
	{
		$this->db = $db;
		$path = parse_url($_SERVER['REQUEST_URI']);//Парсим строку запроса
		$url = explode('/', $path['path']);//Превращаем её в массив
		if(isset($url[1]) && !empty($url[1])){
			if(!array_key_exists($url[1], array('404'=>true,'index'=>true))){
				$ctrl = 'controllers/_'.$url[1].'Ctrl.php';//Создаем ссылку для подключения
				if(file_exists($ctrl)){
					include $ctrl;
				}else include 'controllers/_404Ctrl.php';
			}else include 'controllers/_404Ctrl.php';
		}else include 'controllers/_indexCtrl.php';
	}
}