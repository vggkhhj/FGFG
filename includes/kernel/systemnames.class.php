<?php
/**
 * Фабрика имен файлов и объектов сайта
 * Создает имена объектов сайта
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage kernel
 */
class SystemNames{
  ///////////////////////////////////////////////////
  //плагины
  //////////////////////////////////////////////////
	public static function pluginAdminFile($pluginName){
    $pluginName = strtolower($pluginName);
		return DIR_PLUGINS . $pluginName . '/' . $pluginName . '.admin.class.php';
	}
  public static function pluginAdminClass($pluginName){
    return $pluginName . 'Admin';
  }
  public static function pluginFile($plugin){
    $plugin = strtolower($plugin);
    return DIR_PLUGINS . $plugin . '/' . $plugin . '.class.php';
  }
  public static function pluginClass($plugin){
    return $plugin . 'Plugin';
  }
  public static function pluginModelFile($plugin){
    $plugin = strtolower($plugin);
    return DIR_PLUGINS . $plugin . '/' . $plugin . '.model.class.php';
  }
  public static function pluginModelClass($plugin){
    return $plugin . 'Model';
  }
  ///////////////////////////////////////////////////
  //вид
  //////////////////////////////////////////////////
  public static function mainTemplateFile($fileName, $filePath){
    return Util::glueWithSlash($filePath, strtolower($fileName) . '.tpl');
  }
  public static function blockBlocksFile($fileName){
    return DIR_BLOCKS . strtolower($fileName) . '/' . strtolower($fileName) . '.tpl';
  }
  public static function blockTemplatesFile($fileName){
    return DIR_TPL_BLOCKS . strtolower($fileName) . '.tpl';
  }
  public static function blockFilePath($fileName, $filePath = ''){
    return Util::glueWithSlash($filePath, strtolower($fileName) . '.tpl');
  }
  public static function blockControllerFile($blockName){
    $blockName = strtolower($blockName);
    return DIR_BLOCKS . $blockName . '/' . $blockName . '.class.php';
  }
  public static function langFilePath($className){
	$className = strtolower($className);
	$filePathString = DIR_LANG . '%s' . '.' . $className . '.lang.php';
	//проверить, что язык задан правильно
	$filePath = sprintf($filePathString, $GLOBALS['configSiteLang']);
	if(!file_exists($filePath))
		$filePath = sprintf($filePathString, 'ru');
	return $filePath;
  }
  ///////////////////////////////////////////////////
  //ошибки
  //////////////////////////////////////////////////
  public static function errorTemplate404(){
    return DIR_TPL . '_404.tpl';
  }
  public static function errorTemplateForbidden(){
    return DIR_TPL . '_forbidden.tpl';
  }
  public static function errorTemplateError(){
    return DIR_TPL . '_servererror.tpl';
  }
  public static function errorTemplate423(){
    return DIR_TPL . '_423.tpl';
  }
}