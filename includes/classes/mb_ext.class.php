<?php
/**
 * Библиотека для работы с многобайтовыми кодировками
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage classes
 */
class Mb_ext{
	/**
	* Декодирует символы формата %u#### в строке
	* @param string $string исходная закодированная строка 
	* @return string декодированая строка
	*/
	public static function mb_urldecode($string) {
		$string = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($string));
		return html_entity_decode($string,null, DEFAULT_CHARSET);
	}
 
	/**
	* Возвращает подстроку
	* @param string $string входная строка 
	* @param int $start позиция начального символа
	* @param int $length длина выходной строки (необязательный)
	* @return string выходная строка
	*/
	public static function u_substr($string,$start){
		preg_match_all("/./su", $string, $ar);

		if(func_num_args() >= 3) {
			 $length = func_get_arg(2);
			 return join("",array_slice($ar[0],$start,$length));
		} else {
			 return join("",array_slice($ar[0],$start));
		}
	}
 
	/**
	* Возвращает подстроку
	* @param string $string входная строка 
	* @param int $start позиция начального символа
	* @param int $length длина выходной строки
	* @return string выходная строка
	*/
	public static function u_substr2($string,$start,$length){
		return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $start .'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $length .'}).*#s','$1', $string);
	}
	
	/**
	* pathinfo — Возвращает информацию о пути к файлу
	* @param string $path анализируемый путь
	* @param int $options
	* http://us1.php.net/manual/ru/function.pathinfo.php
	*/
	public static function pathinfo($path, $options=0) {
		preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im',$path,$m);
		if($m[1]) $ret['dirname']=$m[1];
		if($m[2]) $ret['basename']=$m[2];
		if($m[5]) $ret['extension']=$m[5];
		if($m[3]) $ret['filename']=$m[3];
		
		switch($options){
			case PATHINFO_DIRNAME : return $ret['dirname'];
			case PATHINFO_BASENAME : return $ret['basename'];
			case PATHINFO_EXTENSION : return $ret['extension'];
			case PATHINFO_FILENAME : return $ret['filename'];
			default : return $ret;
		}
	}

 
 
}