<?php
  function tags_remover_settings($mArray)
  {
    $cont = '';
    
    $tableCellStyle = 'border: 1px solid #b1b1b1;';

    $cont .= "<form action='".$_SERVER['SCRIPT_NAME']."' method='POST'>";
      //Получаем список полей
      $cont .= "<table cellspacing='2' cellpadding='3' border='0'>";
      $cont .= "<tr><td>&nbsp;</td><td style='".$tableCellStyle."'>Заголовок таблицы</td><td style='".$tableCellStyle."'>Заголовок поля</td><td style='".$tableCellStyle."'>Список тэгов</td></tr>";
      $fQ = mysql_query("SELECT maf.*, mat.table_descr, mat.id as mat_id FROM my_admin_fields maf, my_admin_tables mat WHERE maf.field_type<>'file' and maf.field_table=mat.table_name ORDER BY mat.id;");
      while ($fA = mysql_fetch_array($fQ, MYSQL_ASSOC))
      {
        $irQ = mysql_query("SELECT * FROM my_admin_fdc WHERE fdc_module='".$mArray['id']."' and fdc_field='".$fA['id']."';");
        if (mysql_num_rows($irQ) > 0)
        {
          $irA = mysql_fetch_array($irQ, MYSQL_ASSOC);
          $irChecked = ' checked';
        }
        else
        {
          $irA['fdc_params'] = '';
          $irChecked = '';
        }

        $cont .= "<tr valign='top'>";
          $cont .= "<td style='".$tableCellStyle."'><input type='checkbox' name='tags_remover[".$fA['id']."]'".$irChecked."></td>";
          $cont .= "<td style='".$tableCellStyle."'>".$fA['table_descr']."</td>";
          $cont .= "<td style='".$tableCellStyle."'>".$fA['field_descr']."</td>";
          $cont .= "<td style='".$tableCellStyle."'><TEXTAREA name='tags_remover_params[".$fA['id']."]' cols='20' rows='2'>".$irA['fdc_params']."</TEXTAREA></td>";
        $cont .= "</tr>";
      }

      $cont .= "<input type='hidden' name='moduleId' value='".$mArray['id']."'>";
      $cont .= "<tr><td colspan='4' align='left'><input type='submit' name='".$mArray['module_name']."_save_settings' value='Сохранить'></td></tr>";
      $cont .= "</table>";
    $cont .= "</form>";
    
    return $cont;
  }

  function tags_remover_save_settings($mArray)
  {
    if (@count(@$_POST['tags_remover']) > 0)
      foreach ($_POST['tags_remover'] as $tags_remover_key => $tags_remover_value)
        if (isset($_POST['tags_remover_params'][$tags_remover_key]))
        {
          $irParams = mysql_real_escape_string($_POST['tags_remover_params'][$tags_remover_key]);
          //Если такая запись есть
          $irQ = mysql_query("SELECT * FROM my_admin_fdc WHERE fdc_module='".$mArray['id']."' and fdc_field='".$tags_remover_key."';");
          if (mysql_num_rows($irQ) > 0)
            mysql_query("UPDATE my_admin_fdc SET fdc_params='".$irParams."' WHERE fdc_module='".$mArray['id']."' and fdc_field='".$tags_remover_key."' LIMIT 1;");
          else
            mysql_query("INSERT INTO my_admin_fdc SET fdc_params='".$irParams."', fdc_module='".$mArray['id']."', fdc_field='".$tags_remover_key."';");
            //echo $img_resizer_key."->".$img_resizer_value."<br>";
        }

    //Удаляем те поля, которые модуль больше не использует.
    $irQ = mysql_query("SELECT * FROM my_admin_fdc WHERE fdc_module='".$mArray['id']."';");
    while ($irA = mysql_fetch_array($irQ, MYSQL_ASSOC))
    {
      if (!isset($_POST['tags_remover'][$irA['fdc_field']]))
        mysql_query("DELETE FROM my_admin_fdc WHERE id='".$irA['id']."' LIMIT 1;");
    }
  }
  
  function tags_remover_update_data($fieldName, $params)
  {
    if (@$_POST[$fieldName] != '')
    {
      $searchFor = explode(' ', $params);
      foreach ($searchFor as $trKey => $trValue)
        $_POST[$fieldName] = preg_replace('/<\/*?'.$trValue.'(?:[^>]*?)>/im', '', $_POST[$fieldName]);
    }
  }
?>
