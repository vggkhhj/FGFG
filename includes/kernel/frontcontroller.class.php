<?php
/**
 * Базовый функционал сайта
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage kernel
 */
	
 
class FrontController{
  /**
   * Запускает сайт
   * @return void
   */
	
  public function run(){
  
		#### проверить или запустить сессию
	if(session_id()=='') session_start();
  
		#### получим языковые настройки
	$lang=$this->getLangSettings();
  
    //часовой пояс
	if(DEFAULT_TIMEZONE != '')
		date_default_timezone_set(DEFAULT_TIMEZONE);
    //менеджер событий
    $event = EventDispatcher::getInstance();
    //модель сайта
    $model = Model::getInstance();
		####
		$this->setLangSettings($lang,$model);
    //плагины сайта
    $plugins = $model->getSitePlugins();
    $model->initPlugins($plugins);
    //запустить маршрутизатор
    $requestedPath = $_SERVER['REQUEST_URI'];
    $router = Router::getInstance();
    $router->setRoute($requestedPath);
    //взять имя контролера и действие страницы из урл
    list($controllerName, $actionName) = $router->getControllerRequested();
    $event->fire('onAfterGetControllerRequested');
    //если имя не указано - такой контролер не найден, неверный путь
    if(empty($controllerName))
		//при эмуляции ошибки 404 - указать контроллер вручную
		if(USE_404_EMULATION)
			$controllerName = 'E404';
		else
			Error::page404($requestedPath);
    //попытаться подключить нужный контролер
    $event->fire('onBeforeSelectController', $controllerName);
    $controllerFile = DIR_CTR . strtolower($controllerName) . '.class.php';
	//если такого файла нет - поискать среди аякс-контроллеров
	if(!file_exists($controllerFile))
		$controllerFile = DIR_CTR . strtolower($controllerName) . '.ajax.class.php';
	//если и среди аякс-контроллеров файла нет, значит некорректное имя
    if(file_exists($controllerFile))
    {  
      include_once($controllerFile);
      $functionName = $controllerName . 'Controller';
      $controller = new $functionName();
      $event->fire('onBeforeControllerMain', $controller);
      //вызвать общие действия контроллера
      $controller->main();
      $event->fire('onBeforeControllerAction', $controller);
      //если для этого псевдонима задано действие, вызвать его
      if(!empty($actionName))
      $controller->$actionName();
      $controller->prepareToDisplay();
      $event->fire('onBeforeViewDisplay', $controller);
      //виду модель не доступна
      //$model->__destruct();
      //произвести вывод данных
      $controller->getView()->display();
      $event->fire('onAfterViewDisplay');
    }
    else
    //если файл контролера не нашелся - ошибка сервера
    Error::controllerNotFound($controllerName);
  }
  
    	/** Возвращает и запоминает языковые настройти */
	public function getLangSettings(){
		if(!empty($_COOKIE['lang'])){
			$Glang=addslashes($_COOKIE['lang']);
		}elseif(!empty($_GET['lang'])){
			$Glang=addslashes($_GET['lang']);
		}
		$locarray = array('ru','en','cs');
		if(!empty($Glang) && in_array($Glang, $locarray)){
			$lang = $Glang;
		}
		if(!empty($lang) && in_array($lang, $locarray)){
			$_SESSION['lang'] = $lang;
		}elseif(!empty($_SESSION['lang'])){
			$lang = $_SESSION['lang'];
		}else{
			$lang = $_SESSION['lang'] = $GLOBALS['configSiteLang'];
		}
		empty($lang)?($lang = $GLOBALS['configSiteLang']):(0);
		
		return $lang;
	}
	
	/** Устанавливает настройти */
	public function setLangSettings($lang, $model){
			// установим язык для сайта
		$_SESSION['lang']=$lang; // ?\? вообще-то не нужно, но логично
		if(isset($_SESSION['user']['lang']) && $_SESSION['user']['lang']!=$lang){
			$model->updateAssoc('my_admin_users',array('lang'=>$_SESSION['user']['lang']=$lang),$_SESSION['user']['id']);
		}
		$this->lang=$lang;
		$model->lang=$lang;
			// установим смещение времени для сайта
		if(empty($_SESSION['time']['utc_offset'])){
			$_SESSION['time']['utc_offset']=0;
		}
		$model->TZoffset=sprintf("%+d:00",$_SESSION['time']['utc_offset']);
	}
 
 
}