<?php
	header("Content-Type: text/html; Charset='utf-8'");
   require_once ('func.php');
   //определяем шаблон и путь к нему
   $MA_theme = 'themes/green/template.php';
   $MA_themePath = 'themes/green/';
    $themeArray = $db->select_array_row("SELECT * FROM my_admin_about WHERE about_param='theme' LIMIT 1;");
   if (!empty($themeArray)) {
     $MA_theme = "themes/".$themeArray['about_value']."/template.php";
     $MA_themeName = $themeArray['about_value'];
     $MA_themePath = "themes/".$themeArray['about_value']."/";
   } else {
     $MA_theme = "themes/green/template.php";
     $MA_themePath = "themes/green/";
   }
   //require_once('func.php');
   
   if (@$_REQUEST['loginBtn']) {
     $user = checkAdmin($_POST['login'], $_POST['password']);
     if ($user != false) {
       //В $_SESSION['user'] хранится вся инфа юзера и в подмассиве roles хранятся уровние доступа этого пользователя
       $_SESSION['user'] = $user;
       updateTablesInSession();
		 
				#### v032. запишем ключ
			if(!empty($_POST['with_token'])) user_addToken($user['id']);

       //если залогинился Root, то уведомляем на почту (если включено в админке)
       if ($_SESSION['user']['roles']['role_title'] == 'root') {
           $confArray = $db->select_array_row("SELECT * FROM my_admin_about WHERE about_param='admin_conf' LIMIT 1;");
         if (!empty($confArray)) {
           if (!empty($confArray['about_value'])) {
               $adminArray = $db->select_array_row("SELECT * FROM my_admin_about WHERE about_param='admin_title' LIMIT 1;");
             if (!empty($adminArray)) {
                @mail($confArray['about_value'], $adminArray['about_value'].': root logged in', 'Root logged in: '.date('r'));
             } else @mail($confArray['about_value'], 'Unnamed MyAdmin: root logged in', 'Root logged in: '.date('r'));
           }
         }
       }
       
       header('Location: index.php');
     }
   }
   $MA_content = "
   <form action='login.php' method='post'>
     <table align='center' border ='0' cellspacing='2' cellpadding='0'>
       <tr><td colspan=2'>&nbsp;</td></tr>
       <tr><td align='right'>Логин:</td><td align='left'><input type='text' name='login'></td></tr>
       <tr><td align='right'>Пароль:</td><td align='left'><input type='password' name='password'></td></tr>
       <tr><td align='right'>&nbsp;</td><td align='right' valign='middle'>запомнить меня: <input type='checkbox' name='with_token'></td></tr>
       <tr><td colspan=2' align='right'><input type='submit' value='Войти' name='loginBtn'></td></tr>
     </table>
   </form>
   ";
   //Подключаем шаблон
   include ($MA_theme);