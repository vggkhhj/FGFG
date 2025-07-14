<?php
  require_once ('common.php');
  
  //проверяем права на доступ к это таблице
  if ($_SESSION['user']['roles']['role_about'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header('Location: index.php');
  
  $MA_pageTitle = 'Настройка общих параметров';

  $MA_content .= "<form action='save_admin.php' target='toFrame' method='post'>
      <table width='550' align='center' border ='0' cellspacing='2' cellpadding='0'>
        <tr align='left' valign='top'>
          <td width='200'>Заголовок панели:</td>
          <td><input type='text' size='50' maxlength='100'";
            //Если заголовок уже был, выводим его
            $admin_title = $db->select_result("SELECT `about_value` FROM my_admin_about WHERE about_param='admin_title' LIMIT 1;");
            if (!empty($admin_title)) {
                $MA_content .= " value='".str_replace("'","&#039;",$admin_title)."' ";
            }
          $MA_content .= "name='admin_title'></td>
        </tr>
        <tr align='left' valign='top'>
          <td>Шаблон:</td>
          <td>";
              //Если цветовая гамма уже была выбрана, получаем ее значение
            $theme_value = $db->select_result("SELECT `about_value` FROM my_admin_about WHERE about_param='theme' LIMIT 1;");
            if(empty($theme_value)) $theme_value = 'green';


           $MA_content .= "<select name='colorTheme' size='1'>";
           if ($themesDir = opendir('themes')) {
             while (false !== ($themesDirItem = readdir($themesDir))) {
               if (!is_file($themesDirItem) && $themesDirItem != "." && $themesDirItem != "..") {
                 $MA_content .= "<option value='".$themesDirItem."' ";
                   if ($theme_value == $themesDirItem) $MA_content .= 'selected';
                 $MA_content .= ">".$themesDirItem."</option>";
               }
             }
           }
           $MA_content .= "</select>

          </td>
        </tr>
        <tr align='left' valign='top'>
          <td>При входе Администратора уведомить на e-mail:</td>
          <td><input type='text' size='50' maxlength='100'";
            //Если уже был задан e-mail, выводим его
            $admin_conf = $db->select_result("SELECT `about_value` FROM my_admin_about WHERE about_param='admin_conf' LIMIT 1;");
            if(!empty($admin_conf)){
                $MA_content .= " value='".$admin_conf."' ";
            }
            $MA_content .= "name='admin_conf'></td>
        </tr>
      </table>
      <input type='submit' value='Сохранить'>
    </form>";
?>
<?php
  include ($MA_theme);