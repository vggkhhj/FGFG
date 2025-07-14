<?php
  require_once ('common.php');
  
  //проверяем права на доступ к этой странице
  if ($_SESSION['user']['roles']['role_modules'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header('Location: index.php');
  
  $MA_pageTitle = 'Настройка модулей';

  $error = false;
  if (!empty($_POST['moduleId'])) {

    $moduleA = $db->select_array_row("SELECT * FROM my_admin_modules WHERE id='".$_POST['moduleId']."';");
    if (!empty($moduleA)) {
      require_once('modules/'.$moduleA['module_name'].'/module.php');
      if (!empty($_REQUEST[$moduleA['module_name'].'_save_settings']))
        call_user_func($moduleA['module_name'].'_save_settings', $moduleA);
    }
    else $error = "Модуль с таким ID в таблице не найден.";
  }
  else $error = "Не передан ID модуля для настройки.";

?>
<?php
  if (!$error)
    $MA_content .= call_user_func($moduleA['module_name'].'_settings', $moduleA);
  else $MA_content .= $error;
    $MA_content .= "<a href='setup_modules_settings.php' class='links'>Вернуться</a>";
?>
<?php
  include ($MA_theme);
?>
