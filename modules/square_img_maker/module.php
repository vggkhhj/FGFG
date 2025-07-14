<?php
  function square_img_maker_settings($mArray)
  {
    $cont = '';

    $tableCellStyle = 'border: 1px solid #b1b1b1;';

    $cont .= "<form action='".$SERVER['script_name']."' method='POST'>";
      //Получаем список полей
      $cont .= "<table cellspacing='2' cellpadding='3' border='0'>";
      $cont .= "<tr><td>&nbsp;</td><td style='".$tableCellStyle."'>Заголовок таблицы</td><td style='".$tableCellStyle."'>Заголовок поля</td><td style='".$tableCellStyle."'>Если изображение вертикальное</td><td style='".$tableCellStyle."'>Если изображение горизонтальное</td></tr>";
      $fQ = mysql_query("SELECT maf.*, mat.table_descr, mat.id as mat_id FROM my_admin_fields maf, my_admin_tables mat WHERE maf.field_type='file' and maf.field_table=mat.table_name ORDER BY mat.id;");
      while ($fA = mysql_fetch_array($fQ, MYSQL_ASSOC)) {
      	$irQ = mysql_query("SELECT * FROM my_admin_fdc WHERE fdc_module='".$mArray['id']."' and fdc_field='".$fA['id']."';");
        
        $vT = '';
        $vM = '';
        $vB = '';
        $gL = '';
        $gM = '';
        $gR = '';
        if (mysql_num_rows($irQ) > 0) {
        	$irA = mysql_fetch_array($irQ, MYSQL_ASSOC);
          preg_match('/v=([^$]*?);g=(.*)$/im', $irA['fdc_params'], $irP);
          $vImg = $irP[1];
          switch ($vImg) {
          	case '1': $vT = ' SELECTED'; break;
            default:
          	case '2': $vM = ' SELECTED'; break;
          	case '3': $vB = ' SELECTED'; break;
          }
          $gImg = $irP[2];
          switch ($gImg) {
            default:
            case '1': $gL = ' SELECTED'; break;
            default:
            case '2': $gM = ' SELECTED'; break;
            case '3': $gR = ' SELECTED'; break;
          }
          $irChecked = " checked";
        } else {
          $vImg = '2';
          $gImg = '2';
          $irChecked = '';
          $vM = ' SELECTED';
          $gM = ' SELECTED';
        }
        $cont .= "<tr>";
          $cont .= "<td style='".$tableCellStyle."'><input type='checkbox' name='square_img[".$fA['id']."]'".$irChecked."></td>";
          $cont .= "<td style='".$tableCellStyle."'>".$fA['table_descr']."</td>";
          $cont .= "<td style='".$tableCellStyle."'>".$fA['field_descr']."</td>";
          $cont .= "<td style='".$tableCellStyle."' align='center'>
                      <SELECT name='square_img_v[".$fA['id']."]' size='1'>
                        <OPTION value='1' ".$vT.">Прижать к верху</OPTION>
                        <OPTION value='2' ".$vM.">Посередине</OPTION>
                        <OPTION value='3' ".$vB.">Прижать к низу</OPTION>
                      </SELECT>";
          $cont .= "<td style='".$tableCellStyle."' align='center'>
                      <SELECT name='square_img_g[".$fA['id']."]' size='1'>
                        <OPTION value='1' ".$gL.">Прижать слева</OPTION>
                        <OPTION value='2' ".$gM.">Посередине</OPTION>
                        <OPTION value='3' ".$gR.">Прижать справа</OPTION>
                      </SELECT>";
        $cont .= "</tr>";
      }

      $cont .= "<input type='hidden' name='moduleId' value='".$mArray['id']."'>";
      $cont .= "<tr><td colspan='4' align='left'><input type='submit' name='".$mArray['module_name']."_save_settings' value='Сохранить'></td></tr>";
      $cont .= "</table>";
    $cont .= "</form>";
    
    return $cont;
  }

  function square_img_maker_save_settings($mArray)
  {
    if (@count(@$_POST['square_img']) > 0)
      foreach ($_POST['square_img'] as $img_resizer_key => $img_resizer_value)
        if (@$_POST['square_img_v'][$img_resizer_key] != '' && @$_POST['square_img_g'][$img_resizer_key] != '') {
          $vImg = mysql_real_escape_string($_POST['square_img_v'][$img_resizer_key]);
          $gImg = mysql_real_escape_string($_POST['square_img_g'][$img_resizer_key]);
          //Если такая запись есть
          $irQ = mysql_query("SELECT * FROM my_admin_fdc WHERE fdc_module='".$mArray['id']."' and fdc_field='".$img_resizer_key."';");
          if (mysql_num_rows($irQ) > 0)
            mysql_query("UPDATE my_admin_fdc SET fdc_params='v=".$vImg.";g=".$gImg."' WHERE fdc_module='".$mArray['id']."' and fdc_field='".$img_resizer_key."' LIMIT 1;");
          else
            mysql_query("INSERT INTO my_admin_fdc SET fdc_params='v=".$vImg.";g=".$gImg."', fdc_module='".$mArray['id']."', fdc_field='".$img_resizer_key."';");
            //echo $img_resizer_key."->".$img_resizer_value."<br>";
        }
      
    //Удаляем те поля, которые модуль больше не использует.
    $irQ = mysql_query("SELECT * FROM my_admin_fdc WHERE fdc_module='".$mArray['id']."';");
    while ($irA = mysql_fetch_array($irQ, MYSQL_ASSOC))
    {
      if (!isset($_POST['square_img'][$irA['fdc_field']]))
        mysql_query("DELETE FROM my_admin_fdc WHERE id='".$irA['id']."' LIMIT 1;");
    }
  }
  
  function square_img_maker_update_data($fieldName, $params)
  {
    if (file_exists($_FILES[$fieldName]["tmp_name"]))
    {
      preg_match('/v=([^$]*?);g=(.*)$/im', $params, $irP);
      preg_match("/(?:[^$]*)\.([^$]*)/", $_FILES[$fieldName]["name"], $pockets);
      
      $filename = $_FILES[$fieldName]["tmp_name"];
      list($width, $height) = getimagesize($filename);
      $widthHalf = round($width/2);
      $heightHalf = round($height/2);
      
      $source = false;
      switch(strtolower($pockets[1])) {
        case 'jpg':
        case 'jpeg': $source = imagecreatefromjpeg($filename); break;
        case 'png': $source = imagecreatefrompng($filename); break;
        case 'gif': $source = imagecreatefromgif($filename); break;
      }
      
      //Если изображение вертикальное
      if ($height > $width) {
        $thumb = imagecreatetruecolor($width, $width);
        switch ($irP[1]) {
            case '1': 
            	imagecopy ($thumb, $source, 0, 0, 0, 0, $width, $width);
            	break;
            default:
            case '2':
            	imagecopy ($thumb, $source, 0, 0, 0, $heightHalf-$widthHalf, $width, $width);
              break;
            case '3':
              imagecopy ($thumb, $source, 0, 0, 0, $height-$width, $width, $width);
              break;
          }
      } else {
      //Если изображение горизонтальное
      $thumb = imagecreatetruecolor($height, $height);
        switch ($irP[2]) {
            case '1': 
              imagecopy ($thumb, $source, 0, 0, 0, 0, $height, $height);
              break;
            default:
            case '2':
              imagecopy ($thumb, $source, 0, 0, $widthHalf-$heightHalf, 0, $height, $height);
              break;
            case '3':
              imagecopy ($thumb, $source, 0, 0, $width-$height, 0, $height, $height);
              break;
          }
      }

      if ($source && $thumb) {
        imagejpeg($thumb, $_FILES[$fieldName]["tmp_name"], 100);
      }
    }
  }
?>
