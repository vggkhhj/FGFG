<?php
require_once('common.php');

//проверяем права на доступ к этой странице
if ($_SESSION['user']['roles']['role_modules'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header('Location: index.php');

$MA_pageTitle = 'Настройка модулей';

$MA_content .= "<table cellspacing='2' cellpadding='0' border='0'>";
//список установленных модулей

$modulesQ = $db->select_array("SELECT * FROM my_admin_modules;");
if (!empty($modulesQ)) {
    foreach ($modulesQ as $modulesA) {
        $MA_content .= "<form action='setup_module_settings.php' method='POST'>";
        $MA_content .= "<input type='hidden' name='moduleId' value='" . $modulesA['id'] . "'>";
        $MA_content .= "<tr><td>" . $modulesA['module_title'] . "</td><td><input type='submit' name='oneModule' value='Настроить'></td></tr>";
        $MA_content .= "</form>";
    }
}

$MA_content .= "</table>";
?>
<?php
include($MA_theme);
?>
