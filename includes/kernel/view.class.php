<?php
/**
 * Вид сайта
 * Предоставляет функционал по отображению внешнего вида сайта
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage kernel
 */
class ViewAbstract{
  protected $viewFile = '';
  /**
   * имя главного шаблона-каркаса страницы
   * @access protected
   * @var string
   */
  protected $mainTpl = '_main';
  /**
   * путь к файлу главного шаблона
   * @access protected
   * @var string
   */
  protected $mainTplPath = DIR_TPL;
  /**
   * имя текущего шаблона
   * @access protected
   * @var string
   */
  protected $viewName = '';
  /**
   * путь к файлу текущего шаблона
   * @access protected
   * @var string
   */
  protected $viewNamePath = DIR_TPL;
  /**
   * массив блоков данных шаблона (разные меню и т.д.)
   * @access protected
   * @var string
   */
  protected $dataBlock = null;
  /**
   * массив блоков страницы; содержит имена шаблонов, которые подключаются в каждом блоке
   * @access protected
   * @var string
   */
  protected $pageBlock = null;
  /**
   * ссылка на маршрутизатор
   * @access protected
   * @var string
   */
  protected $router = null;
  /**
   * ссылка на менеджер событий
   * @access protected
   * @var string
   */
  protected $event = null;
  /**
   * массив переменных, доступных в шаблоне
   * @access protected
   * @var string
   */
  protected $templateVar = null;
  /**
   * файлы яваскрипта
   * @access protected
   * @var string
   */
  protected $js = null;
  /**
   * тайтл страницы
   * @access protected
   * @var string
   */
  protected $headTitle = '';
  /**
   * ключевики
   * @access protected
   * @var string
   */
  protected $headKeywords = '';
  /**
   * дескриптион
   * @access protected
   * @var string
   */
  protected $headDescription = '';
  /**
   * конструктор
   */
  public function __construct(){
    $this->dataBlock = Array();
    $this->router = Router::getInstance();
    $this->event = EventDispatcher::getInstance();
    
    $this->templateVar = Array();
    $this->pageBlock = Array();
    $this->js = Array();
  }
  
  /////////////////////////////////////////////////////////////
  //блоки на странице
  /////////////////////////////////////////////////////////////
  
  /**
   * Добавляет новый блок
   * @param string $pageBlock имя группы блоков
   * @param string $includeName имя блока
   * @return void ничего
   */
  public function pageBlockPush($pageBlock, $includeName){
    $this->pageBlock[$pageBlock][] = $includeName;
  }
  /**
   * Добавляет новый блок в начало группы
   * @param string $pageBlock имя группы блоков
   * @param string $includeName имя блока
   * @return void ничего
   */
  public function pageBlockPushFirst($pageBlock, $includeName){
    array_unshift($this->pageBlock[$pageBlock], $includeName);
  }
  /**
   * Устанавливает группу блоков
   * @param string $blockName имя группы блоков
   * @param array $pageBlock новые блоки группы
   * @return void ничего
   */
  public function setPageBlock($blockName, $pageBlock){
    $this->pageBlock[$blockName] = $pageBlock;
  }
  /**
   * Возвращает блоки указанной группы
   * @param string $blockName имя группы блоков
   * @return array массив блоков группы
   */
  public function getPageBlock($blockName){
    return $this->pageBlock[$blockName];
  }
  /**
   * Возвращает все группы блоков текущей страницы
   * @return array массив групп блоков страницы
   */
  public function getPageBlocks(){
    return $this->pageBlock;
  }
  /**
   * Заменяет один блок указанной группы другим
   * @param string $volumeName имя группы блоков
   * @param string $findWhat имя заменяемого блока 
   * @param string $replaceWith имя нового блока 
   * @return void ничего
   */
  public function replacePageBlock($volumeName, $findWhat, $replaceWith){
    $oldBlockIndex = array_search($findWhat, $this->pageBlock[$volumeName]);
    $this->pageBlock[$volumeName][$oldBlockIndex] = $replaceWith;
  }
  /**
   * Удаляет блок из группы
   * @param string $pageBlock имя группы блоков
   * @param string $includeName имя удаляемого блока 
   * @return void ничего
   */
  public function pageBlockRemove($pageBlock, $includeName){
    $index = array_search($includeName, $this->pageBlock[$pageBlock]);
    if($index !== false)
      array_splice($this->pageBlock[$pageBlock], $index, 1);
  }
  /**
   * Отображает блок или группу на странице
   * @param string $blockName имя блока или группы блоков
   * @param string $filePath путь к каталогу, в котором находится шаблон блока 
   * @return void ничего
   */
  public function printPageBlock($blockName, $filePath = ''){
    //открываем переменные для шаблона
    foreach($this->templateVar as $key=>$val)
      global $$key;
    if(array_key_exists($blockName, $this->pageBlock))
      foreach($this->pageBlock[$blockName] as $includeName)
      {
        //если такой темплейт есть в блоках
        $blockFile = SystemNames::blockBlocksFile($includeName);
        if(file_exists($blockFile))
          include $blockFile;
        else{
          //если такой темплейт есть в темплейтах
          $blockFile = SystemNames::blockTemplatesFile($includeName);
          if(file_exists($blockFile))
            include $blockFile;
        }
      }
    else
    {
      //если задан прямой путь к блоку
    	if(!empty($filePath))
    	  $blockFile = SystemNames::blockFilePath($blockName, $filePath);
    	else
    	//иначе - это часть другого темплейта (блоки с контроллерами всегда задаются через массив вида)
        $blockFile = SystemNames::blockTemplatesFile($blockName);
      if(file_exists($blockFile))
        include $blockFile;
    }
  }
  
  /////////////////////////////////////////////////////////////
  //отображение
  /////////////////////////////////////////////////////////////
  
  /**
   * Устанавливает внутренний шаблон страницы
   * @param string $fileName имя шаблона
   * @param string $filePath путь к каталогу, в котором находится шаблон 
   * @return void ничего
   */
  public function setInnerTemplate($fileName, $filePath = ''){
  	$this->viewName = $fileName;
  	if(!empty($filePath))
  	  $this->viewNamePath = $filePath;
  }
  /**
   * Устанавливает внешний шаблон (каркас) страницы
   * @param string $fileName имя шаблона
   * @param string $filePath путь к каталогу, в котором находится шаблон 
   * @return void ничего
   */
  public function setMainTemplate($fileName, $filePath = ''){
  	$this->mainTpl = $fileName;
    if(!empty($filePath))
      $this->mainTplPath = $filePath;
  }
  /**
   * Производит отображение сайта
   * @return void ничего
   */
  public function display(){
  	if(DEBUG_MODE)
  	  echo 'DEBUG MODE';
    $viewFile = SystemNames::mainTemplateFile($this->mainTpl, $this->mainTplPath);
    if(file_exists($viewFile))
    {
    	global $viewRef;
    	$viewRef = $this;
 	  	include_once DIR_LIB . 'view.php';
      //открываем переменные для шаблона
      foreach($this->templateVar as $key=>$val)
      {
        global $$key;
        $$key = $val;
      }
	  if(!empty($this->headers))
		$this->sendHeaders();
      include_once($viewFile);
    }
  }
  /**
   * Отображает внутренний шаблон страницы
   * @return void ничего
   */
  public function getPageContent(){
  	$this->event->fire('onBeforeGetPageContent', $this);
    $viewFile = $this->viewNamePath . strtolower($this->viewName) . '.tpl';
	//добавить вариант с .аякс
	if(!file_exists($viewFile))
		$viewFile = $this->viewNamePath . strtolower($this->viewName) . '.ajax.tpl';
    if(file_exists($viewFile))
    {
      //открываем переменные для шаблона
      foreach($this->templateVar as $key=>$val)
        global $$key;
      
      include_once($viewFile);
    }
    else
      echo "Визуальное представление для данной страницы не задано (не найден вид \"" . $this->viewName . "\")";
    $this->event->fire('onAfterGetPageContent', $this);
  }
  /////////////////////////////////////////////////////////////
  //тэгозаменители
  /////////////////////////////////////////////////////////////

  /**
   * подключает указанный файл стиля
   * @return string тэг подключения стиля
   */
  public function stylesheet($filename){
    return '<link rel="stylesheet" href="' . HREF_CSS . $filename . '">';
  }
  /**
   * добавляет яваскрипт-файл во внутренний массив
   * @return void ничего
   */
  public function addJavascript($js){
    //если расширение файла не указано, то добавляем
    $js = preg_match('/\.js$/', $js) ? $js : $js . '.js';
    $this->js[] = $js;
  }
  /**
   * устанавливает значение тайтла страницы
   * @return void ничего
   */
  public function setTitle($title){
    $this->pageTitle = $title;
  }
  /**
   * возвращает значение тайтла страницы
   * @return string тэг подключения стиля
   */
  public function getTitle(){
    return $this->pageTitle;
  }
  /**
   * отображает тайтл страницы
   * @return string тэг тайтла
   */
  public function title(){
    return '<title>' . $this->pageTitle . '</title>';
  }
  /**
   * устанавливает значение ключевых слов страницы
   * @return void ничего
   */
  public function setKeywords($keywords){
    $this->pageKeywords = $keywords;
  }
  /**
   * возвращает значение ключевых слов страницы
   * @return string ключевые слова
   */
  public function getKeywords(){
    return $this->pageKeywords;
  }
  /**
   * отображает ключевые слова страницы
   * @return string мета-тэг keywords
   */
  public function keywords(){
    return '<META name="keywords" content="' . $this->pageKeywords . '">';
  }
  /**
   * отображает ключевые слова страницы
   * @return string мета-тэг keywords
   */
  public function setDescription($description){
    $this->pageDescription = $description;
  }
  public function getDescription(){
    return $this->pageDescription;
  }
  public function description(){
    return '<META name="Description" content="' . $this->pageDescription . '">';
  }
  
  public function javascript($filename = ''){
    $mask = "<SCRIPT src='" . HREF_JS . "%s' LANGUAGE='JavaScript' type='text/javascript'> </SCRIPT>\n";
    if(empty($filename) && count($this->js)>0)
    {
      $output = '';
      foreach($this->js as $js)
        $output .= sprintf($mask, $js);
      return $output;
    }
    else
      if(!empty($filename))
        return sprintf($mask, $filename);
  }
  
  public function href($alias, $parameters = null){
    return $this->router->makeUrl($alias, $parameters);
  }
  /**
   * Добавляет новый хедер к текущему списку (но НЕ отправляет хедеры браузеру!)
   * @param string $directive новый хедер
   * @return void ничего
   */
  public function header($directive){
    $this->headers[] = $directive;
  }
  /**
   * Отправляет хедер или список хедеров браузеру
   * @param mixed $headers хедер или массив хедеров
   * @return void ничего
   */
  public function sendHeaders($headers = null){
    if(empty($headers))
      foreach($this->headers as $header)
        header($header);
    else
      if(is_array($headers))
        foreach($headers as $header)
          header($header);
      else
        header($headers);
    //exit;
  }
  
  public function redirect($url){
    $this->header('Location: ' . $url);
    $this->sendHeaders();
  }
  
  //переменные для шаблона -------------------------------------------------------------------------
  
  public function setTemplateVar($key, $value){
    $this->templateVar[$key] = $value;
  }
  
  public function getTemplateVar($key){
    return $this->templateVar[$key];
  }
}