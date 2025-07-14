<?php
require_once('func.php');
require_once('view_func.php');
require_once('view_func_cat.php');
require_once("ac_pager.class.php");

#### v032. функция "запомнить меня"
user_checkToken() OR $_SESSION['user'] = checkAdmin($_SESSION['user']['login'], $_SESSION['user']['password']);

if ($_SESSION['user'] == false) {
    header('Location: login.php');
    exit;
}

$MA_userPrefix = 'user';

//определяем шаблон и путь к нему
$MA_theme = 'themes/green/template.php';
$MA_themePath = 'themes/green/';

$themeArray = $db->select_array_row("SELECT * FROM my_admin_about WHERE about_param='theme' LIMIT 1;");
if (!empty($themeArray)) {
    $MA_theme = "themes/" . $themeArray['about_value'] . "/template.php";
    $MA_themeName = $themeArray['about_value'];
    $MA_themePath = "themes/" . $themeArray['about_value'] . "/";
} else {
    $MA_theme = "themes/green/template.php";
    $MA_themePath = "themes/green/";
}

//Подлючаем пользовательские функции
if (file_exists('theme_func.php'))
    require_once('theme_func.php');

//Создаем навигацию
$MA_navigation = '';
$MA_navigationA = Array();

$tables = $db->select_array("SELECT * FROM my_admin_tables ORDER BY table_weight;");
if (!empty($tables)) {
    foreach ($tables as $tablesArray) {
        //Если таблица открыта для отображения и пользователю можно ее просматривать (либо он root, либо нет, но открыты права) - выводим ее
        if ($tablesArray['table_show'] == 1 && ($_SESSION['user']['roles']['role_title'] == 'root' || ($_SESSION['user']['roles']['role_title'] != 'root' && isset($_SESSION['user']['roles'][$tablesArray['table_name']]) && $_SESSION['user']['roles'][$tablesArray['table_name']] > 0))) {
            $MA_navigationA_temp = Array();
            $MA_navigationA_temp['descr'] = $tablesArray['table_descr'];
            $MA_navigationA_temp['icon'] = "<img src='css/icons/" . $tablesArray['table_icon'] . ".png' border='0' width='15' height='15' alt='' title=''>";
            $MA_navigationA_temp['name'] = $tablesArray['table_name'];
            if (!empty($_GET['tableName']) && $_GET['tableName'] == $tablesArray['table_name']) {
                $MA_navigationA_temp['active'] = true;
            } else {
                $MA_navigationA_temp['active'] = false;
            }
            $MA_navigationA_temp['link'] = "view_table.php?tableName=" . $tablesArray['table_name'];
            $MA_navigationA[] = $MA_navigationA_temp;
        }
    }

}

$MA_navigation .= MA_print_navigation($MA_navigationA);


//создаем главные ссылки
$MA_mainLinks = '';
$MA_mainLinksA = Array();
$MA_mainLinksA['main'] = "<a href='index.php'>На главную</a><hr>";
if ($_SESSION['user']['roles']['role_about'] == '7')
    $MA_mainLinksA['setup_admin'] = "<a href='setup_admin.php'>Общие настройки</a>";
if ($_SESSION['user']['roles']['role_tables'] == '7')
    $MA_mainLinksA['setup_tables'] = "<a href='setup_bases.php'>Настройка таблиц</a>";
if ($_SESSION['user']['roles']['role_fields'] == '7')
    $MA_mainLinksA['setup_fields'] = "<a href='setup_fields_settings.php'>Настройка полей</a>";
if ($_SESSION['user']['roles']['role_modules'] == '7') {
    $MA_mainLinksA['install_modules'] = "<a href='setup_modules.php'>Установка модулей</a>";
    $MA_mainLinksA['setup_modules'] = "<a href='setup_modules_settings.php'>Настройка модулей</a>";
}
if ($_SESSION['user']['roles']['role_roles'] == '7')
    $MA_mainLinksA['setup_roles'] = "<a href='setup_roles.php'>Настройка ролей</a>";
if ($_SESSION['user']['roles']['role_fman'] == '7') {
    $MA_mainLinksA['filemanager'] = "<a href='filemanager.php'>Файловый менеджер</a>";
}
if ($_SESSION['user']['roles']['role_about'] == '7') {
    $MA_mainLinksA['fman_setup'] = "<a href='view_table.php?tableName=my_admin_fman'>Настройка менеджера</a>";
}
$MA_mainLinksA['exit'] = "<hr><a href='exit.php'>Выход</a>";
$MA_mainLinks = MA_print_mainLinks($MA_mainLinksA);

//ссылки на управление сайтом
$MA_siteLinks = '';
$MA_siteLinksA = Array();
if ($_SESSION['user']['roles']['role_site_setup'] == '7') {
    $MA_siteLinksA['setup_site_pages'] = "<hr><a href='setup_site_pages.php'>Страницы сайта</a>";
    $MA_siteLinksA['setup_site_siteplugins'] = "<a href='view_table.php?tableName=my_site_siteplugins'>Плагины сайта</a>";
    $MA_siteLinksA['setup_site_plugins'] = "<a href='setup_site_plugins.php'>Схемы плагинов</a>";
    $MA_siteLinksA['setup_site_dynamic_pages_types'] = "<a href='setup_site_dynamic_pages_types.php'>Типы дин. разделов</a>";
}
if ($_SESSION['user']['roles']['role_site_dynamic'] == '7') {
    $MA_siteLinksA['setup_site_dynamic_pages'] = "<a href='setup_site_dynamic_pages.php'>Динамические разделы</a>";
}
$MA_siteLinks = MA_print_siteLinks($MA_siteLinksA);

//ссылки на управление блоками, рассылкой
$MA_siteDataLinks = '';
$MA_siteDataLinksA = Array();
if ($_SESSION['user']['roles']['role_site_data'] == '7') {
    //TODO: конструктор ссылок
    //TODO: блоки на страницах
    $MA_siteDataLinksA['setup_site_links'] = "<a href='setup_site_plugins.php'>Конструктор ссылок</a>";
    $MA_siteDataLinksA['setup_site_text_blocks'] = "<a href='view_table.php?tableName=my_site_text_blocks'>Текстовые блоки</a>";
    $MA_siteDataLinksA['setup_site_emails'] = "<a href='view_table.php?tableName=my_site_emails'>Адреса для рассылок</a>";
    $MA_siteDataLinksA['setup_site_email_data'] = "<a href='view_table.php?tableName=my_site_email_data'>Письма для рассылок</a>";
    $MA_siteDataLinksA['setup_view_catalog_data'] = "<a href='view_table.php?tableName=my_admin_catalog'>Структура каталога</a>";
}
$MA_siteDataLinks = MA_print_siteDataLinks($MA_siteDataLinksA);

//задаем общее содержимое
$MA_content = "
   <div style='position: absolute; text-align: center; width: 600px;'>
   <div id='uploadImageWindow' style='position: relative; display: none; width: 195px; height: 90px; top: 30px; border: 1px solid #555555; text-align: left; text-indent: 10px; margin: auto auto; padding: 0; z-index: 99;'>
    <div style='display: block; position: absolute; width: 195px; height: 90px; left: 0; top: 0; background-color: #CCCCCC; opacity: 0.8; filter: alpha(opacity=80); z-index: 0;'></div>
    <div style='display: block; position: absolute; width: 195px; height: 70px; left: 0; top: 15px; text-align: center;'>
        <div class='addWindowTR'>
          <form action='upload_file.php' target='uploadFrame' method='POST' id='uploadForm' enctype='multipart/form-data'><input type='file' name='uploadFile' size='16'></form>
        </div>
      <div id='okBtn' style='position: absolute; display: block; width: 80px; height: 20px; left: 10px; bottom: 10px; border: 1px solid #b1b1b1; cursor: pointer;' onclick='uploadFile();'>Ок</div>
      <div id='cancelBtn' style='position: absolute; display: block; width: 80px; height: 20px; left: 100px; bottom: 10px; border: 1px solid #b1b1b1; cursor: pointer;' onclick=\"document.getElementById('uploadImageWindow').style.display='none'; return false;\">Отмена</div>
    </div>
   </div>
  </div>
  <div style='display: none;'><iframe id='uploadFrame' name='uploadFrame'></iframe></div>
  <div style='display: none;'><iframe id='toFrame' name='toFrame'></iframe></div>
  ";

//задаем общее содержимое HEAD
$MA_head = "
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
	<meta http-equiv='Content-Type' content='text/html; language=ru'/>
	<meta http-equiv='Content-Script-Type' content='text/javascript'/>
	<link rel='Shortcut Icon' href='" . HREF_DOMAIN . "/favicon.ico'/>
    <script src='js/jquery.js' type='text/javascript'> </script>
    <script src='js/setup.js' type='text/javascript'> </script>
    <script type='text/javascript'>CKEDITOR_BASEPATH = '" . HREF_ADD . "ckeditor/';</script>
    <script src='" . HREF_ADD . "/ckeditor/ckeditor.js' type='text/javascript'></script>
    <link type='text/css' rel='stylesheet' href='css/styles.css'>
  ";
//Проверяем, что нам нужно еще подключить
//Проверка календаря
$includeInHead_q = $db->select_result("SELECT `id` FROM `my_admin_fields` WHERE `field_type`='date' AND `field_tinymce`='calendar' LIMIT 1;");
if (!empty($includeInHead_q)) {
    $calendarPath = HREF_ADD . "jscalendar";
    $MA_head .= "
	    <script src='{$calendarPath}/calendar.js' type='text/javascript'> </script>
	    <script src='{$calendarPath}/calendar-setup.js' type='text/javascript'> </script>
	    <script src='{$calendarPath}/lang/calendar-ru_win_.js' type='text/javascript'> </script>
	    <link rel='stylesheet' type='text/css' media='all' href='{$calendarPath}/calendar-win2k-cold-1.css' title='win2k-cold-1' />
        ";
}

//опреляем название сайта
$MA_logo = '';
$aboutArray = $db->select_array_row("SELECT * FROM my_admin_about WHERE about_param='admin_title';");
if (!empty($aboutArray)) {
    $MA_logo = $aboutArray['about_value'];
}

