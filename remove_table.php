<?php
  require_once ('common.php');

  //Проверка прав доступа на эту страницу
  if ($_SESSION['user']['roles']['role_tables'] > 0 || $_SESSION['user']['roles']['role_title'] == 'root') {
    if(!empty($_GET['tableName'])){
      $tableName = $_GET['tableName'];
      $db->query("DELETE FROM my_admin_tables WHERE `table_name`='{$tableName}' LIMIT 1;");
      if (!empty($_GET['delFields']) && $_GET['delFields'] == 1) {
        $db->query("DELETE FROM my_admin_fields WHERE field_table='{$tableName}';");
        $db->query("ALTER TABLE my_admin_roles DROP {$tableName};");
      }
    }

  }
?>
