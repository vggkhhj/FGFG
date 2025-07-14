<?php
  //TODO: при добавлении нового пользователя делать запись в my_admin_log
require_once ('common.php');

if(!empty($_GET['tableName'])){
    //название таблицы
    $tableName = $_GET['tableName'];
    
    $tableFields = $db->select_array("SELECT * FROM my_admin_fields WHERE field_table='{$tableName}';");

    $view_errors = array();
    //проверяем права на доступ к это таблице
    if(roleCheckReadTable($tableName)){

        //Устанавливаем кол-во записей на странице для просмотра в этой таблице
        if(!isset($_SESSION['setRecordsPerPage_'.$tableName])){
            $_SESSION['setRecordsPerPage_'.$tableName] = 10;
        }
        if(!empty($_GET['setRecordsPerPage'])){
            $_SESSION['setRecordsPerPage_'.$tableName] = $_GET['setRecordsPerPage'];
        }

        //Устанавливаем сортировку для этой страницы
        if (!isset($_SESSION[$tableName]['sorting'])) $_SESSION[$tableName]['sorting'] = 'id DESC';
        if (isset($_GET['sortBy'])) {
            if (preg_match("/^".$_GET['sortBy']."(([[:space:]])|($))/i", $_SESSION[$tableName]['sorting'])) {
                if (strpos($_SESSION[$tableName]['sorting'], ' DESC')) {
                    $_SESSION[$tableName]['sorting'] = $_GET['sortBy'];
                } else $_SESSION[$tableName]['sorting'] .= " DESC";
            } else $_SESSION[$tableName]['sorting'] = $_GET['sortBy'].' DESC';
        }

        //Если запрос на добавление и есть разрешение на запись в таблицу
        if (!empty($_REQUEST['addRecord']) && roleCheckWriteTable($tableName)) {
            if (!empty($tableFields)) {
                $fArray = Array();
                foreach ( $tableFields as $fieldsArray) {
                    #### v031. правило только для чтения
                    $b_readonly=strpos($fieldsArray['field_rules'],'readonly') !==false ? true : false;
                    if($b_readonly) continue;


                    //Если это поле требует какой-то модуль для изменений - вызываем его.
                    $modulesCheckQuery = $db->select_array("SELECT mam.*, mafdc.fdc_params
                                         FROM my_admin_fields maf, my_admin_fdc mafdc, my_admin_modules mam
                                         WHERE maf.field_name='".$fieldsArray['field_name']."' and mafdc.fdc_field=maf.id and mam.id=mafdc.fdc_module and maf.field_table='{$tableName}';");

                    if (!empty($modulesCheckQuery)){
                        foreach ($modulesCheckQuery as $moulesCheckArray){
                            require_once('modules/'.$moulesCheckArray['module_name'].'/module.php');
                            call_user_func($moulesCheckArray['module_name'].'_update_data', $fieldsArray['field_name'], $moulesCheckArray['fdc_params']);
                        }
                    }

                    //Получаем список полей, которые нужно заполнить и пытаемся внести в них данные

                    //В зависимости от типа поля записываем в поле данные
                    //----------------- текстовое поле -----------------------
                    if ($fieldsArray['field_type'] == 'text') {
                        if ($fieldsArray['field_validation'] == 'password') {
                            if (!empty($_POST[$fieldsArray['field_name']])) {

                                //проверяем на уникальность, если поле типа пароль, таблица любая
                                //TODO: если была ошибка смены одного поля, другие тоже сохранять не надо
                                $validPassword = false;
                                $existRec = $db->select_array_row("SELECT * FROM {$tableName} WHERE `".$fieldsArray['field_name']."` = '".sha1($_POST[$fieldsArray['field_name']])."'");
                                if (empty($existRec)) $validPassword = true;
                                else {
                                    //var_dump(mysql_num_rows($loginExistQuery), $_POST['recordId'], $existRec);
                                    if (intval($_POST['recordId']) == intval($existRec['id'])) $validPassword = true;
                                }
                                //var_dump($validPassword);
                                if ($validPassword) $fArray[$fieldsArray['field_name']] = sha1($_POST[$fieldsArray['field_name']]);
                                else $passFieldExist[] = $fieldsArray['field_descr'];
                            }
                        }
                        else $fArray[$fieldsArray['field_name']] = DB::escape($_POST[$fieldsArray['field_name']]);
                        if ($fieldsArray['field_p1'] != '') {
                            $fieldAlias = makeAlias($_POST[$fieldsArray['field_name']]);
                            switch ($fieldsArray['field_p2']) {
                                default:
                                case 'nothing': break;
                                case 'uniqid': $fieldAlias .= "-".uniqid(); break;
                                case 'cur_seconds': $fieldAlias .= "-".date("s"); break;
                                case 'cur_date': $fieldAlias .= "-".date("d-m-Y"); break;
                                case 'cur_datetime': $fieldAlias .= "-".date("d-m-Y-H-i-s"); break;
                                #### v027. Запомним, что нужно добавить
                                case 'this_recordid': $fieldAlias = "-".$fieldAlias;
                                    if(empty($fArray[$fieldsArray['field_p1']])){
                                        $ConcatIdToAlias[]=$fieldsArray['field_p1'];
                                    }
                                    break;
                            }
                            $fArray[$fieldsArray['field_p1']] = $fieldAlias;
                        }
                    }

                    //---------------- textarea ---------------
                    if ($fieldsArray['field_type'] == 'textarea' || $fieldsArray['field_type'] == 'date' || $fieldsArray['field_type'] == 'radio')
                        $fArray[$fieldsArray['field_name']] = DB::escape(str_replace('$','&#036;',$_POST[$fieldsArray['field_name']]));

                    //---------------- link ---------------
                    if ($fieldsArray['field_type'] == 'link') {
                        $linkRecords = '';
                        if ($fieldsArray['field_default'] != '') {
                            //Сохраняем как множественную связь
                            if (count($_POST[$fieldsArray['field_name']]) > 0)
                                $linkRecords = implode($fieldsArray['field_default'], $_POST[$fieldsArray['field_name']]);
                        }
                        else
                            //Сохраняем как однозначную
                            $linkRecords = $_POST[$fieldsArray['field_name']];
                        $fArray[$fieldsArray['field_name']] = DB::escape($linkRecords);
                    }

                    //---------------- checkbox ---------------
                    if ($fieldsArray['field_type'] == 'checkbox')
                    {
                        if (!empty($_POST[$fieldsArray['field_name']]))
                            $fArray[$fieldsArray['field_name']] = DB::escape($fieldsArray['field_default']);
                        else $fArray[$fieldsArray['field_name']] = '0';
                    }

                    //---------------- file ---------------
                    if ($fieldsArray['field_type'] == 'file' && !empty($_POST['actionWithFile'][$fieldsArray['field_name']]))
                    {
                        $action_file = $_POST['actionWithFile'][$fieldsArray['field_name']];
                        //действие над файлом
                        if ($action_file != 'nothing')
                        {


                            //Если требовалось очистить поле или очистить поле и удалить файл
                            if ($action_file == 'delrecord')
                                $fArray[$fieldsArray['field_name']] = '';

                            if ($action_file == 'delall' || $action_file == 'newfile')
                            {
                                //Удаляем файл
                                if (!empty($_POST['recordId']) && !empty($tableName) && roleCheckWriteRecord($tableName, $_POST['recordId']))
                                {
                                    $fileArray = $db->select_array_row("SELECT * FROM {$tableName} WHERE id='".$_POST['recordId']."';");
                                    if (!empty($fileArray)) {
                                        @unlink($_SERVER["DOCUMENT_ROOT"]."/".$fieldsArray['field_p1']."/".$fileArray[$fieldsArray['field_name']]);
                                        //если файл - картинка, и были копии
                                        if ($fieldsArray['field_tinymce']=='pic' && $fieldsArray['field_default']!='') {
                                            $copiesForFile = explode('*', $fieldsArray['field_default']);
                                            if ($copiesForFile)
                                                foreach ($copiesForFile as $copieForFile) {
                                                    $settingForFileCopy = explode(',',$copieForFile);
                                                    @unlink($_SERVER["DOCUMENT_ROOT"]."/".$fieldsArray['field_p1']."/".$settingForFileCopy['3'].$fileArray[$fieldsArray['field_name']]);
                                                }
                                        }
                                    }
                                }
                                if ($action_file == 'delall')
                                    $fArray[$fieldsArray['field_name']] = '';
                            }
                            //Если требовалось заменить файл не удаляя старого или заменить с удалением и файл передан
                            if ($action_file == 'replacefile' || $action_file == 'newfile'){
                                if (!empty($_FILES[$fieldsArray['field_name']])) {
                                    @preg_match("/(?:[^$]*)\.([^$]*)/", @$_FILES[$fieldsArray['field_name']]["name"], $pockets);
                                    //echo @$pockets[1];
                                    $path_parts = pathinfo($_FILES[$fieldsArray['field_name']]["name"]);
                                    if (!empty($pockets[1]))
                                        $fileName = rand(1000,9999)."_".makeAlias($path_parts["filename"]).".".@$pockets[1];
                                    else
                                        $fileName = rand(1000,9999)."_".makeAlias($path_parts["filename"]);
                                    @copy($_FILES[$fieldsArray['field_name']]["tmp_name"], $_SERVER["DOCUMENT_ROOT"]."/".$fieldsArray['field_p1']."/".$fileName);
                                    //echo $_SERVER["DOCUMENT_ROOT"]."/".$fieldsArray['field_p1']."/".$fileName;
                                    $fArray[$fieldsArray['field_name']] = DB::escape($fieldsArray['field_p2'].$fileName);
                                }
                            }

                            //если файл - картинка, и к нему нужны копии
                            if ($fieldsArray['field_tinymce']=='pic' && !empty($fieldsArray['field_default'])) {
                                $copiesForFile = explode('*', $fieldsArray['field_default']);
                                if (!empty($copiesForFile)){
                                    foreach ($copiesForFile as $copieForFile) {
                                        $settingForFileCopy = explode(',',$copieForFile);
                                        imgResample($_SERVER["DOCUMENT_ROOT"]."/".$fieldsArray['field_p1']."/".$fileName, $_SERVER["DOCUMENT_ROOT"]."/".$fieldsArray['field_p1']."/".$settingForFileCopy['3'].$fileName, $settingForFileCopy['0'], $settingForFileCopy['1'], $resize=$settingForFileCopy['2'], $resize=$settingForFileCopy['4'], $resize=$settingForFileCopy['5'], $resize=$settingForFileCopy['6']);
                                    }
                                }

                            }
                        }

                    }
                    ####
                    //---------------- temp ---------------
                    if ($fieldsArray['field_type'] == 'temp'){
                        $fArray[$fieldsArray['field_name']] = DB::escape(str_replace('$','&#036;',$_POST[$fieldsArray['field_name']]));
                    }
                }

                //Собираем поля для запроса
                $updateFields = array();
                foreach ($fArray as $fArrayKey => $fArrayValue) {
                    $updateFields[$fArrayKey] = $fArrayValue;
                }

                //Если был передан ID для перезаписи
                if (!empty($_POST['recordId']) && roleCheckWriteRecord($tableName, $_POST['recordId'])) {
                    $db->update_assoc($tableName, $updateFields, "id=".$_POST['recordId']);
                    $db->query("UPDATE my_admin_log SET log_table='{$tableName}', log_record='".$_POST['recordId']."', log_modifier='".$_POST['modifierId']."' WHERE id='".$_POST['logId']."' LIMIT 1;");
                    MA_afterSave($_POST['recordId'], $tableName, false);
                    #### v027. Префикс к alias`у из ID записи
                    if(!empty($ConcatIdToAlias)){
                        foreach($ConcatIdToAlias as $tmpCFiled){
                            $tmpSet[]="`$tmpCFiled`=CONCAT(`id`,`$tmpCFiled`)";
                        }
                        $tmpSetString=implode(', ',$tmpSet);
                        $db->query("UPDATE {$tableName} SET ".$tmpSetString." WHERE id='".$_POST['recordId']."' LIMIT 1;");
                    }
                }
                elseif(empty($_POST['recordId'])) {

                    $iID = $db->insert_assoc($tableName, $updateFields);
                    #### v027. Префикс к alias`у из ID записи
                    if(!empty($ConcatIdToAlias)){
                        foreach($ConcatIdToAlias as $tmpCFiled){
                            $tmpSet[]="`$tmpCFiled`=CONCAT(`id`,`$tmpCFiled`)";
                        }
                        $tmpSetString=implode(', ',$tmpSet);
                        $db->query("UPDATE {$tableName} SET ".$tmpSetString." WHERE id='".$iID."' LIMIT 1;");
                    }
                    $db->query("INSERT INTO my_admin_log SET log_table='".$tableName."', log_record='".$iID."', log_creator='".$_POST['creatorId']."', log_modifier='".$_POST['modifierId']."';");
                    MA_afterSave($iID, $tableName, true);
                }else{
                    $view_errors[] = "Не достаточно прав для изменения записи";
                }
            }
        }elseif(!empty($_GET['recordId'])){
            $view_errors[] = "Не достаточно прав для изменения записи";
        }

        //Если был запрос на удаление
        if (!empty($tableName) && !empty($_GET['del']) && !empty($_GET['recordId'])) {
            //Проверяем права
            if(roleCheckDelete($tableName, $_GET['recordId'])){
                $deletingRecord = $db->select_array_row("SELECT * FROM {$tableName} WHERE id='".$_GET['recordId']."' LIMIT 1;");

                //удаляем файлы, которые были прикреплены к записи и все копии файла
                $fileFieldsQuery = $db->select_array("SELECT * FROM `my_admin_fields` WHERE `field_table`='".$tableName."' AND (field_type='file' OR field_type='textarea');");
                if (!empty($fileFieldsQuery)) {
                    foreach ($fileFieldsQuery as $fileFiled) {
                        //Для удаления файлов, которые хранились в полях типа file: получим все поля типа file для этой таблицы
                        if ($fileFiled['field_type'] == 'file') {
                            //Удаляем основной файл
                            @unlink($_SERVER["DOCUMENT_ROOT"]."/".$fileFiled['field_p1']."/".$deletingRecord[$fileFiled['field_name']]);
                            //Выбираем настройки для копий
                            $fileCopies = explode('*', $fileFiled['field_default']);
                            if ($fileCopies)
                                foreach ($fileCopies as $fileCopy) {
                                    $fileCopySettings = explode(',', $fileCopy);
                                    //удаляем каждую копию
                                    @unlink($_SERVER["DOCUMENT_ROOT"]."/".$fileFiled['field_p1']."/".$fileCopySettings['3'].$deletingRecord[$fileFiled['field_name']]);
                                }
                        }
                        //Для удаления картинок, загруженных через ckeditor
                        if ($fileFiled['field_type'] == 'textarea') {
                            preg_match_all('/(?:src="\/files\/user\/)([^>]*?)(?:")/im',$deletingRecord[$fileFiled['field_name']],$foundTextAreaFiles);
                            if (@$foundTextAreaFiles[1])
                                foreach ($foundTextAreaFiles[1] as $fileFromTextArea) {
                                    //удаляем его
                                    @unlink($_SERVER["DOCUMENT_ROOT"]."/files/user/".$fileFromTextArea);
                                }
                        }
                    }
                }

                $db->query("DELETE FROM {$tableName} WHERE id='".$_GET['recordId']."' LIMIT 1;");
                $db->query("DELETE FROM my_admin_log WHERE log_record='".$_GET['recordId']."' AND log_table='".$tableName."';");
                MA_afterDelete($_GET['recordId'], $tableName, $deletingRecord);
            }else{
                $view_errors[] = "Не достаточно прав для удаления записи";
            }


        }

        //Проверяем передано ли имя таблицы и существует ли она
        $continue = false;
        $currentTableA = $db->select_array_row("SELECT * FROM my_admin_tables WHERE table_name='{$tableName}';");
        if (!empty($tableFields) && $db->query("SELECT * FROM {$tableName} LIMIT 1;")) {
            $MA_pageTitle = $currentTableA['table_descr'];
            $continue = true;
        }

        if ($continue === true) {
            //Формируем запрос на выборку из таблицы.

            //--------------- логика поиска ------------------
            //записываем логику поиска в сессию а уже потом берем из сессии

            if( !empty($_REQUEST['searchBtn']) && !empty($_POST['searchInTable']) && !empty($_POST['searchLogic'])){
                $_SESSION[$_POST['searchInTable']]['search'] = array(
                    'searchBox'=> (!empty($_POST['searchBox'])) ? $_POST['searchBox'] : array(),
                    'search'=>$_POST['search'],
                    'searchType'=>$_POST['searchType'],
                    'searchLogic'=>$_POST['searchLogic'],
                    'searchBtn'=>$_REQUEST['searchBtn']

                );
            }elseif(!empty($_POST['searchClear'])){
                $_SESSION[$tableName]['search'] = array();
            }
            if(!empty($_SESSION[$tableName]['search'])){
                $searchLogic = $_SESSION[$tableName]['search'];
            }else{
                $searchLogic = array(
                    'searchBox'=>array(),
                    'search'=>array(),
                    'searchType'=>array(),
                    'searchLogic'=>'or',
                    'searchBtn'=>'Найти'
                );
            }

            if (!empty($searchLogic['searchBox']))
            {
                $where = '';
                if (count($searchLogic['searchBox'])>0)
                    foreach ($searchLogic['searchBox'] as $searchKey => $searchValue)
                    {
                        if ($where != '') $where .= " ".$searchLogic['searchLogic']." ";
                        if (getFieldTypeByName($searchKey) == 'text' || getFieldTypeByName($searchKey) == 'textarea')
                        {
                            //Если точно или неточное соответствие
                            if ($searchLogic['searchType'][$searchKey] == '=')
                                $where .= $tableName.".".$searchKey."='".DB::escape($searchLogic['search'][$searchKey])."'";
                            else
                                $where .= $tableName.".".$searchKey." LIKE '%".DB::escape($searchLogic['search'][$searchKey])."%'";
                        }
                        if (getFieldTypeByName($searchKey) == 'date') $where .= "(".$tableName.".".$searchKey.">='".$searchLogic['search'][$searchKey]['after']."' and ".$tableName.".".$searchKey."<='".$searchLogic['search'][$searchKey]['before']."')";
                        if (getFieldTypeByName($searchKey) == 'file')
                        {
                            if ($searchLogic['search'][$searchKey] == 'on') $where .= $tableName.".".$searchKey."<>''";
                            if ($searchLogic['search'][$searchKey] == 'off') $where .= $tableName.".".$searchKey."=''";
                        }
                        if (getFieldTypeByName($searchKey) == 'checkbox')
                        {
                            if ($searchLogic['search'][$searchKey] == 'on') $where .= $tableName.".".$searchKey."<>'0'";
                            if ($searchLogic['search'][$searchKey] == 'off') $where .= $tableName.".".$searchKey."='0'";
                        }
                        if (getFieldTypeByName($searchKey) == 'link') {
                            if (getFieldValue($searchKey, 'field_default') != '' && $searchLogic['search'][$searchKey] != '')
                                $where .= $tableName.".".$searchKey." LIKE '%".$searchLogic['search'][$searchKey]."%'";
                            else
                                $where .= $tableName.".".$searchKey."='".$searchLogic['search'][$searchKey]."'";
                        }
                    }
                $selectQuery = "SELECT ".$tableName.".* FROM ";
                $from = $tableName;
                //Проверяем права на чтение
                //если можно читать только свои записи
                if (roleCheckReadTheirOnly($tableName)) {
                    if ($where != '') $where = "(".$where.") and (mal.log_creator='".$_SESSION['user']['id']."' AND mal.log_table='".$tableName."' AND mal.log_record=".$tableName.".id)";
                    else $where .= "my_admin_creator='".$_SESSION['user']['id']." AND mal.log_table='".$tableName."' AND mal.log_record=".$tableName.".id";
                    $from .= ", my_admin_log mal";
                }
                $selectQuery .= $from;
                if ($where != '') $selectQuery .= " WHERE ".$where;
                $cPager = new ac_pager();
                $cPager->setQuery($selectQuery." ORDER BY ".$_SESSION[$tableName]['sorting'].";");
                #### Вася. Запомним строку
                $search_filter=$where;
                //Все записи выводятся на одной странице
                $cPager->setRecordsPerPage($cPager->recordsCnt);
            }
            //Если нет, то по умолчанию:
            else
            {
                $cPager = new ac_pager();
                if (roleCheckReadTheirOnly($tableName)) {
                    //$where = " WHERE my_admin_creator='".$_SESSION['user']['id']."'";
                    $QS = "SELECT ".$tableName.".*, my_admin_log.log_creator FROM ".$tableName.", my_admin_log WHERE my_admin_log.log_record=".$tableName.".id AND my_admin_log.log_table='".$tableName."' AND my_admin_log.log_creator='".$_SESSION['user']['id']."' ORDER BY ".$_SESSION[$tableName]['sorting'].";";
                } else $QS = "SELECT * FROM {$tableName} ORDER BY ".$_SESSION[$tableName]['sorting'].";";
                $cPager->setQuery($QS);
                if (!empty($_SESSION['setRecordsPerPage_'.$tableName]))
                    $cPager->setRecordsPerPage($_SESSION['setRecordsPerPage_'.$tableName]);
                else $cPager->setRecordsPerPage(10);
            }
            //--------------- конец логика поиска ------------------

            //Проверяем, есть ли записи в таблице
            $nowOnPage=1;
            if (!empty($_REQUEST['pageNbr'])) $nowOnPage = $_REQUEST['pageNbr'];

            $cPager->setCurrentPage($nowOnPage);

            $rA = Array(); //Будет массив с записями таблицы, которые необходимо отобразить
            $pA = Array(); //Будет массив со списком страниц, на которые разбиты записи
            $sA = Array(); //Будет массив со списком полей для поиска
            $oA = Array(); //Будет массив c ссылками для управления таблицей
            if ($cPager->recordsCnt > 0) {
                #### Вася. Получаем данные здесь
                $fieldsIdentData = $db->select_array("SELECT * FROM my_admin_fields WHERE field_table='".$tableName."' and field_ident='1';");
                while ($fieldsArray = $cPager->getRow()) {
                    //Формируем массив с записями
                    $rA_temp = Array();
                    $rA_temp['id'] = $fieldsArray['id'];
                    $rA_temp['zebra'] = $cPager->zebra;
                    $rA_temp['table_name'] = $tableName;
                    //Получаем все поля-идентификаторы
                    $ident = "";//получим в эту переменную собранные через запятую все слова-идентификаторы
                    $IA = Array(); //массив, где ключ - имя поля-идентификатора, а значение - значение этого поля
                    foreach ($fieldsIdentData as $fieldsIdentArray) {
                        if ($ident != '') $ident .= ", ";
                        $ident .= $fieldsArray[$fieldsIdentArray['field_name']];
                        $IA[$fieldsIdentArray['field_name']] = $fieldsArray[$fieldsIdentArray['field_name']];
                    }
                    $rA_temp['descr'] = htmlspecialchars(stripslashes($ident), ENT_IGNORE, DEFAULT_CHARSET); ####
                    $rA_temp['descrArray'] = $IA;
                    $rA_temp['links'] = Array();//Здесь храним сразу сформированные ссылки (само значение href)
                    $rA_temp['links']['edit'] = "add_to_table.php?tableName=".$tableName."&recordId=".$fieldsArray['id'];
                    if ($currentTableA['table_may_delete'] != '0') {
                        //Проверяем права на удаление
                        if (roleCheckDelete($tableName, $fieldsArray['id']))
                            $rA_temp['links']['delete'] = "view_table.php?tableName=".$tableName."&recordId=".$fieldsArray['id']."&del=true";
                    }
                    else
                        $rA_temp['links']['delete'] = "";
                    $rA[] = $rA_temp;
                }

                //Формируем массив со списком страниц
                $SCRIPT_NAME = $_SERVER['SCRIPT_NAME'];
                $pA['firstPage'] = $SCRIPT_NAME."?tableName=".$tableName."&pageNbr=1";
                $pA['lastPage'] = $SCRIPT_NAME."?tableName=".$tableName."&pageNbr=".$cPager->pagesCnt;
                $pA['currentPage'] = $cPager->currentPage;
                $pA['pagesCnt'] = $cPager->pagesCnt;
                $pA_temp =  Array();
                if ($cPager->pagesCnt > 1)
                    for ($i = 1; $i <= $cPager->pagesCnt; $i++)
                        $pA_temp[$i] = $SCRIPT_NAME."?tableName=".$tableName."&pageNbr=".$i;
                $pA['pagesLinks'] = $pA_temp;

            } else $pA['pagesLinks'] = Array(); //Значит на страницы не разбито

            //-------------------- поиск ---------------------

            //Формируем массив полей поиска
            $sA['submit'] = "<input type='submit' name='searchBtn' value='Найти'>";
            $sA['clear'] = "<input type='submit' name='searchClear' value='Сбросить фильтр'>";

            $logic_ = "<select name='searchLogic' size='1'><option value='or'";
            if (!isset($searchLogic['searchLogic']) || $searchLogic['searchLogic'] == 'or')
                $logic_ .= " selected";
            $logic_ .= ">Логическое 'ИЛИ'</option><option value='and'";
            if (!empty($searchLogic['searchLogic']) && $searchLogic['searchLogic'] == 'and')
                $logic_ .= " selected";
            $logic_ .= ">Логическое 'И'</option></option>";
            $sA['logic'] = $logic_;


            $sA['formStart'] = "<form action='".u_print_Eback(false)."' method='POST'><input type='hidden' name='searchInTable' value='".$tableName."'>";
            $sA['formEnd'] = "</form>";
            $sA['fields'] = Array();
            //Получаем список полей для поиска
            if(!empty($tableFields)){
                foreach ($tableFields as $searchFieldsArray) {
                    $sA_ = Array();
                    $checkbox_ = "<input type='checkbox' name='searchBox[".$searchFieldsArray['field_name']."]'";
                    if (!empty($searchLogic['searchBox'][$searchFieldsArray['field_name']]))
                        $checkbox_ .= " checked";
                    $checkbox_ .= ">";
                    $sA_['checkbox'] = $checkbox_;
                    $sA_['descr'] = $searchFieldsArray['field_descr'];
                    if (!empty($searchLogic['searchBtn']) && !empty($searchLogic['search'][$searchFieldsArray['field_name']]))
                        $sA_['field'] = searchingField($searchFieldsArray, str_replace("'","&#039;", $searchLogic['search'][$searchFieldsArray['field_name']]));
                    else
                        $sA_['field'] = searchingField($searchFieldsArray, NULL);
                    $sA_['like'] = '';
                    if ($searchFieldsArray['field_type'] == 'text' || $searchFieldsArray['field_type'] == 'textarea') {
                        $sA_['like'] .= "<select name='searchType[".$searchFieldsArray['field_name']."]' size='1'>";
                        $sA_['like'] .= "<option value='='";
                        if (!isset($searchLogic['searchType'][$searchFieldsArray['field_name']]) || $searchLogic['searchType'][$searchFieldsArray['field_name']] == '=')
                            $sA_['like'] .= " selected";
                        $sA_['like'] .= ">Полное</option>";
                        $sA_['like'] .= "<option value='like'";
                        if (!empty ($searchLogic['searchType'][$searchFieldsArray['field_name']]) && $searchLogic['searchType'][$searchFieldsArray['field_name']] == 'like')
                            $sA_['like'] .= " selected";
                        $sA_['like'] .= ">Не полное</option>";
                        $sA_['like'] .= "</select>";

                        $sA['fields'][] = $sA_;
                    }
                }
            }

            //-------------------- конец поиск ---------------------

            /*
              /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            */

            #### Ссылки "добавить" и "вернуться" для таблиц.
            $links=u_print_Vback();
            //Формируем массив кнопок управления таблицей
            // [ Вернуться ]
            if(!empty($links['back'])){
                $oA[]=$links['back'];
            }
            if($currentTableA['table_may_add'] != '0'){ // если разрешено редактиров.
                // [ Добавить запись ]
                if(!empty($links['add']))
                    $oA[]=$links['add'];
                // [ Экспорт в Excel ]
                $oA_temp['title'] = "export";
                $oA_temp['descr'] = "Экспорт в Excel";
                $oA_temp['link'] = "export.php?tableName=".$tableName;
                $oA[] = $oA_temp;
            }

            $MA_content .= MA_print_VIEW_TABLE($rA, $pA, $oA, $sA, $tableName);
        }
    }else{
        $MA_content .= "Не достаточно прав для просмотра раздела.";
    }

}else{
    $MA_content .= "Такой таблицы не существует";
}

?>
<?php
  include ($MA_theme);
?>
