<?php
/**
 * Менеджер событий, рассылает сообщения о событиях плагинам
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage kernel
 */
class EventDispatcher{
  /**
   * Уникальный экземпляр класса
   * @access private
   * @var object
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
  private static $instance;
  /**
   * Список зарегистрированных в системе событий
   * @access private
   * @var array
   */
  private $events = Array(
	  'onBeforeSelectController',
	  'onAfterGetControllerRequested',
	  'onBeforeViewDisplay',
	  'onAfterViewDisplay',
	  'onBeforeControllerMain',
	  'onBeforeControllerAction',
	  'onBeforeGetPageContent',
	  'onAfterGetPageContent',
  );
  /**
   * Список имен зарегистрированных в системе плагинов
   * @access private
   * @var array
   */
  private $eventListeners = Array();
  /**
   * Список (ссылок на объекты) зарегистрированных в системе плагинов
   * @access private
   * @var array
   */
  private $listeners = Array();
  /**
   * Закрытый конструктор
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
  private function __construct(){}
  /**
   * Закрытый метод копий
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
  private function __clone(){}
  /**
   * Возвращает ссылку на экземпляр класса
   * @return object
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
  public static function getInstance(){
    if (self::$instance === null) {
      self::$instance = new self;
    }
    return self::$instance;
  }
  /**
   * Регистрирует плагин в системе
   * @param object &$newListener объект плагина
   * @param array $eventList массив строк-имен событий, которые слушает плагин
   * @return void ничего
   */
  public function registerListener(&$newListener, $eventList){
    $pluginName = get_class($newListener);
    //если такой плагин еще не регистрирован
    if(!isset($this->listeners[$pluginName])){
      $this->listeners[$pluginName] =& $newListener;
      //указать события, которые будет слушать этот плагин
      foreach($eventList as $event){
        /*
        //проверка на валидность события
        if(!in_array($event, $this->events))
          return $this->noEventError();
        */
        //если слушатели для этого события еще не регистрировались - инициализация
        if(!isset($this->eventListeners[$event]))
        $this->eventListeners[$event] = Array();
        //собственно запись в массив
        $this->eventListeners[$event][] = $pluginName;
      }
    }
  }
  /**
   * Регистрирует новое допустимое событие в системе
   * @param string $newEvent имя события
   * @return void ничего
   */
  public function registerEvent($newEvent){
    if(!in_array($newEvent, $this->events))
    $this->events[] = $newEvent;
  }
  /**
   * Возвращает список зарегистрированных плагинов
   * @return array список имен плагинов
   */
  public function getRegisteredListeners(){
    return array_keys($this->listeners);
  }
  /**
   * Возвращает список зарегистрированных событий
   * @return array список имен событий
   */
  public function getRegisteredEvents(){
    return $this->events;
  }
  /**
   * Запускает оповещение о событии в системе
   * @param string $event имя события
   * @param mixed &$params данные для плагинов
   * @return mixed данные плагинов
   */
  public function fire($event, &$params = null){
    /*
    //проверить валидность события
    if(!in_array($event, $this->events))
      return $this->noEventError();
    */
    //если слушатели для этого события регистрировались
    if(!empty($this->eventListeners[$event]))
      //для всех плагинов, слушающих это событие
      foreach($this->eventListeners[$event] as $elName){
        $this->listeners[$elName]->$event($params);
      }
    return $params;
  }
  /**
   * Генерирует ошибку при вызове недействительного события
   * @return int 0
   */
  public function noEventError(){
    throw new Exception('Вызов несуществующего события');
  }
}