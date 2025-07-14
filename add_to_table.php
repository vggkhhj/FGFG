<?php
require_once ('common.php');

//Проверка прав доступа на эту страницу
$tableName = DB::escape($_GET['tableName']);

//------- проверяем права на чтение из таблицы
if(roleCheckReadTable($tableName)){

  $recordId = !empty($_GET['recordId']) ? $_GET['recordId'] : '';
  //проверяем права на запись в таблицу
  $readOnly = !roleCheckWriteTable($tableName);


  if(empty($recordId) && $readOnly){
    $MA_content .= "Не достаточно прав для добавления записей";
  }elseif(!empty($recordId) && ! roleCheckReadRecord($tableName, $recordId)){
    $MA_content .= "Не достаточно прав для чтения записи";
  }else{

    //Проверяем, передано ли имя таблицы и есть ли поля этой таблицы для обработки
    $continue = false;

    if (!empty($tableName)) {
      $fieldsQuery = $db->select_array("SELECT * FROM my_admin_fields WHERE field_table='{$tableName}' ORDER BY field_weight;");
      $MA_pageTitle = $db->select_result("SELECT `table_descr` FROM my_admin_tables WHERE table_name='{$tableName}';");
      if (!empty($fieldsQuery)) $continue = true;
    }

    if ($continue === true) {
      //Проверяем, создается новая запись или редактируется старая
      if (!empty($recordId)) {
        //Проверяем, есть ли такая запись
        $recordArray = $db->select_array_row("SELECT * FROM {$tableName} WHERE id='{$recordId}';");

        #### Вася. Запретить доступ к записям админов ВООБЩЕ
        if (!empty($recordArray)) {
          if ($tableName == 'my_admin_users' && $recordArray['user_role'] < 3 && $recordArray['user_role'] < $_SESSION['user']['roles']['id']) {
            header("Location: index.php");
          }
        } else unset($recordId);
      }

      #### Вася. Сохранять с переходом, учитывающим каталогизатор
      // (было: view_table.php?tableName=".$_GET['tableName'].")
      $MA_content .= "<form action='" . u_print_Eback() . "' method='POST' enctype='multipart/form-data'>
                    <table width='90%' border='0' align='center' cellpadding='0' cellspacing='2'>";
      $formValidate = Array();
      if(!empty($fieldsQuery) ){
        foreach ($fieldsQuery as $fieldsArray) {

          $fieldArray = $db->select_array_row("SELECT * FROM my_admin_fields WHERE id='" . $fieldsArray['id'] . "';");
          if(!empty($fieldArray)){
            $MA_content .= "<tr align='left' valign='top'>";
            if ($fieldArray['field_type'] == 'checkbox') {
              $MA_content .= "<td width='150'><label for='checkbox_" . $fieldsArray['id'] . "'>" . $fieldsArray['field_descr'] . ":</label></td>";
            } else {
              #### Вася. stripslashes()
              $MA_content .= "<td width='150'>" . stripslashes($fieldsArray['field_descr']) . ":</td>";
            }
            $MA_content .= "<td>";

            if (!empty($recordArray))
              $MA_content .= printField($fieldsArray['id'], str_replace("'", "&#039;", $recordArray[$fieldsArray['field_name']]), $readOnly);
            else
              $MA_content .= printField($fieldsArray['id'], NULL, $readOnly);

            $MA_content .= "</td>";
            $MA_content .= "</tr>";
            if ($fieldsArray['field_validation'] != '') {
              $formV['type'] = $fieldsArray['field_type'];
              $formV['id'] = $fieldsArray['field_name'];
              $formV['title'] = $fieldsArray['field_descr'];
              $formV['validation'] = $fieldsArray['field_validation'];
              $formValidate[] = $formV;
            }
          }

        }

      }

      if (!empty($recordArray)) {
        //Если мы редактируем запись
        $MA_content .= "<input type='hidden' name='recordId' value='" . $recordArray['id'] . "'>";
        $MA_content .= "<input type='hidden' name='logId' value='" . getLogId($recordArray['id'], $tableName) . "'>";
      } else {
        //Если создаем новую
        $MA_content .= "<input type='hidden' name='creatorId' value='" . @$_SESSION['user']['id'] . "'>";
      }
      $MA_content .= "<input type='hidden' name='modifierId' value='" . @$_SESSION['user']['id'] . "'>";

      if(!$readOnly){
        $MA_content .= "<tr align='left' valign='top'><td colspan='2'><input type='submit' name='addRecord' value='Сохранить' onclick='return formValidate();'></td></tr>";
      }
      $MA_content .= "<script language='javascript' type='text/javascript'>";
      $MA_content .= "       function formValidate() {";
      //Проверяем есть ли поля для обработки в этой таблице
      if (count($formValidate) > 0)
        foreach ($formValidate as $fVKey => $fVValue)
          $MA_content .= printFieldValidation($fVValue['id'], $fVValue['type'], $fVValue['title'], $fVValue['validation']);

      $MA_content .= "    return true;
                    }
                  </script>";
      $MA_content .= "<tr>
             <td colspan='2'>";
      #### Вася. Ссылка вернуться для таблиц.
      $MA_content .= "<H4 class='btmBackLink'><a href='" . u_print_Eback() . "'>[ Вернуться ]</a></H4>";
      ####   $MA_content .= "  <input type='hidden' name='pageNbr' value='".$_GET['returnToPage']."'>";
      $MA_content .= "</td>
           </tr>
         </table>
       </form>";
    } else
      $MA_content .= "Не выбраны поля для этой таблицы";
  }


}else{
  $MA_content .= "Доступ запрещен";
}

include ($MA_theme);

