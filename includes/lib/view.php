<?php
/**
 * хелпер с обертками для функций класса view, для краткости записей
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage lib
 */

    if(false){
        $viewRef = new View();
    }
	/*
	 * создание текста ссылки для <a href="...
	 */
  function href($alias, $parameters = null){
  	global $viewRef;
  	echo $viewRef->href($alias, $parameters);
  }
 /*
 * выводит тайтл, заданный в параметрах вида для текущей страницы
 */
  function title(){
    global $viewRef;
    echo $viewRef->title();
  }
 /*
 * выводит meta keywords, заданный в параметрах вида для текущей страницы
 */
  function keywords(){
    global $viewRef;
    echo $viewRef->keywords();
  }
 /*
 * выводит meta description, заданный в параметрах вида для текущей страницы
 */
  function description(){
    global $viewRef;
    echo $viewRef->description();
  }
 /*
 * подключает все файлы ЯС, заданные в параметрах вида для текущей страницы
 * синоним includeJavascripts()
 */
  function javascript($filename){
    global $viewRef;
    echo $viewRef->javascript($filename);
  }
 /*
 * подключает все файлы ЯС, заданные в параметрах вида для текущей страницы
 * синоним javascript()
 */
  function includeJavascripts(){
    global $viewRef;
    echo $viewRef->javascript();
  }
 /*
 * подключает указанный файл стилей: подставляет тэг link и адрес HREF_CSS
 */
  function stylesheet($filename){
    global $viewRef;
    echo $viewRef->stylesheet($filename);
  }
 /*
 * подключает в шаблон блок или группу блоков из папки templates/blocks
 * если переданная строка соответствует имени файла, подключается файл
 * если соответствует имени блока страницы (группы файлов), заданного для этой
 * страницы в базе - подключаются все файлы этого блока 
 */
  function printPageBlock($blockName){
    global $viewRef;
    echo $viewRef->printPageBlock($blockName);
  }
 /*
 * подключает файл шаблона, заданный в контролере через view->setViewName()
 * если в контролере ничего не задано, подключается файл с именем как у контролера
 */
  function getPageContent(){
    global $viewRef;
    echo $viewRef->getPageContent();
  }
