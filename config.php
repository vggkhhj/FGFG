<?php
/**
 * Файл дополнительной конфигурации админки
 * 
 */

	error_reporting(E_ALL &~E_STRICT ^E_NOTICE ^E_WARNING);
	//error_reporting(E_ALL);
	define('ADM_MYSQL_TIMEZONE', '');
	define('ADM_PHP_TIMEZONE', '');
	if(!defined('DB_USE_MYSQLI')) define('DB_USE_MYSQLI', false);

 