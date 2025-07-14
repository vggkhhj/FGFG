<?php
//TODO: сабмит формы недоступен до тех пор, пока не будут заполнены все обязательные поля
//TODO: сделать автоматическое масштабирование с сохранением в поле и автоматическое установление урл-кей с уст. в поле. 
/**
 * Процессор форм
 * Обрабатывает формы
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage classes
 */
class FormProcessor{
  /**
   * Ссылка на объект базы данных
   * @access protected
   * @var object
   */
  protected $db = null;
  /**
   * Ошибки проверки формы
   * @access protected
   * @var array
   */
  protected $checkError = null;
  /**
   * Уже проверенные (и обработанные) значения полей формы
   * @access protected
   * @var array
   */
  protected $filteredField = null;
  /**
   * Обработанные значения временных полей
   * @access protected
   * @var array
   */
  protected $filteredTempField = null;
  /**
   * Группы полей
   * @access protected
   * @var array
   */
  protected $group = null;
  /**
   * Настройки формы
   * @access protected
   * @var array
   */
  protected $form = null;
  /**
   * Настройки контролов формы
   * @access protected
   * @var array
   */
  protected $controls = null;
  /**
   * Значения полей формы
   * @access protected
   * @var array
   */
  protected $values = null;
  /**
   * Суффикс сабмита формы
   * @access protected
   * @var string
   */
  protected $submit_suffix = '_submit';
  /**
   * массив со списком уже подключенных внешних файлов для формы
   * @access protected
   * @var array
   */
  protected $included_files = null;
  protected $fckEditorInserted = false;

  const INC_JCALENDAR = 100;
  
  const FORM_NO_UPDATE = 0;
  const FORM_INSERT = 1;
  const FORM_UPDATE = 2;
  
  public function __construct(&$db = null){
	require_once(SystemNames::langFilePath('formprocessor'));
  	if(empty($db))
  	  $db = new DB(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    $this->db =& $db;
  }

  /**
   * Создает объект формы
   * Список допустимых правил полей см. в функциях @link controlBody() и @link validateForm()
   * @param array $formData ассоц. массив массивов с правилами для создания формы
   * @return void ничего
   */
  public function createForm($formData){
    $this->form = $formData['form'];
    $this->controls = $formData['controls'];
    //псевдонимы
    foreach($this->controls as &$c)
      switch($c['type']){
        case 'captcha':
        	$ct = $c;
        	 
          $c = Array('type'=>'string', 'title'=>$ct['title'], 'required'=>true, 'temporary'=>true, 'captcha'=>true, 'control_type'=>'captcha');
          break;
        case 'image':
          $c['type'] = 'file';
          break;
      }
		if(!empty($formData['values']))
    $this->values = $formData['values'];
    $this->included_files = Array();
  }
  /**
   * Открывает тэг формы
   */
  public function startForm(){?>
    <form action="<?php echo $this->form['action']?>" method="<?php echo $this->form['method']?>" name="<?php echo $this->form['name']?>" id="<?php echo $this->form['id']?>"  enctype='multipart/form-data'>
  <?php }
  /**
   * Печатает title контрола
   * @param string $name имя контрола
   * @return void ничего 
   */
  public function controlTitle($name){
    $ref = $this->controls[$name];
    if($ref['control_type']!='hidden')
      echo $ref['title'] . ':' . (!empty($ref['required']) ? '<span class="form_required">*</span>' : '');
  }
  /**
   * Печатает тэг контрола в соответствии с заданными для него правилами
   * Допустимые ключи контрола:
   * control_type - тип контрола (text, textarea, select, checkbox, password, date, hidden, captcha, file, image);
   * size - соответствующий атрибут;
   * id - соответствующий атрибут;
   * cols - соответствующий атрибут;
   * rows - соответствующий атрибут;
   * fck - использовать fckeditor, ключ указывает на имя тулбарсета; 
   * select_options - для селекта указывает массив options, формат: Array('option'=>'string', 'value'=>'string');
   * shows_time - для календаря указывает, позволяет он выбирать дату или нет;
   * hidden_value - значение для поля хидден;
   * copy_to - для загрузчика картинок указывает, в какую папку сохранять;
   * .
   * @param string $name имя контрола
   * @return void ничего
   */
  public function controlBody($name){
    $ref = $this->controls[$name];
    
    if(!empty($this->values))
      foreach($this->values as &$v){
			if(is_string($v)){
				$v = stripslashes($v);
			}
		}
        
    $ref['id'] = empty($ref['id']) ? $name : $ref['id'];
    
    switch($ref['control_type']){
      ////////////////////////////////////////////////////////////
      //input - text
      ////////////////////////////////////////////////////////////
      case 'text':
        printf(
          "<input type='text' name='%s' id='%s' value='%s' size='%d' maxlength='%d'>",
          $name, $ref['id'], $this->values[$name], $ref['size'], $ref['max']
        );
        break;
      ////////////////////////////////////////////////////////////
      //textarea
      ////////////////////////////////////////////////////////////
      case 'textarea':
        if(isset($ref['fck'])){
		
          $view = is_string($ref['fck']) ? $ref['fck'] : 'Basic';
		 /*
          include_once(DIR_ADD . "FCKeditor/fckeditor.php");
          $oFCKeditor = new FCKeditor($name) ;
          $oFCKeditor->BasePath = HREF_ADD . '/FCKeditor/';
          $oFCKeditor->Value = $this->values[$name];
          $oFCKeditor->Id = $ref['id'];
          $oFCKeditor->Width = $ref['cols']*8;
          $oFCKeditor->Height = $ref['rows']*15;
          $oFCKeditor->ToolbarSet = $view;
          $oFCKeditor->Config['ToolbarCanCollapse'] = false;
          $oFCKeditor->Create();
		 */
		  if(!$this->fckEditorInserted){
			echo '<script language="javascript" type="text/javascript">CKEDITOR_BASEPATH = "' . HREF_ADD . 'ckeditor/";</script>';
			echo '<script type="text/javascript" src="' . HREF_ADD . 'ckeditor/ckeditor.js"></script>';
			$this->fckEditorInserted = true;
		  }
          printf(
            "<textarea name='%s' id='%s' cols='%d' rows='%d'>%s</textarea>",
            $name, $ref['id'], $ref['cols'], $ref['rows'], $this->values[$name]		  
		  );
		  printf(
			'<script type="text/javascript"> CKEDITOR.replace( "%s", { toolbar : "%s", toolbarCanCollapse : false }); </script>',
			$ref['id'], $view
		  );
        }else
        if(empty($ref['max']))
          //-------------------неограниченный текстареа
          printf(
            "<textarea name='%s' id='%s' cols='%d' rows='%d'>%s</textarea>",
            $name, $ref['id'], $ref['cols'], $ref['rows'], $this->values[$name]
          );
        else{
          //-------------------ограниченный текстареа - со счетчиком
          $display_name = $name . '_symbols_counter_display';
          $ref['id'] = empty($ref['id']) ? $name : $ref['id'];
          //пишем саму функцию
          ?>
          <script language="javascript">
          <!--
          function countText(textarea, display, maxlength)
          {
            if(document.getElementById(textarea).value.length > maxlength)
              document.getElementById(textarea).value = document.getElementById(textarea).value.substr(0,maxlength);
            document.getElementById(display).value = document.getElementById(textarea).value.length;
          }
          // -->
          </script>
          <?php
          //вывод поля
          $eventHandler = "countText(\"" . $ref['id'] . "\", \"" . $display_name . "\", " . $ref['max'] . ")";
          printf(
            "<textarea name='%s' id='%s' cols='%d' rows='%d' " .
            "onfocus='%s' onblur='%s' onchange='%s' onkeyup='%s'>%s</textarea>",
            $name, $ref['id'], $ref['cols'], $ref['rows'], 
            $eventHandler, $eventHandler, $eventHandler, $eventHandler, 
            $this->values[$name]
          );
          echo '<br>';
          //вывод табло
          printf(
            "<input type='text' id='%s' value='0' size='2' readonly>/%d<br>",
            $display_name, $ref['max']
          );
        }
        break;
      ////////////////////////////////////////////////////////////
      //captcha
      ////////////////////////////////////////////////////////////
      case 'captcha':
		if(USE_RECAPTCHA){
				
				?>
					 <script type="text/javascript">
					 var RecaptchaOptions = {
						theme : RECAPTCHA_THEME
					 };
					 </script>		
				<?php
				
				require_once DIR_ADD . 'recaptcha/recaptchalib.php';
				echo recaptcha_get_html(RECAPTCHA_PUBLIC_KEY);
		}else{   	
          echo "<img src='" . HREF_LIB . "captcha.php" . "' id='_main_captcha_img' alt='". langFormprocTurnOnBrowserPictures ."'>";
          printf(
            "<div style='float:left;margin:0 4px;'><input type='text' name='%s' id='%s' size='20' maxlength='10'>",
            $name, $ref['id']
          );
          printf(
            '<br /><a href="#" onclick="document.getElementById(\'_main_captcha_img\').src = \'%s?\' + Math.random(); return false" style="text-decoration:none; border-bottom:1px dashed;">'. langFormprocRefreshImage .'</a></div>',
            HREF_LIB . "captcha.php"
          );
			 echo '<div style="clear:both;"></div>';
		}
        break;
      ////////////////////////////////////////////////////////////
      //select
      ////////////////////////////////////////////////////////////
      case 'select':
        printf(
          "<select name='%s' id='%s'>",
          $name, $ref['id']
        );
        if(isset($ref['select_options'])){
          //задан набор пар значений для селекта, их не надо запрашивать из базы
          $data = $ref['select_options'];
          foreach($data as $d){
            $selected = $this->values[$name] == $d['value'] ? 'selected' : '';
            echo '<option value="' . $d['value'] . '" ' . $selected . ' >' . $d['option'];
          }
        }
        echo "</select>";
        break;
      ////////////////////////////////////////////////////////////
      //checkbox
      ////////////////////////////////////////////////////////////
      case 'checkbox':
        $checked = $this->values[$name] ? 'checked' : '';
        printf(
          "<input type='checkbox' name='%s' id='%s' " . $checked . " value='1'>",
          $name, $ref['id']
        );
        break;
      ////////////////////////////////////////////////////////////
      //date
      ////////////////////////////////////////////////////////////
      case 'date':
        if(!in_array(self::INC_JCALENDAR, $this->included_files)){
          ?>
          <!-- calendar stylesheet -->
          <link rel="stylesheet" type="text/css" media="all" href="<?php echo HREF_ADD?>jscalendar/skins/aqua/theme.css" title="Aqua">
          <!-- main calendar program -->
          <script type="text/javascript" src="<?php echo HREF_ADD?>jscalendar/calendar.js"></script>
          <!-- language for the calendar -->
          <script type="text/javascript" src="<?php echo HREF_ADD?>jscalendar/lang/calendar-ru_win_.js"></script>
          <script type="text/javascript" src="<?php echo HREF_ADD?>jscalendar/calendar-setup.js"></script>
          <?php
          $this->included_files[] = self::INC_JCALENDAR;
        }
        printf(
          "<input type='text' name='%s' id='%s' value='%s' readonly >",
          $name, $ref['id'], $this->values[$name]
        );
        $buttonId = $ref['id'] . '_start_button';
        if(!empty($ref['shows_time'])){
          $dataFormat = '%Y-%m-%d %H:%M';
          $showsTime = true;
        }else{
          $dataFormat = '%Y-%m-%d';
          $showsTime = false;
        }
        ?>
        <input type="button" id="<?php echo $buttonId?>" value="...">
        <script type="text/javascript">
        <!--
          Calendar.setup({
              inputField     :    "<?php echo $ref['id']?>",
              ifFormat       :    "<?php echo $dataFormat?>",
              showsTime      :    <?php echo $showsTime ? 'true' : 'false'?>,
              button         :    "<?php echo $buttonId?>",
              singleClick    :    true,
              step           :    1
          });
        // -->
        </script>
        <?php
        break;
      ////////////////////////////////////////////////////////////
      //hidden
      ////////////////////////////////////////////////////////////
      case 'hidden':
        printf(
          "<input type='hidden' name='%s' id='%s' value='%s'>",
          $name, $ref['id'], $ref['hidden_value']
        );
        break;
      ////////////////////////////////////////////////////////////
      //password
      ////////////////////////////////////////////////////////////
      case 'password':
        printf(
          "<input type='password' name='%s' id='%s' size='%d' maxlength='%d'>",
          $name, $ref['id'], $ref['size'], $ref['max']
        );
        break;
      ////////////////////////////////////////////////////////////
      //file, image
      ////////////////////////////////////////////////////////////
      case 'file':
      case 'image':
        printf(
          "<input type='file' name='%s' id='%s'>",
          $name, $ref['id']
        );
        if($ref['control_type']==='image')
          if(!empty($this->values[$name])){
            $fileHref = Util::dirToHref($ref['copy_to']) . $this->values[$name];
            $filePath = $ref['copy_to'] . $this->values[$name];
            if(file_exists($filePath))
              printf("<br><img src='%s' width='140'>", $fileHref);
          }
        break;
    }
  }
  /**
   * Печатает тэг сабмита
   * @param string $name имя контрола
   * @return void ничего
   */
  public function placeSubmit(){
    $submitTitle = !empty($this->form['submit_title']) ? $this->form['submit_title'] : langFormprocSaveButton; 
    echo "<input type='submit' name='" . $this->form['name'] . $this->submit_suffix . "' id='" . $this->form['id'] . $this->submit_suffix . "' value='" . $submitTitle . "'>";
  }
  /**
   * Печатает закрывающий тэг формы
   */
  public function endForm(){
    echo "</form>";
  }
  /**
   * Печатает ошибки формы
   */
  public function printSubmitErrors(){
  	if(!empty($this->checkError)):?>
      <!--<div class="form_errors">
      <?php /*echo langFormprocYouMadeMistakes*/?>
      <ul>
      <?php /*foreach($this->checkError as $d):*/?>
        <li><?php /*echo $d*/?></li>
      <?php /*endforeach*/?>
      </ul>-->
	      <?php foreach($this->checkError as $d):?>
		      <?php echo $d?><br>
	      <?php endforeach?>
      </div>
    <?php endif;
   }
  /**
   * Печатает форму
   */
  public function printForm(){?>
    <?php $this->startForm()?>
    <table width="100%" border="0">
    <tr><td colspan="2"><?php $this->printSubmitErrors()?></td></tr>
		<?php foreach($this->controls as $key=>$val):?>
			<?php if($val['control_type']=='captcha'){ ?>
				<tr>
					<td align="right"><?php $this->controlTitle('code')?></td>
					<td>
						<?php //$this->placeSubmit()?>
						<?php $this->controlBody('code')?>
					</td>
				</tr>
			<?php }else{ ?>
				<tr valign="top">
					<td align="right"><?php $this->controlTitle($key)?></td>
					<td><?php $this->controlBody($key)?></td>
				</tr>
			<?php } ?>
    <?php endforeach?>
    <tr>
      <td></td>
      <td><?php $this->placeSubmit()?></td>
    </tr>
    </table>
    <?php $this->endForm()?>
  <?php }
  /**
   * Проверяет отправленные формой данные
   * @param string $insertTable имя таблицы, в которую будет проведена вставка принятых данных
   * @param string $updateWhere критерий WHERE - если указан, будет UPDATE с ним, иначе INSERT
   * @return array массив отфильтрованных полей если все ОК, либо 0 если ошибки или не было сабмита формы
   */
  public function checkFormSubmit($insertTable = '', $updateWhere = null){

    if(!empty($_POST[$this->form['name'] . $this->submit_suffix])){
      //если кнопка этой формы вызвала перезагрузку
      //проверить форму
      if($this->validateForm($this->controls)){
        $fields = $this->getFilteredFields();
        //если указано - провести вставку в базу
        if(!empty($insertTable))
          if(empty($updateWhere))
            $this->db->insert_assoc($insertTable, $fields);
          else
            $this->db->update_assoc($insertTable, $fields, $updateWhere);
        return $fields;
      }
      else{//если форма заполнена неправильно
        $this->values = $this->getFilteredFields();//передача введенных значений в форму
        return 0;
      }
    }else
      return 0;
  }
  /**
 * Проверяет переданные поля формы
 * Ключи массива проверки:
 * title - заголовок поля, используется при сообщении об ошибке в этом поле;
 * mask - маска допустимого значения поля, регулярное выражение;
 * type - тип данных поля (string, int, float, file, email, captcha), если не указано - считается как string;
 * group - имя группы, к которой относится поле (значения разных групп возвращаются в отдельных массивах);
 * required - поле является обязательным;
 * unique - значение поля должно быть уникальным, ключ указывает на имя таблицы, в которой проводится проверка;
 * captcha - поле хранит капчу, значение будет сравниваться со значением сессии;
 * temporary - временное поле (значение не будет включено в массив отфильтрованных полей);
 * strip_tags - из текста поля будут удалены тэги, ключ указывает на список допустимых тэгов;
 * links_nofollow - в тексте поля все ссылки будут сделаны недоступными для поисковиков;
 * nl2br - в тексте поля все переносы будут заменены на br;
 * this_record_id - указывает на id записи, из которой взято значения для поля (при редактировании),
 *  может использоваться например вместе с unique, чтобы не блокировалось сохранения этого же значения; 
 * password - значение 1 - в поле хранится пароль, 2 - в поле хранится подтверждение;
 * min - минимальная длина значения (для типа строка), либо минимальное значение - для численного;
 * max - максимальная длина значения (для типа строка), либо максимальное значение - для численного;
 * .
 * @param array &$fields правила проверки полей формы
 * @return bool если ошибок в форме нет, то true
 */
  public function validateForm(&$fields){
    $this->checkError = Array();//хранит ошибки проверки
    $this->group = Array();//группы
    $query_fields = Array();//значения результата
    $temp_fields = Array();//временные значения
    $uploadedFiles = Array();//загруженные файлы
    $nl2brStrings = Array();//поля, в которых разрешены /r/n - при ошибках их надо дополнительно экранировать, чтобы не выводилось rn
    
    foreach($fields as $key=>&$f)
    {
      $name = $key;
      $title = @$f['title'];
      $mask = @$f['mask'];
      
      if(isset($f['type']))
        $type = $f['type'];
      else $type = 'string';
      
      if(array_key_exists('group', $f))
        $group = $f['group'];
      else
        $group = null;
      
      $required = isset($f['required']) ? $f['required'] : false;
      $unique = isset($f['unique']) ? $f['unique'] : false;
      $code = isset($f['captcha']) ? $f['captcha'] : false;
      $temporary = isset($f['temporary']) ? $f['temporary'] : false;
      $strip_tags = isset($f['strip_tags']) ? $f['strip_tags'] : false;
      $links_nofollow = isset($f['links_nofollow']) ? $f['links_nofollow'] : false;
      $nl2br = isset($f['nl2br']) ? $f['nl2br'] : false;
      $thisId = isset($f['this_record_id']) ? $f['this_record_id'] : false;
      
      if(!empty($_POST[$name]) || !empty($_FILES[$name]['name']))
      {
        if($type === 'string'){
          $field = trim($_POST[$name]);
          if(!empty($strip_tags))
            $field = strip_tags($field, $strip_tags);
          if(!empty($links_nofollow))
            $field = self::linksNofollow($field);
          if($nl2br){
            $nl2brStrings[] = $name;
          }
          $field = DB::escape($field);
        }
        else
        if($type === 'int'){
          $field = intval($_POST[$name]);
        }
        else
        if($type === 'float')
          $field = floatval($_POST[$name]);
        else
        if($type === 'file'){//обработать как файл
          $file = &$_FILES[$name];
          //разложить имя файла на части, они будут обработаны отдельно
          $file_name = Mb_ext::pathinfo($file['name'], PATHINFO_FILENAME);
          $file_ext = Mb_ext::pathinfo($file['name'], PATHINFO_EXTENSION);
          //перевести в транслит (расширение не передавать, потому что из него удалится точка)
          $newname = self::makeUrlKey($file_name) . '.' . $file_ext;
          //проверить, что имя корректное
          if(empty($newname)){
            $this->checkError[] = $title . langFormprocInvalidFilename;
          }
          //создать уникальное имя
          $newname = self::uniqueFilename($newname, $f['copy_to']);
          //и скопировать
          copy($file['tmp_name'], $newname);
          //если контрол - картинка, то надо сделать ресайз
          if($f['control_type']==='image' && $f['resize']===true){ #### не надо
            $mw = intval($f['resize_width']) ? intval($f['resize_width']) : 80;
            $mh = intval($f['resize_height']) ? intval($f['resize_height']) : 80;
            if(!self::resizeImgFile($newname, $mw , $mh)){
              $this->checkError[] = $title . langFormprocInvalidFileType;
            }
          }
          //занести этот файл в список, если возникнут ошибки то он будет удален, чтобы не засорять винт
          $uploadedFiles[] = $newname;
          //в переменную сохраняется только имя, без пути
          $newname = Mb_ext::pathinfo($newname, PATHINFO_BASENAME);
          $field = $newname;
        }
        else
        if($type == 'email')
        {
          $field = DB::escape(trim($_POST[$name]));
          if(preg_match_all('/(.+)@(.+)\.(.+)/', $field, $matches) != 1){
            $this->checkError[] = $title . langFormProcInvalidValue;
          }
        } #### 3-07-20
		  else
			$field = DB::escape(trim($_POST[$name]));
        
        if(!$temporary)
          $query_fields[$name] = $field;
        else
          $temp_fields[$name] = $field;
        
        //поля можно группировать для занесения в разные таблицы базы; группируются только не временные поля
        if(!empty($group))
        {
          if(!array_key_exists($group, $this->group))
            $this->group[$group] = Array();
          $this->group[$group][$name] = &$query_fields[$name];
        }
          
            
        if(!empty($unique))
        {
          $user_query = sprintf(' SELECT * FROM `%s` WHERE `%s`="%s" ',
            $unique, $name, $field);
          if(!empty($thisId))
            $user_query .= sprintf(' AND `id`<>"%d" ', $thisId);
          if($this->db->select_num_rows($user_query)>0){
            $this->checkError[] = $title .' '. langFormprocVariant . $field . langFormprocAlreadyTaken;
          }
        }
            
        if(isset($f['password']))
          if($f['password'] == 1)
          {
            $password1 = $field;
            $passwordValue = &$query_fields[$name];
          }
          else
          if($f['password'] == 2)
            $password2 = $field;
            
        if(($type === 'string') || ($type === 'email'))
          $length = strlen($field);
        else $length = $field;
            
        if(isset($f['min']))
        {
          if($length < $f['min']){
            $this->checkError[] = $title . langFormprocValueTooShort;
          }
        }
          
        if(isset($f['max']))
        {
          if($length > $f['max']){
            $this->checkError[] = $title . langFormprocValueTooLong;
          }
        }
        
        if($mask != '')
        {
          if(preg_match_all($mask, $field, $matches) != 1){
            $this->checkError[] = $title . langFormProcInvalidValue;
          }
        }
            
        if($code)
        {
          @session_start();
          require_once DIR_ADD . 'securimage/securimage.php';
          $securimage = new Securimage();
          if(!$securimage->check($field)){
            $this->checkError[] = langFormprocInvalidCaptcha;
          }
          /*
          if(empty($_SESSION[CAPTCHA_KEY]))
            $this->checkError[] = 'Код подтверждения не создан.';
          else
            if($_SESSION[CAPTCHA_KEY] != $field)
              $this->checkError[] = 'Код подтверждения указан неправильно.';
          unset($_SESSION[CAPTCHA_KEY]);
          */
        }

        if(isset($f['nospam']) && $f['nospam']){
          $field = trim($_POST[$name]);
          #### чтобы был хоть один русский символ в сообщении
          if( $f['type'] == 'string' && !preg_match('/[а-я]/iu', $field) ){
            $this->checkError[] = 'Сообщение содержит спам!';
            break;
          }
        }
			
      }// not isset $_POST
      else
        if($required){
      		$this->checkError[] = langFormprocNotSpecified . $title;
      	}
    }
        
    if(isset($password1) && isset($password2))
    {
      if($password1 != $password2) $this->checkError[] = langFormprocPasswordsDiffer;
    }
    
    if(!empty($this->checkError)){//если есть ошибки
      //значение пароля нужно обнулить
      $passwordValue = '';
      //загруженные файлы необходимо удалить
      foreach($uploadedFiles as $uf)
        @unlink($uf);
      //в текстовых полях надо дополнительно экранировать \r\n - иначе при выводе текста в stripslashes они испортятся
      foreach($nl2brStrings as $uf){
        $query_fields[$uf] = self::encodeNl($query_fields[$uf]);
      }
        
    }
    else{//если ошибок нет; некоторые действия можно проводить только если ошибок нет
      //перевод переносов в хтмл
      foreach($nl2brStrings as $uf){
        //var_dump($query_fields[$uf]);
        $query_fields[$uf] = self::decodeNl($query_fields[$uf]);
        //var_dump($query_fields[$uf]);
        $query_fields[$uf] = nl2br($query_fields[$uf]);
        //var_dump($query_fields[$uf]);
      }
      //пароль шифруется
      //$passwordValue = $passwordValue;//self::generatePass($passwordValue);
    }
    
    $this->filteredField = $query_fields;
    $this->filteredTempField = $temp_fields;
    
    if(empty($this->checkError))
      return true;
    else 
      return false;
  }
  /**
  * закрывает \r\n чтобы они не ломались при stripslashes
  * @param string $plainString некодированная строка
  * @return string кодированная строка
  */
  public static function encodeNl($plainString){
    return str_replace("\\r\\n", "\\\r\\\n", $plainString);
  }
  /**
  * открывает \r\n которые были закрыты с помощью encodeNl
  * @param string $encodedString кодированная строка
  * @return string раскодированная строка 
  */
  public static function decodeNl($encodedString){
    return str_replace("\\r\\n", "\r\n", $encodedString);
  }
  /**
  * Возвращает обработанные значения полей формы
  * @param string $group имя группы - если указано, вернет значения только этой группы, иначе все
  * @return array значения полей 
  */
  public function getFilteredFields($group = ''){
    if(empty($group))
      return $this->filteredField;
    else
      return $this->group[$group];
  }
  /**
  * Возвращает обработанные значения временных полей формы
  * @return array значения полей 
  */
  public function getFilteredTempFields(){
    return $this->filteredTempField;
  }
  /**
  * Возвращает описание ошибок заполнения формы
  * @return array массив строк с описаниями ошибок 
  */
  public function getCheckErrors(){
    return $this->checkError;
  }
  /**
  * Возвращает описание ошибок заполнения формы
  * @return array массив строк с описаниями ошибок 
  */
  public function getErrors(){
    return $this->checkError;
  }
  /**
  * Устанавливает собственные ошибки для текущей формы
  * @param array $errors массив строк с сообщениями об ошибках
  */
  public function setErrors($errors){
    $this->checkError = $errors;
  }
  /**
  * Добавляет новые собственные ошибки для текущей формы
  * @param array $errors массив строк с сообщениями об ошибках
  */
  public function addErrors($errors){
    $this->checkError = array_merge($this->checkError, $errors);
  }
  /**
  * Добавляет новое собственное сообщение об ошибке для текущей формы
  * @param string $error строка сообщения об ошибке
  */
  public function addError($error){
    $this->checkError[] = $error;
  }
  /**
  * Устанавливает значения полей формы
  * @param array $values массив значений
  */
  public function setValues($values){
    $this->values = $values;
  }
  /**
  * Шифрует заданную строку
  * @param string $iStr строка
  * @param string $iSalt соль (доп. строка для усиления шифрования) 
  */
  public static function generatePass($iStr, $iSalt = ""){
    if($iSalt != "")
      return md5(md5($iStr) . $iSalt);
    else
      return md5($iStr);
  }
  /**
  * Генерирует случайную строку
  * @param int $iLow минимальная длина строки
  * @param int $iHi максимальная длина строки, если не задана то равна минимальной 
  * @return string случайная строка
  */
  public static function getRandomStr($iLow = 25, $iHi = 0){
    mt_srand();
    $iHi = ($iHi<$iLow) ? $iLow : $iHi;
    $len = mt_rand($iLow, $iHi);
    $str = "";
    $pattern = "123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    for($i=0;$i<$len;$i++)
      $str .= substr($pattern, mt_rand(0,strlen($pattern)), 1);
    return $str;
  }
  /**
  * Инициализация капчи
  */
  public static function initCode($name){
    @session_start();
    mt_srand();
    $value = mt_rand(1000,9999);
    session_register($name);
    $_SESSION[$name] = $value;
    return $value;
  }
  /**
  * Транслитерирует строку
  * @param string $str строка на русском
  * @return string строка транслитом
  */
  public static function translit($string){
    $rus = array("ё","й","ю","ь","ч","щ","ц","у","к","е","н","г","ш","з","х","ъ","ф","ы","в","а","п","р","о","л","д","ж","э","я","с","м","и","т","б","Ё","Й","Ю","Ч","Ь","Щ","Ц","У","К","Е","Н","Г","Ш","З","Х","Ъ","Ф","Ы","В","А","П","Р","О","Л","Д","Ж","Э","Я","С","М","И","Т","Б");
    $eng = array("yo","iy","yu","'","ch","sh","c","u","k","e","n","g","sh","z","h","'","f","y","v","a","p","r","o","l","d","j","е","ya","s","m","i","t","b","Yo","Iy","Yu","CH","'","SH","C","U","K","E","N","G","SH","Z","H","'","F","Y","V","A","P","R","O","L","D","J","E","YA","S","M","I","T","B");
    return str_replace($rus, $eng,  $string);
  }
  /**
  * Транслитерирует и заменяет все не буквенно-числовые символы на "-"
  * @param string $str строка на русском
  * @param int $len максимальная длина строки 
  * @return string обработанная строка
  */
  public static function makeUrlKey($str, $len = 50){
    $str = substr($str, 0, $len);
    $str = self::translit($str);
    $str = strtolower($str);
    $str = preg_replace('/[^a-z0-9\-]/', ' ', $str);
    $str = trim($str);//лишние пробелы и херня в начале и конце строки
    $str = preg_replace('/ +/', '-', $str);
    return $str;
  }
  /**
  * Генерирует уникальное имя файла в указанном каталоге, в виде "файл(1).doc"
  * @param string $str имя файла
  * @param string $dirpath каталог, в котром имя должно быть уникальным 
  * @return string уникальное имя
  */
  public static function uniqueFilename($str, $dirpath = ''){
    $filename = $dirpath . $str;
    $newfilename = $filename;
    $name = Mb_ext::pathinfo($filename, PATHINFO_FILENAME);
    $ext = Mb_ext::pathinfo($filename, PATHINFO_EXTENSION);
    $i = 1;
    while(file_exists($newfilename))
      $newfilename = $dirpath . $name . '(' . $i++ . ').' . $ext;
    return $newfilename;
  }
  /**
  * Уменьшает размеры указанного файла изображения до заданных значений
  * @param string $filename имя файла
  * @param int $maxw максимальная ширина изображения 
  * @param int $maxh максимальная высота изображения 
  * @param int $quality качество изображения при использовании JPEG-сжатия (в процентах) 
  * @return bool false если в ходе операции произошла ошибка
  */
  public static function resizeImgFile($filename, $maxw = 350, $maxh = 350, $quality = 85){
    if(!file_exists($filename)) return false;
    
    $info = getimagesize($filename);
    $type = $info[2];
    
    switch($type) /* выбор способа открытия по типу изображения */
    {
      case IMAGETYPE_JPEG:
        $im = @imagecreatefromjpeg($filename);
        break;
       case IMAGETYPE_PNG:
        $im = @imagecreatefrompng($filename);
        break;
      case IMAGETYPE_WBMP: 
        $im = @imagecreatefromwbmp($filename);
        break;
      case IMAGETYPE_GIF: 
        $im = @imagecreatefromgif($filename);
        break;
      default: /* если ничего не подошло */
        $im = null;
    }
    
    if(!empty($im)){
      self::resizeImg($im, $info, $maxw, $maxh);
      imagejpeg($im, $filename, $quality);
      return true;
    }else
      return false;
  }
  /**
  * Уменьшает размеры переданного изображения (ресурса) до заданных значений
  * @param object &$source ссылка на изображение в памяти
  * @param array $info данные getimagesize() для файла изображения 
  * @param int $mw максимальная ширина изображения 
  * @param int $mh максимальная высота изображения 
  */
public static function resizeImg(&$source, $info, $mw=350, $mh=350){
  	list($width, $height, $type) = $info;   
   	if (($width >= 50 && $height >= 50)){
   		$percent = round($width/$height, 3);
     	if($width > $mw || $height > $mh){
     		if ($width > $height){
          		$newwidth = $mw;
          		$newheight = floor($mw/$percent);
      		}
      		else
      			if ($height > $width){
          			$newheight = $mh;
          			$newwidth = floor($mh*$percent);
      			}
      			else{
          			$newheight = $mh;
          			$newwidth = $mw;      			
      			}
      			$thumb = imagecreatetruecolor($newwidth, $newheight);
      			imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
      			$source = $thumb;
      	}
  	}
}
  /**
  * Закрывает все html ссылки в указанной строке от индексации роботами
  * @param string $text строка
  * @return string обработанная строка 
  */
  public static function linksNofollow($text){
    //удалить все noindex
    str_replace('<noindex>', '', $text);
    str_replace('</noindex>', '', $text);
    str_replace('rel="nofollow"', '', $text);
    str_replace('rel=\'nofollow\'', '', $text);
    str_replace('rel=nofollow', '', $text);
    //
    
    //замена
    $text = preg_replace_callback(
      '@(<a.*?)(>.*?</a>)@i',
      array('FormProcessor', 'linkNofollow'),
      $text
    );
    return $text;
  }

  private static function linkNofollow($match){
    $link = stripslashes($match[0]);
    $params = stripslashes($match[1]);
    $tail = stripslashes($match[2]);
    if( strpos($params, 'http://')>-1 || strpos($params, 'https://')>-1 )//если в адресе указан протокол, это может быть адрес другого сайта
    {
      preg_match('@://(.*)/@i', HREF_DOMAIN, $domain);
      $domain = stripslashes($domain[1]);
      //проверить область текста ссылки на наличие адреса текущего сайта
      if(!preg_match('@href=.*?' . $domain . '.*?(?:\s|>)@i', $params)){
        $link = '<noindex>' . $params . ' rel="nofollow" ' . $tail . '</noindex>';
      }
    }
    return $link;
  }
  
	//Изменяет размеры исходного изображения
	function imgResample($imgFrom, $imgTo, $maxw, $maxh, $resize='fit', $r=false, $g=false, $b=false) {
		####
		$CUR_ERROR_REP=error_reporting();
		error_reporting(E_ERROR);

		//если хотяб один из размеров указан
		if (!(($maxw == 0) && ($maxh == 0))) {
			switch (strtolower(Mb_ext::pathinfo($imgFrom, PATHINFO_EXTENSION))) {
				case 'jpg':
				case 'jpeg': $src = imageCreateFromJpeg($imgFrom); break;
				case 'gif': $src = imageCreateFromGif($imgFrom); break;
				case 'png': $src = imageCreateFromPng($imgFrom); break;
				default: return false;
			}
			$w = imagesx($src);
			$h = imagesy($src);

			//если указаны оба размера
			if (($maxw != 0) && ($maxh != 0)) {
				$prop = $w / $h;
				$newprop = $maxw/$maxh;
				if ($resize != 'crop') {
					if ($prop >= $newprop) {
						$new_w = $w > $maxw ? $maxw : $w ;
						$new_h = $new_w / $prop;
					} else {
						$new_h = $h > $maxh ? $maxh : $h ;
						$new_w = $new_h * $prop;
					}
					//если указан цвет фона
					if (($r && $g && $b) && $resize == 'letterbox') {
						$thumb = imageCreateTrueColor($maxw, $maxh);
						$bgcol = imagecolorallocate($thumb, $r, $g, $b);
						imagefill($thumb, 0, 0, $bgcol);
						$pos_x = ($maxw - $new_w) / 2;
						$pos_y = ($maxh - $new_h) / 2;
						imageCopyResampled($thumb, $src, $pos_x, $pos_y, 0, 0, $new_w, $new_h, $w, $h);
					}

					//если фон не указан
					elseif ($resize == 'fit') {
						$thumb = imageCreateTrueColor($new_w, $new_h);
						imageCopyResampled($thumb, $src, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
					}
				}
				else {
					if ($prop <= $newprop) {
						$new_w = $w > $maxw ? $maxw : $w ;
						$new_h = $new_w / $prop;
					} else {
						$new_h = $h > $maxh ? $maxh : $h ;
						$new_w = $new_h * $prop;
					}
					$pos_x = ($maxw - $new_w) / 2;
					$pos_y = ($maxh - $new_h) / 2;
					$thumb = imageCreateTrueColor($maxw, $maxh);
						#### Вася. заполнять указанным фоном даже обрезаемык изображения
					$bgcol = imagecolorallocate($thumb, $r, $g, $b);
					imagefill($thumb, 0, 0, $bgcol);
					
					imageCopyResampled($thumb, $src, $pos_x, $pos_y, 0, 0, $new_w, $new_h, $w, $h);
				}
			}

			//если указан один размер
			else {
				if ($maxw != 0) {
					$new_w = $maxw;
					$new_h = $h * ($new_w / $w);
				}
				elseif ($maxh != 0) {
					$new_h = $maxh;
					$new_w = $w * ($new_h / $h);
				}
				$thumb = imageCreateTrueColor($new_w, $new_h);
				imageCopyResampled($thumb, $src, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
			}
			switch (strtolower(Mb_ext::pathinfo($imgFrom, PATHINFO_EXTENSION))) {
				case 'jpg':
				case 'jpeg': imageJpeg($thumb, $imgTo, 100); break;
				case 'gif': imageGif($thumb, $imgTo); break;
				case 'png': imagePng($thumb, $imgTo); break;
				default: return false;
			}
			imageDestroy($src);
			imageDestroy($thumb);
		}
		error_reporting($CUR_ERROR_REP);
	}
  
  
  
}