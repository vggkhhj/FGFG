<?php
  require_once ('common.php');
  
  //проверяем права на доступ к этой странице
  if ($_SESSION['user']['roles']['role_fields'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header('Location: index.php');

  //проверяем, передалили нам поля и имя таблицы в БД
  if (!empty($_POST['fieldsBoxes']) && !empty($_POST['tableName']))
  { 
    $fieldsToDelete = '';
    $tableName = DB::escape($_POST['tableName']);


    //------------------- Удаляем отключенные поля ------------------------
    $fieldsToDeleteQuery = $db->select_array("SELECT * FROM my_admin_fields WHERE field_table='{$tableName}';");
    if(!empty($fieldsToDeleteQuery)){
      foreach ($fieldsToDeleteQuery as $fieldsToDeleteArray)
      {
        //Если такое поле не передано
        $fieldFound = false;
        foreach ($_POST['fieldsBoxes'] as $fieldsBoxes_key => $fieldsBoxes_value)
          if ($fieldsToDeleteArray['field_name'] == $fieldsBoxes_key) $fieldFound = true;

        if ($fieldFound !== true)
        {
          if ($fieldsToDelete != '') $fieldsToDelete .= ' or ';
          $fieldsToDelete .= "id='".$fieldsToDeleteArray['id']."'";
        }
      }
    }
    if ($fieldsToDelete != ''){
      $db->query("DELETE FROM my_admin_fields WHERE ".$fieldsToDelete.";");
    }
    //------------------- Конец Удаляем отключенные поля ------------------------
    
    //Добавляем новые или изменяем старые
    foreach ($_POST['fieldsBoxes'] as $fieldsBoxes_key => $fieldsBoxes_value)
    {

      //проверяем, выбран ли тип поля и его описание
      if (!empty($_POST['fieldType'][$fieldsBoxes_key]) && !empty($_POST['saveFieldAs'][$fieldsBoxes_key]))
      {
        $field_type = $_POST['fieldType'][$fieldsBoxes_key];
        //Создаем поле validation
        $validation = '';
        if ($field_type == 'text')
          $validation = @$_POST['textFieldValidation'][$fieldsBoxes_key];

        //Создаем поле rules
        $rules = '';
        if ($field_type == 'text')
          $rules = "size='".@$_POST['textFieldSize'][$fieldsBoxes_key]."', maxlength='".@$_POST['textFieldMaxlenght'][$fieldsBoxes_key]."'";
        if ($field_type == 'textarea')
          $rules = "rows='".@$_POST['textareaFieldRows'][$fieldsBoxes_key]."', cols='".@$_POST['textareaFieldCols'][$fieldsBoxes_key]."'";
        if ($field_type == 'checkbox' && @$_POST['checkboxFieldRules'][$fieldsBoxes_key]=='on')
          $rules = "checked";
        if ($field_type == 'link' && @$_POST['linkFieldRules'][$fieldsBoxes_key]!='')
          $rules = $_POST['linkFieldRules'][$fieldsBoxes_key];
        if ($field_type == 'file')
          $rules = "size='".@$_POST['fileFieldRules'][$fieldsBoxes_key]."'";
				#### v031. правило только для чтения
			if(!empty($_POST['textFieldReadonly'][$fieldsBoxes_key])){
				$rules .= (!empty($rules)?', ':'')."readonly";
			}
        $rules = DB::escape($rules);

        //Создаем поле default
        $default = '';
        if ($field_type == 'text')
          $default = @$_POST['textFieldDefault'][$fieldsBoxes_key];
        if ($field_type == 'textarea')
          $default = @$_POST['textareaFieldDefault'][$fieldsBoxes_key];
        if ($field_type == 'checkbox' && @$_POST['checkboxFieldRules'][$fieldsBoxes_key]!='')
          $default = @$_POST['checkboxFieldDefault'][$fieldsBoxes_key];
          //Для связи проверяем разрешен ли множественный выбор или нет
              //чтобы разрешить множественный выбор, нужно чтоб был передан селект и сепаратор записей
        if ($field_type == 'radio'){
          $default = $_POST['radioFieldDefault'][$fieldsBoxes_key];
        }

        if ($field_type == 'link' && @$_POST['linkFieldMultiple'][$fieldsBoxes_key] == 'yes' && @$_POST['linkFieldDefault'][$fieldsBoxes_key] != '')
          $default = $_POST['linkFieldDefault'][$fieldsBoxes_key];
		if ($field_type == 'file') {
			if (@$_POST['fileFieldDefault'][$fieldsBoxes_key]) {
				$imageCopySettings = Array();
				foreach ($_POST['fileFieldDefault'][$fieldsBoxes_key] as $settingsForCopy) {
									/*
                    					0 - width
                    					1 - height
                    					2 - action
                    					3 - prefix
                    					4 - r
                    					5 - g
                    					6 - b
                    				*/
						#### v017. пропускаем к сохранению, если одно из полей размера изображения не указано
					if(empty($settingsForCopy['0'])) $settingsForCopy['0']=0;
					if(empty($settingsForCopy['1'])) $settingsForCopy['1']=0;
					if($settingsForCopy['0']!=0 || $settingsForCopy['1']!=0){
						$imageCopySettings[] = intval($settingsForCopy['0']).','.intval($settingsForCopy['1']).','.$settingsForCopy['2'].','.$settingsForCopy['3'].','.$settingsForCopy['4'].','.$settingsForCopy['5'].','.$settingsForCopy['6'];
					}
          	}
          		$default =implode('*',$imageCopySettings);
			}
		}
        
        $default = DB::escape($default);

        //Создаем поля p1 и p2. Эти поля хранят доп.инфу. Например, для связанных таблиц - таблицу, с которой связываем и поле, по которому связываем.
        //для даты - что хранить в поле (только дату или дату-время) и что хранить по умолчанию.
        $p1 = '';
        $p2 = '';
        if ($field_type == 'text' && !empty($_POST['textFieldP1'][$fieldsBoxes_key]))
          $p1 = $_POST['textFieldP1'][$fieldsBoxes_key];
        if ($field_type == 'text' && !empty($_POST['textFieldP2'][$fieldsBoxes_key]))
          $p2 = $_POST['textFieldP2'][$fieldsBoxes_key];
        if ($field_type == 'date' && !empty($_POST['dateFieldDefault'][$fieldsBoxes_key]))
          $p1 = $_POST['dateFieldDefault'][$fieldsBoxes_key];
        if ($field_type == 'date' && !empty($_POST['dateFieldRules'][$fieldsBoxes_key]))
          $p2 = $_POST['dateFieldRules'][$fieldsBoxes_key];
        //if ($field_type == 'radio' && @$_POST['radioFieldP1'][$fieldsBoxes_key] != '')
          //$p1 = $_POST['radioFieldP1'][$fieldsBoxes_key];
        if ($field_type == 'link' && !empty($_POST['linkFieldP1P2'][$fieldsBoxes_key]))
        {
            //Получаем таблицу и поле, на которые ссылается данное поле
            $p1p2 = explode('->',$_POST['linkFieldP1P2'][$fieldsBoxes_key]);
            $p1 = $p1p2[0]; //Это имя таблицы, на которую ссылаемся
            $p2 = $p1p2[1]; //Это имя поля, на которое ссылаемся
        }
        if ($field_type == 'file')
        {
            $p1 = @$_POST['fileFieldP1'][$fieldsBoxes_key];
            $p2 = @$_POST['fileFieldP2'][$fieldsBoxes_key];
        }

        $p1 = DB::escape($p1);
        $p2 = DB::escape($p2);

        //Создаем поле tinymce
        $tinymce = '';
        if ($field_type == 'textarea')
          $tinymce = $_POST['textareaFieldTM'][$fieldsBoxes_key];
        if ($field_type == 'file')
          $tinymce = @$_POST['fileFieldValidation'][$fieldsBoxes_key];
        if ($field_type == 'date')
          $tinymce = @$_POST['dateFieldTinymce'][$fieldsBoxes_key];
          
        $tinymce = DB::escape($tinymce);
        
        //Убираем escape-символы
        $_POST['saveFieldAs'][$fieldsBoxes_key] = DB::escape($_POST['saveFieldAs'][$fieldsBoxes_key]);



        //Если такая запись есть

        $fieldArray = $db->select_array_row("SELECT * FROM my_admin_fields WHERE field_name='".$fieldsBoxes_key."' and field_table='".$_POST['tableName']."' LIMIT 1;");
        if (!empty($fieldArray))
        {
            //Обновляем имя с которым хранить
				#### Вася. htmlspecialchars()
            $db->query("UPDATE my_admin_fields SET field_table='".$_POST['tableName']."', field_ident='".@$_POST['fieldIsIdent'][$fieldsBoxes_key]."', field_name='".$fieldsBoxes_key."', field_descr='".htmlspecialchars($_POST['saveFieldAs'][$fieldsBoxes_key], ENT_IGNORE, DEFAULT_CHARSET)."', field_type='".$field_type."', field_rules='".$rules."', field_default='".$default."', field_p1='".$p1."', field_p2='".$p2."', field_tinymce='".$tinymce."', field_validation='".$validation."', field_weight='".@$_POST['fieldWeight'][$fieldsBoxes_key]."' WHERE id='".$fieldArray['id']."' LIMIT 1;");
        } else{
          //Добавляем новую запись
          $db->query("INSERT INTO my_admin_fields SET field_table='".$_POST['tableName']."', field_ident='".@$_POST['fieldIsIdent'][$fieldsBoxes_key]."', field_name='".$fieldsBoxes_key."', field_descr='".$_POST['saveFieldAs'][$fieldsBoxes_key]."', field_type='".$field_type."', field_rules='".$rules."', field_default='".$default."', field_p1='".$p1."', field_p2='".$p2."', field_tinymce='".$tinymce."', field_validation='".$validation."', field_weight='".@$_POST['fieldWeight'][$fieldsBoxes_key]."';");
        }

      }
    }
  }
?>
