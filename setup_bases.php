<?php
  require_once ('common.php');
  
  //проверяем права на доступ к этой странице
  if ($_SESSION['user']['roles']['role_tables'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header('Location: index.php');
  
  $MA_pageTitle = 'Настройка таблиц';
  
  //окно выбора имени таблицы для хранения
$MA_content .= "
<div style='position: absolute; text-align: center; width: 600;'>
 <div id='addWindow' style='position: relative; display: none; width: 195px; height: 220px; top: 30px; border: 1px solid #555555; text-align: left; text-indent: 10px; margin: auto auto; padding: 0px;'>
  <div id='addWindowBG' style='display: block; position: absolute; width: 195px; height: 220px; left: 0px; top: 0px; background-color: #CCCCCC; opacity: 0.8; filter: alpha(opacity=80); z-index: 0;'></div>
  <div id='addWindowContent' style='display: block; position: absolute; width: 195px; height: 70px; left: 0px; top: 15px; text-align: center;'>
      <div class='addWindowTR'>Хранить это таблицу как:</div>
      <div class='addWindowTR'>
        <input type='text' value='' name='saveTableAs' id='saveTableAs' size='20' maxlength='25'>
        <input type='hidden' value='' name='currentId' id='currentId'>
      </div> 
      <div class='addWindowTR'>Альбом иконок:</div>
      <div class='addWindowTR'>
        <SELECT size='1' name='iconsAlbum' id='iconsAlbum' onchange='changeAlbum(this.value);'>";
        //Получаем список всех найденных папок с иконками
        if ($iconsDir = scandir('css/icons')) {
          foreach ($iconsDir as $iconsDirItem) {
            if (is_dir('css/icons/'.$iconsDirItem) && $iconsDirItem != "." && $iconsDirItem != "..") {
              $MA_content .= "<OPTION value='".$iconsDirItem."'>".$iconsDirItem."</OPTION>";
            }
          }
        }
/*
        if ($iconsDir = opendir('css/icons')) {
          while (false !== ($iconsDirItem = readdir($iconsDir))) {
            if (!is_file($iconsDirItem) && $iconsDirItem != "." && $iconsDirItem != "..") {
              $MA_content .= "<OPTION value='".$iconsDirItem."'>".$iconsDirItem."</OPTION>";
            }
          }
        }
*/
$MA_content .= "
        </SELECT>
      </div>
      <div class='addWindowTR'>Иконка для таблицы:</div>
      <div class='addWindowTR'>
        <table cellspacing='5' cellpadding='0' border='0' align='center'>
          <tr>";
          $cnt = 0;
          for ($i = 1; $i<11; $i++)
          {
            $cnt++;
            $MA_content .= "<td><img src='css/icons/".$i.".gif' width='15' height='15' class='iconNS' onclick='selectIcon(".$i.");' onmouseout='previewLastSelected();' onmousemove='showPreview(".$i.")' id='icon".$i."'></td>";
            if ($i == 4) $MA_content .= "<td rowspan='3'><img src='' width='45' height='45' id='previewImage' align='left' title='' alt='' border='0'><input type='hidden' id='selectedIcon' value='' name='selectedIcon'></td>";
            if ($cnt==4)
            {
              $MA_content .= "</tr><tr>";
              $cnt = 0;
            }
          }
          while ($cnt != 4)
          {
            $MA_content .= "<td>&nbsp;</td>";
            $cnt++;
          }
      $MA_content .= "
          </tr>
        </table>
      </div>
    <div id='okBtn' style='position: absolute; display: block; width: 80px; height: 20px; left: 10px; top: 170px; border: 1px solid #b1b1b1; cursor: hand; cursor: pointer;' onclick='saveTableName();'>Ок</div>
    <div id='cancelBtn' style='position: absolute; display: block; width: 80px; height: 20px; left: 100px; top: 170px; border: 1px solid #b1b1b1; cursor: hand; cursor: pointer;' onclick=\"cancelSaving();\">Отмена</div>
  </div>
 </div>
</div>   ";

  $MA_content .= "
  <div class='setupQuestion'>Какие таблицы использовать для редактирования?</div>
  <form action='save_bases.php' target='toFrame' method='post'>
      <table cellspacing='2' cellpadding='3' border='0' align='center'>
         <tr><td>&nbsp;</td><td class='tableCell'>Имя таблицы</td><td class='tableCell'>Отображать таблицу</td><td class='tableCell'>Вес</td><td class='tableCell'>Добавление</td><td class='tableCell'>Удаление</td><td class='tableCell'>R1</td><td class='tableCell'>R2</td><td class='tableCell'>R3</td></tr>";
      //список таблиц БД
      $tablesQuery = $db->select_result_array("SHOW TABLES FROM {$DB}");
      $boxI = 0;
      foreach( $tablesQuery as $table){
          //if ($table!='my_admin_tables' && $table!='my_admin_fields' && $table!='my_admin_about')
          if (true /*strpos($table, 'my_admin_') === false /*&& strpos($table, 'my_site_') === false /*|| $table == TABLE_USERS || $table == 'my_admin_roles'*/)
          {
              $MA_content .= "<tr><td class='tableCell'>";
              $MA_content .= "<input type='checkbox' name='tablesBoxes[{$table}]' id='tableBox_{$table}' onchange=\"setNameForTable(this);\"";
              if ($db->select_num_rows("SELECT * FROM my_admin_tables WHERE `table_name`='$table';")>0)
                  $MA_content .= " checked";
              $MA_content .= ' boxI="'.$boxI.'"';
              $boxI ++;
              $MA_content .= ">"; //если метка включается, открыть окно выбора названия
              $MA_content .= "</td><td class='tableCell'>";
              $MA_content .= $table;
              $MA_content .= "</td><td class='tableCellCenter'>";

              //Получаем имя таблицы, если уже заполняли ее
              $tablesA = $db->select_array_row("SELECT * FROM my_admin_tables WHERE `table_name` = '{$table}' LIMIT 1;");
              if (empty($tablesA)){
                  $tablesA['table_descr'] = '';
                  $tablesA['table_icon'] = '';
                  $tablesA['table_show'] = '';
                  $tablesA['table_weight'] = 0;
                  $tablesA['table_r1'] = '';
                  $tablesA['table_r2'] = '';
                  $tablesA['table_r3'] = '';
              }
              //Скрытые поля для каждой таблицы
              $MA_content .= "<input type='hidden' name='tablesNames[{$table}]' id='tableName_{$table}' value='".$tablesA['table_descr']."'>";
              $MA_content .= "<input type='hidden' name='tablesIcons[{$table}]' id='tableIcon_{$table}' value='".$tablesA['table_icon']."'>";

              //Если включаено, чтобы таблицу отображать
              $MA_content .= "<input type='checkbox' name='tablesShows[{$table}]' id='tableShow_{$table}'";
              if ($tablesA['table_show'] == 1)
                  $MA_content .= " checked";
              $MA_content .= ">";
              $MA_content .= "</td><td class='tableCellCenter'>";
              //Вес таблицы. Определяет положение в списке навигации
              $MA_content .= "<select size='1' name='tablesWeights[{$table}]' id='tableWeight_{$table}'>";
              for ($i = 0; $i<30 ;$i++)
              {
                  $MA_content .= "<option value='".$i."'";
                  if ($i == (int)$tablesA['table_weight'])
                      $MA_content .= " selected";
                  $MA_content .= ">".$i."</option>";
              }
              $MA_content .= "</select>";

              $MA_content .= "</td><td class='tableCellCenter'>&nbsp;";
              //Если включаено, чтобы можно было добавлять записи в таблицу
              $MA_content .= "<input type='checkbox' name='tablesMayAdds[{$table}]' id='tableMayAdd_{$table}'";
              if (isset($tablesA['table_may_add']) && $tablesA['table_may_add'] == 1)
                  $MA_content .= " checked";
              $MA_content .= ">";
              $MA_content .= "</td><td class='tableCellCenter'>&nbsp;";
              //Если включаено, чтобы можно было удалять записи из таблицы
              $MA_content .= "<input type='checkbox' name='tablesMayDeletes[{$table}]' id='tableMayDelete_{$table}'";
              if (isset($tablesA['table_may_add']) && $tablesA['table_may_delete'] == 1)
                  $MA_content .= " checked";
              $MA_content .= ">";
              $MA_content .= "</td>";
              $MA_content .= "<td class='tableCellCenter'><input type='text' size=5 name='tablesR1[{$table}]' id='tableR1_{$table}' value='".$tablesA['table_r1']."'></td>";
              $MA_content .= "<td class='tableCellCenter'><input type='text' size=5 name='tablesR2[{$table}]' id='tableR2_{$table}' value='".$tablesA['table_r2']."'></td>";
              $MA_content .= "<td class='tableCellCenter'><input type='text' size=5 name='tablesR3[{$table}]' id='tableR3_{$table}' value='".$tablesA['table_r3']."'></td>";
              $MA_content .= "</tr>";
          }
      }


      $MA_content .= "<tr><td colspan='3'><input type='submit' name='setupFields' value='Сохранить'></td></tr>";
      $MA_content .= "</table>";
  $MA_content .= "</form>";
?>
<?php
  include ($MA_theme);
?>
