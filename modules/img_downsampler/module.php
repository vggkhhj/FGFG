<?php
  function img_downsampler_settings($mArray)
  {
    $cont = '';
    
    $tableCellStyle = 'border: 1px solid #b1b1b1;';

    $cont .= "<form action='".$SERVER['script_name']."' method='POST'>";
      //Получаем список полей
      $cont .= "<table cellspacing='2' cellpadding='3' border='0'>";
      $cont .= "<tr><td>&nbsp;</td><td style='".$tableCellStyle."'>Заголовок таблицы</td><td style='".$tableCellStyle."'>Заголовок поля</td><td style='".$tableCellStyle."'>Размер на выходе</td></tr>";
      $fQ = mysql_query("SELECT maf.*, mat.table_descr, mat.id as mat_id FROM my_admin_fields maf, my_admin_tables mat WHERE maf.field_type='file' and maf.field_table=mat.table_name ORDER BY mat.id;");
      while ($fA = mysql_fetch_array($fQ, MYSQL_ASSOC))
      {
        $irQ = mysql_query("SELECT * FROM my_admin_fdc WHERE fdc_module='".$mArray['id']."' and fdc_field='".$fA['id']."';");
        if (mysql_num_rows($irQ) > 0)
        {
          $irA = mysql_fetch_array($irQ, MYSQL_ASSOC);
          preg_match('/w=([^$]*?);h=(.*)$/im', $irA['fdc_params'], $irP);
          $irWidth = $irP[1];
          $irHeight = $irP[2];
          $irChecked = " checked";
        }
        else
        {
          $irWidth = '';
          $irHeight = '';
          $irChecked = '';
        }
        
        $cont .= "<tr>";
          $cont .= "<td style='".$tableCellStyle."'><input type='checkbox' name='img_resizer[".$fA['id']."]'".$irChecked."></td>";
          $cont .= "<td style='".$tableCellStyle."'>".$fA['table_descr']."</td>";
          $cont .= "<td style='".$tableCellStyle."'>".$fA['field_descr']."</td>";
          $cont .= "<td style='".$tableCellStyle."'><input type='text' name='img_resizer_width[".$fA['id']."]' size='5' value='".$irWidth."'> x <input type='text' name='img_resizer_height[".$fA['id']."]' size='5' value='".$irHeight."'></td>";
        $cont .= "</tr>";
      }

      $cont .= "<input type='hidden' name='moduleId' value='".$mArray['id']."'>";
      $cont .= "<tr><td colspan='4' align='left'><input type='submit' name='".$mArray['module_name']."_save_settings' value='Сохранить'></td></tr>";
      $cont .= "</table>";
    $cont .= "</form>";
    
    return $cont;
  }

  function img_downsampler_save_settings($mArray)
  {
    if (@count(@$_POST['img_resizer']) > 0)
      foreach ($_POST['img_resizer'] as $img_resizer_key => $img_resizer_value)
        if (@$_POST['img_resizer_width'][$img_resizer_key] != '' && @$_POST['img_resizer_height'][$img_resizer_key] != '')
        {
          $irWidth = mysql_real_escape_string($_POST['img_resizer_width'][$img_resizer_key]);
          $irHeight = mysql_real_escape_string($_POST['img_resizer_height'][$img_resizer_key]);
          //Если такая запись есть
          $irQ = mysql_query("SELECT * FROM my_admin_fdc WHERE fdc_module='".$mArray['id']."' and fdc_field='".$img_resizer_key."';");
          if (mysql_num_rows($irQ) > 0)
            mysql_query("UPDATE my_admin_fdc SET fdc_params='w=".$irWidth.";h=".$irHeight."' WHERE fdc_module='".$mArray['id']."' and fdc_field='".$img_resizer_key."' LIMIT 1;");
          else
            mysql_query("INSERT INTO my_admin_fdc SET fdc_params='w=".$irWidth.";h=".$irHeight."', fdc_module='".$mArray['id']."', fdc_field='".$img_resizer_key."';");
            //echo $img_resizer_key."->".$img_resizer_value."<br>";
        }

    //Удаляем те поля, которые модуль больше не использует.
    $irQ = mysql_query("SELECT * FROM my_admin_fdc WHERE fdc_module='".$mArray['id']."';");
    while ($irA = mysql_fetch_array($irQ, MYSQL_ASSOC))
    {
      if (!isset($_POST['img_resizer'][$irA['fdc_field']]))
        mysql_query("DELETE FROM my_admin_fdc WHERE id='".$irA['id']."' LIMIT 1;");
    }
  }
  
  function img_downsampler_update_data($fieldName, $params)
  {
    define('TOO_SMALL', 35);//минимальная размерность, ниже которой изображение отбрасывается как слишком мелкое
    define('INFINITE', 10000);//соответствует "бесконечной" размерности
    
    if (file_exists($_FILES[$fieldName]["tmp_name"]))
    {
      preg_match('/w=([^$]*?);h=(.*)$/im', $params, $irP);
      preg_match("/(?:[^$]*)\.([^$]*)/", $_FILES[$fieldName]["name"], $pockets);
      
      $filename = $_FILES[$fieldName]["tmp_name"];
      list($width, $height, $type) = getimagesize($filename);

      //Если мы знаем только высоту, то пропорционально подстраиваем ширину
      $mw = $irP[1] == '*' ? INFINITE : intval($irP[1]);
      $mh = $irP[2] == '*' ? INFINITE : intval($irP[2]);
      
      $unset = false;
      if(($width>=TOO_SMALL && $height>=TOO_SMALL))
      {
        $percent = round($width/$height, 3);

        if($width>$mw || $height>$mh)//если изображение больше чем максимум
        {
	        if($width>$height){
	              $newwidth = $mw;
	              $newheight = floor($mw/$percent);
	          }
          	else{
	              $newheight = $mh;
	              $newwidth = floor($mh*$percent);
		    }
		                  
          	$thumb = imagecreatetruecolor($newwidth, $newheight);

          	$source = null;
          	
          	switch($type) /* выбор способа открытия по типу изображения */
          	{
	              case 1: // GIF
	              $source = imagecreatefromgif($filename);
	              break;
	              case 2: // JPG
	              $source = imagecreatefromjpeg($filename);
	              break;
	              case 3: // PNG
	              $source = imagecreatefrompng($filename);
	              break;
	              default: $unset = true;
            }
         
          	if(!empty($source)){
	            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);        
	            imagejpeg($thumb, $_FILES[$fieldName]["tmp_name"], 85);
          	}
          	else $unset = true;         
        }//уменьшать не надо, изображение и так в нужных пределах
      }else $unset = true;
      
      if($unset)
        unset($_FILES[$fieldName]);
    }
  }
?>
