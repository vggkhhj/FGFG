<?php
  require_once ('common.php');
  
  //проверяем права на доступ к этой странице
  if ($_SESSION['user']['roles']['role_modules'] > 0) {
    if (@$_GET['moduleName'] != '')
    {
      $db->query("DELETE FROM my_admin_modules WHERE module_name='".$_GET['moduleName']."' LIMIT 1;");
      require_once('modules/'.$_GET['moduleName'].'/uninstall.php');
    }
  }
?>
