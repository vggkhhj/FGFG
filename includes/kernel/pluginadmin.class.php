<?php
/**
 * Админ плагина сайта
 * Базовый функционал админчасти плагина сайта
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage kernel
 */
abstract class PluginAdmin{
  /**
   * @var DB
   */
  protected $db;

  public function __construct(){
    $this->db = new DB(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
  }


  /**
   * Отображает форму настройки админчасти
   * @param string &$contents текущее содержимое (HTML) формы
   * @param string $schemeName имя схемы настройки плагина
   * @return void ничего
   */
	abstract function setupForm(&$contents, $schemeName);
  /**
   * Принимает отправленные с формы setupForm данные и разбирает их
   * @param array &$submitValues значения полей формы
   * @return void ничего
   */
  abstract function submitForm(&$submitValues);
  /**
   * Устанавливает схему настройки плагина в текущую БД
   * @param string $schemeName имя схемы
   * @return void ничего
   */
  abstract function installScheme($schemeName);
  /**
   * Возвращает данные настройки плагина из БД в виде массива
   * @param string $schemeName имя схемы
   * @return array ассоц. массив настроек плагина
   */
  abstract function getSetupData($schemeName);
}