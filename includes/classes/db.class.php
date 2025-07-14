<?php


/**
 * Оболочка базы данных
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage classes
 */
class DB{

  /**
   * mysqli object
   * @var resource|mysqli
   */
  protected static $dbHandle;

  /**
   * Количество строк в результате запроса
   * @var int
   */
  protected $num_rows = 0;

  /**
   * Количество затронутых строк в результате запроса
   * @var int
   */
  static $affected_rows = 0;

  /**
   * Текст ошибки запроса
   * @var string
   */

  protected $error = '';
  /**
   * Массив типов полей для обратной совместимости
   * @var array
   */
  static $types = array();

  /**
   * Массив флагов полей для обратной совместимости
   * @var array
   */
  static $flags = array();

  /**
   * Устанавливает соединение с БД
   * @param $hostname
   * @param $username
   * @param $password
   * @param $database
   * @return int
   */
  protected function connect($hostname, $username, $password, $database)
  {
    //проверка каким расширением мы пользуемся
    if(DB_USE_MYSQLI){
      //подключение к базе данных
      self::$dbHandle = mysqli_connect($hostname, $username, $password, $database);

      if (mysqli_connect_error()) {
        Error::serverError('Ошибка подключения (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
      }//устанавливаем кодировку
      elseif (!mysqli_set_charset(self::$dbHandle, DATABASE_CHARACTER_SET)) {
        Error::serverError("Не удалось установить кодировку соединения ". mysqli_error(self::$dbHandle));
      } else {
        return 1;
      }
    }else{
      if(self::$dbHandle = @mysql_connect($hostname, $username, $password))
        if(@mysql_select_db($database))
          if(mysql_query("SET CHARACTER SET " . DATABASE_CHARACTER_SET, self::$dbHandle))
            return 1;
          else Error::serverError("Не удалось установить кодировку соединения " . DATABASE_CHARACTER_SET . ", код ошибки: " . mysql_error(self::$dbHandle));
        else Error::serverError(mysql_error(self::$dbHandle));
      else Error::serverError(mysql_error(self::$dbHandle));
    }
    return 0;
  }

  /**
   * Закрывает соединение с БД
   */
  public function close(){
    if(self::$dbHandle != null){
      if(DB_USE_MYSQLI){
        @mysqli_close(self::$dbHandle);
      }else{
        @mysql_close(self::$dbHandle);
      }
    }



  }

  /**
   * Конструктор объекта БД
   * @param $host
   * @param $user
   * @param $pwd
   * @param $db
   */
  public function __construct($host, $user, $pwd, $db)
  {
    if( self::$dbHandle == null)
      return $this->connect($host, $user, $pwd, $db);
    else return 1;
  }

  /**
   * Возвращает ошибку последнего запроса
   * @return string
   */
  public function error(){
    return $this->error;
  }

  /**
   * Возвращает результат запроса mysql_query
   * @param $query
   * @return bool|mysqli_result|resource
   */
  public function select($query){
    if(DB_USE_MYSQLI){
      $result = mysqli_query(self::$dbHandle, $query);
      $this->error = mysqli_error(self::$dbHandle);
      if($result){
        $this->num_rows = intval(mysqli_num_rows($result));
      }

    }else{
      $result = mysql_query($query, self::$dbHandle);
      $this->error = mysql_error(self::$dbHandle);
      if($result){
        $this->num_rows = intval(mysql_num_rows($result));
      }

    }

    if(DEBUG_MODE && $this->error){
      echo '<br>DB->select: query, mysql_error():';
      var_dump($query, $this->error);
    }

    return $result;
  }

  /**
   * Выполняет запрос к БД mysql_query
   * @param $query
   * @return bool|mysqli_result|resource
   */
  public static function query($query){
    if(DB_USE_MYSQLI){
      $result = mysqli_query( self::$dbHandle, $query);
    }else{
      $result = mysql_query($query, self::$dbHandle);

    }

    if(DEBUG_MODE && DB::get_error()){
      echo '<br>DB->select: query, mysql_error():';
      var_dump($query, DB::get_error());
    }

    return $result;
  }

  /**
   * Возвращает значение одного поля запроса
   * @param $query
   * @param string $field
   * @return bool|null|string
   */
  public function select_result($query, $field = ''){
    if(DB_USE_MYSQLI){
      $result = mysqli_query( self::$dbHandle, $query);
      if(mysqli_num_rows($result)>0){
        if(empty($field)){
          return $this->mysqli_result($result, 0);
        }
        else{
          return $this->mysqli_result($result, 0, $field);
        }

      }
    }else{
      $result = mysql_query($query,  self::$dbHandle);
      if(@mysql_num_rows($result)>0){
        if(empty($field))
          return mysql_result($result, 0);
        else
          return mysql_result($result, 0, $field);
      }
    }
    return null;
  }

  /**
   * Возвращает массив строк запроса
   * строки представлены в виде ассоциативного массива
   * @param $query
   * @return array
   */
  public function select_array($query){
    $array = Array();
    $result = $this->select($query);
    if($this->num_rows()>0){
      if(DB_USE_MYSQLI){
        while($fa = mysqli_fetch_assoc($result))
          $array[] = $fa;
      }else{
        while($fa = mysql_fetch_assoc($result))
          $array[] = $fa;
      }
    }
    return $array;
  }

  /**
   * Возвращает массив значений одного поля запроса
   * (Если в запросе указано несколько полей возвращает первое)
   * @param $query
   * @return array|null
   */
  public function select_result_array($query){
    $result = $this->select($query);
    if($this->num_rows > 0){
      $array = Array();
      if(DB_USE_MYSQLI){
        while($fa = mysqli_fetch_array($result))
          $array[] = $fa[0];
      }else{
        while($fa = mysql_fetch_array($result))
          $array[] = $fa[0];
      }
      return $array;
    }else{
      return null;
    }
  }

  /**
   * Возвращает одну строку в виде ассоциативного массива
   * @param $query
   * @param int $row
   * @return array|null
   */
  public function select_array_row($query, $row = 0){
    $result = $this->select($query);
    $fa = null;
    if($this->num_rows()>0)
    {
      if(DB_USE_MYSQLI){
        mysqli_data_seek($result, $row);
        $fa = mysqli_fetch_assoc($result);
      }else{
        @mysql_data_seek($result, $row);
        $fa = mysql_fetch_assoc($result);
      }
    }
    return $fa;
  }

  /**
   * Возвращает количество полученых строк в результате запроса
   * @param mysqli_result|bool|resource $query_result
   * @return int
   */
  public function num_rows($query_result = false){
    if($query_result !== false){
      if(DB_USE_MYSQLI){
        return mysqli_num_rows($query_result);
      }else{
        return @mysql_num_rows($query_result);
      }
    }else{
      return $this->num_rows;
    }

  }

  /**
   * Возвращает количество полученых строк в запросе
   * @param $query
   * @return int
   */
  public function select_num_rows($query){
    $this->select($query);
    return $this->num_rows;
  }

  /**
   * Возвращает количество строк COUNT(*)
   * @param $table
   * @param string $where
   * @return bool|null|string
   */
  public function select_count($table, $where = ''){
    $where = empty($where) ? '' : 'WHERE ' . $where;
    return $this->select_result("SELECT COUNT(*) FROM {$table} {$where}", 0);
  }

  /**
   * Вставляет новую запись в таблицу
   * переданную в виде ассоциативного массива
   * @param string $table
   * @param array $assoc
   * @return int|string
   */
  public function insert_assoc($table, $assoc)
  {
    $query = self::assocToQuery($assoc);

    if(DB_USE_MYSQLI){
      mysqli_query(self::$dbHandle, " INSERT INTO {$table} SET {$query} ");
      $this->error = mysqli_error(self::$dbHandle);
      $insert_id = mysqli_insert_id(self::$dbHandle);
    }else{
      mysql_query(" INSERT INTO {$table} SET {$query} ", self::$dbHandle);
      $this->error = mysql_error(self::$dbHandle);
      $insert_id = mysql_insert_id(self::$dbHandle);
    }

    if(DEBUG_MODE){
      var_dump($query);
      var_dump($this->error);
    }

    return $insert_id;
  }

  /**
   * Обновляет запись в таблице
   * переданную в виде ассоциативного массива
   * @param string $table
   * @param array $assoc
   * @param string $where
   * @return resource
   */
  public function update_assoc($table, $assoc, $where='')
  {
    $query = self::assocToQuery($assoc);
    $where = ($where !='' ? ' WHERE '. $where : '');
    if(DB_USE_MYSQLI){
      $result = mysqli_query( self::$dbHandle, " UPDATE {$table} SET {$query} {$where} ");
      $this->error = mysqli_error(self::$dbHandle);
    }else{
      $result = mysql_query(" UPDATE {$table} SET {$query} {$where} ", self::$dbHandle);
      $this->error = mysql_error(self::$dbHandle);
    }

    if(DEBUG_MODE){
      var_dump($query);
      var_dump($this->error);
    }
    return $result;
  }

  /**
   * Удаляет одну или несколько записей из таблицы
   * @param string $table
   * @param string $where
   */
  public function delete($table, $where = ''){
    $where = empty($where) ? $where : ' WHERE ' . $where;
    $query = "DELETE FROM {$table} {$where}";
    if(DB_USE_MYSQLI){
      mysqli_query( self::$dbHandle, $query);
      $this->error = mysqli_error(self::$dbHandle);
    }else{
      mysql_query($query, self::$dbHandle);
      $this->error = mysql_error(self::$dbHandle);
    }

    if(DEBUG_MODE){
      echo '<br>DB->delete: query, mysql_error:';
      var_dump($query, $this->error);
    }
  }





  //------ дополнительные функции для работы c БД ----------

  /**
   * Возвращает ID записи вставленной INSERT
   * @return int|string
   */
  public static function insert_id(){
    if(DB_USE_MYSQLI){
      return mysqli_insert_id(self::$dbHandle);
    }else{
      return mysql_insert_id(self::$dbHandle);
    }
  }

  /**
   * Эквивалент mysql_real_escape_string
   * @param string $string
   * @return string
   */
  public static function escape($string){
    if(DB_USE_MYSQLI){
      return mysqli_real_escape_string(self::$dbHandle, $string);
    }else{
      return mysql_real_escape_string($string);
    }
  }


  /**
   * Преобразует массив в строку запроса для обновления или вставки
   * @param $assoc
   * @return string
   */
  public static function assocToQuery($assoc){
    $query = '';
    #### будем проверять
    $i=0;
    if(!empty($assoc))
      foreach($assoc as $key=>$val)
        $query .= sprintf(' `%s` = "%s" %s ', $key, $val, ( $i++ < count($assoc)-1 ? ',' : '' ) );
    return $query;
  }

  /**
   * Возвращает количество полей в результате запроса.
   * @param $query_result
   * @return bool|int
   */
  public static function num_fields($query_result){
    if($query_result){
      if(DB_USE_MYSQLI){
        return mysqli_num_fields($query_result);
      }else{
        return mysql_num_fields($query_result);
      }
    }else{
      return false;
    }
  }

  /**
   * Получает информацию о поле
   * @param $query_result
   * @param $index
   * @return bool|object
   */
  public static function fetch_field($query_result, $index){
    if($query_result){
      if(DB_USE_MYSQLI){
        if(!empty($index)){
          mysqli_field_seek($query_result, $index);
        }
        return mysqli_fetch_field($query_result);
      }else{
        if(!empty($index)){
          return mysql_fetch_field($query_result, $index);
        }else{
          return mysql_fetch_field($query_result);
        }

      }
    }else{
      return false;
    }
  }

  /**
   * Получает тип поля
   * @param $query_result
   * @param $index
   * @return bool|string
   */
  public static function field_type($query_result, $index){
    if($query_result){
      if(DB_USE_MYSQLI){
        if(!empty($index)){
          return self::mysqli_field_type($query_result, $index);
        }else{
          return self::mysqli_field_type($query_result, 0);
        }
      }else{
        if(!empty($index)){
          return mysql_field_type($query_result, $index);
        }else{
          return mysql_field_type($query_result, 0);
        }

      }

    }else{
      return false;
    }
  }

  /**
   * Получает флаги поля
   * @param $query_result
   * @param $index
   * @return bool|string
   */
  public static function field_flags($query_result, $index){
    if($query_result){
      if(DB_USE_MYSQLI){
        if(!empty($index)){
          return self::mysqli_field_flags($query_result, $index);
        }else{
          return self::mysqli_field_flags($query_result, 0);
        }
      }else{
        if(!empty($index)){
          return mysql_field_flags($query_result, $index);
        }else{
          return mysql_field_flags($query_result, 0);
        }
      }
    }else{
      return false;
    }
  }

  /**
   * Получает длину поля
   * @param $query_result
   * @param $index
   * @return bool|int
   */
  public static function field_len($query_result, $index){
    if($query_result){
      if(DB_USE_MYSQLI){
        mysqli_fetch_row($query_result);
        $length_array = mysqli_fetch_lengths($query_result);
        return $length_array[$index];
      }else{
        return mysql_field_len($query_result, $index);
      }

    }else{
      return false;
    }
  }

  /**
   * Получение строки результирующей таблицы в виде массива
   * @param $query_result
   * @return array|bool
   */
  public static function fetch_row($query_result){
    if($query_result){
      if(DB_USE_MYSQLI){
        return mysqli_fetch_row($query_result);
      }else{
        return mysql_fetch_row($query_result);
      }
    }else{
      return false;
    }
  }

  /**
   * Возвращает ошибку последнего запроса
   * @return string
   */
  public static function get_error(){
    if(DB_USE_MYSQLI){
      return mysqli_error(DB::$dbHandle);
    }else{
      return mysql_error(DB::$dbHandle);
    }
  }

  //-----------------  функции обратной совместимости -----------------------
  /**
   * Аналог функции mysql_field_type для расширения mysqli
   * Возвращает строку, представляющую тип MySQL поля
   * @param mysqli_result $result Результат возвращаемый функцией mysql_query().
   * @param integer $field_offset Числовое значение смещения. FIELD_OFFSET начинается с 0. Если FIELD_OFFSET не существует, выдается ошибка уровня E_WARNING.
   * @return string|null
   */
  public static function mysqli_field_type( $result, $field_offset ) {

    $type_id = mysqli_fetch_field_direct($result, $field_offset)->type;

    if (empty(self::$types))
    {
      self::$types = array();
      $constants = get_defined_constants(true);
      foreach ($constants['mysqli'] as $c => $n) if (preg_match('/^MYSQLI_TYPE_(.*)/', $c, $m)) self::$types[$n] = $m[1];
    }
    return array_key_exists($type_id, self::$types)? self::$types[$type_id] : NULL;
  }

  /**
   * Аналог функции mysql_field_flags для расширения mysqli
   * Возвращает строку, которая представляет флаги MySQL поля
   * @param mysqli_result $result Результат возвращаемый функцией mysql_query().
   * @param integer $field_offset Числовое значение смещения. FIELD_OFFSET начинается с 0. Если FIELD_OFFSET не существует, выдается ошибка уровня E_WARNING.
   * @return string| null
   */
  public static function mysqli_field_flags( $result , $field_offset ) {

    // Get the field directly
    $flags_num = mysqli_fetch_field_direct($result ,$field_offset)->flags;

    if (empty(self::$flags))
    {
      self::$flags = array();
      $constants = get_defined_constants(true);
      foreach ($constants['mysqli'] as $c => $n) if (preg_match('/MYSQLI_(.*)_FLAG$/', $c, $m)) if (!array_key_exists($n, self::$flags)) self::$flags[$n] = $m[1];
    }

    $result = array();
    foreach (self::$flags as $n => $t) if ($flags_num & $n) $result[] = $t;

    $return = implode(' ', $result);
    $return = str_replace('PRI_KEY','PRIMARY_KEY',$return);
    $return = strtolower($return);

    return $return;
  }

  /**
   * Аналог функции mysql_result для расширения mysqli
   * @param $res
   * @param int $row
   * @param int $col
   * @return bool
   */
  public static function mysqli_result($res,$row=0,$col=0){
    $numrows = mysqli_num_rows($res);
    if ($numrows && $row <= ($numrows-1) && $row >=0){
      mysqli_data_seek($res, $row);
      $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
      if (isset($resrow[$col])){
        return $resrow[$col];
      }
    }
    return false;
  }

  /**
   * @param $result
   * @param $offset
   */
  public static function data_seek($result, $offset){
    if(DB_USE_MYSQLI){
      mysqli_data_seek($result, $offset);
    }else{
      mysql_data_seek($result, $offset);
    }
  }

  /**
   * @param $result
   * @return array
   */
  public static function fetch_assoc($result)
  {
    if (DB_USE_MYSQLI) {
      return mysqli_fetch_assoc($result);
    } else {
      return mysql_fetch_assoc($result);
    }
  }

  /**
   * @param $result
   * @return array|null
   */
  public static function fetch_array($result){
    if (DB_USE_MYSQLI) {
      return mysqli_fetch_array($result);
    } else {
      return mysql_fetch_array($result);
    }
  }
}

