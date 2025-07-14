<?php
abstract class UserAbstract{
    protected $db = null;
    
    const NOT_LOGGED = 0;
    const LOGGED = 1;
    const SESSION_OBJECT_NAME = "user";
    const COOKIE_NAME = "_ka_user_data";
    const TOKEN_COOKIE_NAME = "_token"; // название куки для сохранения ключа авторизации
    const TOKEN_DAYS_DURATION = 30; // длительность сохранения ключа авторизации
    const AUTH_TRYING_LIMIT = 0; // кол-во попыток ввода пароля


    private $logState = self::NOT_LOGGED;
    private $authError = null;//массив ошибок авторизации
    protected $authFields = null;//массив введенных в окно авторизации данных
    protected $data = null;//массив данных пользователя
    protected $dbTable = '';
    protected $dbUser = '';
    protected $dbPassword = '';
    protected $userDataQuery = "SELECT * FROM `%s` WHERE `%s`='%s' AND `%s`='%s'";
    protected $userIdQuery = "SELECT * FROM `%s` WHERE `id`='%d'";
    
    public function __construct(){
      $this->db = new DB(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
      $this->dbTable = TABLE_USERS;
      $this->dbUser = FIELD_USER_LOGIN;
      $this->dbPassword = FIELD_USER_PASSWORD;
			#### проверять перед запуском
		if(session_id()==''){
			session_start();
		}
			#### v036. ограничение кол-ва попыток ввода пароля
		if($this->checkIPLockedStatus($_SERVER['REMOTE_ADDR'])){
			Error::page__423();
		}
      if(!empty($_SESSION) && array_key_exists(self::SESSION_OBJECT_NAME, $_SESSION))
      {
        $this->data = $_SESSION[self::SESSION_OBJECT_NAME];
        
        foreach($this->data as &$d)
          if(is_string($d))
            $d = DB::escape($d);
        $this->setLogState(self::LOGGED);
      }
      else
        if(FALSE && isset($_COOKIE[self::COOKIE_NAME])){
          $newData = $this->unpackCookie($_COOKIE[self::COOKIE_NAME]);
          $queryRes =
            $this->db->select_array_row(
              sprintf(
                $this->userDataQuery, 
                $this->dbTable, $this->dbUser, $newData[0], $this->dbPassword, $newData[1]
              )
            );
          if(!empty($queryRes)){
            $this->data = $queryRes;
            $this->updateSessionData();
				$GLOBALS['b_isNewlyLogined']=true; #### оставим пометку, о недавнем входе
            $this->setLogState(self::LOGGED);
          }else
            $this->setLogState(self::NOT_LOGGED);
        }
				#### v032. функция "запомнить меня"
			elseif($user_id=$this->checkToken()){
				$this->updateFromBase($user_id);
            $this->setLogState(self::LOGGED);
			}
        else
          $this->setLogState(self::NOT_LOGGED);
    }
    
    public function unlog(){
		$this->delToken((int)$_SESSION[self::SESSION_OBJECT_NAME]['id']); #### v032. функция "запомнить меня"
      unset($_SESSION[self::SESSION_OBJECT_NAME]);
      $this->sendCookie(false);
      $this->setLogState(self::NOT_LOGGED);
    }
    
    protected function sendCookie($value){
      if(!setcookie(self::COOKIE_NAME, $value , time()+60*60*24*30 , '/' /*, '.' . substr(HREF_DOMAIN, 0, -1)*/ ))
        echo 'NO COOKIE';
    }
    
    protected function packCookie($obj){
      return $obj[$this->dbUser] . ';' . $obj[$this->dbPassword];
    }
    
    protected function unpackCookie($str){
      $obj = explode(';', $str);
      foreach($obj as &$o)
        $o = DB::escape(strip_tags($o));
      return $obj;
    }
    
    public function logout(){
      $this->unlog();
    }
    
    //функция в основном нужна, когда во время нахождения пользователя в состоянии авторизации, поменялись данные в базе,
    //и соответственно данные сессии устарели - например, при изменении настроек, чтобы не проводить повторную авторизацию
    public function updateFromBase($id = 0){
      $id = empty($id) ? intval($this->data['id']) : $id;
      $query = sprintf($this->userIdQuery, $this->dbTable, $id);
      $result = $this->db->select_array_row($query);
      if(empty($result))//запрос в базу не прошел, пользователь невалидный
        $this->logout();
      else{
        $this->data = $result;
        $this->updateSessionData();
      }
    }
    
    //функция нужна, когда данные поменялись скриптом, чтобы отразить эти изменения в сессии
    public function updateSessionData(){
      @session_start();
      $_SESSION[self::SESSION_OBJECT_NAME] = $this->data;
      //echo 'SESSION: '; var_dump($_SESSION[self::SESSION_OBJECT_NAME]);
      $this->sendCookie($this->packCookie($this->data));
    }

    public function getData($key = ''){
      if(empty($key))
        return $this->data;
      else
        return $this->data[$key];
    }
    
    public function setLogState($iLogState){
      $this->logState = $iLogState;
    }
    
    public function isLoggedIn(){
      #### +2 поправочки
      if($this->logState > self::NOT_LOGGED && !empty($_SESSION[self::SESSION_OBJECT_NAME]))return true;
      else return false;
    }
    
    public function getAuthSubmit($iTable = '', $iUsername = '', $iPassword = ''){
      $iTable = empty($iTable) ? $this->dbTable : $iTable;
      $iUsername = empty($iUsername) ? $this->dbUser : $iUsername;
      $iPassword = empty($iPassword) ? $this->dbPassword : $iPassword;
      if(isset($_POST[$iUsername]) && isset($_POST[$iPassword]))
      {
        $this->authError = Array();
				#### v035. возможность сложного хеширования пароля
			$username = self::getHashingLogin($_POST[$iUsername]);
			$password = self::getHashingPassword($_POST[$iPassword],$username);

        if(!empty($username) && !empty($password))
        {
          $query = sprintf(
            $this->userDataQuery, 
            $iTable, $iUsername, $username, $iPassword, $password
          );
          $fa = $this->db->select_array($query);
          if($this->db->num_rows()==1)
          {
            $this->setLogState(self::LOGGED);
            $this->data = $fa[0];
//------------------------------------------------------------            
            $this->data['login'] = $_POST[$iUsername];
            $this->data['password'] = $_POST[$iPassword];   
//-------------------------------------------------------------  			
            $this->updateSessionData();
				$this->addToken($this->data['id']); #### v032. функция "запомнить меня"
				
				$auth_status='succes';
          }
			 else{
				$this->authError[] = "неверный логин или пароль";
				$auth_status='failure';
			 }
			 $this->putAuthStatus($_SERVER['REMOTE_ADDR'],$auth_status);
        }
        else
          $this->authError[] = "укажите ваш логин и пароль";
        
        $this->authFields[$iUsername] = $username;
        $this->authFields[$iPassword] = $password;
        return $this->authFields;
      }
      else return 0;
    }
    
    public function hasAuthErrors(){
      if(empty($this->authError)) return false;
      else return true;
    }
    public function getAuthErrors(){
      return $this->authError;
    }
	 
	 
	/** Проверяет наличие и правильность ключа для авторизации без пароля */
	function checkToken(){
		if(!empty($_COOKIE[self::TOKEN_COOKIE_NAME])){
				// проверим ключ
			$tokenQuery="SELECT `user_id` FROM `my_admin_tokens` WHERE token='".DB::escape($_COOKIE[self::TOKEN_COOKIE_NAME])."' AND `date_losing`>'".date('Y-m-d')."' LIMIT 1;";
			$user_id=$this->db->select_result($tokenQuery);
			if(!empty($user_id)){
				return $user_id;
			}
		}
		return false;
	}
	
	/** Записывает новый ключ для авторизации без пароля */
	function addToken($user_id){
			// сгенерируем ключ
		$token=md5($user_id.'_'.time());
			// запишем ключ
		$tokenAddQuery="
		INSERT INTO `my_admin_tokens` (`user_id`, `token`, `date_losing`)
			VALUES ('$user_id','$token','".date('Y-m-d',time()+self::TOKEN_DAYS_DURATION*24*60*60)."')
			ON DUPLICATE KEY UPDATE `token`=VALUES(`token`),`date_losing`=VALUES(`date_losing`);";		
		$this->db->query($tokenAddQuery);
		setcookie(self::TOKEN_COOKIE_NAME, $token, time()+self::TOKEN_DAYS_DURATION*24*60*60, '/');
	}
	
	/** Удаляет ключ для авторизации без пароля */
	function delToken($user_id){
		$tokenDelQuery="DELETE FROM `my_admin_tokens` WHERE `user_id`=$user_id;";		
		$this->db->query($tokenDelQuery);
		setcookie(self::TOKEN_COOKIE_NAME, '', time()-60, '/');
	}

	/**
	* Хеширует строку, использует сложное кодирование и соль
	* @param string $string исходная строка / ПАРОЛЬ
	* @param string $string соль для хеширования / ХЕШ ЛОГИНА
	* @return string захешированная строка
	*/
	final public function getHashingPassword($string,$salt=''){
		return sha1($string); // по умолчанию для наших сайтов
		$salt='$1$'.(!empty($salt)?Mb_ext::u_substr(sprintf('%08s',$salt),0,8):'hashsalt').'$';
		return md5(base64_encode(crypt(crypt($string,$salt),$salt)));
	}
	
	/**
	* Хеширует строку с помощью md5
	* @param string $string исходная строка
	* @param string $string соль для хеширования
	* @return string захешированная строка
	*/
	final public function getHashingLogin($string){
		return sha1($string);
	}
	
	/** Генерирует случайный пароль */
	public function randomPassword($length=8) {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < $length; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}
	 
	/** Проверяет состояние блокировки IP пользователя 
	* true - заблокирован
	*/
	public function checkIPLockedStatus($user_ip){
		if(self::AUTH_TRYING_LIMIT==0) return false;
		$query="SELECT count(*) FROM `ip_locked` WHERE `user_ip`=INET_ATON('$user_ip') AND `date_locked`>'".date('Y-m-d H:i:s')."'-INTERVAL 1 HOUR;";
		return $this->db->select_result($query);
	}
	
	/** Сохраняет в БД состояние успешности авторизации пользователя */
	public function putAuthStatus($user_ip,$status){
		$LIMIT=self::AUTH_TRYING_LIMIT;
		if($LIMIT==0) return;
			// запишем попытку входа
		$query="INSERT INTO `user_login_trying` SET `user_ip`=INET_ATON('$user_ip'), `time`=NOW(), `status`='$status'".(!empty($this->data['id'])?" ,`user_id`={$this->data['id']}":'').";";
		$this->db->query($query);
			// проверим кол-во неуспешных попыток
		$query="
		SELECT count(*)=$LIMIT 
			FROM (
				SELECT * FROM `user_login_trying` WHERE `user_ip`=INET_ATON('$user_ip') ORDER BY `time` DESC LIMIT $LIMIT
			) AS `last_limited`
			WHERE `status`='failure';";
		if($this->db->select_result($query)){
				// заблокируем IP-адрес
			$query="INSERT INTO `ip_locked` SET `user_ip`=INET_ATON('$user_ip'), `date_locked`=NOW();";
			$this->db->query($query);
				// запишем состояние блокировки
			$query="INSERT INTO `user_login_trying` SET `user_ip`=INET_ATON('$user_ip'), `time`=NOW(), `status`='locked';";
			$this->db->query($query);
		}
	}

  }