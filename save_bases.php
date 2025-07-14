<?php
  require_once ('common.php');
  
  //проверяем права на доступ к этой странице
  if ($_SESSION['user']['roles']['role_tables'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header('Location: index.php');
  
  //Если был переход со страницы выбора таблиц для обработки
  if (!empty($_POST['tablesBoxes']) && !empty($_POST['tablesNames']) && !empty($_REQUEST['setupFields']))
  {
    //Сохраняем выбранные базы
    foreach ($_POST['tablesBoxes'] as $tablesBoxesKey => $tablesBoxesValue)
    {
        //Убираем escape-символы
        $_POST['tablesNames'][$tablesBoxesKey] = DB::escape($_POST['tablesNames'][$tablesBoxesKey]);
        
        //Проверяем передано ли имя иконки
        if (!empty($_POST['tablesIcons'][$tablesBoxesKey])) $iconName = $_POST['tablesIcons'][$tablesBoxesKey]; else $iconName = '';
        
        //Проверяем нужно ли отображать эту таблицу
        if (!empty($_POST['tablesShows'][$tablesBoxesKey])) $show = '1'; else $show = '0';
        
        //Проверяем разрешено ли добавление
        if (!empty($_POST['tablesMayAdds'][$tablesBoxesKey])) $may_add = '1'; else $may_add = '0';

        //Проверяем разрешено ли удаление
        if (!empty($_POST['tablesMayDeletes'][$tablesBoxesKey])) $may_delete = '1'; else $may_delete = '0';
        
        //Проверяем вес таблицы
        if (!empty($_POST['tablesWeights'][$tablesBoxesKey])) $weight = $_POST['tablesWeights'][$tablesBoxesKey]; else $weight = '0';
        
        //Проверяем резервированные поля 
        if (!empty($_POST['tablesR1'][$tablesBoxesKey])) $r1 = $_POST['tablesR1'][$tablesBoxesKey]; else $r1 = '';
        if (!empty($_POST['tablesR2'][$tablesBoxesKey])) $r2 = $_POST['tablesR2'][$tablesBoxesKey]; else $r2 = '';
        if (!empty($_POST['tablesR3'][$tablesBoxesKey])) $r3 = $_POST['tablesR3'][$tablesBoxesKey]; else $r3 = '';
        
        //Если такая запись в my_admin_tables есть
        $existId = $db->select_result("SELECT `id` FROM my_admin_tables WHERE table_name='{$tablesBoxesKey}' LIMIT 1;");
        if (!empty($existId)) {
          //Обновляем имя с которым хранить
            $db->query("UPDATE my_admin_tables SET table_descr='".$_POST['tablesNames'][$tablesBoxesKey]."', table_icon='".$iconName."', table_show='".$show."', table_weight='".$weight."', table_may_add=".$may_add.", table_may_delete=".$may_delete.", table_r1='" . $r1 . "', table_r2='" . $r2 . "', table_r3='" . $r3 . "' WHERE id='".$existId."' LIMIT 1;");
        } else {
          //Добавляем новую запись
            $db->query("INSERT INTO my_admin_tables SET table_descr='".$_POST['tablesNames'][$tablesBoxesKey]."', table_name='".$tablesBoxesKey."', table_icon='".$iconName."', table_show='".$show."', table_weight='".$weight."', table_may_add=".$may_add.", table_may_delete=".$may_delete.", table_r1='" . $r1 . "', table_r2='" . $r2 . "', table_r3='" . $r3 . "' ;");
          if ($show == '1')
              $db->query("ALTER TABLE my_admin_roles ADD {$tablesBoxesKey} INT( 1 ) NOT NULL ;");
        }
    }
  }

