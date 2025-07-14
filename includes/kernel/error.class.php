<?php
/**
 * Ошибки сайта
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage kernel
 */
class Error{
  /**
   * Выдает страницу ошибки 404
   * @param string $pageName имя ненайденной страницы
   * @return void ничего
   */
  public static function page404($pageName = '')
  {
    header('HTTP/1.1 404 Not Found');
    include SystemNames::errorTemplate404();
    exit;
  }
  public static function controllerNotFound($controllerName = '')
  {
  	self::serverError('контроллер "' . $controllerName . '" не найден');
  }
  /**
   * Выдает страницу ошибки 403 - просмотр запрещен
   * @return void ничего
   */
  public static function forbidden(){
    header('HTTP/1.1 403 Forbidden');
    include SystemNames::errorTemplateForbidden();
    exit;
  }
  public static function moduleNotFound($moduleAlias){
    self::serverError('модуль не найден: ' . $moduleAlias);
  }
  public static function dataBlockNotFound($blockName){
    self::serverError('блок не найден: ' . $blockName);
  }
  /**
   * Выдает страницу ошибки 500 - внутренняя ошибка сервера
   * @param string $msg сообщение, которое необходимо вывести на экран
   * @return void ничего
   */
  public static function serverError($msg){
    header('HTTP/1.1 500 Internal Server Error');
    include SystemNames::errorTemplateError();
    exit;
  }
  
  /**
   * Выдает страницу ошибки 423 - Locked
   * @return void ничего
   */
  public static function page__423(){
    header('HTTP/1.1 423 Locked');
    include SystemNames::errorTemplate423();
    exit;
  }
}