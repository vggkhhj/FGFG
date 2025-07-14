<?php
require_once ('common.php');
$MA_pageTitle = 'Панель администрирования';

//Выводим навигацию на главной странице
$MA_content .= MA_print_indexNavigation($MA_navigationA);

//Подключаем шаблон
include ($MA_theme);
