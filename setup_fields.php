<?php
require_once ('common.php');

//проверяем права на доступ к этой странице
if ($_SESSION['user']['roles']['role_fields'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header('Location: index.php');

$MA_pageTitle = 'Настройка полей';

//окно выбора имени таблицы для хранения

if (!@$_REQUEST['exitSetup']) //Если настройка еще не закончена
{
    //Настраиваем поля для каждой таблицы
    //Определяем id таблицы в my_admin_tables, для которой будем настраивать поля
    if (isset($_POST['tableId']))
        $tableId = $_POST['tableId'];
    else
    {
        $tableArray = $db->select_array_row("SELECT * FROM my_admin_tables ORDER BY id LIMIT 1;");
        $tableId = $tableArray['id'];
    }


    //Выводим список полей для таблицы с текущим id
    $MA_content .= "<form method='POST' enctype='multipart/form-data' action='save_fields.php' target='toFrame' id='formFrame'>";//Одна форма для всех полей
    $tableArray = $db->select_array_row("SELECT * FROM my_admin_tables WHERE id='".$tableId."';");

    //Имя таблицы в БД, для которой сохраняем поля
    $MA_content .= "<input type='hidden' name='tableName' value='".$tableArray['table_name']."'>";

    $MA_content .= "Настройте поля для таблицы '".$tableArray['table_descr']."'";
    $fieldsQuery = $db->query("SELECT * FROM {$tableArray['table_name']} LIMIT 1;");
    for ($i=0; $i< DB::num_fields($fieldsQuery); $i++)
    {
        $fieldsData = DB::fetch_field($fieldsQuery, $i);

//Это поле не должно быть системеным (например my_admin_creator или my_admin_modifier)
        if (strpos($fieldsData->name, 'my_admin_') === false) {
            //Если это поле уже существует, получаем данные по нему
            $fieldExistsArray = $db->select_array_row("SELECT * FROM my_admin_fields WHERE field_name='".$fieldsData->name."' and field_table='".$tableArray['table_name']."' LIMIT 1;");
            //Определяем смысл полей по умолчанию
            $MA_content .= "<div class='fieldBlock'>";
            $MA_content .= "<div class='fieldHead'>";
            $MA_content .= $fieldsData->name;
            $MA_content .= "<div class='fieldOnOff'>
                      <input type='checkbox' name='fieldsBoxes[".$fieldsData->name."]' id='fieldBox_".$fieldsData->name."' onchange='onOffField(this);'";
            if (!empty($fieldExistsArray)) $MA_content .= " checked";
            $MA_content .= "> - Использовать поле</div>";
            $MA_content .= "</div>";
            $MA_content .= "<div id='fieldBlockData_".$fieldsData->name."'>";
            $MA_content .= "
              <table width='90%' align='center' border='0' cellspacing='2' cellpadding='0'>
                <tr valign='top' align='left'>
                  <td width='200'>Хранить как:</td>
                  <td>
                    <input type='text' size='25' maxlength='30' name='saveFieldAs[".$fieldsData->name."]'";
            if (!empty($fieldExistsArray)) $MA_content .= " value='".str_replace("'","&#039;",$fieldExistsArray['field_descr'])."'";
            $MA_content .= "
                    >
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Тип поля:</td>
                  <td>";
            //Если поле не было внесено
            if (empty($fieldExistsArray)) {
                $currentFieldType =	getFieldType(DB::field_type($fieldsQuery, $i), DB::field_flags($fieldsQuery, $i));
                $MA_content .= "
                        <select name='fieldType[".$fieldsData->name."]' id='fieldType_".$fieldsData->name."' onchange=\"editProp('".$fieldsData->name."');\">";
                $MA_content .= "<option value='text' ";
                if ($currentFieldType=='text') $MA_content .= "selected";
                $MA_content .= ">Однострочное</option>";
                $MA_content .= "<option value='textarea' ";
                if ($currentFieldType=='textarea') $MA_content .= "selected";
                $MA_content .= ">Многострочное</option>";
                $MA_content .= "<option value='checkbox' ";
                if ($currentFieldType=='checkbox') $MA_content .= "selected";
                $MA_content .= ">Выключатель</option>";
                $MA_content .= "<option value='radio' ";
                if ($currentFieldType=='radio') $MA_content .= "selected";
                $MA_content .= ">Переключатель</option>";
                $MA_content .= "<option value='date' ";
                if ($currentFieldType=='date') $MA_content .= "selected";
                $MA_content .= ">Дата</option>";
                $MA_content .= "<option value='file'>Файл</option>
                          <option value='link'>Связь</option>
                        </select>";
            } else
            {
                $MA_content .= "
                        <select name='fieldType[".$fieldsData->name."]' id='fieldType_".$fieldsData->name."' onchange=\"editProp('".$fieldsData->name."');\">";
                $MA_content .= "<option value='text' ";
                if (@$fieldExistsArray['field_type']=='text') $MA_content .= "selected";
                $MA_content .= ">Однострочное</option>";
                $MA_content .= "<option value='textarea' ";
                if (@$fieldExistsArray['field_type']=='textarea') $MA_content .= "selected";
                $MA_content .= ">Многострочное</option>";
                $MA_content .= "<option value='checkbox' ";
                if (@$fieldExistsArray['field_type']=='checkbox') $MA_content .= "selected";
                $MA_content .= ">Выключатель</option>";
                $MA_content .= "<option value='radio' ";
                if (@$fieldExistsArray['field_type']=='radio') $MA_content .= "selected";
                $MA_content .= ">Переключатель</option>";
                $MA_content .= "<option value='date' ";
                if (@$fieldExistsArray['field_type']=='date') $MA_content .= "selected";
                $MA_content .= ">Дата</option>";
                $MA_content .= "<option value='file' ";
                if (@$fieldExistsArray['field_type']=='file') $MA_content .= "selected";
                $MA_content .= ">Файл</option>";
                $MA_content .= "<option value='link' ";
                if (@$fieldExistsArray['field_type']=='link') $MA_content .= "selected";
                $MA_content .= ">Связь</option>";
                $MA_content .= "</select>";
            }
            $MA_content .= "
                  </td>
                </tr>
                <tr>
                  <td>Это поле-идентификатор:</td>
                  <td>
                    <select size='1' name='fieldIsIdent[".$fieldsData->name."]'>
                      <option value='0'";
            if (!empty($fieldExistsArray))                                 //
            {                                                                          //  Если такое поле уже настраивалось
                if (@$fieldExistsArray['field_ident'] == '0')  $MA_content .= "selected";          //  извлекаем эти настройки
            } else $MA_content .= "selected";                                                    //
            $MA_content .= "
                      >нет</option>
                      <option value='1'";
            if (!empty($fieldExistsArray))                                 //  Если такое поле уже настраивалось
                if (@$fieldExistsArray['field_ident'] == '1')  $MA_content .= "selected";          //  извлекаем эти настройки
            $MA_content .= "
                      >да</option>
                    </select></td>
                </tr>
                <tr valign='top' align='left'>
                  <td width='200'>Вес:</td>
                  <td>
                    <select size='1' name='fieldWeight[".$fieldsData->name."]'>
                      ";
            if (!empty($fieldExistsArray)) {
                $fieldWeight = @$fieldExistsArray['field_weight'];
            } else $fieldWeight = '';
            for ($wI = 0; $wI<30 ;$wI++) {
                $MA_content .= "<option value='".$wI."'";
                if ($wI == $fieldWeight)
                    $MA_content .= " selected";
                $MA_content .= ">".$wI."</option>";
            }
            $MA_content .= "</td>
                </tr>
              </table>

              <!--свойства однострочного поля-->";
            //Значение по умолчанию
            $textDefault = '';
            if (!empty($fieldExistsArray))                                /////////////////////////////////////////                                     //
                if (@$fieldExistsArray['field_type'] == 'text')                          //                                     //
                    $textDefault = @$fieldExistsArray['field_default'];                    //  Если такое поле уже настраивалось  //                                 //
            //  извлекаем эти настройки            //
            //Размер поля и его максимальная длина                                     //  для поля типа                      //
            $textSize = '50';                                                          //  text                               //
            $textMaxlength = DB::field_len($fieldsQuery, $i);                        //                                     //
            if (!empty($fieldExistsArray))                                /////////////////////////////////////////
                if (@$fieldExistsArray['field_type'] == 'text')
                {
                    preg_match("/size=\'([^$]*?)\'(?:[^$]*)maxlength=\'([^$]*?)\'/", @$fieldExistsArray['field_rules'], $pockets);
                    $textSize = @$pockets[1];
                    $textMaxlength = @$pockets[2];
                }

            #### v031. правило только для чтения
            $b_readonly=false;
            if(!empty($fieldExistsArray['field_rules'])){
                if(strpos($fieldExistsArray['field_rules'], 'readonly')!==false)
                    $b_readonly=true;
            }

            //Значение для проверки ввода
            $validationNotEmpty = '';
            $validationPass = '';
            if (!empty($fieldExistsArray)) {
                if (@$fieldExistsArray['field_validation'] == 'notempty') {
                    $validationNotEmpty = 'selected';
                }
                if (@$fieldExistsArray['field_validation'] == 'password') {
                    $validationPass = 'selected';
                }
            }

            //Значение для проверки ввода
            $validationNotEmpty = '';
            $validationPass = '';
            $validationEmail = '';
            $validationEmailOrEmpty = '';
            $validationDigit = '';
            if (!empty($fieldExistsArray)) {
                if (@$fieldExistsArray['field_validation'] == 'notempty') {
                    $validationNotEmpty = 'selected';
                }
                if (@$fieldExistsArray['field_validation'] == 'password') {
                    $validationPass = 'selected';
                }
                if (@$fieldExistsArray['field_validation'] == 'email') {
                    $validationEmail = 'selected';
                }
                if (@$fieldExistsArray['field_validation'] == 'emailorempty') {
                    $validationEmailOrEmpty = 'selected';
                }
                if (@$fieldExistsArray['field_validation'] == 'digit') {
                    $validationDigit = 'selected';
                }
            }

            //Значение для создания alias'а
            $textP1 = '';
            if (!empty($fieldExistsArray))
                $textP1 = @$fieldExistsArray['field_p1'];

            //Пефикс alias'a
            $prefix_nothing = '';
            $prefix_uniqid = '';
            $prefix_cur_seconds = '';
            $prefix_cur_date= '';
            $prefix_cur_datetime = '';
            $prefix_this_recordid = '';
            if (!empty($fieldExistsArray)) {
                if (@$fieldExistsArray['field_p2'] == 'nothing') {
                    $prefix_nothing = 'selected';
                }
                if (@$fieldExistsArray['field_p2'] == 'uniqid') {
                    $prefix_uniqid = 'selected';
                }
                if (@$fieldExistsArray['field_p2'] == 'cur_seconds') {
                    $prefix_cur_seconds = 'selected';
                }
                if (@$fieldExistsArray['field_p2'] == 'cur_date') {
                    $prefix_cur_date = 'selected';
                }
                if (@$fieldExistsArray['field_p2'] == 'cur_datetime') {
                    $prefix_cur_datetime = 'selected';
                }
                if (@$fieldExistsArray['field_p2'] == 'this_recordid') {
                    $prefix_this_recordid = 'selected';
                }
            }
            $MA_content .= "
              <div id='textProp_".$fieldsData->name."'>
              <table width='90%' align='center' border ='0' cellspacing='2' cellpadding='0'>
                <tr valign='top' align='left'>
                  <td width='200'>Размер поля:</td>
                  <td><input type='text' name='textFieldSize[".$fieldsData->name."]' size='10' maxlength='10' value='".str_replace("'","&#039;",$textSize)."'></td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Максимальная длина ввода:</td>
                  <td><input type='text' name='textFieldMaxlenght[".$fieldsData->name."]' size='10' maxlength='10' value='".str_replace("'","&#039;",$textMaxlength)."'></td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Текст по умолчанию:</td>
                  <td>
                    <input type='text' name='textFieldDefault[".$fieldsData->name."]' size='50' maxlength='200' value='".str_replace("'","&#039;",$textDefault)."'>
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Проверка ввода:</td>
                  <td>
                    <select size='1' name='textFieldValidation[".$fieldsData->name."]'>
                      <option value=''>Нет</option>
                      <option value='notempty' ".$validationNotEmpty.">Не пустое</option>
                      <option value='digit' ".$validationDigit.">Только целое число</option>
                      <option value='password' ".$validationPass.">Пароль</option>
                      <option value='email' ".$validationEmail.">E-Mail</option>
                      <option value='emailorempty' ".$validationEmailOrEmpty.">E-Mail или пустота</option>
                    </select>
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Записать alias в:</td>
                  <td>
                    <select size='1' name='textFieldP1[".$fieldsData->name."]'>
                      <option value=''>Нет</option>";
            $forAliasQuery = $db->select_array("DESCRIBE {$tableArray['table_name']};");
            foreach($forAliasQuery as $forAliasData){
                $MA_content .= "<option value='".$forAliasData['Field']."'";
                if ($textP1 == $forAliasData['Field']) $MA_content .= " selected";
                $MA_content .= ">".$forAliasData['Field']."</option>";
            }
            $MA_content .= "</select>
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Префикс alias:</td>
                  <td>
                    <select size='1' name='textFieldP2[".$fieldsData->name."]'>
                      <option value='nothing' ".$prefix_nothing.">Нет префикса</option>
                      <option value='uniqid' ".$prefix_uniqid.">Уникальное число</option>
                      <option value='cur_seconds' ".$prefix_cur_seconds.">Текущие секунды</option>
                      <option value='cur_date' ".$prefix_cur_date.">Текущую дату</option>
                      <option value='cur_datetime' ".$prefix_cur_datetime.">Текущую дату и время</option>
                      <option value='this_recordid' ".$prefix_this_recordid.">ID записи</option>
                    </select>
                  </td>
                </tr>
              </table>
              </div>
              <!--свойства однострочного поля-->

              <!--свойства многострочного поля-->";
            //Значение по умолчанию
            $textAreaDefault = '';
            if (!empty($fieldExistsArray))                                /////////////////////////////////////////                                     //
                if (@$fieldExistsArray['field_type'] == 'textarea')                      //                                     //
                    $textAreaDefault = @$fieldExistsArray['field_default'];                //  Если такое поле уже настраивалось  //                                 //
            //  извлекаем эти настройки            //
            //Количество строк и столбцов                                              //  для поля типа                      //
            $textareaRows = '10';                                                      //  textarea                           //
            $textareaCols = '37';                                                      //                                     //
            if (!empty($fieldExistsArray))                                /////////////////////////////////////////
                if (@$fieldExistsArray['field_type'] == 'textarea')
                {
                    preg_match("/rows=\'([^$]*?)\'(?:[^$]*)cols=\'([^$]*?)\'/", @$fieldExistsArray['field_rules'], $pockets);
                    $textareaRows = @$pockets[1];
                    $textareaCols = @$pockets[2];
                }

            //Использование TinyMCE
            $tinyMCE = 'advanced';
            if (!empty($fieldExistsArray))
                if (@$fieldExistsArray['field_type'] == 'textarea')
                    $tinyMCE = @$fieldExistsArray['field_tinymce'];
            $MA_content .= "
              <div id='textareaProp_".$fieldsData->name."'>
              <table width='90%' align='center' border ='0' cellspacing='2' cellpadding='0'>
                <tr valign='top' align='left'>
                  <td width='200'>Кол-во столбцов:</td>
                  <td><input type='text' name='textareaFieldCols[".$fieldsData->name."]' size='10' maxlength='10' value='".str_replace("'","&#039;",$textareaCols)."'></td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Кол-во строк:</td>
                  <td><input type='text' name='textareaFieldRows[".$fieldsData->name."]' size='10' maxlength='10' value='".str_replace("'","&#039;",$textareaRows)."'></td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Текст по умолчанию:</td>
                  <td>
                    <TEXTAREA class='mceNoEditor' name='textareaFieldDefault[".$fieldsData->name."]' cols='37' rows='10'>".str_replace("'","&#039;",$textAreaDefault)."</TEXTAREA>
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Включить FCKeditor:</td>
                  <td>
                    <select name='textareaFieldTM[".$fieldsData->name."]' size='1'>";
            $MA_content .= "<option value='' ";
            if ($tinyMCE == '') $MA_content .= "selected";
            $MA_content .= ">Отключено</option>";
            $MA_content .= "<option value='Basic' ";
            if ($tinyMCE == 'Basic') $MA_content .= "selected";
            $MA_content .= ">Упрощенный</option>";
            $MA_content .= "<option value='Advanced' ";
            if ($tinyMCE == 'Advanced') $MA_content .= "selected";
            $MA_content .= ">Расширенный</option>";
            $MA_content .= "<option value='Wizard' ";
            if ($tinyMCE == 'Wizard') $MA_content .= "selected";
            $MA_content .= ">Максимальный</option>";
            $MA_content .= "<select>";
            $MA_content .= "
                  </td>
                </tr>
              </table>
              </div>
              <!--свойства многострочного поля-->

              <!--свойства выключателя-->";
            //Значение по умолчанию
            $checkboxDefaultOn = '';                                                   /////////////////////////////////////////
            $checkboxDefaultOff = '';                                                  //                                     //
            if (!empty($fieldExistsArray))                                //  Если такое поле уже настраивалось  //
                if (@$fieldExistsArray['field_type'] == 'checkbox')                      //  извлекаем эти настройки            //
                    if (@$fieldExistsArray['field_rules'] == 'checked')                    //  для поля типа                      //
                    {                                                                      //  checkbox                           //
                        $checkboxDefaultOn = 'selected';                                     //                                     //
                    } else $checkboxDefaultOff = 'selected';                               /////////////////////////////////////////

            //Записывать в поле, если выбрано
            $checkboxWrite = '1';
            if (!empty($fieldExistsArray))
                if (@$fieldExistsArray['field_type'] == 'checkbox')
                    $checkboxWrite = @$fieldExistsArray['field_default'];
            $MA_content .= "
              <div id='checkboxProp_".$fieldsData->name."'>
              <table width='90%' align='center' border ='0' cellspacing='2' cellpadding='0'>
                <tr valign='top' align='left'>
                  <td width='200'>По умолчанию:</td>
                  <td>
                    <select name='checkboxFieldRules[".$fieldsData->name."]' size='1'>
                      <option value='on' ".$checkboxDefaultOn.">Включен</option>
                      <option value='off' ".$checkboxDefaultOff.">Выключен</option>
                    </select>
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td width='200'>Если включен, записать в поле:</td>
                  <td><input type='text' name='checkboxFieldDefault[".$fieldsData->name."]' size='10' maxlength='10' value='".str_replace("'","&#039;",$checkboxWrite)."'></td>
                </tr>
              </table>
              </div>
              <!--свойства выключателя-->

              <!--свойства переключателя-->";
            //Значение по умолчанию
            $radioDefault = '';                                                   /////////////////////////////////////////
            if (!empty($fieldExistsArray))                           //  Если такое поле уже настраивалось  //
                if (@$fieldExistsArray['field_type'] == 'radio')                    //  извлекаем эти настройки            //
                    $radioDefault = @$fieldExistsArray['field_default'];              //  для поля типа                      //
            //  radio                              //
            //                                     //
            /////////////////////////////////////////

            //получаем возможные значения поля enum

            $enumDataArray = $db->select_array_row("SHOW COLUMNS FROM ".$tableArray['table_name']." LIKE '".$fieldsData->name."';");
            $enumData = Array();
            while (preg_match("/'(.*?)'[,|)]/i", $enumDataArray['Type'], $enumDataPockets)) {
                $enumData[] = $enumDataPockets[1];
                $enumDataArray['Type']=preg_replace("/'(.*?)'[,|)]/i", '', $enumDataArray['Type'], 1);
            }
            $MA_content .= "
              <div id='radioProp_".$fieldsData->name."'>
              <table width='90%' align='center' border ='0' cellspacing='2' cellpadding='0'>
                <tr valign='top' align='left'>
                  <td width='200'>По умолчанию:</td>
                  <td>";
            if (count($enumData)>0) {
                $MA_content .= "<select name='radioFieldDefault[".$fieldsData->name."]' size='1'>";
                foreach($enumData as $enumDataKey => $enumDataValue) {
                    $MA_content .= "<option value='".$enumDataKey."'";
                    if ($radioDefault == $enumDataKey) $MA_content .= " selected";
                    $MA_content .= ">".str_replace("''","'",$enumDataValue)."</option>";
                }
                $MA_content .= "</select>";
            } else $MA_content .= "нет вариантов";
            $MA_content .= "
                  </td>
                </tr>
              </table>
              </div>
              <!--свойства переключателя-->

              <!--свойства даты-->";
            //Значение по умолчанию
            $dateDefaultCurrentdate = '';                                              /////////////////////////////////////////
            $dateDefaultempty = '';                                                    //                                     //
            if (!empty($fieldExistsArray))                                //  Если такое поле уже настраивалось  //
                if (@$fieldExistsArray['field_type'] == 'date')                          //  извлекаем эти настройки            //
                    if (@$fieldExistsArray['field_p1'] == 'currentdate')                   //  для поля типа                      //
                    {                                                                      //  date                               //
                        $dateDefaultCurrentdate = 'selected';                                //                                     //
                    } else $dateDefaultempty = 'selected';                                 /////////////////////////////////////////

            //Что хранить
            $dateRulesDate = '';
            $dateRulesDatetime = '';
            if (!empty($fieldExistsArray))
                if (@$fieldExistsArray['field_type'] == 'date')
                    if (@$fieldExistsArray['field_p2'] == 'date')
                    {
                        $dateRulesDate = 'selected';
                    } else $dateRulesDatetime = 'selected';

            //Подключать ли календарь
            $dateFieldTinymceYes = '';
            $dateFieldTinymceNo = '';
            if (!empty($fieldExistsArray))
                if (@$fieldExistsArray['field_type'] == 'date')
                    if (@$fieldExistsArray['field_tinymce'] == 'calendar')
                    {
                        $dateFieldTinymceYes = 'selected';
                    } else $dateFieldTinymceNo = 'selected';
            $MA_content .= "
              <div id='dateProp_".$fieldsData->name."'>
              <table width='90%' align='center' border ='0' cellspacing='2' cellpadding='0'>
                <tr valign='top' align='left'>
                  <td width='200'>По умолчанию:</td>
                  <td>
                    <select name='dateFieldDefault[".$fieldsData->name."]' size='1'>
                      <option value='currentdate' ".$dateDefaultCurrentdate.">Текущее значение</option>
                      <option value='empty' ".$dateDefaultempty.">Пусто</option>
                    </select>
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td width='200'>Хранить:</td>
                  <td>
                    <select name='dateFieldRules[".$fieldsData->name."]' size='1'>
                      <option value='date' ".$dateRulesDate.">Только дату</option>
                      <option value='datetime' ".$dateRulesDatetime.">Дату и время</option>
                    </select>
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td width='200'>Подключить календарь:</td>
                  <td>
                    <select name='dateFieldTinymce[".$fieldsData->name."]' size='1'>
                      <option value='calendar' ".$dateFieldTinymceYes.">Да</option>
                      <option value='' ".$dateFieldTinymceNo.">Нет</option>
                    </select>
                  </td>
                </tr>
              </table>
              </div>
              <!--свойства даты-->

              <!--свойства файла-->";
            //Размер поля                                                                         /////////////////////////////////////////
            $fileSize = '50';
            //                                     //
            if (!empty($fieldExistsArray))                                           //  Если такое поле уже настраивалось  //
                if (@$fieldExistsArray['field_type'] == 'file')                                     //  извлекаем эти настройки            //
                {                                                                                   //  для поля типа                      //
                    preg_match("/size=\'([^$]*?)\'/", @$fieldExistsArray['field_rules'], $pockets);   //  file                               //
                    $fileSize = @$pockets[1];                                                         //                                     //
                }                                                                                   /////////////////////////////////////////

            //Папка для сохранения
            $fileFolder = '';
            if (!empty($fieldExistsArray))
                if (@$fieldExistsArray['field_type'] == 'file')
                    $fileFolder = @$fieldExistsArray['field_p1'];

            //Приписка перед именем файла
            $filePre = '';
            if (!empty($fieldExistsArray))
                if (@$fieldExistsArray['field_type'] == 'file')
                    $filePre = @$fieldExistsArray['field_p2'];
            $isPicOn = '';
            $isPicOff = '';
            //Это поле-картинка или нет
            if (!empty($fieldExistsArray))
                if (@$fieldExistsArray['field_tinymce'] == 'pic') {
                    $isPicOn = ' selected'; $isPicOff = '';
                } else {
                    $isPicOn = ''; $isPicOff = ' selected';
                }

            //Настройки для сохранения уменьшенных копий
            if (!empty($fieldExistsArray))
                if (!empty($fieldExistsArray['field_default'])) {
                    $fileCopies[$fieldsData->name] = explode('*',$fieldExistsArray['field_default']);
                } else $fileCopies[$fieldsData->name] = Array();
            $MA_content .= "
              <div id='fileProp_".$fieldsData->name."'>
              <table width='90%' align='center' border ='0' cellspacing='2' cellpadding='0'>
                <tr valign='top' align='left'>
                  <td width='200'>Размер поля:</td>
                  <td><input type='text' name='fileFieldRules[".$fieldsData->name."]' size='10' maxlength='10' value='".str_replace("'","&#039;",$fileSize)."'></td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Сохранять в папку:</td>
                  <td>
                    <input type='text' name='fileFieldP1[".$fieldsData->name."]' size='50' maxlength='200' value='".str_replace("'","&#039;",$fileFolder)."'><br>
                    Правильно: img<br>Правильно: img/photos<br>Неправильно: /img<br>Неправильно: img/<br>Неправильно: /img/
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Приписывать перед именем в БД:</td>
                  <td>
                    <input type='text' name='fileFieldP2[".$fieldsData->name."]' size='50' maxlength='200' value='".str_replace("'","&#039;",$filePre)."'><br>
                    Правильно: img/<br>Правильно: img/photos/<br>Неправильно: /<br>Неправильно: /img<br>Неправильно: /img/
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Это картинка:</td>
                  <td>
                    <select name='fileFieldValidation[".$fieldsData->name."]' size='1'>
                      <option value='pic' ".$isPicOn.">Да</option>
                      <option value='' ".$isPicOff.">Нет</option>
                    </select>
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Создать копии:</td>
                  <td class='imageCopies'>
                  	<div id='imageCopies_".$fieldsData->name."'>";
            if (!empty($fileCopies) && !empty($fileCopies[$fieldsData->name])) {
                $fCCounter = 0;
                foreach ($fileCopies[$fieldsData->name] as $fileCopy) {
                    $settingsForCopy = explode(',',$fileCopy);

                    $sFCCrop = ''; $sFCFit = ''; $sFCLb = '';
                    if(isset($settingsForCopy[2])){
                        switch($settingsForCopy[2]) {
                            case 'crop': $sFCCrop = ' selected'; break;
                            case 'fit': $sFCFit = ' selected'; break;
                            case 'letterbox': $sFCLb = ' selected'; break;

                        }
                    }else{
                        $settingsForCopy[1] = '';
                        $settingsForCopy[2] = '';
                        $settingsForCopy[3] = '';
                        $settingsForCopy[4] = '';
                        $settingsForCopy[5] = '';
                        $settingsForCopy[6] = '';
                    }

                    /*
                        0 - width
                        1 - height
                        2 - action
                        3 - prefix
                        4 - r
                        5 - g
                        6 - b
                    */
                    $MA_content .= "
                    				<span>
                    				w: <input type='text' name='fileFieldDefault[".$fieldsData->name."][".$fCCounter."][0]' value='".$settingsForCopy[0]."' size='8'> "."
                    				h: <input type='text' name='fileFieldDefault[".$fieldsData->name."][".$fCCounter."][1]' value='".$settingsForCopy[1]."' size='8'> | "."
                    				r: <input type='text' name='fileFieldDefault[".$fieldsData->name."][".$fCCounter."][4]' value='".$settingsForCopy[4]."' size='4'> "."
                    				g: <input type='text' name='fileFieldDefault[".$fieldsData->name."][".$fCCounter."][5]' value='".$settingsForCopy[5]."' size='4'> "."
                    				b: <input type='text' name='fileFieldDefault[".$fieldsData->name."][".$fCCounter."][6]' value='".$settingsForCopy[6]."' size='4'> | "."
                    				действие: <select name='fileFieldDefault[".$fieldsData->name."][".$fCCounter."][2]'>"."
                    							<option value='crop' ".$sFCCrop.">Подрезать</option>
                    							<option value='fit' ".$sFCFit.">Вписать</option>
                    							<option value='letterbox' ".$sFCLb.">Вписать на фон</option>
                    						  </select> | "."
                    				префикс: <input type='text' name='fileFieldDefault[".$fieldsData->name."][".$fCCounter."][3]' value='".$settingsForCopy[3]."' size='8'> "."
                    				<span class='spanBtn removeCopyBtn'>-</span>
                    				<br>
                    				</span>";
                    $fCCounter++;
                }
            }
            $MA_content .= "</div><span class='spanBtn addCopyBtn' rel='".$fieldsData->name."'>+</span>
                  </td>
                </tr>
              </table>
              </div>
              <!--свойства файла-->

              <!--свойства связи-->";
            //Может ли быть пустым                                                              /////////////////////////////////////////
            $linkRulesEmpty = '';                                                               //                                     //
            $linkRulesNoempty = '';                                                             //  Если такое поле уже настраивалось  //
            if (!empty($fieldExistsArray))                                         //  извлекаем эти настройки            //
                if (@$fieldExistsArray['field_type'] == 'link')                                     //  для поля типа                      //
                    if (@$fieldExistsArray['field_rules'] == 'empty')                                 //  link                               //
                    {                                                                                 //                                     //
                        $linkRulesEmpty = 'selected';                                                   /////////////////////////////////////////
                    } else $linkRulesNoempty = 'selected';

            //Множественная или нет
            $linkMultiple = '';
            $linkSingle = 'selected';
            $linkDefault = '*';
            if (!empty($fieldExistsArray))
                if (@$fieldExistsArray['field_type'] == 'link') {
                    if (@$fieldExistsArray['field_default'] != '') {
                        $linkMultiple = 'selected';
                        $linkSingle = '';
                        $linkDefault = $fieldExistsArray['field_default'];
                    } else {
                        $linkSingle = 'selected';
                        $linkDefault = '*';
                    }
                }
            $MA_content .= "
              <div id='linkProp_".$fieldsData->name."'>
              <table width='90%' align='center' border ='0' cellspacing='2' cellpadding='0'>
                <tr valign='top' align='left'>
                  <td width='200'>C каким полем связать:</td>
                  <td>
                    <select name='linkFieldP1P2[".$fieldsData->name."]' size='1'>";
            //Формируем список таблиц и полей
            $allTablesQuery = $db->query("SHOW TABLES FROM {$DB}");
            while($allTablesArray = DB::fetch_row($allTablesQuery))
                //if (strpos($allTablesArray[0], 'my_admin_') === false)
            {
                $allFieldsQuery = $db->query("SELECT * FROM {$allTablesArray[0]};");
                for ($j=0; $j<DB::num_fields($allFieldsQuery); $j++)
                {
                    $allFieldsData = DB::fetch_field($allFieldsQuery, $j);
                    $MA_content .= "<option value='".$allTablesArray[0]."->".$allFieldsData->name."'";
                    if (!empty($fieldExistsArray))
                        if (@$fieldExistsArray['field_type'] == 'link')
                        {
                            if (@$fieldExistsArray['field_p1']==$allTablesArray[0] && @$fieldExistsArray['field_p2']==$allFieldsData->name)
                                $MA_content .= " selected";
                        }
                    $MA_content .= ">".$allTablesArray[0]."->".$allFieldsData->name."</option>";
                }
            }
            $MA_content .= "
                    </select>
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Может быть пустым:</td>
                  <td>
                    <select name='linkFieldRules[".$fieldsData->name."]' size='1'>
                      <option value='empty' ".$linkRulesEmpty.">Да</option>
                      <option value='noempty' ".$linkRulesNoempty.">Нет</option>
                    </select>
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Множественный выбор:</td>
                  <td>
                    <select name='linkFieldMultiple[".$fieldsData->name."]' size='1'>
                      <option value='yes' ".$linkMultiple.">Да</option>
                      <option value='' ".$linkSingle.">Нет</option>
                    </select>
                  </td>
                </tr>
                <tr valign='top' align='left'>
                  <td>Разделитель записей:</td>
                  <td>
                    <input type='text' name='linkFieldDefault[".$fieldsData->name."]' size='50' maxlength='5' value='".str_replace("'","&#039;",$linkDefault)."'>
                  </td>
                </tr>
              </table>
              </div>
              <!--свойства связи-->

				   <!--правило только для чтения -->
					<table width='90%' align='center' border ='0' cellspacing='2' cellpadding='0'>
					 <tr valign='top' align='left'>
                  <td width='200'>Только для чтения:</td>
                  <td><input type='checkbox' name='textFieldReadonly[".$fieldsData->name."]' ".($b_readonly?' checked':'')."></td>
                </tr>
					</table>

              <SCRIPT LANGUAGE='JavaScript' type='text/javascript'> editProp('".$fieldsData->name."'); </SCRIPT>";

            $MA_content .= "</div>";
            $MA_content .= "</div>";
            $MA_content .= "<SCRIPT LANGUAGE='JavaScript' type='text/javascript'> onOffField(document.getElementById('fieldBox_'+'".$fieldsData->name."')); </SCRIPT>";

        }

    }
    $MA_content .= "<input type='submit' name='saveFields' value='Сохранить' onclick='return checkCorrection();'>";
    $MA_content .= "</form>";


    //Форма для перехода к следующей таблице
    $MA_content .= "<form action='setup_fields.php' method='post'>";
    //Если есть еще таблицы, получаем id следующей
    $tableArray = $db->select_array_row("SELECT * FROM my_admin_tables WHERE id>".$tableId." ORDER BY id LIMIT 1;");
    if (empty($tableArray) || !empty($_REQUEST['oneTable']))
    {
        if (@$_REQUEST['oneTable'])
            $MA_content .= "<a href='setup_fields_settings.php' class='links'>Вернуться</a>";
        else
            $MA_content .= "<input type='submit' name='exitSetup' value='Завершить настройку'>";
    }
    else
    {
        $tableId = $tableArray['id'];
        $MA_content .= "<input type='hidden' name='tableId' value='".$tableId."'>";
        $MA_content .= "<input type='submit' name='nextId' value='Следующая таблица'>";
    }
    $MA_content .= "</form>";
}

if (@$_REQUEST['exitSetup']) {
    $MA_content .= "Все таблицы настроены<br>";
}
?>
<?php
//Подключаем шаблон
include ($MA_theme);
?>
