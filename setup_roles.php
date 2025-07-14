<?php
  require_once ('common.php');
  
  //Проверка прав доступа на эту страницу
  if ($_SESSION['user']['roles']['role_roles'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header("Location: index.php");
  
  $MA_pageTitle = 'Настройка прав доступа';
  
  //Если был запрос на сохранение изменений
  if (!empty($_REQUEST['saveRoles'])) {
    //Если были записи ролей
    if (count(@$_POST['roles'])>0)
      //Перебираем каждую роль
      foreach($_POST['roles'] as $roleId => $rolesArray) {
        //Перебираем каждую таблицу в правах
        $rolesQ = '';
        foreach($rolesArray as $roleName => $roleValue) {
          if ($rolesQ != '') $rolesQ .= ', ';
          $rolesQ .= "`".$roleName."`='".DB::escape($roleValue)."'";
        }
        $db->query("UPDATE my_admin_roles SET {$rolesQ} WHERE id='{$roleId}' LIMIT 1;");
      }
  }
  
  //Если был запрос на добавление новой роли
  if (!empty($_REQUEST['addRole']) && @$_POST['newRole']!='')
    $db->query("INSERT INTO my_admin_roles SET role_title='".getRandomName()."', role_descr='".DB::escape($_POST['newRole'])."';");
  
  //Если был запрос на удаление роли
  if (!empty($_GET['del']))
    $db->query("DELETE FROM my_admin_roles WHERE id='".DB::escape($_GET['del'])."' and role_title<>'root' LIMIT 1;");

  //Получаем массив полей для всех ролей без поля id и полей настройки общих прав
  $fieldsQuery = $db->query("SELECT * FROM my_admin_roles LIMIT 1;");
  $fieldsData = Array();
  for ($i=0; $i<DB::num_fields($fieldsQuery); $i++) {
    $fData = DB::fetch_field($fieldsQuery, $i);
    if (($fData->name != 'id') && (strpos($fData->name, 'role_') === false)) {
      $tmpArray = Array();
      $tmpArray['name'] = $fData->name;
      $tmpArray['descr'] = tableDescrByTableName($fData->name);
      $fieldsData[] = $tmpArray;
    }
  }

  //Выводим список ролей
  $cPager = new ac_pager();
  $cPager->setQuery("SELECT * FROM my_admin_roles WHERE role_title<>'root' ORDER BY role_descr;");
  $cPager->setRecordsPerPage($cPager->recordsCnt);
    $MA_content .= "
      <form action='setup_roles.php' method='post'>
        <table cellspacing='2' cellpadding='3' border='0' align='center'>";
  if ($cPager->recordsCnt > 0) {
    while ($rolesArray = $cPager->getRow()) {
      $MA_content .= "
            <tr valign='top'>
              <td class='tableCell' align='center'><b>".$rolesArray['role_descr']."</b><br><a href='setup_roles.php?del=".$rolesArray['id']."' onclick=\"return window.confirm('Удалить запись?');\">[удалить запись]</a></td>
              <td>
                <table cellspacing='2' cellpadding='3' border='0' align='center'>
                ";
                //Выводим список прав и их значения для этой роли.
                  //Для вывода значения нужно передать в функцию генерации опций селекта
                  //массив с вариантами выбора и значения, которое сейчас установлено.
                  //Варианты массивов
                  $selGen1 = array('Разрешено' => '7', 'Запрещено' => '0');
                  $selGen2 = array('Доступ: нет' => '0', 'Свои: чтение' => '1', 'Свои: чтение + изменение' => '2', 'Свои: чтение + изменение + удаление' => '3', 'Все: чтение' => '4', 'Все: чтение + изменение' => '5', 'Все: чтение + изменение + удаление' => '6', 'Полный доступ' => '7');
                  //$selGen2 = array('Чтение: нет' => '0', 'Чтение: свои' => '1', 'Чтение: все' => '2', 'Изменение: свои' => '3', 'Изменение: все' => '4', 'Изменение и удаление: свои' => '5', 'Полный доступ' => '7');
                //Статические права (общие)
                $MA_content .= "
                  <tr>
                    <td class='tableCell'>Личные данные</td>
                    <td class='tableCell'>
                      <select name='roles[".$rolesArray['id']."][role_info]' size='1'>".rolesOptGen($selGen1, $rolesArray['role_info'])."</select>
                    </td>
                  </tr>
                  <tr>
                    <td class='tableCell'>Настройка таблиц</td>
                    <td class='tableCell'>
                      <select name='roles[".$rolesArray['id']."][role_tables]' size='1'>".rolesOptGen($selGen1, $rolesArray['role_tables'])."</select>
                    </td></tr>
                  <tr>
                    <td class='tableCell'>Настройка полей</td>
                    <td class='tableCell'>
                      <select name='roles[".$rolesArray['id']."][role_fields]' size='1'>".rolesOptGen($selGen1, $rolesArray['role_fields'])."</select>
                    </td>
                  </tr>
                  <tr>
                    <td class='tableCell'>Настройка модулей</td>
                    <td class='tableCell'>
                      <select name='roles[".$rolesArray['id']."][role_modules]' size='1'>".rolesOptGen($selGen1, $rolesArray['role_modules'])."</select>
                    </td>
                  </tr>
                  <tr>
                    <td class='tableCell'>Общие настройки</td>
                    <td class='tableCell'>
                      <select name='roles[".$rolesArray['id']."][role_about]' size='1'>".rolesOptGen($selGen1, $rolesArray['role_about'])."</select>
                    </td>
                  </tr>
                  <tr>
                    <td class='tableCell'>Пользователи</td>
                    <td class='tableCell'>
                      <select name='roles[".$rolesArray['id']."][role_users]' size='1'>".rolesOptGen($selGen1, $rolesArray['role_users'])."</select>
                    </td>
                  </tr>
                  <tr>
                    <td class='tableCell'>Роли</td>
                    <td class='tableCell'>
                      <select name='roles[".$rolesArray['id']."][role_roles]' size='1'>".rolesOptGen($selGen1, $rolesArray['role_roles'])."</select>
                    </td>
                  </tr>
                  <tr>
                    <td class='tableCell'>Управление сайтом</td>
                    <td class='tableCell'>
                      <select name='roles[".$rolesArray['id']."][role_site_setup]' size='1'>".rolesOptGen($selGen1, $rolesArray['role_site_setup'])."</select>
                    </td>
                  </tr>
                  <tr>
                    <td class='tableCell'>Динамические разделы</td>
                    <td class='tableCell'>
                      <select name='roles[".$rolesArray['id']."][role_site_dynamic]' size='1'>".rolesOptGen($selGen1, $rolesArray['role_site_dynamic'])."</select>
                    </td>
                  </tr>
                  <tr>
                    <td class='tableCell'>Блоки сайта</td>
                    <td class='tableCell'>
                      <select name='roles[".$rolesArray['id']."][role_site_data]' size='1'>".rolesOptGen($selGen1, $rolesArray['role_site_data'])."</select>
                    </td>
                  </tr>
                  <tr>
                    <td class='tableCell'>Файловый менеджер</td>
                    <td class='tableCell'>
                      <select name='roles[".$rolesArray['id']."][role_fman]' size='1'>".rolesOptGen($selGen1, $rolesArray['role_fman'])."</select>
                    </td>
                  </tr>
                ";
                //Динамические права (права для таблиц)
                foreach ($fieldsData as $fDKey => $fDValue) {
                  $MA_content .= "
                    <tr>
                      <td class='tableCell'>".$fDValue['descr']."</td>
                      <td class='tableCell'>
                        <select name='roles[".$rolesArray['id']."][".$fDValue['name']."]' size='1'>
                          ".rolesOptGen($selGen2, $rolesArray[$fDValue['name']])."
                        </select>
                      </td>
                    </tr>
                  ";
                }
      $MA_content .= "
                </table>
              </td>
            </tr>
            <tr><td colspan='2'>&nbsp;</td></tr>
      ";
    }
    $MA_content .= "<tr><td align='right' colspan='2'><input type='submit' value='Сохранить' name='saveRoles'></td></tr>";
    $MA_content .= "<tr><td colspan='2'>&nbsp;</td></tr>";
  }
    $MA_content .= "<tr><td><b>Новая роль:</b></td><td><input type='text' size='40' name='newRole' maxlength='150'></td></tr>";
    $MA_content .= "<tr><td align='right' colspan='2'><input type='submit' value='Добавить' name='addRole'></td></tr>";
    $MA_content .= "</table></form>";
?>
<?php
  include ($MA_theme);
?>
