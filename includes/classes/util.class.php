<?php
/**
 * Библиотека полезностей
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage classes
 */
class Util{
  /**
  * Форматирует TIMESTAMP-строку
  * @param string $timestamp исходная TIMESTAMP-строка 
  * @param bool $showTime показывать ли время 
  * @param bool $showSeconds показывать ли секунды  
  * @return string форматированная строка
  */
  public static function fromTimestamp($timestamp, $showTime = true, $showSeconds = false){
    $parts = explode(' ', $timestamp);
    list($year, $month, $day) = explode('-', $parts[0]);
    list($hours, $minutes, $seconds) = explode(':', $parts[1]);
    if($showSeconds)
      $seconds = ':' . $seconds;
    else
      $seconds = '';
    if($showTime)
      $time = sprintf(' %s:%s', $hours, $minutes) . $seconds;
    else
      $time = '';
    $date = sprintf('%s.%s.%s', $day, $month, $year) . $time;
    return $date;
  }
  /**
  * Урезает строку до указанной длины и добавляет "..."
  * @param string $str исходная строка 
  * @param int $maxlen максимальная длина 
  * @return string форматированная строка
  */
  public static function fitString($str, $maxlen = 23){
    if(strlen($str)>$maxlen)
		#### return substr($str, 0, $maxlen-3) . '...';
		return preg_replace('/^(.{'.$maxlen.'}).*/uSs', '\1', $str);
    else
      return $str;
  }
  /**
  * Переводит текст ASCII в HTML с сохранением переносов и абзацев 
  * @param string $str исходная ASCII строка 
  * @return string форматированная HTML строка
  */
  public static function text2html($str){
    $str = str_replace("\r\n", "<br>", $str);
    $str = str_replace("\n", "<br>", $str);
    $str = str_replace("<br><br>", "<p>", $str);
    return $str;
  }
  /**
  * Превращает массив ассоц. массивов (как полученный из бд) в ассоц. массив ассоц. массивов с указанным ключом 
  * пример: из базы получен массив записей с полями id, f1, f2; нужно из него выбрать запись, 
  * но ее индекс в массиве неизвестен; зато известно значение ее id - тогда нужно 
  * сделать dbToArray($array, 'id') и можно будет обратиться к нужной записи напрямую: $array[$id]
  * @param array &$dbResult исходный линейный массив 
  * @param string $key ключ из которого брать ключи для ассоц. массива 
  * @return array ассоц. массив
  */
  public static function dbToArray($dbResult, $key){
    $array = Array();
	if (!empty($dbResult))
		foreach($dbResult as $c)
		  $array[$c[$key]] = $c;
    return $array;
  }
  /**
  * Удаляет из строки обычные (двойные) кавычки
  * @param string $str исходная строка 
  * @return string строка без кавычек
  */
  public static function removeQuotes($str){
    $str = str_replace('"', '', $str);
    return $str;
  }
  /**
  * Возвращает соответствующий адресу на диске адрес http
  * @param string $filePath адрес файла или каталога на диске 
  * @return string адрес http
  */
  public static function dirToHref($filePath){
    $rootPos = strpos($filePath, DIR_ROOT)+strlen(DIR_ROOT);
    $fromRoot = substr($filePath, $rootPos);
    return HREF_DOMAIN . $fromRoot;
  }
  /**
  * Возвращает var_dump() переменной в строке, без вывода в браузер
  * @param mixed $var переменная 
  * @return string значение var_dump()
  */
  public static function varDumpToVar($var){
    ob_start();
    var_dump($var);
    $out = ob_get_clean();
    ob_end_clean();
    return $out;
  }
  /**
  * Возвращает адрес текущей страницы без GET параметров
  * @return string адрес страницы
  */
  public static function getThisAddress(){
  	return $_SERVER['SCRIPT_NAME'];
  }
  /**
  * Возвращает адрес текущей страницы с GET параметрами
  * @return string адрес страницы
  */
  public static function getThisAddressGet(){
    return self::getThisAddress() . substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?'));
  }
  /**
  * Склеивает две строки с помощью слэша
  * Полезно, когда есть две части адреса (например путь файла и имя), и неизвестно, какое из них
  * заканчивается/начинается на слэш; функция гарантирует, что между ними будет только один слэш.
  * @param string $leftPart левая часть строки 
  * @param string $rightPart правая часть строки
  * @return string левая и правая части, соединенные одним слэшем
  */
  public static function glueWithSlash($leftPart, $rightPart){
  	if(substr($leftPart, -1, 1)==='/')
  	  $leftPart = substr($leftPart, 0, -1);
    if(substr($rightPart, 0, 1)==='/')
      $rightPart = substr($leftPart, 1);
 	  return $leftPart . '/' . $rightPart;
  }
}