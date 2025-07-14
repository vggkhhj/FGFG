<?php
  require_once ('common.php');
  
  //проверяем права на доступ к этой странице
  if ($_SESSION['user']['roles']['role_modules'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header('Location: index.php');
  
  $MA_pageTitle = 'Настройка модулей';
?>
<?php
  //Получаем список всех найденных модулей
  if ($modulesDir = opendir('modules')) {
    $MA_content .= "<form action='save_modules.php' target='toFrame' method='post'>";
    $MA_content .= "<table cellspacing='2' cellpadding='3' border='0'>";
    $MA_content .= "<tr align='center'><td>&nbsp;</td><td class='tableCell'>Имя модуля</td><td class='tableCell'>Описание модуля</td></tr>";
    while (false !== ($modulesDirItem = readdir($modulesDir))) {
      if (!is_file($modulesDirItem) && $modulesDirItem != "." && $modulesDirItem != "..") {
        //Проверяем все ли смодулем в порядке, все ли файлы на месте, и если да, получаем данные о модуле из info.php
        if (moduleIsCorrect($modulesDirItem)) {
          require_once('modules/'.$modulesDirItem.'/info.php');
          $infoArray = call_user_func($modulesDirItem.'_info');
          //Выводим список модулей, с которыми возможна работа
          $MA_content .= "<tr valign='top'>";
            $MA_content .= "<td class='tableCell'>";
              $MA_content .= "<input type='checkbox' name='modulesBoxes[".$modulesDirItem."]' id='moduleBox_".$modulesDirItem."' onchange=\"modulesActivation(this);\"";
              if ($db->select_result("SELECT `id` FROM my_admin_modules WHERE module_name='{$modulesDirItem}';"))
                $MA_content .= " checked";
              $MA_content .= ">"; //если метка включается, открыть окно выбора названия
            $MA_content .= "</td>";
            $MA_content .= "<td class='tableCell'>".$infoArray['title']."</td>";
            $MA_content .= "<td class='tableCell'>".$infoArray['descr']."</td>";
          $MA_content .= "</tr>";
          //Скрытые поля для каждого модуля
          $MA_content .= "<input type='hidden' name='modulesNames[".$modulesDirItem."]' id='moduleName_".$modulesDirItem."' value='".$modulesDirItem."'>";
          $MA_content .= "<input type='hidden' name='modulesTitles[".$modulesDirItem."]' id='moduleTitle_".$modulesDirItem."' value='".$infoArray['title']."'>";
          $MA_content .= "<input type='hidden' name='modulesDesrcs[".$modulesDirItem."]' id='moduleDesrc_".$modulesDirItem."' value='".$infoArray['descr']."'>";
          //echo $modulesDirItem."<br>";
        }
      }
    }
    $MA_content .= "<tr><td colspan='3'><input type='submit' name='saveModules' value='Сохранить'></td></tr>";
    $MA_content .= "</table>";
    $MA_content .= "</form>";
    closedir($modulesDir);
  } else $MA_content .= "Директория модулей не найдена.";
?>
<?php
  include ($MA_theme);
?>
