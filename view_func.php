<?php
  ////////////////////////////////////////
  //                                    //
  //     Общие функции отоборажения     //
  //                                    //
  ////////////////////////////////////////

                                            ///////////////////////////////////////////////////
                                            // Отображение ссылок главного меню админ панели //
                                            ///////////////////////////////////////////////////
  /*
    массив
    $MLA
        main - ссылка на главную
        setup_admin - ссылка на Общие настройки
        download_file - кнопка загрузки файла на сервер
        setup_tables - ссылка на страницу Настройки таблиц
        setup_fields - ссылка на страницу Настройки полей
        install_modules - ссылка на страницу Установки модулей
        setup_modules - ссылка на страницу Настройки модулей
        setup_users - ссылка на страницу Настройки пользователей
        setup_roles - ссылка на страницу Настройки ролей
        exit - кнопка выхода
     все ссылки хранятся ПОЛНОСТЬЮ в виде "<a href=''> </a>"
  */
  function MA_print_mainLinks($MLA) {
    $cont = '';
    if (function_exists("MA_print_mainLinks_".$GLOBALS['MA_userPrefix']))
      $cont = call_user_func("MA_print_mainLinks_".$GLOBALS['MA_userPrefix'], $MLA);
    else {
      $cont .= "<table width='100%' border='0' cellpadding='0' cellspacing='0'><tr><td>".implode('</td></tr><tr><td>', $MLA)."</td></tr></table>";
    }
    return $cont;
  }
  
  /*
    массив
    $MLA
        setup_site_pages - страницы сайта
        setup_site_siteplugins - плагины сайта
        setup_site_plugins - схемы плагинов
        setup_site_dynamic_pages_types - типы динамических разделов
        setup_site_dynamic_pages - динамические разделы
     все ссылки хранятся ПОЛНОСТЬЮ в виде "<a href=''> </a>"
  */
  function MA_print_siteLinks($MLA) {
    $cont = '';
    if (function_exists("MA_print_siteLinks_".$GLOBALS['MA_userPrefix']))
      $cont = call_user_func("MA_print_siteLinks_".$GLOBALS['MA_userPrefix'], $MLA);
    else {
      $cont .= "<table width='100%' border='0' cellpadding='0' cellspacing='0'><tr><td>".implode('</td></tr><tr><td>', $MLA)."</td></tr></table>";
    }
    return $cont;
  }
  
  /*
    массив
    $MLA
        setup_site_links - конструктор ссылок
        setup_site_text_blocks - текстовые блоки
        setup_site_emails - адреса для рассылок
        setup_site_email_data - письма для рассылок
     все ссылки хранятся ПОЛНОСТЬЮ в виде "<a href=''> </a>"
  */
  function MA_print_siteDataLinks($MLA) {
    $cont = '';
    if (function_exists("MA_print_siteDataLinks_".$GLOBALS['MA_userPrefix']))
      $cont = call_user_func("MA_print_siteDataLinks_".$GLOBALS['MA_userPrefix'], $MLA);
    else {
      $cont .= "<table width='100%' border='0' cellpadding='0' cellspacing='0'><tr><td>".implode('</td></tr><tr><td>', $MLA)."</td></tr></table>";
    }
    return $cont;
  }
  

  /*
    массив
    $NA (каждая запись - это массив)
        descr - человеческое название этой таблицы
        icon - иконка для этой таблицы. Хранится готовым тегом img
        name - имя таблицы
        active - является ли эта таблица активной в данный момент (TRUE или FALSE)
        link - ссылка на просмотр записей этой таблицы
  */
  function MA_print_navigation($NA) {
    $cont = '';
    if (function_exists("MA_print_navigation_".$GLOBALS['MA_userPrefix']))
      $cont = call_user_func("MA_print_navigation_".$GLOBALS['MA_userPrefix'], $NA);
    else {
      $cont .= '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
      if (@count($NA) > 0) {
        foreach ($NA as $NAKey => $NAValue) {
          $style = '';
          if ($NAValue['active'] !== false) $style = " style='font-weight: bold;'";
          $cont .= "<tr".$style."><td><a href='".$NAValue['link']."'>".$NAValue['icon']."&nbsp;&nbsp;".$NAValue['descr']."</a></td></tr>";
        }
      } else $cont .= "<tr><td>Не выбраны таблицы</td></tr>";
      $cont .= "</table>";
    }
    return $cont;
  }

  /*
    массив
    $NA (каждая запись - это массив)
        descr - человеческое название этой таблицы
        icon - иконка для этой таблицы. Хранится готовым тегом img
        name - имя таблицы
        active - является ли эта таблица активной в данный момент (TRUE или FALSE)
        link - ссылка на просмотр записей этой таблицы
  */
  function MA_print_indexNavigation($NA) {
    $cont = '';
    if (function_exists("MA_print_indexNavigation_".$GLOBALS['MA_userPrefix']))
      $cont = call_user_func("MA_print_indexNavigation_".$GLOBALS['MA_userPrefix'], $NA);
    else {
      $cont .= "<table cellspacing='20' cellpadding='0' border='0' width='70%' align='center'>";
      if (@count($NA) > 0) {
        $cnt = 0;
        $cont .= "<tr valign='center'>";
        foreach ($NA as $NAKey => $NAValue) {
          $cnt++;
          $s[1] = " width='15' height='15'";
          $r[1] = " width='128' height='128'";
          $s[2] = " title=''";
          $r[2] = " title='".$NAValue['descr']."'";
          $cont .= "<td height='150' align='center'><a href='".$NAValue['link']."'>".str_replace($s, $r, $NAValue['icon'])."<br>".$NAValue['descr']."</a></td>";
          if ($cnt==3) {
            $cont .= "</tr><tr>";
            $cnt = 0;
          }
        }
        while ($cnt != 3) {
          $cont .= "<td>&nbsp;</td>";
          $cnt++;
        }
        $cont .= "</tr>";
      } else $cont .= "<tr><td>Не выбраны таблицы</td></tr>";
    }
    $cont .= "</table>";
    return $cont;
  }

  ///////////////////////////////////////////
  //                                       //
  //     Функции страницы отоборажения     //
  //                                       //
  ///////////////////////////////////////////

                                       /////////////////////////////////////////////////
                                       //  Формирование страницы отображения записей  //
                                       /////////////////////////////////////////////////
  /*
    $_rA - массив с записями этой таблицы
    $_pA - массив со списком страниц, на которые эти записи разбиты
    $_oA - массив операций с записями
    $_sA - массив с полями для поиска
    $_tableName - имя обрабатываемой таблицы
  */

  function MA_print_VIEW_TABLE($_rA, $_pA, $_oA, $_sA, $_tableName) {
    $cont = '';
    if(!empty($_SESSION[$_tableName]['search']['searchBox'])){
        $cont .= "<div style='position: relative'><div style='color: red; width: 89%; margin: 0 auto;'>Включен фильтр</div></div>";
    }
    if (function_exists("MA_print_VIEW_TABLE_".$GLOBALS['MA_userPrefix']))
      $cont .= call_user_func("MA_print_VIEW_TABLE_".$GLOBALS['MA_userPrefix'], $_rA, $_pA, $_oA, $_sA, $_tableName);
    else {
      //Вызов функции для отображения вариантов сортировки, если есть что сортировать и есть поля, по которым сортировать
    	if (count($_rA) > 0)
        if (count($_rA[0]['descrArray']) > 0)
    	    $cont .= MA_printSorting($_rA[0]['descrArray'], $_tableName);
      $cont .= MA_printRecords($_rA);
      $cont .= MA_printPages($_pA, $_tableName);
      $cont .= MA_printOperations($_oA);
      $cont .= MA_printSearch($_sA);
    }
    return $cont;
  }

                                                   /////////////////////////////////////////
                                                   //  Сортировка записей в этой таблице  //
                                                   /////////////////////////////////////////  
  /*
    массив
    $dA - массив, где ключ - имя поля-идентификатора, а значение - значение этого поля
    $table_name - имя таблицы, из которой извлечена запись
  */
  function MA_printSorting($dA, $table_name) {
  	$cont = '';
    if(function_exists("MA_printSorting_" . $GLOBALS['MA_userPrefix']))
		$cont = call_user_func("MA_printSorting_" . $GLOBALS['MA_userPrefix'], $dA, $table_name);
    else {
      $cont .= "<table width='90%' border='0' align='center' cellpadding='2' cellspacing='2'><tr><td>";
      $cont .= "Сортировать записи: ";
      if (strpos($_SESSION[$table_name]['sorting'], ' DESC'))
        $sortDirection = "&#8593;";
      else $sortDirection = "&#8595;";
      $cont .= "<a href='view_table.php?tableName=".$table_name."&sortBy=id'>id ";
      if (preg_match("/^id(([[:space:]])|($))/i", $_SESSION[$table_name]['sorting']))
        $cont .= $sortDirection;
      $cont .= "</a>";
      foreach($dA as $dA_key => $dA_value) {
        $cont .= " :: <a href='view_table.php?tableName=".$table_name."&sortBy=".$dA_key."'>".fieldDescrByFieldName($dA_key)." ";
        if (preg_match("/^".$dA_key."(([[:space:]])|($))/i", $_SESSION[$table_name]['sorting']))
          $cont .= $sortDirection;
        $cont .= "</a>";
      }
      $cont .= "</td></tr></table>";
    }
    return $cont;
  }
  
                                                   /////////////////////////////
                                                   //  Записи каждой таблицы  //
                                                   /////////////////////////////
  /*
    массив
    $RA (каждая запись - это массив)
        id - id записи в базе данных
        zebra - четный или нечетный ряд
        table_name - имя таблицы, из которой извлечена запись
        descr - все записи-идентификаторы собраны в одну строку через запятую
        descrArray - массив, где ключ - имя поля-идентификатора, а значение - значение этого поля
        links
              edit - ссылка (именно href) для редактирования
              delete - ссылка (именно href) для удаления (если пустая, значит удаление запрещено)
  */
  function MA_printRecords($RA) {
    $cont = '';
    if (function_exists("MA_printRecords_".$GLOBALS['MA_userPrefix']))
      $cont = call_user_func("MA_printRecords_".$GLOBALS['MA_userPrefix'], $RA);
    else {
      if (count($RA) > 0) {
        $cont .= "<table width='90%' border='0' align='center' cellpadding='2' cellspacing='2'>";
        foreach ($RA as $RAKey => $RAValue) {
          $cont .= "<tr align='left' valign='top'";
          if ($RAValue['zebra']) $cont .= " bgcolor='#f1f1f1'";
          $cont .= ">";
            if(!empty($RAValue['id'])){
                $cont .= "<td width='50'>{$RAValue['id']}</td>";
            }
          $cont .= "<td><a href='".$RAValue['links']['edit']."' class='links'>".$RAValue['descr']."</a></td>";
          if ($RAValue['links']['delete'] != '')
            $cont .= "<td><a href='".$RAValue['links']['delete']."' class='links' onclick=\"return window.confirm('Удалить запись?');\">[ Удалить эту запись ]</a></td>";
          else $cont .= "<td>&nbsp;</td>";
          $cont .= "</tr>";
        }
        $cont .= "</table>";
      } else $cont .= "Нет записей";
    }
    return $cont;
  }


                                                   /////////////////////////////
                                                   //     Список страниц      //
                                                   /////////////////////////////
  /*
    массив
    $PA (каждая запись - это массив)
        firstPage - ссылка на первую страницу
        lastPage - ссылка на последнюю страницу
        currentPage - номер текущей страницы
        pagesCnt - кол-во страниц всего
        pagesLinks - массив со всеми ссылками, где ключ - это номер страницы, а значение - ссылка на нее.
        tableName - имя обрабатываемой таблицы
  */
  function MA_printPages($PA, $tableName) {
    $cont = '';
    if (function_exists("MA_printPages_".$GLOBALS['MA_userPrefix']))
      $cont = call_user_func("MA_printPages_".$GLOBALS['MA_userPrefix'], $PA, $tableName);
    else {
      $cont .= "<table width='90%' border='0' align='center' cellpadding='2' cellspacing='2'>";
      $cont .= "<tr><td>Страницы:</td></tr><tr><td>";
      if (count($PA['pagesLinks']) > 0) {
        foreach ($PA['pagesLinks'] as $PAKey => $PAValue) {
          if ($PAKey != $PA['currentPage'])
            $cont .= "<a href='".$PAValue."' class='links'>".$PAKey."</a>&nbsp;&nbsp; ";
          else
            $cont .= "<b>".$PAKey."</b>&nbsp;&nbsp; ";
        }
      } else $cont .= "<b>1</b>";  
      $recordsPerPage10 = "<a href='view_table.php?tableName=".$tableName."&setRecordsPerPage=10'>10</a>";
      $recordsPerPage20 = "<a href='view_table.php?tableName=".$tableName."&setRecordsPerPage=20'>20</a>";
      $recordsPerPage50 = "<a href='view_table.php?tableName=".$tableName."&setRecordsPerPage=50'>50</a>";
      $recordsPerPage100 = "<a href='view_table.php?tableName=".$tableName."&setRecordsPerPage=100'>100</a>";
      $setRecordsPerPage = @$_SESSION['setRecordsPerPage_'.$tableName];
      switch ($setRecordsPerPage) {
        default:
        case '10': $recordsPerPage10 = "<b>10</b>"; break;
      	case '20': $recordsPerPage20 = "<b>20</b>"; break;
      	case '50': $recordsPerPage50 = "<b>50</b>"; break;
      	case '100': $recordsPerPage100 = "<b>100</b>"; break;
      }
      $cont .= "<tr><td>По ".$recordsPerPage10." ".$recordsPerPage20." ".$recordsPerPage50." ".$recordsPerPage100." на странице</td></tr>";
      $cont .= "</table>";
    }
    return $cont;
  }


                                                   ////////////////////////////////
                                                   // Список операций управления //
                                                   ////////////////////////////////
  /*
    массив
    $OA (каждая запись - это массив)
        title - англ. версия операции управления
        descr - русская версия операции управления
        link - ссылка
  */
  function MA_printOperations($OA) {
    $cont = '';
    if (function_exists("MA_printOperations_".$GLOBALS['MA_userPrefix']))
      $cont = call_user_func("MA_printOperations_".$GLOBALS['MA_userPrefix'], $OA);
    else {
      if (count($OA) > 0) {
        $cont .= "<table width='90%' border='0' align='center' cellpadding='2' cellspacing='2'>";
        $cont .= "<tr><td>&nbsp;</td></tr><tr><td>";
        foreach ($OA as $OAKey => $OAValue)
          $cont .= "<a href='".$OAValue['link']."'><b>[ ".$OAValue['descr']." ]</b></a> ";
        $cont .= "</td></tr><tr><td>&nbsp;</td></tr>";
        $cont .= "</table>";
      }
    }
    return $cont;
  }


                                                   /////////////////////////////
                                                   //         Поиск           //
                                                   /////////////////////////////
  /*
    массив
    $SA (каждая запись - это массив)
        submit - кнопка старта поиска
        logic - логика поиска (И или ИЛИ)
        formStart - скрытые параметры поиска и заголовок формы, куда отправлять данные
        formEnd - закрытие формы
        fields
               checkbox - искать по полю или нет (checkbox)
               descr - заголовок поля
               field - значение поля
               like - если это текстовое поле: выбор полного соответствия поиска или не полного
  */
  function MA_printSearch($SA) {
    $cont = '';
    if (function_exists("MA_printSearch_".$GLOBALS['MA_userPrefix']))
      $cont = call_user_func("MA_printSearch_".$GLOBALS['MA_userPrefix'], $SA);
    else {
      if (count($SA['fields']) > 0) {
        $cont .= "<table width='90%' border='0' align='center' cellpadding='2' cellspacing='2' class='searchTable'>";
        $cont .= "<tr><td>".$SA['formStart'];
        $cont .= "<div class='fieldBlock'><div class='fieldHead'>Поиск</div>";
        $cont .= "<table width='95%' border='0' align='center' cellpadding='0' cellspacing='2'>";
        $cont .= "<tr><td width='150'>Искать в поле:</td><td>Значение:</td><td width='100'>Соответствие:</td></tr>";
        foreach ($SA['fields'] as $SAKey => $SAValue)
          $cont .= "<tr align='left'><td>".$SAValue['checkbox']." ".$SAValue['descr']."</td><td>".$SAValue['field']."</td><td>".$SAValue['like']."</td></tr>";
        $cont .= "<tr><td>Связать поиск через:</td><td colspan='2'>".$SA['logic']."</td></tr>";
          $cont .= "<tr><td colspan='3' style='border-bottom: 1px solid #aeaeae'></td></tr>";
        $cont .= "<tr><td align='left'>".$SA['clear']."</td><td colspan='2' align='right'>".$SA['submit']."</td></tr>";
        $cont .= "</table>";
        $cont .= "</div>".$SA['formEnd']."</td></tr>";
        $cont .= "</table>";
      }
    }
    return $cont;
  }
?>
