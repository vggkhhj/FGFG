<?php
/**
 * Роутер сайта
 * Реализует всю адресацию страниц сайта и связанные с этим функции
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage kernel
 */
class Router{
  /**
   * Уникальный экземпляр класса
   * @access private
   * @var object
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
	private static $instance;
  /**
   * Ссылка на объект модели
   * @access protected
   * @var object
   */
  protected $model = null;
  /**
   * Исходный адрес, разобранный на составляющие
   * @access protected
   * @var object
   */
  private $route = null;
  /**
   * Исходная строка адреса
   * @access protected
   * @var string
   */
  private $routeStr = '';
  private $routeSource = null;
  /**
   * имя контролера для текущей страницы, полученное по маске запрошенного пути
   * @access protected
   * @var string
   */
  private $controllerName = '';
  /**
   * имя действия для текущей страницы, полученное по маске запрошенного пути
   * @access protected
   * @var string
   */
  private $actionName = '';
  
  private $paramString = '';
  private $uriMask = '';
  private $alias = '';
  private $paramStringMask = '';
  private $parameters = null;
  private $getString = '';
  
  const URI_MASK = 'mask';//0;
  const CONTROLLER = 'controller';//1;
  const ACTION = 'action';//2;
  const ALIAS = 'alias';//3;
  const BLOCK_GET_PARAMETERS = 'block_get_parameters';
  /**
   * текущее состояние роутера
   * @access private
   * @var int
   */
  private $state = 0;
  /**
   * массив путей
   * @access private
   * @var array
   */
  private $routeData = null;
  /**
   * Устанавливает стартовый адрес роутера
   * @param string $route адрес
   * @return void ничего
   */
  public function setRoute($route){
    $this->routeStr = $route;
  }
  /**
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
  private function __construct(){
    $this->model = Model::getInstance();
    //$this->route = self::parseUrl($route);
    //$this->routeSource = $this->route;
    //$this->state = self::MODULE;
    $this->parameters = Array();
    $this->routeData = Util::dbToArray($this->model->getSitePages(), 'alias');//include_once(DIR_CONFIG . 'routes.php');
  }
  /**
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
  private function __clone(){}
  /**
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
  public static function getInstance(){
  	if(self::$instance === null)
  	  self::$instance = new self;
 	  return self::$instance;
  }
  /**
   * Возвращает данные страниц сайта
   * @return array массив ассоц. массивов страниц сайта
   */
  public function getSitePages(){
    return $this->routeData;
  }
  /**
   * Добавляет строку GET-параметров к строке адреса
   * @param string $uri строка адреса
   * @param array $getParams массив GET-параметров
   * @param bool $saveGet сохранять ли GET-параметры, которые уже содержатся в исходной строке
   * @return string новая строка адреса
   */
  public function addGetParams($uri, $getParams, $saveGet){
    if(empty($getParams)) return $uri;
    
    if(strpos($uri, '?')>-1){
      //параметры уже есть, необходимо их дополнить
      //$uri .= '&' . $getParams;
      //очистить адрес от параметров
      $uri = substr($uri, 0, strpos($uri, '?'));
      $gets = $getParams;
      if($saveGet){
        //перебрать все параметры GET и заменить значения в совпадающих параметрах (с теми же именами)
        $gets = $_GET;
        foreach($getParams as $kn=>$vn)
          $gets[$kn] = $vn;
      }
      //склеить новые параметры и прикрепить к адресу
      $sPar = Array();
      foreach($gets as $key=>$val)
        $sPar[] = $key . '=' . $val;
      $getStr = implode('&', $sPar);
      $uri .= '?' . $getStr;
    }
    else{
      //других параметров нет, достаточно склеить и прикрепить текущие
      $sPar = Array();
      foreach($getParams as $key=>$val)
        $sPar[] = $key . '=' . $val;
      $getStr = implode('&', $sPar);
      $uri .= '?' . $getStr;
    }
    return $uri;
  }
  /**
   * Формирует адрес страницы из ее псевдонима и параметров
   * @param string $alias псевдоним страницы
   * @param array $parameters массив параметров
   * @return string строка адреса
   */
  public function makeUrl($alias, $parameters = null){
    $routeData = $alias;
    //поддержка массива
    if(is_array($routeData)){
      $alias = $routeData['alias'];
      if(array_key_exists('parameters', $routeData))
        $parameters = $routeData['parameters'];
      else
        $parameters = null;
    }
    //GET
    $get = '';
    if(!empty($parameters) && array_key_exists('_GET', $parameters)){
      $get = $parameters['_GET'];
      $sPar = Array();
      foreach($get as $key=>$val)
        $sPar[] = $key . '=' . $val;
      $getStr = implode('&', $sPar);
      unset($parameters['_GET']);
    }
    
    $saveGet = false;
    if(!empty($parameters) && array_key_exists('_SAVE_GET', $parameters)){
      $saveGet = true;
      unset($parameters['_SAVE_GET']);
    }
    
    if($alias == 'self'){
      /*$thisUri = substr($this->routeStr, 1);//пропускаем первый символ, это слэш
      if(strpos($thisUri, '?')>-1)//есть GET
        $thisUri = substr($thisUri, 0, strpos($thisUri, '?'));
      $realUri = explode('/', $thisUri);
      $maskUri = explode('/', $this->routeStr);
      return HREF_DOMAIN . */
      $uri = substr($_SERVER['REQUEST_URI'], 1);//пропускаем первый символ, это слэш
      $uri = HREF_DOMAIN . $uri;
      $uri = $this->addGetParams($uri, $get, $saveGet);
      return $uri;
    }
    else{
      //найти маршрут по псевдониму
      if(empty($this->routeData[$alias]))
        return '';
      $route = $this->routeData[$alias];
      $uri = $route[self::URI_MASK];
      //подставить переданные параметры
      if(!empty($parameters))
        foreach($parameters as $key=>$val){
          $uri = str_replace(':' . $key, $val, $uri);
        }
      //удалить все, начиная с первого непереданного параметра (пропуск параметров запрещен)
      $uri = preg_replace('|:.+|', '', $uri);
      //убрать все лишние слэши
      //$uri = preg_replace('|/{2,}|', '/', $uri);
      //вернуть строку-урл
      $href = HREF_DOMAIN . $uri;
      $href = $this->addGetParams($href, $get, $saveGet);
      return $href;
    }
  }
  
  /*public static function parseUrl($route)
  {
    $route = parse_url($route);
    $path = $route['path'];
    $path = preg_replace('/^\//', '', $path);
    $path = preg_replace('/\/$/', '', $path);
    $parsed = !empty($path) ? explode('/', $path) : Array();
    return $parsed;
  }*/
  /**
   * Возвращает имена контроллера и действия, соответствующие текущему стартовому адресу
   * @return array линейный массив с именем контроллера и действия
   */
  public function getControllerRequested(){
    if($this->state !== self::CONTROLLER){//только если еще не определяли контроллер
        //удалить из строки ГЕТ-запрос
        $thisRoute = $this->routeStr;
        if(strpos($thisRoute, '?')>-1)
          $thisRoute = substr($thisRoute, 0, strpos($thisRoute, '?'));
          
      $matches = Array();
      /*
       * найти все входящие подстрокой в запрос маски путей
       * если масок больше одной, выбрать маску с максимальной длиной
       */
      //найти все входящие подстрокой в запрос маски путей
      foreach($this->routeData as $key=>$val){
        $controllerPath = $val[self::URI_MASK];
        //взять подстроку до начала параметров, если параметры не предусмотрены то взять все
        $paramPos = strpos($controllerPath, ':');
        if($paramPos>-1)
          $controllerPath = substr($controllerPath, 0, $paramPos);

        /*если подстрока пути к контроллеру содержится в вызванном ури 
         * в позиции 1 (после ведущего слэша), то этот контроллер и вызвали
        */
        if(@strpos($thisRoute, $controllerPath)===1){
          //необходимо проверить, что запрос завершается верно (фраза совпадает до слэша)
          if(substr($thisRoute, strlen($controllerPath), 1)==='/' ||
             substr($thisRoute, strlen($controllerPath)+1, 1)==='/')
            $matches[] = $key;
        }
        else
        //если запрос пустой (запрощена главная) и текущая итерация указывает на индекс-контроллер
          if($thisRoute==='/' && empty($controllerPath))
            $matches[] = $key;
      }
        
        //если маски найдены
        if(!empty($matches)){
        	
        	//если масок больше одной, выбрать маску с максимальной длиной
        	if(count($matches)>1){
				#### $val = '';
        		$val = array(self::URI_MASK=>0);
        		foreach($matches as $key)
        		  if(strlen($this->routeData[$key][self::URI_MASK])>strlen($val[self::URI_MASK]))
        		    $val = $this->routeData[$key];
        	}else
        	  $val = $this->routeData[$matches[0]];

        	//обработка данных для запрошенной страницы
          $this->controllerName = $val[self::CONTROLLER];
          $this->actionName = $val[self::ACTION];
          //проверить, есть ли в запросе ?, если есть то сохранить его и удалить из исходной строки
          $getPos = strpos($this->routeStr, '?');
          if($getPos>-1)
          {
			//проверить, разрешены ли гет-параметры для этой страницы, если нет - 404
			if(!empty($val[self::BLOCK_GET_PARAMETERS])){
				return Array('', '');
			}
            $this->getString = substr($this->routeStr, $getPos+1);
            $this->routeStr = substr($this->routeStr, 0, $getPos);
          }
          $this->uriMask = $val[self::URI_MASK];
          $this->alias = $val[self::ALIAS];
          $paramPos = strpos($this->uriMask, ':');
			 if($paramPos===false) $paramPos=strlen($this->uriMask);
          $this->paramStringMask = substr($this->uriMask, $paramPos-1);//текущая маска параметров ури
          $this->paramString = substr($this->routeStr, $paramPos);//текущая строка параметров ури
		  //сравнить количество параметров в маске с фактическим количеством параметров
		  $paramMaskTrimmed = substr($this->paramStringMask, 0, 1) === '/' ? substr($this->paramStringMask, 1) : $this->paramStringMask;
		  $maskParams = explode('/', $paramMaskTrimmed);
		  $paramStringTrimmed = substr($this->paramString, 0, 1) === '/' ? substr($this->paramString, 1) : $this->paramString;
		  $givenParams = explode('/', $paramStringTrimmed);
		  if(count($givenParams) > count($maskParams)){//передано больше параметров, чем предусмотрено маской адреса
			return Array('', '');
		  }
          $this->state = self::CONTROLLER;
        }else{
        	$this->controllerName = '';
        	$this->actionName = '';
        }
    }
    return Array($this->controllerName, $this->actionName);
  }
  /**
   * Возвращает параметр из строки адреса
   * @param string $key имя параметра в маске адреса
   * @return string строковое представление параметра
   */
  public function getParameter($key){
    if(!array_key_exists($key, $this->parameters))//такой параметр еще не искался
    {
      $paramsMask = explode('/', $this->paramStringMask);
      $paramPos = array_search(':' . $key, $paramsMask);
      if($paramPos>-1)//если такой параметр есть в маске ури
      {
        $params = explode('/', $this->paramString);
        /*//взять подстроку начиная с параметра (чтобы быстро можно было найти слэш - конец параметра)
        $paramSubstr = substr($this->paramString, $paramPos);
        //узнать длину параметра по позиции слэша
        $paramSubstrLen = strpos($paramSubstr, '/')>-1 ? strpos($paramSubstr, '/') : strlen($paramSubstr);
        //вернуть подстроку со значением параметра
        $paramValue = substr($paramSubstr, 0, $paramSubstrLen);*/
        $paramValue = isset($params[$paramPos])?$params[$paramPos]:NULL; #### 
        $this->parameters[$key] = $paramValue;
        return $paramValue;
      }
      else
      //вообще, если запрошенного параметра нет даже в маске пути, то это ошибочная ситуация, но пока пусть будет так
      {
        $this->parameters[$key] = null;
        return null;
      }
    }
    else return $this->parameters[$key];
  }
  /**
   * Возвращает относительный адрес текущей страницы
   * @return string относительный адрес текущей страницы
   */
  public function getSelfUri(){
    return $this->routeStr;
  }
  /**
   * Возвращает абсолютный адрес текущей страницы
   * @return string абсолютный адрес текущей страницы
   */
  public function getSelfUrl(){
    return HREF_DOMAIN . substr($this->getSelfUri(), 1);
  }  
  /**
   * Возвращает псевдоним текущей страницы
   * @return string псевдоним текущей страницы
   */
  public function getSelfAlias(){
    return $this->alias;
  }
  
  /*//public function 
  
  public static function getElementAt($index, $route = ''){
    $route = empty($route) ? $_SERVER['REQUEST_URI'] : $route;
    $path = self::parseUrl($route);
    return @$path[$index];
  } */
}