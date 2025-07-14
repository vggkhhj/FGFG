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
* Класс конфигурация сайта
*/
class config
{
	// База данных
	public $hostDB = "localhost";//Хост Базы
	public $userDB = "finloto_leonid1";//Логин пользователя Базы
	public $passDB = "v}Q)zxDhJGUf";//Пароль от Базы
	public $baseDB = "finloto_568543468";//Имя Базы

    // VK APP
    public $client_id = '6362268'; // ID приложения
    public $client_secret = '17TSDrsbF6Yj0lfekrZe'; // Защищённый ключ
    public $redirect_uri = 'https://gigpay.ru/login'; // Адрес сайта

    // Purse
    public $yandex = "410014663399217";
    public $qiwi = "+79096866952";
    public $webmoney = "ваш вм";

}