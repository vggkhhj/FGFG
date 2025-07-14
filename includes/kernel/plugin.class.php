<?php
/**
 * Плагин сайта
 * Базовый функционал плагина сайта
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage kernel
 */
abstract class Plugin{
	protected $name = '';
	protected $pluginModel = null;
	protected $event = null;
	
	public function __construct($schemeName){
		$className = get_class($this);
		$this->event = EventDispatcher::getInstance();
		$this->name = substr($className, 0, strpos($className, 'Plugin'));
		//модель плагина
		$pluginModelFile = SystemNames::pluginModelFile($this->name);
		include_once $pluginModelFile;
		$pluginModelClass = SystemNames::pluginModelClass($this->name);
		$this->pluginModel = new $pluginModelClass();
		//настройки схемы
		$model = Model::getInstance();
		$adminObj = $model->getPluginAdminObject($this->name);
		$setupData = $adminObj->getSetupData($schemeName);
		$this->setup($setupData);
		//зарегистрировать в качестве слушателя
		$this->register();
	}
	
	abstract function setup($setupData);
	
	public function register(){
		$methods = get_class_methods(get_class($this));
		$eventList = Array();
		foreach($methods as $method)
		  if(strpos($method, 'on')===0)
		    $eventList[] = $method;
		$this->event->registerListener($this, $eventList);
	}
	
}