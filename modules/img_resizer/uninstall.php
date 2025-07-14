<?php
  $iruQ = mysql_query("SELECT * FROM my_admin_modules WHERE module_name='img_resizer' LIMIT 1;");
  if (mysql_num_rows($iruQ > 0))
  {
    $iruA = mysql_fetch_array($iruQ, MYSQL_ASSOC);
    mysql_query("DELETE FROM my_admin_fdc WHERE fdc_module='".$iruA['id']."';");
  }
?>
