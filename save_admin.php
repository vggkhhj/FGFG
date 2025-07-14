<?php
require_once ('common.php');

//проверяем права на доступ к это таблице
if ($_SESSION['user']['roles']['role_about'] == 0 && $_SESSION['user']['roles']['role_title'] != 'root') header('Location: index.php');

//Сохраняем поля
if (!empty($_POST['admin_title'])) {
    //Убираем escape-символы
    $_POST['admin_title'] = DB::escape($_POST['admin_title']);

    //Если такая запись есть
    $adminArray = $db->select_array_row("SELECT * FROM my_admin_about WHERE about_param='admin_title' LIMIT 1;");
    if (!empty($adminArray))
    {
        //Обновляем имя с которым хранить
        $db->query("UPDATE my_admin_about SET about_param='admin_title', about_value='".$_POST['admin_title']."' WHERE id='".$adminArray['id']."' LIMIT 1;");
    } else {
        //Добавляем новую запись
        $db->query("INSERT INTO my_admin_about SET about_param='admin_title', about_value='".$_POST['admin_title']."';");
    }

}

if (!empty($_POST['colorTheme'])) {
    //Убираем escape-символы
    $_POST['colorTheme'] = DB::escape($_POST['colorTheme']);

    //Если такая запись есть
    $adminArray = $db->select_array_row("SELECT * FROM my_admin_about WHERE about_param='theme' LIMIT 1;");
    if (!empty($adminArray)) {
        //Обновляем имя с которым хранить
        $db->query("UPDATE my_admin_about SET about_param='theme', about_value='".$_POST['colorTheme']."' WHERE id='".$adminArray['id']."' LIMIT 1;");
    } else{
        //Добавляем новую запись
        $db->query("INSERT INTO my_admin_about SET about_param='theme', about_value='".$_POST['colorTheme']."';");
    }


}

if (!empty($_POST['admin_conf'])) {
    //Убираем escape-символы
    $_POST['admin_conf'] = DB::escape($_POST['admin_conf']);

    //Если такая запись есть
    $adminArray = $db->select_array_row("SELECT * FROM my_admin_about WHERE about_param='admin_conf' LIMIT 1;");
    if (!empty($adminArray)) {
        //Обновляем имя с которым хранить
        $db->query("UPDATE my_admin_about SET about_param='admin_conf', about_value='".$_POST['admin_conf']."' WHERE id='".$adminArray['id']."' LIMIT 1;");
    } else{
        //Добавляем новую запись
        $db->query("INSERT INTO my_admin_about SET about_param='admin_conf', about_value='".$_POST['admin_conf']."';");
    }

}