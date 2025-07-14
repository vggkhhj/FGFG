<?php
require_once ('common.php');

//проверяем права на доступ к этой странице
if ($_SESSION['user']['roles']['role_fields'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header('Location: index.php');

$MA_pageTitle = 'Настройка полей';

$MA_content .= "<table cellspacing='2' cellpadding='0' border='0' align='center'>";
//список таблиц БД
$tablesQ = $db->select_array("SELECT * FROM my_admin_tables ORDER BY table_weight;");
if(!empty($tablesQ)){
    foreach($tablesQ as $tablesA)
    {
        $MA_content .= "<form action='setup_fields.php' method='POST'>";
        $MA_content .= "<input type='hidden' name='tableId' value='".$tablesA['id']."'>";
        $MA_content .= "<tr><td class='tableCell'>".$tablesA['table_descr']."</td><td><input type='submit' name='oneTable' value='Настроить'></td></tr>";
        $MA_content .= "</form>";
    }
}

$MA_content .= "</table>";

$MA_content .= "<p><form action='setup_fields.php' method='POST'><input type='submit' value='Пошаговая настройка'></form></p>";


include ($MA_theme);

