<?php
/**
 * Модель сайта
 * Производит все действия, связанные с получением данных
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage kernel
 */
abstract class ModelAbstract{
  /**
   * Объект базы данных
   * @access protected
   * @var DB
   */
  protected $db = null;
  /**
   * Уникальный экземпляр класса
   * @access private
   * @var object
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
  private static $instance;
  /**
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
  private function __construct() {
    //подключиться к базе
    //$this->db = new DB(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
  }
  /**
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
  private function __clone() {}
  /**
   * @link http://ru.wikipedia.org/wiki/Singleton
   */
  public static function getInstance() {  }
  /**
   * деструктор модели
   * @return void ничего
   */
//  public function __destruct(){
//    $this->db->close();
//  }
  
  ////////////////////////////////////////////////////////////
  //управление страницами
  ////////////////////////////////////////////////////////////

  /**
   * Возвращает список страниц сайта с пейджером. Для внутреннего использования
   * @param array $params параметры пейджера
   * @return array массив страниц и данные пейджера
   */
  protected function getSitePagesList($params){
  	$params = array_merge(
  	  $params,
  	  array('from'=>TABLE_SITE_PAGES, 'order'=>'mask', 'limits'=>array(5000))
  	);
  	return $this->getContentPage($params);
  }
  /**
   * Возвращает список страниц сайта в виде массива
   * @return array массив страниц
   */
  public function getSitePages(){
    return $this->db->select_array('SELECT * FROM my_site_pages');
  }
  /**
   * Правит страницы сайта в базе
   * @param array $values массив данных запроса
   * @param int $id код записи - если задан, будет проведено обновление, иначе вставка
   * @return void ничего
   */
  public function updateSitePages($values, $id = null){
  	if(!empty($id))
      $this->db->update_assoc(TABLE_SITE_PAGES, $values, sprintf('id="%d"', intval($id)));
    else
      $this->db->insert_assoc(TABLE_SITE_PAGES, $values);
  }
  /**
   * Удаляет страницу сайта
   * @param int $id код записи
   * @return void ничего
   */
  public function deleteSitePage($id){
    if(!empty($id))
      if(intval($id)>0)
        $this->db->delete(TABLE_SITE_PAGES, 'id="' . intval($id) . '"');
  }
  
  ////////////////////////////////////////////////////////////
  //пейджер
  ////////////////////////////////////////////////////////////
  
  /**
   * Возвращает данные, разбитые по страницам, из определенной таблицы
   * @param string $pageName имя метода с параметрами (метод модели)
   * @param string $rows имя переменной для сохранения рядов
   * @param string $pages имя переменной для сохранения пейджера
   * @return array массив рядов (ключ 'rows') и массив параметров пейджера (ключ 'pages')
   */
  public function getContentPageParams($pageName, $rows = 'rows', $pages = 'pages'){
    $methodName = 'get' . $pageName;
    $params = Array();
    if(!empty($rows))
      $params['rows'] = $rows;
    if(!empty($pages))
      $params['pages'] = $pages;
    return $this->$methodName($params);
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
   * select_count - количество обрабатываемых записей (если не указано row_count). 
   * @param array $params параметры пейджера
   * @return array массив рядов (ключ 'rows') и массив параметров пейджера (ключ 'pages')
   */
  public function getContentPage($params){
    //вывести все закладки постранично
    if(isset($params['from'])){//только если заданы таблицы, из которых делать выборку
      $from = $params['from'];
      $where = isset($params['where']) ? $params['where'] : '';//условие выборки необязательно
      
      if(isset($params['limits']))
        $pager = new Pager($params['limits']);
      else
        $pager = new Pager();
      $pager->getPageSubmit();//получить отправленные через GET параметры пейджера

      $select = new MysqlSelect($from, $where);//работа через объект запроса, для наглядности
      
      if(isset($params['row_count'])){//если сразу передано общее количество рядов
			#### v029. здесь тоже отнимем значение начального смещения
			$pages = $pager->init($params['row_count']-$pager->getStartRow());//в переменную сохраняются все данные, необходимые для работы пейджера
      }
      else{//если количество рядов не указано, нужно его подсчитать, чтобы правильно отобразить ярлыки страниц
        if(isset($params['select_count']))
          $select->select($params['select_count']);
        else
          $select->select('COUNT(*)');
        //$select->limit($pager->getStartRow(), $pager->getRangeSize());//подсчет идет до конца диапазона (обычно диапазон это 10 страниц), дальше считать смысла нет
        $pages = $pager->init($this->db->select_result($select->get())-$pager->getStartRow());
      }
      
      //выбрать непосредственно ряды с текущей страницы
      if(isset($params['select']))
        $select->select($params['select']);
      else
        $select->select('*');
      if(isset($params['group']))
        $select->group($params['group']);
      if(isset($params['order']))//если задано упорядочивание, использовать его
        $select->order($params['order']);
      $select->limit($pager->getStartRow(), $pager->getPageSize());//выбрать только ряды текущей страницы

      $rows = $this->db->select_array($select->get());

      return Array('rows'=>$rows, 'pages'=>$pages);
    }
    else
      return null;
  }
  
  ////////////////////////////////////////////////////////////
  //управление плагинами
  ////////////////////////////////////////////////////////////
  
  /**
   * Возвращает список плагинов
   * @return array линейный массив имен плагинов
   */
  public function getPluginNames(){
    $plugins = Array();
    $dir = opendir(DIR_PLUGINS);
    while(false !== ($file = readdir($dir)))
      if($file !== '..' && $file !== '.')
        $plugins[] = $file;
    return $plugins;
  }
  /**
   * Возвращает список схем настроек плагинов
   * @return array массив ассоц. массивов схем плагинов (getPluginShemes()[$row][$key])
   */
  public function getPluginShemes(){
    return $this->db->select_array('SELECT * FROM ' . TABLE_SITE_PLUGIN_SCHEMES);
  }
  /**
   * Возвращает схему настроек плагина
   * @param string $schemeName имя схемы
   * @return array ассоц. массив схемы 
   */
  public function getPluginSheme($schemeName){
    return $this->db->select_array_row(
      sprintf(
        'SELECT * FROM `%s` WHERE `item_key`="%s" ', 
        TABLE_SITE_PLUGIN_SCHEMES, 
        $schemeName
        )
      );
  }
  /**
   * Создает новую схему настроек плагина
   * @param array $fields массив значений схемы
   * @param int $id код схемы (какую схему править, если не указано - будет создана новая)
   * @return int код результата операции
   */
  public function newPluginScheme($fields, $id = null){
    if(!empty($id))
      return $this->db->update_assoc(TABLE_SITE_PLUGIN_SCHEMES, $fields, 'item_key="' . $id . '"');
    else
      return $this->db->insert_assoc(TABLE_SITE_PLUGIN_SCHEMES, $fields);
  }
  /**
   * Создает новую схему настроек плагина
   * @param array $fields массив значений схемы
   * @param int $id код схемы (какую схему править, если не указано - будет создана новая)
   * @return int код результата операции
   */
  public function deletePluginScheme($schemeName){
    if(!empty($schemeName)){
      return $this->db->delete(TABLE_SITE_PLUGIN_SCHEMES, 'item_key="' . $schemeName . '"');
    }
  }
  /**
   * Возвращает объект администрирования плагина
   * @param string $pluginName имя плагина
   * @return object объект админа плагина
   */
  public function getPluginAdminObject($pluginName){
      if(file_exists(SystemNames::pluginAdminFile($pluginName))){
        //если для плагина существует админчасть
        include SystemNames::pluginAdminFile($pluginName);
        $className = SystemNames::pluginAdminClass($pluginName);
        return new $className();
      }else
        //для плагина не создана админчасть
        return null;
  }
  /**
   * Возвращает массив плагинов, подключенных к сайту
   * @return array массив ассоц. массивов схем плагинов, подключенных к сайту
   */
  public function getSitePlugins(){
    $result = $this->db->select_array(
      " SELECT ps.plugin_name, ps.item_key FROM " . TABLE_SITE_PLUGIN_SCHEMES . " ps," . TABLE_SITE_SITEPLUGINS . " sp" .
      " WHERE sp.scheme_id=ps.item_key "
    );
    $plugins = Array();
    foreach($result as $rs)
      $plugins[] = Array('plugin'=>$rs['plugin_name'], 'scheme'=>$rs['item_key']);
    return $plugins;
  }
  /**
   * Подключает файлы плагинов и создает их объекты
   * @param array $plugins массив имен плагинов
   * @return void ничего
   */
 public static function initPlugins($plugins){
      foreach($plugins as $plugin){
        $pluginPath = SystemNames::pluginFile($plugin['plugin']);
       if(file_exists($pluginPath)){
         require_once($pluginPath);
        $pluginClassName = SystemNames::pluginClass($plugin['plugin']);
         $pluginObject = new $pluginClassName($plugin['scheme']);
       }
      }
  }
  
  ////////////////////////////////////////////////////////////
  //динамические страницы
  ////////////////////////////////////////////////////////////
  
  /**
   * Возвращает типы динамических страниц
   * @param int $id код типа - если указан, будет возвращен один конкретный тип, иначе все
   * @return array массив ассоц. массивов типов, либо ассоц. массив указанного типа
   */
  public function getDynamicPagesTypes($id = 0){
  	$query = 'SELECT * FROM ' . TABLE_SITE_PAGES_DYNAMIC_TYPES;
  	if(!empty($id)){
  	  $query .= ' WHERE id="' . $id . '"';
  	  return $this->db->select_array_row($query);
  	}else
  	  return $this->db->select_array($query);
  }
  /**
   * Возвращает список динамических страниц, разбитый по страницам. Для внутреннего использования
   * @param array $params параметры пейджера
   * @return array массив ассоц. массивов строк и пейджера
   */
  protected function getSiteDynamicPagesList($params){
    $params = array_merge(
      $params,
      Array(
        'select'=>'p.*, t.descr',
        'from'=>TABLE_SITE_PAGES . ' p, ' . TABLE_SITE_PAGES_DYNAMIC_TYPES . ' t' ,
        'where'=>'p.controller="Empty" AND p.plugins=t.plugin_name', 
        'order'=>'p.mask', 
        'limits'=>Array(10, 20, 50)
      )
    );
    return $this->getContentPage($params);
  }
  /**
   * Удаляет тип динамической страницы
   * @param int $id код типа
   * @return int код результата операции
   */
  public function deleteDynamicPageType($id = 0){
    if(!empty($id)){
      return $this->db->delete(TABLE_SITE_PAGES_DYNAMIC_TYPES, 'id="' . $id . '"');
    }
  }
  
  ////////////////////////////////////////////////////////////
  //разное
  ////////////////////////////////////////////////////////////

  /**
   * Возвращает текст текстового блока
   * @param string $item_key код-ключ текстового блока
   * @return string текст текстового блока
   */
  public function getTextBlock($item_key){
    return $this->db->select_result('SELECT html FROM my_site_text_blocks WHERE item_key="' . $item_key . '"');
  }
  /**
   * Устанавливает текст текстового блока
   * @param string $item_key код-ключ текстового блока
   * @param string $value новый текст текстового блока 
   * @return int код результата операции
   */
  public function setTextBlock($item_key, $value){
    $value = DB::escape($value);
    $this->db->select(sprintf('SELECT * FROM `%s` WHERE `item_key`="%s" ', TABLE_SITE_TEXT_BLOCKS, $item_key));
    if($this->db->num_rows()>0)
      return $this->db->update_assoc(TABLE_SITE_TEXT_BLOCKS, Array('html'=>$value), '`item_key`="' . $item_key . '"');
    else
      return $this->db->insert_assoc(TABLE_SITE_TEXT_BLOCKS, Array('html'=>$value, 'item_key'=>$item_key));
  }
  /**
   * Возвращает данные почтовых рассылок
   * @param string $item_key код-ключ почтового блока
   * @return array массив данных письма рассылки
   */
  public function getMailData($item_key){
    return $this->db->select_array_row('SELECT * FROM my_site_email_data WHERE item_key="' . $item_key . '"');
  }
  /**
   * Возвращает код записи от следующей вставки (ориентировочно)
   * @param string $table имя таблицы
   * @return int код следующей записи
   */
  public function getNextNewId($table){
    return intval($this->db->select_result('SELECT MAX(id) FROM `' . $table . '`')) + 1;
  }
}