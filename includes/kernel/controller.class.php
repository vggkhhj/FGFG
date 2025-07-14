<?php
/**
 * Базовый контроллер сайта
 * Предоставляет базовый функционал и возможности по управлению сайтом
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage kernel
 */
abstract class ControllerAbstract{
  /**
   * Ссылка на объект роутера
   * @access protected
   * @var Router
   * 
   */
  public $router = null;
  /**
   * Объект вида
   * @access public
   * @var View
   */
  protected $view = null;
  /**
   * Ссылка на объект модели
   * @access protected
   * @var Model
   */
  protected $model = null;
  /**
   * Ссылка на объект менеджера событий
   * @access protected
   * @var EventDispatcher
   */
  protected $event = null;
  /**
   * Имя текущего класса (контроллера страницы)
   * @access protected
   * @var string
   */
  protected $name = '';
  /**
   * Блоки данных
   * @access protected
   * @var array
   */
  protected $dataBlock = null;
  /**
   * Массив хлебных крошек
   * @access protected
   * @var array
   */
  protected $breadcrumbs = null;
  /**
   * Объект пользователя
   * @access protected
   * @var UserAbstract
   */
  public $user = null;
  /**
   * Массив страниц сайта
   * @access protected
   * @var array
   */
  protected $sitePages = null;
  /**
   * Псевдоним текущей страницы
   * @access protected
   * @var string
   */
  protected $alias = null;
  /**
   * Массив общих переменных
   * @access protected
   * @var array
   */
  protected $commonVar = Array();
    /**
     * Конструктор 
     */
  public function __construct(){
    $this->model = Model::getInstance();
    $this->router = Router::getInstance();
    $this->view = new View($this->router);
    $this->event = EventDispatcher::getInstance();
    //задать имя шаблона по умолчанию - то же, что и контролера
    $className = get_class($this);
    $this->name = strtolower(substr($className, 0, strpos($className, 'Controller')));
    $this->view->setInnerTemplate($this->name);
    //параметры страниц
    $this->sitePages = $this->router->getSitePages();
    //псевдоним текущей страницы
    $this->alias = $this->router->getSelfAlias();
    //параметры текущей страницы
    $thisPage = $this->sitePages[$this->alias];
    //плагины страницы
    $this->registerPagePlugins($this->alias);
    //мета-таги
    $this->view->setTitle(stripslashes($thisPage['html_title']));
    $this->view->setKeywords(Util::removeQuotes(stripslashes($thisPage['html_keywords'])));
    $this->view->setDescription(Util::removeQuotes(stripslashes($thisPage['html_description'])));
    //блоки страницы
    $this->setupPageBlocks($this->alias);
    //путь к странице
    $this->setupBreadcrumbs($this->alias);
	
	
	//заглушка для парсера IDE
	if (false) {
		$this->view = new View();
		$this->model = Model::getInstance();
		$this->router = Router::getInstance();
		$this->user = new MySiteUser();
	}
  }
  /**
   * Подготовка к выводу шаблонов на экран, последние действия контроллера
   * @return void ничего
   */
  public function prepareToDisplay(){
    //передать крохи
    if(!empty($this->breadcrumbs))
      $this->view->setTemplateVar('breadcrumbs', $this->breadcrumbs);
    //вызвать контроллеры подключенных к странице блоков
    $this->initPageBlocks();
  }
  /**
   * Стартовая функция контроллера, все стартовые действия прописываются здесь
   * @return void ничего
   */
  abstract public function main();
  /**
   * Возвращает ссылку на объект вида
   * @return object ссылка на объект вида
   */
  public function &getView(){
    return $this->view;
  }
  /**
   * Задает блок данных
   * @param string $blockName имя группы блоков
   * @param string $key имя блока данных
   * @return void ничего
   */
  public function setBlockData($blockName, $key){
    $this->view->setDataBlock($blockName, $this->dataBlock[$blockName][$key]);
  }
  /**
   * Подключает блоки данных указанной страницы
   * @param string $pageAlias псевдоним страницы
   * @return void ничего
   */
  public function setupPageBlocks($pageAlias){
    if(!empty($this->sitePages[$pageAlias]['page_blocks'])){//если для текущей страницы заданы блоки, иначе использовать блоки родителя
      $ar = explode(PAGE_GROUP_DELIMITER, $this->sitePages[$pageAlias]['page_blocks']);
      foreach($ar as $a){
        if(empty($a)) continue;
        $subBlocks = explode(PAGE_BLOCK_DELIMITER, $a);
        if(count($subBlocks)>1){
          $blockName = array_shift($subBlocks);
        }else
          $blockName = $subBlocks[0];
        $this->view->setPageBlock($blockName, $subBlocks);
      }
    }else//блоки не заданы, использовать блоки родителя
      if(!empty($this->sitePages[$pageAlias]['parent_alias']))
        $this->setupPageBlocks($this->sitePages[$pageAlias]['parent_alias']);
  }
  /**
   * Подключает плагины указанной страницы
   * @param string $pageAlias псевдоним страницы
   * @return void ничего
   */
  public function registerPagePlugins($pageAlias){
  	if(!empty($this->sitePages[$pageAlias]['plugins'])){
  	  $schemes = Util::dbToArray($this->model->getPluginShemes(), 'item_key');
  		$pagePlugins = explode(';', $this->sitePages[$pageAlias]['plugins']);
  		$plugins = Array();
  		foreach($pagePlugins as $pp)
  		  $plugins[] = Array('plugin'=>$schemes[$pp]['plugin_name'], 'scheme'=>$pp);
      $this->model->initPlugins($plugins);
  	}
  }
  /**
   * Запускает контроллеры всех блоков текущей страницы
   * @return void ничего
   */
  public function initPageBlocks(){
    //вызвать контроллеры для всех блоков, которые будут на странице
    $blocks = $this->view->getPageBlocks();
    foreach($blocks as $b)
      foreach($b as $s){
        $fileName = SystemNames::blockControllerFile($s);
        if(file_exists($fileName)){
          include_once($fileName);
          $sblock = $s . 'Block';
          $sub = new $sblock($this);
        }
      }
  }
  /**
   * Формирует начальные хлебные крошки указанной страницы
   * @return void ничего
   */
  public function setupBreadcrumbs($pageAlias){
    if(!empty($this->sitePages[$pageAlias]['parent_alias'])){
      $this->setupBreadcrumbs($this->sitePages[$pageAlias]['parent_alias']);
    }
    $this->breadcrumbs[] = Array('title'=>$this->sitePages[$pageAlias]['short_title'], 'href'=>$this->router->makeUrl($pageAlias));
  }
  /**
   * Возвращает данные, разбитые по страницам (пейджер).
   * используемые ключи:
   * select - секция запроса, напр: 'select'=>'field1, field2'; если не указано - используется *;
   * from - секция запроса, напр: 'from'=>'table1, table2'; обязательный ключ;
   * where - секция запроса, напр: 'where'=>'a<b AND b>c';
   * order - секция запроса, напр: 'order'=>'id DESC';
   * group - секция запроса, напр: 'group'=>'field1';
   * limits - массив вариантов макс. количества рядов на странице, напр: 'limits'=>Array(5, 10, 20);
   * row_count - общее количество записей в таблице;
   * select_count - количество обрабатываемых записей (если не указано row_count);
   * rows - имя переменной, в которую будут переданы ряды страницы;
   * pages - имя переменной, в которую будут сохранены настройки пейджера. 
   * Если указаны ключи rows и/или pages, соответствующие переменные передаются в Вид.
   * @param array $params параметры пейджера
   * @return array массив рядов (ключ 'rows') и массив параметров пейджера (ключ 'pages')
   */
  public function getContentPage($params){
    //вывести все закладки постранично
    $res = $this->model->getContentPage($params);

    if(!empty($res)){
	    if(isset($params['rows']))
	      $this->view->setTemplateVar($params['rows'], $res['rows']);
	    if(isset($params['pages']))
	      $this->view->setTemplateVar($params['pages'], $res['pages']);
    }

    return $res;
  }
  
  /*public function useModule($moduleName, $params){
    $moduleFile = DIR_CTR_MOD . $moduleName . '.class.php';
    if(file_exists($moduleFile)){
      $this->view->useModule();
      $this->view->setViewName($moduleName);
      include($moduleFile);
      $className = $moduleName . 'Module';
      $class = new $className($this, $params);
    }else
      Error::serverError('module not found: ' . $moduleFile);
  }*/
  /**
   * Посылает письмо по указанному адресу. Замена функции mail
   * @param string $to адрес получателя
   * @param string $title тема письма 
   * @param string $msg текст письма 
   * @param string $replyTo обратный адрес 
   * @param bool $isHtml письмо в формате HTML 
   * @return void ничего
   */
  public function sendMail($to, $title, $msg, $replyTo = '', $isHtml = false){
    if(USE_SENDMAIL){
      include_once DIR_CLASSES . 'class.phpmailer.php';
      $mail = new PHPMailer();
      $mail->From = $replyTo;
      $mail->FromName = 'Администрация сайта ' . HREF_DOMAIN;
      $mail->IsHTML($isHtml);
      $mail->Subject = $title;
      $mail->AltBody = $msg;
      $mail->Body = $msg;
      $mail->AddAddress($to);
      if(!$mail->Send())
        echo "Mailer Error: " . $mail->ErrorInfo;
    }else{
      mail($to, $title, $msg);
    }
  }
  /**
   * Посылает письмо по указанному адресу, функция авторассылки
   * @param string $baseKey код-ключ тела письма 
   * @param string $to адрес получателя
   * @return void ничего
   */
  public function sendMailFromBase($baseKey, $to){
    $mailData = $this->model->getMailData($baseKey);
    $this->sendMail($to, $mailData['theme'], $mailData['message'], $mailData['reply_to'], $mailData['isHTML']);
  }
  /**
   * Производит перенаправление на указанную страницу (посылает header Location)
   * @param string $alias псевдоним страницы 
   * @param array $parameters параметры страницы
   * @return void ничего
   */
  public function redirectTo($alias, $parameters = null){
    $this->view->sendHeaders('Location: ' . $this->router->makeUrl($alias, $parameters));
  }
  
  /**
   * Устанавливает значение общей переменной
   * @param string $key имя переменной 
   * @param mixed $value новое значение
   * @return void ничего
   */
  public function setCommonVar($key, $value){
    $this->commonVar[$key] = $value;
  }
  /**
   * Возвращает значение общей переменной
   * @param string $key имя переменной 
   * @return mixed значение переменной
   */
  public function getCommonVar($key){
    if(isset($this->commonVar[$key]))
      return $this->commonVar[$key];
    else
      return null;
  }
  /**
   * Проверяет присланные с формы данные авторизации, проводит авторизацию
   * @return void ничего
   */
  public function getAuthSubmit(){
    $this->event->fire('onControllerGetAuthSubmit', $this);
  }
  /**
   * Устанавливает уровень пользователя, необходимый для просмотра данной страницы.
   * Если текущий уровень пользователя ниже необходимого, выводится сообщение об ошибке.
   * @param mixed $level ключ или код уровня пользователя 
   * @return void ничего
   */
  public function setAccessLevel($level){
    $requiredLevel = $this->accessLevel[$level];
    $userLevel = $this->accessLevel[$this->user->getData('type')];
    if($userLevel < $requiredLevel)
      Error::forbidden();
  }
  
}