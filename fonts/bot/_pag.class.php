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
// Пагинация
class pag{
	public function getPages($count,$page,$link,$sub = '?'){
		$count_pages = ceil($count);
		$active = $page;
		$count_show_pages = 10;
		$url = $link;
		$url_page = $url.$sub."page=";
		$pags = '';
		if ($count_pages > 1) {
			$left = $active - 1;
			$right = $count_pages - $active;
			if ($left < floor($count_show_pages / 2)) $start = 1;
			else $start = $active - floor($count_show_pages / 2);
			$end = $start + $count_show_pages - 1;
			if ($end > $count_pages) {
				$start -= ($end - $count_pages);
				$end = $count_pages;
				if ($start < 1) $start = 1;
			}
			if ($active != 1) {
				$pags .= '<li><a href="'.$url.'"><i class="fa fa-angle-double-left"></i></a></li>';
				$pags .= '<li><a href="'.(($active == 2)? $url : $url_page.($active - 1)).'"><i class="fa fa-angle-left"></i></a></li>';
			}
			for ($i = $start; $i <= $end; $i++) {
				if ($i == $active) {
					$pags .= '<li class="active"><a>'.$i.'</a></li>';
				}else {
					$pags .= '<li><a href="'.(($i == 1)? $url : $url_page.$i).'">'.$i.'</a></li>';
				}
			}
			if ($active != $count_pages) {
				$pags .= '<li><a href="'.$url_page.($active + 1).'"><i class="fa fa-angle-right"></i></a></li>';
				$pags .= '<li><a href="'.$url_page.$count_pages.'"><i class="fa fa-angle-double-right"></i></a></li>';
			}
		}
		return $pags;
	}
}