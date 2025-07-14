<?php
  require_once ('common.php');
  
  //проверяем права на доступ к этой странице
  if ($_SESSION['user']['roles']['role_modules'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header('Location: index.php');
  
  //Если был переход со страницы выбора таблиц для обработки
  if (@count($_POST['modulesBoxes'])>0 && @$_REQUEST['saveModules'])
  {
    //Сохраняем выбранные базы
    foreach ($_POST['modulesBoxes'] as $modulesBoxesKey => $modulesBoxesValue)
    {
      //Убираем escape-символы
      $module_name = DB::escape($_POST['modulesNames'][$modulesBoxesKey]);
      $module_title = DB::escape($_POST['modulesTitles'][$modulesBoxesKey]);
      $module_descr = DB::escape($_POST['modulesDesrcs'][$modulesBoxesKey]);

      //Если такая запись есть
      $moduleQuery = $db->select_result("SELECT `id` FROM my_admin_modules WHERE module_name='".$modulesBoxesKey."' LIMIT 1;");
      if (empty($moduleQuery))
        //Добавляем новую запись
        $db->query("INSERT INTO my_admin_modules SET module_name='".$module_name."', module_title='".$module_title."', module_descr='".$module_descr."';");
        
      //Запускаем установщик модуля
      require_once('modules/'.$module_name.'/install.php');
    }
  }
?>
