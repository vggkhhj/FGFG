<?php
require_once '../config.php';
require_once './config.php'; #### дополнительная конф. для админки
include_once '../common_libs.php';

if(!defined('DEFAULT_CHARSET')) define('DEFAULT_CHARSET', 'UTF-8');

$DB = DB_DATABASE;
$db = new DB(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);


#### установим нужную временныю зону
if (ADM_MYSQL_TIMEZONE) $db->query("SET time_zone = '" . ADM_MYSQL_TIMEZONE . "'");
if (ADM_PHP_TIMEZONE) date_default_timezone_set(ADM_PHP_TIMEZONE);

@session_start();

function checkAdmin($login, $password)
{
    global $db;
    //Проверяем, есть ли пользователь


    $adminArray = $db->select_array_row("SELECT * FROM `" . TABLE_USERS . "` WHERE user_login='" . sha1($login) . "' AND user_password='" . sha1($password) . "' LIMIT 1;");
    if (!empty($adminArray)) {
        //Если есть, то получаем значения его роли (уровень доступа)

        $roleA = $db->select_array_row("SELECT * FROM my_admin_roles WHERE id='" . $adminArray['user_role'] . "' LIMIT 1;");
        if (!empty($roleA)) {
            unset($adminArray['user_login'], $adminArray['user_password']);
            $userInfo = $adminArray;
            $userInfo['login'] = $login;
            $userInfo['password'] = $password;
            $userInfo['roles'] = $roleA;
            return $userInfo;
        }
    }
    return array();
}

/** Проверяет наличие и правильность ключа для авторизации без пароля */
function user_checkToken()
{
    global $db;
    if (!empty($_COOKIE['_token'])) {
        // проверим ключ
        $tokenQuery = "SELECT `user_id` FROM `my_admin_tokens` WHERE token='" . DB::escape($_COOKIE['_token']) . "' AND `date_losing`>'" . date('Y-m-d') . "' LIMIT 1;";
        $user_id = $db->select_result($tokenQuery);
        if (!empty($user_id)) {
            $userData = user_getData($user_id);
            $_SESSION['user'] = $userData;
            if (!empty($userData)) return true;
        }
    }
    return false;
}

/** Записывает новый ключ для авторизации без пароля */
function user_addToken($user_id)
{
    global $db;
    if (!defined('TOKEN_DAYS_DURATION')) define('TOKEN_DAYS_DURATION', 30);
    // сгенерируем ключ
    $token = md5($user_id . '_' . time());
    // запишем ключ
    $tokenAddQuery = "
		INSERT INTO `my_admin_tokens` (`user_id`, `token`, `date_losing`)
			VALUES ('$user_id','$token','" . date('Y-m-d', time() + TOKEN_DAYS_DURATION * 24 * 60 * 60) . "')
			ON DUPLICATE KEY UPDATE `token`=VALUES(`token`),`date_losing`=VALUES(`date_losing`);";
    $db->query($tokenAddQuery);
    setcookie('_token', $token, time() + TOKEN_DAYS_DURATION * 24 * 60 * 60, '/');
}

/** Удаляет ключ для авторизации без пароля */
function user_delToken($user_id)
{
    global $db;
    $tokenDelQuery = "DELETE FROM `my_admin_tokens` WHERE `user_id`=$user_id;";
    $db->query($tokenDelQuery);
    setcookie('_token', '', time() - 60, '/');
}


function user_getData($user_id)
{
    global $db;
    //Проверяем, есть ли пользователь
    $adminArray = $db->select_array_row("SELECT * FROM `" . TABLE_USERS . "` WHERE `id`=$user_id LIMIT 1;");
    if (!empty($adminArray)) {
        //Если есть, то получаем значения его роли (уровень доступа)
        $roleA = $db->select_array_row("SELECT * FROM my_admin_roles WHERE id='" . $adminArray['user_role'] . "' LIMIT 1;");
        if (!empty($roleA)) {
            unset($adminArray['user_login'], $adminArray['user_password']);
            $userInfo = $adminArray;
            $userInfo['roles'] = $roleA;
            return $userInfo;
        }
    }
    return array();
}

function updateTablesInSession()
{
    global $db;
    $tablesArray = Array();
    $tQ = $db->select_array("SELECT * FROM my_admin_tables;");
    if (!empty($tQ)) {
        foreach ($tQ as $tA) {
            $tA['fields'] = Array();
            $fQ = $db->select_array("SELECT * FROM my_admin_fields WHERE field_table='" . $tA['table_name'] . "';");
            if (!empty($fQ)) {
                foreach ($fQ as $fA) {
                    $tA['fields'][] = $fA;
                }
            }
            $tablesArray[$tA['table_name']] = $tA;
        }
    }
    $_SESSION['MA_tables'] = $tablesArray;
}

function getFieldType($fType, $fFlags)
{
    if (($fType == 'int' || $fType == 'string') && strpos($fFlags, 'binary') === false && strpos($fFlags, 'enum') === false) return 'text';
    if ($fType == 'string' && strpos($fFlags, 'binary') !== false) return 'checkbox';
    if ($fType == 'string' && strpos($fFlags, 'enum') !== false) return 'radio';
    if ($fType == 'blob') return 'textarea';
    if ($fType == 'timestamp' || $fType == 'date' || $fType == 'datetime') return 'date';
}



function my_admin_tablesExists()
{
    global $db;
    //Настройка полей возможна, если таблица со списком таблиц существует и в ней есть записи.
    $table = $db->select_array_row("SELECT * FROM my_admin_tables LIMIT 1;");
    if (!empty($table)) return 1; else return 0;
}

function printField($fieldId, $fieldValue, $readOnlyField = false)
{
    global $db;
    $fieldContent = '';
    $fieldArray = $db->select_array_row("SELECT * FROM my_admin_fields WHERE id='{$fieldId}';");
    if (!empty($fieldArray)) {
        $readOnly = $readOnlyField ? ' readonly ' : '';
        if ($fieldValue)
            $defaultValue = $fieldValue;
        else
            $defaultValue = $fieldArray['field_default'];

        #### v031. правило только для чтения
        $b_readonly = strpos($fieldArray['field_rules'], 'readonly') !== false ? true : false;
        //В зависимости от типа поля выводим его теги
        switch ($fieldArray['field_type']) {
            case 'text':
                if ($fieldArray['field_validation'] == 'password')
                    $fieldContent .= "<input type='password' name='" . $fieldArray['field_name'] . "' value='' " . $fieldArray['field_rules'] . " id='" . $fieldArray['field_name'] . "' ".$readOnly."><br>повторить:<br><input type='password' name='" . $fieldArray['field_name'] . "2' value='' " . $fieldArray['field_rules'] . " id='" . $fieldArray['field_name'] . "2'>";
                else
                    $fieldContent .= "<input type='text' name='" . $fieldArray['field_name'] . "' value='" . stripslashes(str_replace("'", "&#039;", $defaultValue)) . "' " . $fieldArray['field_rules'] . " id='" . $fieldArray['field_name'] . "'" . ($b_readonly ? ' disabled' : '') . " ".$readOnly.">";
                break;
            case 'textarea':
                $fieldContent .= "<textarea name='{$fieldArray['field_name']}' {$fieldArray['field_rules']} id='editor_{$fieldArray['field_name']}'" . ($b_readonly ? ' disabled' : '') . " ".$readOnly.">" . stripslashes($defaultValue) . "</textarea>";
                if (!empty($fieldArray['field_tinymce']))
                    $fieldContent .= "
                   		<script type='text/javascript'>
                   			CKEDITOR.replace('editor_" . $fieldArray['field_name'] . "', {toolbar:'" . $fieldArray['field_tinymce'] . "'});
                   		</script>
                   	";
                break;
            case 'checkbox':
                $fieldContent .= "<input type='checkbox' name='" . $fieldArray['field_name'] . "' id='checkbox_$fieldId' ";
                if ((@$fieldValue != '' && @$fieldValue != '0') || ($fieldValue === null && $defaultValue == '1' && $fieldArray['field_rules'] == 'checked')) $fieldContent .= "CHECKED";
                $fieldContent .= "" . ($b_readonly ? ' disabled' : '') . $readOnly.">";
                break;
            case 'radio':
                //получаем возможные значения поля enum

                $enumValuesArray = $db->select_array_row("SHOW COLUMNS FROM " . $fieldArray['field_table'] . " LIKE '" . $fieldArray['field_name'] . "';");
                $indexCounter = 0;
                while (preg_match("/'(.*?)'[,|)]/i", $enumValuesArray['Type'], $enumValuesPockets)) {
                    $fieldContent .= "<input name='" . $fieldArray['field_name'] . "' type='radio' value='" . ($indexCounter + 1) . "'";
                    if (!is_null($fieldValue) && $fieldValue == str_replace("''", "&#039;", $enumValuesPockets[1])) {
                        $fieldContent .= " checked";
                    }
                    if (is_null($fieldValue) && $defaultValue == $indexCounter) {
                        $fieldContent .= " checked";
                    }
                    $fieldContent .= "" . ($b_readonly ? ' disabled' : '') .$readOnly."> " . str_replace("''", "'", $enumValuesPockets[1]) . "<br>";
                    $enumValuesArray['Type'] = preg_replace("/'(.*?)'[,|)]/i", '', $enumValuesArray['Type'], 1);
                    $indexCounter++;
                }
                /*
                  $fieldContent .= "<SELECT name='".$fieldArray['field_name']."' size='1'>";
                     foreach($enumValues as $enumValuesKey => $enumValuesValue) {
                       $fieldContent .= "<OPTION value='".$enumValuesValue."'";
                       if ($fieldArray['field_default'] == $enumValuesKey) $fieldContent .= " SELECTED";
                       $fieldContent .= ">".$enumValuesValue."</OPTION>";
                     }
                $fieldContent .= "</SELECT>";
                */
                break;
            case 'date':
                $dateId = uniqid();
                $dateReadOnly = '';
                if ($fieldArray['field_tinymce'] == 'calendar') $dateReadOnly = "readonly";
                $dataValue = "";
                $calendarFormat = "";
                if ($fieldArray['field_p2'] == 'date') {
                    $calendarFormat = "%Y-%m-%d";
                    $dataValue .= date("Y-m-d");
                }
                if ($fieldArray['field_p2'] == 'datetime') {
                    $calendarFormat = "%Y-%m-%d %H-%M-00";
                    $dataValue .= date("Y-m-d H-i-s");
                }
                if ($fieldValue)
                    $dataValue = str_replace("'", "&#039;", $fieldValue);
                $fieldContent .= "<input type='text' name='" . $fieldArray['field_name'] . "' value='" . $dataValue . "' ";
                $fieldContent .= $fieldArray['field_rules'] . " id='" . $dateId . "' " . $dateReadOnly . "" . ($b_readonly ? ' disabled' : '') .$readOnly.">";
                //Если нужно подключить календарь
                if ($fieldArray['field_tinymce'] == 'calendar')
                    $fieldContent .= "
									   <script type='text/javascript'>
									     Calendar.setup({
									       inputField     :    '" . $dateId . "',      // id of the input field
									       ifFormat       :    '" . $calendarFormat . "',       // format of the input field
									       showsTime      :    true,            // will display a time selector
									       timeFormat     :    24,
									       button         :    '" . $dateId . "',   // trigger for the calendar (button ID)
									       singleClick    :    false,           // double-click mode
									       step           :    1                // show all years in drop-down boxes (instead of every other year as default)
									     });
									   </script>
									 ";
                break;
            case 'link':
                if ($fieldArray['field_default'] == '') {
                    //Для одиночного выбора
                    $fieldContent .= "<select size='1' name='" . $fieldArray['field_name'] . "'" . (($b_readonly || $readOnly) ? ' disabled' : '') . ">";
                    //создаем массив выбранных значений для этой записи
                    $fieldValueArray = array($fieldValue);
                } else {
                    //Для множественного выбора
                    $fieldContent .= "<select size='5' name='" . $fieldArray['field_name'] . "[]' multiple='yes'" . (($b_readonly || $readOnly) ? ' disabled' : '') . ">";
                    //создаем массив выбранных значений для этой записи
                    $fieldValueArray = explode($fieldArray['field_default'], $fieldValue);
                }
                //Если поле может быть пустым - выводим пустую строку
                if ($fieldArray['field_rules'] == 'empty')
                    $fieldContent .= "<option value=''> </option>";
                //Выводим содержимое поля, на которое ссылается данное поле и содержимое полей-идентификаторови таблицы, которой это поле принадлежит
                $linkFieldQuery = $db->select_array("SELECT * FROM {$fieldArray['field_p1']};");

                if (!empty($linkFieldQuery)) {
                    foreach ($linkFieldQuery as $linkFieldArray) {
                        if ($fieldArray['field_name'] == 'user_role' && $fieldArray['field_table'] == TABLE_USERS && $linkFieldArray[$fieldArray['field_p2']] == 1 && $_SESSION['user'][FIELD_USER_ROLE] != 1) continue;
                        $fieldContent .= "<option value='" . str_replace("'", "&#039;", $linkFieldArray[$fieldArray['field_p2']]) . "'";
                        if (in_array($linkFieldArray[$fieldArray['field_p2']], $fieldValueArray)) $fieldContent .= " selected";

                        #### Вася. Автовыбор категорий при каталогизаторе при добавлении новой записи
                        if (empty($_GET['recordId']) && !empty($_GET[$fieldArray['field_name']]) && $_GET[$fieldArray['field_name']] == $linkFieldArray[$fieldArray['field_p2']]) {
                            $fieldContent .= "selected"; // выделение категории, из кот. происходит добавление записи
                        }

                        $fieldContent .= ">";
                        $fieldContent .= $linkFieldArray[$fieldArray['field_p2']];


                        $identFieldsQuery = $db->select_array("SELECT * FROM my_admin_fields WHERE field_table='" . $fieldArray['field_p1'] . "' AND field_ident='1';");
                        $identFields = '';
                        if (!empty($identFieldsQuery)) {
                            foreach ($identFieldsQuery as $identFieldsArray) {
                                if ($identFields != '') $identFields .= ', ';
                                $identFields .= $linkFieldArray[$identFieldsArray['field_name']];
                            }
                        }
                        if ($identFields != '') $fieldContent .= ', ' . $identFields;
                        $fieldContent .= "</option>";
                    }
                }
                $fieldContent .= "</select>";
                //Если поле - значение прав доступа. Тогда его менять может только тот, кому разрешено
                if ($fieldArray['field_name'] == 'user_role' && $fieldArray['field_table'] == TABLE_USERS && $_SESSION['user']['roles']['role_roles'] == '0' && !in_array($_SESSION['user'][FIELD_USER_ROLE], array(1, 2))) {
                    $ROLE_EXPECTING = $identFields;
                    $fieldContent = "<input type='hidden' name='" . $fieldArray['field_name'] . "' value='" . $fieldValue . "'>" . $ROLE_EXPECTING;
                }
                break;
            case 'file':
                $fieldContent .= "Текущее значение: ";
                if (!empty($fieldValue)) {
                    $fieldContent .= $fieldValue . "<br>";
                    if ($fieldArray['field_tinymce'] == 'pic') $fieldContent .= "<img src='http://" . $_SERVER["SERVER_NAME"] . "/" . $fieldArray['field_p1'] . "/" . $fieldValue . "' width='100' border='0'><br>";
                } else $fieldContent .= "пусто";

                if(!$readOnly){
                    $fieldContent .= "<br>";
                    $fieldContent .= "<select size='1' name='actionWithFile[" . $fieldArray['field_name'] . "]'" . ($b_readonly ? ' disabled' : '') . ">";
                    $fieldContent .= "<option value='nothing'>";
                    if (!empty($fieldValue)) $fieldContent .= "Оставить без изменений"; else $fieldContent .= "Оставить пустым";
                    $fieldContent .= "</option>";
                    if (!empty($fieldValue)) $fieldContent .= "<option value='newfile'>Заменить файл на новый, а старый удалить</option>";
                    $fieldContent .= "<option value='replacefile'>";
                    if (!empty($fieldValue)) $fieldContent .= "Заменить файл на новый не удаляя старого"; else $fieldContent .= "Просто загрузить файл";
                    $fieldContent .= "</option>";
                    if (!empty($fieldValue)) $fieldContent .= "<option value='delrecord'>Очистить поле в БД</option>";
                    if (!empty($fieldValue)) $fieldContent .= "<option value='delall'>Очистить поле в БД и удалить файл</option>";
                    $fieldContent .= "</select><br>";
                    $fieldContent .= "<input type='file' name='" . $fieldArray['field_name'] . "'>";
                }

                break;
            #### ИЗВРАЩЁННОЕ ОТОБРАЖЕНИЕ
            case 'temp':
                switch ($fieldArray['field_table']) {
                    case 'articles': { // ТЕГИ ДЛЯ СТАТЕЙ
                        if (!empty($_GET['recordId'])) {
                            $query = "
									SELECT GROUP_CONCAT(`title` ORDER BY `title` SEPARATOR ', ') 
										FROM `tags` WHERE `id` IN 
										(
											SELECT `tag_id` FROM `tags_relation` WHERE `table`='{$fieldArray['field_table']}' AND `rec_id`=" . intval($_GET['recordId']) . "
										) AND `lang`='{$fieldArray['field_p1']}'";
                            $oldValue = $db->select_result($query);
                        } else {
                            $oldValue = '';
                        }
                        $fieldContent .= "<input type='text' name='" . $fieldArray['field_name'] . "' value='" . $oldValue . "' " . $fieldArray['field_rules'] . " id='" . $fieldArray['field_name'] . "' class='tags_autocomplete'>";
                        $fieldContent .= "<script type='text/javascript'>autocomleterToQueue('#{$fieldArray['field_name']}','" . uniqid() . "')</script>";
                    }
                        break;
                }
                break;

            default:
                $fieldContent .= "Тип не определен";
                break;
        }

        //echo mysql_num_rows($fieldQuery);
        //echo "<p>SELECT * FROM my_admin_fields WHERE id='".$fieldId."';</p>";
    } else $fieldContent .= "Поле не найдено";
    return $fieldContent;
}

function getRandomName()
{
    mt_srand();
    $generatedName = '';
    for ($i = 1; $i < 11; $i++)
        $generatedName .= chr(mt_rand(97, 122));
    return $generatedName;
}

function searchingField($fieldData, $fieldValue)
{
    global $db;
    $field = '';

    if ($fieldData['field_type'] == 'text' || $fieldData['field_type'] == 'textarea') {
        $field = "<input type='text' size='40' maxlength='200' name='search[" . $fieldData['field_name'] . "]'";
        if ($fieldValue) $field .= " value='" . $fieldValue . "'";
        $field .= ">";
    }

    if ($fieldData['field_type'] == 'date') {
        $field = "c <input type='text' size='10' maxlength='10' name='search[" . $fieldData['field_name'] . "][after]'";
        if ($fieldValue) $field .= " value='" . $fieldValue['after'] . "'";
        $field .= ">";
        $field .= " по <input type='text' size='10' maxlength='10' name='search[" . $fieldData['field_name'] . "][before]'";
        if ($fieldValue) $field .= " value='" . $fieldValue['before'] . "'";
        $field .= ">";
    }

    if ($fieldData['field_type'] == 'checkbox') {
        $field = "<select size='1' name='search[" . $fieldData['field_name'] . "]'>";
        $field .= "<option value='on'";
        if (@$fieldValue == 'on') $field .= " selected";
        $field .= ">Включено</option>";
        $field .= "<option value='off'";
        if (@$fieldValue == 'off') $field .= " selected";
        $field .= ">Выключено</option>";
        $field .= "</select>";
    }

    if ($fieldData['field_type'] == 'file') {
        $field = "<select size='1' name='search[" . $fieldData['field_name'] . "]'>";
        $field .= "<option value='on'";
        if (@$fieldValue == 'on') $field .= " selected";
        $field .= ">Есть файл</option>";
        $field .= "<option value='off'";
        if (@$fieldValue == 'off') $field .= " selected";
        $field .= ">Нет файла</option>";
        $field .= "</select>";
    }

    if ($fieldData['field_type'] == 'link') {
        //Получаем данные связанной таблицы
        $linkedTableQuery = $db->select_array("SELECT * FROM {$fieldData['field_p1']};");
        //Составляем значение из полей-идентификаторов связанной таблицы
        $identFieldsQuery = $db->select_array("SELECT * FROM my_admin_fields WHERE field_table='" . $fieldData['field_p1'] . "' AND field_ident='1';");
        //Если есть записи в связанной таблице и поля-идентификаторы для нее:
        if (!empty($linkedTableQuery) && !empty($identFieldsQuery)) {
            $field = "<select size='1' name='search[" . $fieldData['field_name'] . "]'>";
            //$field .= "<OPTION value='no_matter' SELECTED>Не важно</OPTION>";
            if ($fieldData['field_rules'] == 'empty')
                $field .= "<option value=''>Поле пустое</option>";


            foreach ($linkedTableQuery as $linkedTableArray) {
                $option = '';
                foreach ($identFieldsQuery as $identFieldsArray) {
                    if ($option != '') $option .= ', ';
                    $option .= $linkedTableArray[$identFieldsArray['field_name']];
                }
                $field .= "<option value='" . $linkedTableArray[$fieldData['field_p2']] . "'";
                if (@$fieldValue == $linkedTableArray[$fieldData['field_p2']]) $field .= " selected";
                $field .= ">" . $option . "</option>";
            }

            $field .= "</select>";
        } else $field .= "Поиск по полю невозможен";
    }
    return $field;
}

function getFieldTypeByName($fieldName)
{
    global $db;
    return $db->select_result("SELECT `field_type` FROM my_admin_fields WHERE field_name='{$fieldName}' LIMIT 1;");
}

function getFieldValue($fieldName, $key)
{
    global $db;
    return $db->select_result("SELECT {$key} FROM my_admin_fields WHERE field_name='{$fieldName}' LIMIT 1;");
}

//Проверяем все ли необходимые для модуля файлы есть
function moduleIsCorrect($moduleDir)
{
    $info = false;
    $install = false;
    $uninstall = false;
    $module = false;
    if ($dir = opendir('modules/' . $moduleDir)) {
        while (false !== ($dirItem = readdir($dir))) {
            switch ($dirItem) {
                case 'info.php':
                    $info = true;
                    break;
                case 'install.php':
                    $install = true;
                    break;
                case 'uninstall.php':
                    $uninstall = true;
                    break;
                case 'module.php':
                    $module = true;
                    break;
            }
        }
        closedir($dir);
    }
    if ($info && $install && $uninstall && $module)
        return true;
    else
        return false;
}

//Создаем скрипт проверки правильности ввода
//Создаем скрипт проверки правильности ввода
function printFieldValidation($id, $type = '', $title, $validation = '')
{
    $vText = '';
    switch ($type) {
        case 'text':
            if ($validation == 'notempty') {
                $vText .= "
                           if (!document.getElementById('" . $id . "').value) {
                             alert('Не заполнено поле " . $title . "!');
                             document.getElementById('" . $id . "').focus();
                             return false;
                           }
                         ";
            }
            if ($validation == 'password') {
                $vText .= "
                           if (document.getElementById('" . $id . "').value != document.getElementById('" . $id . "2').value) {
                             alert('Пароли не совпадают');
                             return false;
                           }
                         ";
            }
            if ($validation == 'email') {
                $vText .= "
                           var email = document.getElementById('" . $id . "').value;
                           email = email.toLowerCase();
                           emailTest = '^[_\\.0-9a-z-]+@([0-9a-z][0-9a-z_-]+\\.)+[a-z]{2,4}$';
                           var regex = new RegExp(emailTest);
                           if (!regex.test(email) || !(email.length > 0)) {
                             alert('Некорректный e-mail');
                             return false;
                           }
                         ";
            }
            if ($validation == 'emailorempty') {
                $vText .= "
                           var email = document.getElementById('" . $id . "').value;
                           email = email.toLowerCase();
                           emailTest = '^[_\\.0-9a-z-]+@([0-9a-z][0-9a-z_-]+\\.)+[a-z]{2,4}$';
                           var regex = new RegExp(emailTest);
                           if (!regex.test(email) && (email.length > 0)) {
                             alert('Некорректный e-mail');
                             return false;
                           }
                         ";
            }
            if ($validation == 'digit') {
                $vText .= "
                           var data = document.getElementById('" . $id . "').value;
                           dataTest = '^[0-9]*$';
                           var regex = new RegExp(dataTest);
                           if (!regex.test(data) && (data.length > 0)) {
                             alert('Поле " . $title . " может содержать только целое число!');
                             return false;
                           }
                         ";
            }
            break;
    }
    return $vText;
}

function printMA($MA_var = '')
{
    switch ($MA_var) {
        case 'MA_content':
        case 'MA_themePath':
        case 'MA_head':
        case 'MA_logo':
        case 'MA_pageTitle':
        case 'MA_navigation':
        case 'MA_siteLinks':
        case 'MA_siteDataLinks':
        case 'MA_mainLinks':
            if (isset($GLOBALS[$MA_var])) {
                echo $GLOBALS[$MA_var];
            }
            break;
    }
}

//Возвращает человеческое название таблицы по ее имени
function tableDescrByTableName($tName)
{
    global $db;
    $description = $db->select_result("SELECT table_descr FROM my_admin_tables WHERE `table_name`='{$tName}' LIMIT 1;");
    if (!empty($description)) {

        return $description;
    } else return false;
}

//Возвращает человеческое название поля по его имени
function fieldDescrByFieldName($fName)
{
    global $db;
    $field_descr = $db->select_result("SELECT `field_descr` FROM `my_admin_fields` WHERE `field_name` ='{$fName}' LIMIT 1;");
    if (!empty($field_descr)) {
        return $field_descr;
    } else return false;
}

//Возвращает опции для ролей по массиву возможных значение и предложенному варианту выбора
function rolesOptGen($optArray, $case = '')
{
    $opt = '';
    foreach ($optArray as $k => $v) {
        $opt .= "<option value='" . $v . "'";
        if ($v == $case) $opt .= " selected";
        $opt .= ">";
        $opt .= $k;
        $opt .= "</option>";
    }
    return $opt;
}

//Возвращает ID записи в журнале
function getLogId($rID, $tN)
{
    global $db;
    $logId = $db->select_result("SELECT `id` FROM my_admin_log WHERE log_record='" . $rID . "' AND log_table='" . $tN . "' LIMIT 1;");
    if (!empty($logId)) {
        return $logId;
    } else return 0;
}

//Срабатывает после добавления новой записи. Параметры: ID записи и имя таблицы
function MA_afterSave($rId, $tableName, $isNew = false)
{
    if (function_exists("MA_afterSave_" . $GLOBALS['MA_userPrefix']))
        call_user_func("MA_afterSave_" . $GLOBALS['MA_userPrefix'], $rId, $tableName, $isNew);
}

//Срабатывает после удаления записи. Параметры: ID записи, имя таблицы и массив данных, который был удален
function MA_afterDelete($rId, $tableName, $deletedArray)
{
    if (function_exists("MA_afterDelete_" . $GLOBALS['MA_userPrefix']))
        call_user_func("MA_afterDelete_" . $GLOBALS['MA_userPrefix'], $rId, $tableName, $deletedArray);
}

//Создает транслит входной строки
function translit4Alias($string)
{ #### v019. теперь нормально работает с UTF-8
    $rus = array("ё", "й", "ю", "ь", "ч", "щ", "ц", "у", "к", "е", "н", "г", "ш", "з", "х", "ъ", "ф", "ы", "в", "а", "п", "р", "о", "л", "д", "ж", "э", "я", "с", "м", "и", "т", "б", "Ё", "Й", "Ю", "Ч", "Ь", "Щ", "Ц", "У", "К", "Е", "Н", "Г", "Ш", "З", "Х", "Ъ", "Ф", "Ы", "В", "А", "П", "Р", "О", "Л", "Д", "Ж", "Э", "Я", "С", "М", "И", "Т", "Б");
    $eng = array("yo", "iy", "yu", "'", "ch", "sh", "c", "u", "k", "e", "n", "g", "sh", "z", "h", "'", "f", "y", "v", "a", "p", "r", "o", "l", "d", "j", "е", "ya", "s", "m", "i", "t", "b", "Yo", "Iy", "Yu", "CH", "'", "SH", "C", "U", "K", "E", "N", "G", "SH", "Z", "H", "'", "F", "Y", "V", "A", "P", "R", "O", "L", "D", "J", "E", "YA", "S", "M", "I", "T", "B");
    return str_replace($rus, $eng, $string);
}

//Создает алиас входной строки на основе ее транслита
function makeAlias($str, $len = 250)
{
    $str = substr($str, 0, $len);
    $str = translit4Alias($str);
    $str = strtolower($str);
    $str = preg_replace('/[^a-z0-9\-]/', ' ', $str);
    $str = trim($str);//лишние пробелы и херня в начале и конце строки
    $str = preg_replace('/ +/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return $str;
}

// собирает структуру файлов и папок, доступных для редактирования
function scanPath($path, $openNode = false)
{
    global $settings;
    // если файл - возвращаем true
    if (!is_dir($path)) {
        $output = true;
    } // если папка - сканируем глубже
    else {
        $output = array();
        $content = scandir($path);
        foreach ($content as $rec) {
            // пропускаем относительные пути
            if ($rec == '.' || $rec == '..') continue;
            $absPath = $path . $rec;
            if (is_dir($absPath)) $absPath .= '/';
            // если элемент полностью открыт
            if ($openNode) {
                $output[$rec] = scanPath($absPath, true);
            } // если нет - он может вести к открытому элементу
            else {
                foreach ($settings as $mask) {
                    $compare = substr_compare($mask, $absPath, 0);
                    // если путь СОДЕРЖИТСЯ в одном из элементов в настройках
                    if ($compare > 1) {
                        $output[$rec] = scanPath($absPath);
                        break;
                    } // если путь СОВПАДАЕТ с одним из элементов в настройках
                    elseif ($compare === 0) {
                        $output[$rec] = scanPath($absPath, true);
                        break;
                    }
                }
            }
        }
    }
    return $output;
}

// выводит дерево
function printTree($data, $relPath = '')
{
    $output = '';
    if ($data) foreach ($data as $name => $val) {
        $output .= '';
        if (is_array($val)) {
            $output .= '<div class="row folder">';
            $output .= '<div class="title expanded"><a class="foldLink" href="#"></a><span class="name">' . $name . '</span><span class="actions"><a href="?upload=' . $relPath . '/' . $name . '">Загрузить</a></span></div>';
            $output .= '<div class="folderContent">' . printTree($val, $relPath . '/' . $name) . '</div>';
            $output .= '</div>';
        } elseif ($val === true) {
            $output .= '<div class="row file"><div class="title"><span class="name">' . $name . '</span><span class="actions"><a href="?edit=' . $relPath . '/' . $name . '">Редактировать</a>  <a href="?download=' . $relPath . '/' . $name . '" class="loadFile">Скачать</a> <a href="?del=' . $relPath . '/' . $name . '" class="delFile">Удалить</a></span></div></div>';
        }
    }
    return $output;
}

//Изменяет размеры исходного изображения
function imgResample($imgFrom, $imgTo, $maxw, $maxh, $resize = 'fit', $r = false, $g = false, $b = false)
{

    $ext = strtolower(pathinfo($imgFrom, PATHINFO_EXTENSION));
    //если хотяб один из размеров указан
    if (!(($maxw == 0) && ($maxh == 0))) {
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $src = imagecreatefromjpeg($imgFrom);
                break;
            case 'gif':
                $src = imagecreatefromgif($imgFrom);
                break;
            case 'png':
                $src = imagecreatefrompng($imgFrom);
                break;
            default:
                return false;
        }
        $w = imagesx($src);
        $h = imagesy($src);

        //если указаны оба размера
        if (($maxw != 0) && ($maxh != 0)) {
            $prop = $w / $h;
            $newprop = $maxw / $maxh;
            if ($resize != 'crop') {
                if ($prop >= $newprop) {
                    $new_w = $w > $maxw ? $maxw : $w;
                    $new_h = $new_w / $prop;
                } else {
                    $new_h = $h > $maxh ? $maxh : $h;
                    $new_w = $new_h * $prop;
                }
                //если указан цвет фона
                if ((!empty($r) && !empty($g) && !empty($b)) && $resize == 'letterbox') {
                    $thumb = imagecreatetruecolor($maxw, $maxh);
                    $bgcol = imagecolorallocate($thumb, $r, $g, $b);
                    imagefill($thumb, 0, 0, $bgcol);
                    $pos_x = ($maxw - $new_w) / 2;
                    $pos_y = ($maxh - $new_h) / 2;
                    imagecopyresampled($thumb, $src, $pos_x, $pos_y, 0, 0, $new_w, $new_h, $w, $h);
                } //если фон не указан
                elseif ($resize == 'letterbox'){
                    $thumb = imagecreatetruecolor($maxw, $maxh);
                    if($ext == 'png'){
                        $bgcol = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
                        imagefill($thumb, 0, 0, $bgcol);
                        imagecolortransparent($thumb, $bgcol);
                        imagealphablending($thumb, false);
                        imagesavealpha($thumb, true);
                    }
                    if($ext == "gif"){
                        $bgcol = imagecolorallocatealpha($thumb, 0, 0, 0,127);
                        imagefill($thumb, 0, 0, $bgcol);
                        imagecolortransparent($thumb, $bgcol);
                    }
                    $pos_x = ($maxw - $new_w) / 2;
                    $pos_y = ($maxh - $new_h) / 2;
                    imagecopyresampled($thumb, $src, $pos_x, $pos_y, 0, 0, $new_w, $new_h, $w, $h);
                }
                elseif ($resize == 'fit') {
                    $thumb = imagecreatetruecolor($new_w, $new_h);
                    if($ext == "gif" or $ext == "png"){
                        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
                        imagealphablending($thumb, false);
                        imagesavealpha($thumb, true);
                    }
                    imagecopyresampled($thumb, $src, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
                }
            } else {
                if ($prop <= $newprop) {
                    $new_w = $w > $maxw ? $maxw : $w;
                    $new_h = $new_w / $prop;
                } else {
                    $new_h = $h > $maxh ? $maxh : $h;
                    $new_w = $new_h * $prop;
                }
                $pos_x = ($maxw - $new_w) / 2;
                $pos_y = ($maxh - $new_h) / 2;
                $thumb = imagecreatetruecolor($maxw, $maxh);
                #### Вася. заполнять указанным фоном даже обрезаемые изображения
                if (!empty($r) && !empty($g) && !empty($b)){
                    $bgcol = imagecolorallocate($thumb, $r, $g, $b);
                    imagefill($thumb, 0, 0, $bgcol);
                }elseif($ext == "gif" or $ext == "png"){
                    imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);
                }

                imagecopyresampled($thumb, $src, $pos_x, $pos_y, 0, 0, $new_w, $new_h, $w, $h);
            }
        } //если указан один размер
        else {
            if ($maxw != 0) {
                $new_w = $maxw;
                $new_h = $h * ($new_w / $w);
            } elseif ($maxh != 0) {
                $new_h = $maxh;
                $new_w = $w * ($new_h / $h);
            }
            $thumb = imagecreatetruecolor($new_w, $new_h);
            if($ext == "gif" or $ext == "png"){
                imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
            }
            imagecopyresampled($thumb, $src, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
        }

        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($thumb, $imgTo, 100);
                break;
            case 'gif':
                imagegif($thumb, $imgTo);
                break;
            case 'png':
                imagepng($thumb, $imgTo);
                break;
            default:
                return false;
        }
        imagedestroy($src);
        imagedestroy($thumb);
    }
}

//--------------------- набор функций для проверки прав пользователя ----------------------
/**
 * Проверяет права чтения таблицы
 * @param $tableName
 * @return bool
 */
function roleCheckReadTable($tableName){
    $userRoles = $_SESSION['user']['roles'];
    if($userRoles['role_title'] == 'root') return true;
    elseif(!isset($userRoles[$tableName]) || $userRoles[$tableName] == 0) return false;
    else return true;
}

/**
 * Проверяет права записи в таблицу
 * @param $tableName
 * @return bool
 */
function roleCheckWriteTable($tableName){
    $userRoles = $_SESSION['user']['roles'];
    if($userRoles['role_title'] == 'root') return true;
    elseif(isset($userRoles[$tableName]) && !in_array($userRoles[$tableName], array(0, 1, 4))) return true;
    else return false;
}

/**
 * Проверяет права чтения только своих записей
 * @param $tableName
 * @return bool
 */
function roleCheckReadTheirOnly($tableName){
    $userRoles = $_SESSION['user']['roles'];
    if($userRoles['role_title'] != 'root' &&  isset($userRoles[$tableName]) && in_array($userRoles[$tableName], array(1,2,3))) return true;
    else return false;
}

/**
 * Проверяет права на чтение записи
 * @param $tableName
 * @param $recordId
 * @return bool
 */
function roleCheckReadRecord($tableName, $recordId){
    global $db;
    $userRoles = $_SESSION['user']['roles'];
    if ($userRoles['role_title'] == 'root'  || in_array($userRoles[$tableName], array(4,5,6,7))) return true;
    elseif(isset($userRoles[$tableName]) && in_array($userRoles[$tableName], array(1,2,3))){
        $log_creator = $db->select_result("SELECT `id` FROM `my_admin_log` WHERE log_table = '{$tableName}' AND log_record = '{$recordId}' AND `log_creator` = '{$_SESSION['user']['id']}'");
        if(!empty($log_creator)) return true;
        else return false;
    }else return false;
}


/**
 * Проверяет права на изменение записи
 * @param $tableName
 * @param $recordId
 * @return bool
 */
function roleCheckWriteRecord($tableName, $recordId){
    global $db;
    $userRoles = $_SESSION['user']['roles'];
    if ($userRoles['role_title'] == 'root' || $userRoles[$tableName] == 5 || $userRoles[$tableName] == 6 || $userRoles[$tableName] == 7) return true;
    elseif(isset($userRoles[$tableName]) && ($userRoles[$tableName] == 2 || $userRoles[$tableName] == 3)){
        $log_creator = $db->select_result("SELECT `id` FROM `my_admin_log` WHERE log_table = '{$tableName}' AND log_record = '{$recordId}' AND `log_creator` = '{$_SESSION['user']['id']}'");
        if(!empty($log_creator)) return true;
        else return false;
    }else return false;

}

/**
 * Проверяет права на удаление записи
 * @param $tableName
 * @param $recordId
 * @return bool
 */
function roleCheckDelete($tableName, $recordId){
    global $db;
    $userRoles = $_SESSION['user']['roles'];
    if ($userRoles['role_title'] == 'root' || $userRoles[$tableName] == 6 || $userRoles[$tableName] == 7) return true;
    elseif($userRoles[$tableName] == 3){
        $log_creator = $db->select_result("SELECT `id` FROM `my_admin_log` WHERE log_table = '{$tableName}' AND log_record = '{$recordId}' AND `log_creator` = '{$_SESSION['user']['id']}'");
        if(!empty($log_creator)) return true;
        else return false;
    }else return false;
}
