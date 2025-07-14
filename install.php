<?php
  require_once('func.php');
  
  $CUR_ERROR_REP=error_reporting();
  #!!!!error_reporting(E_ALL ^E_NOTICE ^E_WARNING);
  
  
  //Проверяем существует ли таблица описания таблиц
  $query = "CREATE TABLE IF NOT EXISTS my_admin_tables (id INT NOT NULL AUTO_INCREMENT , table_name VARCHAR( 50 ) NOT NULL , table_descr VARCHAR( 30 ) NOT NULL , table_icon VARCHAR( 30 ) NOT NULL , table_show BINARY NOT NULL , table_weight INT (3) NOT NULL , table_may_delete BINARY NOT NULL , table_may_add BINARY NOT NULL, table_r1 VARCHAR( 300 ) NOT NULL, table_r2 VARCHAR( 300 ) NOT NULL, table_r3 VARCHAR( 300 ) NOT NULL , PRIMARY KEY ( id ))  ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
  if($db->query($query)){
    $query = 
    "INSERT INTO `my_admin_tables` (`id`, `table_name`, `table_descr`, `table_icon`, `table_show`, `table_weight`, `table_may_delete`, `table_may_add`, `table_r1`, `table_r2`, `table_r3`) VALUES
    (1, 'my_admin_roles', 'Права пользователя', '1', '0', 10, '0', '0', '', '', ''),
    (2, 'my_admin_users', 'Учетные записи', 'black_n_white/1', '1', 29, '1', '1', '', '', ''),
    (3, 'my_site_email_data', 'Письма для рассылок', '2', '0', 26, '1', '1', '', '', ''),
    (4, 'my_site_emails', 'Адреса для рассылок', '4', '0', 0, '26', '1', '', '', ''),
    (5, 'my_site_text_blocks', 'Текстовые блоки', '2', '0', 0, '21', '1', '', '', ''),
    (6, 'my_site_plugin_schemes', 'Схемы плагинов', '1', '0', 10, '0', '0', '', '', ''),
    (7, 'my_site_siteplugins', 'Плагины сайта', '2', '0', 19, '1', '1', '', '', ''),
	(8, 'my_admin_fman', 'Настройки файлменеджера', 'black_n_white/4', '0', 18, '1', '1', '', '', ''),
	(9, 'my_admin_catalog', 'Структура каталога', 'black_n_white/8', '0', 25, '1', '1', '', '', ''),
	(10, 'site_pages_constant', 'Статические страницы', 'black_n_white/2', '1', 6, '1', '1', '', '', ''),
	(11, 'site_pages_variable', 'Изменяемые страницы', 'black_n_white/2', '1', 6, '1', '1', '', '', ''),
	(12, 'my_site_params', 'Параметры', 'black_n_white/4', '1', 20, '0', '0', '', '', '');
	";
    $db->query($query);
  }

  //Проверяем существует ли таблица описания полей
  $query = "CREATE TABLE IF NOT EXISTS `my_admin_fields` (id INT NOT NULL AUTO_INCREMENT , field_ident BINARY NOT NULL , field_table VARCHAR( 50 ) NOT NULL , field_name VARCHAR( 50 ) NOT NULL , field_descr VARCHAR( 30 ) NOT NULL , field_type VARCHAR( 30 ) , field_rules VARCHAR( 100 ) , field_default TEXT , field_p1 VARCHAR( 50 ) , field_p2 VARCHAR( 50 ) , field_tinymce VARCHAR( 15 ) , field_validation VARCHAR( 30 ), field_weight INT (3) NOT NULL , PRIMARY KEY ( id ))  ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
  if($db->query($query)){
    $query =
	"INSERT INTO `my_admin_fields` (`field_ident`, `field_table`, `field_name`, `field_descr`, `field_type`, `field_rules`, `field_default`, `field_p1`, `field_p2`, `field_tinymce`, `field_validation`, `field_weight`) VALUES
		('1', 'my_admin_roles', 'role_descr', 'Права пользователя', 'text', 'size=''50'', maxlength=''150''', '', '', '', '', 'notempty',0),
		('1', 'my_admin_users', 'user_who', 'Пользователь', 'text', 'size=''50'', maxlength=''150''', '', '', '', '', 'notempty',0),
		('0', 'my_admin_users', 'user_login', 'Логин', 'text', 'size=''50'', maxlength=''150''', '', '', '', '', 'password',0),
		('0', 'my_admin_users', 'user_password', 'Пароль', 'text', 'size=''50'', maxlength=''150''', '', '', '', '', 'password',0),
		('0', 'my_admin_users', 'user_role', 'Права пользователя', 'link', 'noempty', '', 'my_admin_roles', 'id', '', '',0),
		('1', 'my_site_email_data', 'item_key', 'Ключ', 'text', 'size=''50'', maxlength=''150''', '', '', '', '', 'notempty',0),
		('1', 'my_site_email_data', 'theme', 'Тема письма', 'text', 'size=''50'', maxlength=''100''', '', '', '', '', 'notempty',0),
		('0', 'my_site_email_data', 'message', 'Сообщение', 'textarea', 'rows=''10'', cols=''37''', '', '', '', 'simple', '',0),
		('1', 'my_site_email_data', 'reply_to', 'Обратный адрес', 'text', 'size=''50'', maxlength=''250''', '', '', '', '', '',0),
		('0', 'my_site_email_data', 'isHTML', 'В формате HTML', 'checkbox', 'checked', '1', '', '', '', '',0),
		('1', 'my_site_emails', 'email', 'Адрес', 'text', 'size=''50'', maxlength=''250''', '', '', '', '', 'notempty',0),
		('1', 'my_site_emails', 'category', 'Категория', 'text', 'size=''50'', maxlength=''11''', '', '', '', '', '',0),
		('0', 'my_site_emails', 'user_id', 'Пользователь', 'link', 'empty', '', 'my_admin_users', 'id', '', '',0),
		('1', 'my_site_text_blocks', 'item_key', 'Ключ', 'text', 'size=''50'', maxlength=''50''', '', '', '', '', 'notempty',0),
		('0', 'my_site_text_blocks', 'html', 'Текст', 'textarea', 'rows=''10'', cols=''37''', '', '', '', 'advanced', '',0),
		('1', 'my_site_siteplugins', 'scheme_id', 'Плагин', 'link', 'noempty', '', 'my_site_plugin_schemes', 'item_key', '', '',0),
		('1', 'my_admin_fman', 'path', 'Путь', 'text', 'size=''50'', maxlength=''200''', '', '', 'nothing', '', 'notempty',0),
			-- КОНТАКТЫ
		(0, 'contacts', 'email_info', 'E-Mail', 'text', '".DB::escape("size='50', maxlength='765'")."', '', '', 'nothing', '', '',4),
		(0, 'contacts', 'email_admin', 'E-Mail администратора', 'text', '".DB::escape("size='50', maxlength='765'")."', '', '', 'nothing', '', '',6),
		(1, 'contacts', 'phone', 'Телефон', 'text', '".DB::escape("size='50', maxlength='1533'")."', '', '', 'nothing', '', '',8),
		(0, 'contacts', 'address', 'Адрес', 'textarea', '".DB::escape("rows='10', cols='37'")."', '', '', '', 'Basic', '',9),
		(0, 'contacts', 'weight', 'Вес', 'text', '".DB::escape("size='50', maxlength='6'")."', '', '', 'nothing', '', '',16),
		(0, 'contacts', 'allow_show', 'Показывать', 'checkbox', 'checked', '1', '', '', '', '',18),
			-- ШАБЛОНЫ ПИСЕМ
		(1, 'tpl_emails', 'head', 'Заголовок сообщения', 'text', '".DB::escape("size='50', maxlength='765'")."', '', '', 'nothing', '', '',4),
		(0, 'tpl_emails', 'content', 'Содержимое', 'textarea', '".DB::escape("rows='10', cols='37'")."', '', '', '', 'Advanced', '',6),
		(0, 'tpl_emails', 'description', 'Описание', 'textarea', '".DB::escape("rows='10', cols='37'")."', '', '', '', '', '',18),
			-- ОТПРАВЛЕННЫЕ ПИСЬМА
		(1, 'log_send_email', 'name', 'Имя', 'text', '".DB::escape("size='50', maxlength='765'")."', '', '', 'nothing', '', 'notempty',4),
		(1, 'log_send_email', 'email_to', 'E-Mail получателя', 'text', '".DB::escape("size='50', maxlength='384'")."', '', '', 'nothing', '', '',2),
		(0, 'log_send_email', 'head', 'Заголовок сообщения', 'text', '".DB::escape("size='50', maxlength='765'")."', '', '', 'nothing', '', '',6),
		(0, 'log_send_email', 'body', 'Текст письма', 'textarea', '".DB::escape("rows='10', cols='37'")."', '', '', '', 'Advanced', '',8),
		(0, 'log_send_email', 'note', 'Примечание', 'text', '".DB::escape("size='50', maxlength='765'")."', '', '', 'nothing', '', '',14),
		(0, 'log_send_email', 'date', 'Дата', 'date', '', '', 'currentdate', 'date', 'calendar', '',15),
			-- СТРУКТУРА КАТАЛОГА
		('1', 'my_admin_catalog', 'table_name', 'название таблицы', 'text', '".DB::escape("size='50', maxlength='127'")."', '', '', 'nothing', '', 'notempty', 2),
		('0', 'my_admin_catalog', 'field_descr', 'поле \\&quot;анонс\\&quot;', 'text', '".DB::escape("size='50', maxlength='255'")."', '', '', 'nothing', '', 'notempty', 3),
		('0', 'my_admin_catalog', 'drawfunc', 'функция для отображения', 'text', '".DB::escape("size='50', maxlength='255'")."', '', '', 'nothing', '', '', 6),
		('0', 'my_admin_catalog', 'field_img', 'поле с изображением', 'text', '".DB::escape("size='50', maxlength='255'")."', '', '', 'nothing', '', '', 7),
		('0', 'my_admin_catalog', 'folder_img', 'каталог к изображениям', 'text', '".DB::escape("size='50', maxlength='255'")."', '', '', 'nothing', '', '', 7),
		('0', 'my_admin_catalog', 'link_table', 'родительская таблица', 'text', '".DB::escape("size='50', maxlength='255'")."', '', '', 'nothing', '', '', 11),
		('0', 'my_admin_catalog', 'link_title', 'текст ссылки для перехода', 'text', '".DB::escape("size='50', maxlength='255'")."', '', '', 'nothing', '', '', 12),
		('0', 'my_admin_catalog', 'link_parent', 'поле связи родителя', 'text', '".DB::escape("size='50', maxlength='127'")."', '', '', 'nothing', '', '', 13),
		('0', 'my_admin_catalog', 'link_child', 'поле связи потомка', 'text', '".DB::escape("size='50', maxlength='127'")."', '', '', 'nothing', '', '', 14),
		('0', 'my_admin_catalog', 'show', 'учитывать', 'checkbox', 'checked', '1', '', '', '', '', 20),
		('0', 'my_admin_catalog', 'field_div', 'разделитель полей', 'text', '".DB::escape("size='50', maxlength='5'")."', '', '', 'nothing', '', '', 4),
			-- СТАТИЧЕСКИЕ СТРАНИЦЫ
		('1', 'site_pages_variable', 'title', 'Заголовок', 'text', '".DB::escape("size='50', maxlength='511'")."', '', '', 'nothing', '', 'notempty', 2),
		('0', 'site_pages_variable', 'content', 'Содержимое', 'textarea', '".DB::escape("rows='10', cols='37'")."', '', '', '', 'Advanced', '', 6),
		('0', 'site_pages_variable', 'meta_title', 'Заголовок страницы', 'text', '".DB::escape("size='50', maxlength='511'")."', '', '', 'nothing', '', '', 15),
		('0', 'site_pages_variable', 'meta_keywords', 'SEO. Ключевые слова', 'text', '".DB::escape("size='50', maxlength='511'")."', '', '', 'nothing', '', '', 15),
		('0', 'site_pages_variable', 'meta_description', 'SEO. Описание', 'text', '".DB::escape("size='50', maxlength='511'")."', '', '', 'nothing', '', '', 15),
		('0', 'site_pages_variable', 'meta_modify', 'SEO. Дата обновления', 'date', '', '', 'currentdate', 'date', 'calendar', '', 15),
		('1', 'site_pages_constant', 'title', 'Заголовок', 'text', '".DB::escape("size='50', maxlength='511'")."', '', '', 'nothing', '', 'notempty', 2),
		('0', 'site_pages_constant', 'content', 'Содержимое', 'textarea', '".DB::escape("rows='10', cols='37'")."', '', '', '', 'Advanced', '', 6),
		('0', 'site_pages_constant', 'meta_title', 'Заголовок страницы', 'text', '".DB::escape("size='50', maxlength='511'")."', '', '', 'nothing', '', '', 15),
		('0', 'site_pages_constant', 'meta_keywords', 'SEO. Ключевые слова', 'text', '".DB::escape("size='50', maxlength='511'")."', '', '', 'nothing', '', '', 15),
		('0', 'site_pages_constant', 'meta_description', 'SEO. Описание', 'text', '".DB::escape("size='50', maxlength='511'")."', '', '', 'nothing', '', '', 15),
		('0', 'site_pages_constant', 'meta_modify', 'SEO. Дата обновления', 'date', '', '', 'currentdate', 'date', 'calendar', '', 15),
			-- ПАРАМЕТРЫ		
		(1, 'my_site_params', 'title', 'Заголовок', 'text', '".DB::escape("size='50', maxlength='765'")."', '', '', 'nothing', '', '',20),
		(0, 'my_site_params', 'value', 'Значение', 'text', '".DB::escape("size='50', maxlength='765'")."', '', '', 'nothing', '', '',20)
		;"
    ;
    $db->query($query);
  }

  //Проверяем существует ли таблица описания админпанели
  $query = "CREATE TABLE IF NOT EXISTS my_admin_about (id INT NOT NULL AUTO_INCREMENT , about_param VARCHAR( 15 ) NOT NULL , about_value VARCHAR( 100 ) NOT NULL , PRIMARY KEY ( id ))  ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
  $db->query($query);

  //Проверяем существует ли таблица описания модулей
  $query = "CREATE TABLE IF NOT EXISTS my_admin_modules (id INT NOT NULL AUTO_INCREMENT , module_name VARCHAR( 50 ) NOT NULL , module_title VARCHAR( 30 ) NOT NULL , module_descr VARCHAR( 250 ) NOT NULL , PRIMARY KEY ( id )) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
  $db->query($query);

  //Проверяем существует ли таблица описания модулей
  $query = "CREATE TABLE IF NOT EXISTS my_admin_fdc (id INT NOT NULL AUTO_INCREMENT , fdc_module INT( 3 ) NOT NULL , fdc_field INT( 5 ) NOT NULL , fdc_params TEXT NOT NULL , PRIMARY KEY ( id )) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
  $db->query($query);

  //Проверяем существует ли таблица ведения журнала
  $query = "CREATE TABLE IF NOT EXISTS `my_admin_log` (id INT NOT NULL AUTO_INCREMENT , log_table VARCHAR( 50 ) NOT NULL , log_record INT( 11 ) NOT NULL , log_creator INT( 11 ) NOT NULL , log_modifier INT( 11 ) NOT NULL , PRIMARY KEY ( id )) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
  if($db->query($query)){
    $query = 
    "INSERT INTO `my_admin_log` (`id`, `log_table`, `log_record`, `log_creator`, `log_modifier`) VALUES
    (1, 'my_site_siteplugins', 1, 1, 1), (2, 'my_admin_users', 2, 2, 2);"
    ;
    $db->query($query);
  }

    $table_users = TABLE_USERS;

	//Проверяем существует ли таблица пользователей
	$query = "
	CREATE TABLE IF NOT EXISTS `{$table_users}` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`user_who` varchar(150) NOT NULL,
		`user_login` varchar(150) NOT NULL,
		`user_password` varchar(150) NOT NULL,
		`user_role` int(11) NOT NULL,
		`email` varchar(128) NOT NULL,
		`date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`wall` varchar(64) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;";
  $db->query($query);

  //Проверяем существует ли таблица ролей
  $query = "CREATE TABLE my_admin_roles (
                                         id INT NOT NULL AUTO_INCREMENT ,
                                         role_title VARCHAR( 150 ) NOT NULL ,
                                         role_descr VARCHAR( 150 ) NOT NULL ,
                                         role_info INT( 1 ) NOT NULL,
                                         role_tables INT( 1 ) NOT NULL,
                                         role_fields INT( 1 ) NOT NULL,
                                         role_modules INT( 1 ) NOT NULL,
                                         role_about INT( 1 ) NOT NULL,
                                         role_users INT( 1 ) NOT NULL,
                                         role_roles INT( 1 ) NOT NULL,
                                         role_site_setup INT( 1 ) NOT NULL,
                                         role_site_dynamic INT( 1 ) NOT NULL,
                                         role_site_data INT( 1 ) NOT NULL,
  										 role_fman int(1) NOT NULL,
                                         {$table_users} INT( 1 ) NOT NULL,
										 my_admin_catalog INT( 1 ) NOT NULL,
										 my_admin_fman INT( 1 ) NOT NULL,
										 my_site_text_blocks INT( 1 ) NOT NULL,
										 `my_site_params` INT(1) NOT NULL DEFAULT '0',
                                         site_pages_constant INT(1) NOT NULL DEFAULT '0',
                                         site_pages_variable INT(1) NOT NULL DEFAULT '0',

                                         PRIMARY KEY ( id )
                                         ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
                                         /*
                                         role
                                              title - системное название роли
                                              descr - человеческое название роли
                                              info - доступ на редактирования личных данных
                                              tables - доступ к настройке таблиц
                                              fields - доступ к настройке полей
                                              modules - доступ к установке и настройке модулей
                                              about - доступ к настройке админ-панели в целом
                                              users - доступ к данным пользователей
                                              roles - доступ к ролям
                                         */
  $db->query($query);
  
  //Обновление записи root только в том случае если ее нет
  if ($db->select_num_rows("SELECT * FROM my_admin_roles WHERE role_title='root' LIMIT 1;") > 0) {
    //
  } else {
    //Создаем только роль root и пользователя root
    $db->query("TRUNCATE TABLE my_admin_roles");
    $db->query("INSERT INTO my_admin_roles SET
                                                 role_title='root',
                                                 role_descr='Администратор',
                                                 role_info='7',
                                                 role_tables='7',
                                                 role_fields='7',
                                                 role_modules='7',
                                                 role_about='7',
                                                 role_users='7',
                                                 role_roles='7',
                                                 role_site_setup='7',
                                                 role_site_dynamic='7',
                                                 role_site_data='7',
                                                 my_admin_catalog='7',
                                                 my_admin_fman='7',
                                                 my_site_text_blocks='7',
                                                 role_fman='7',
                                                 my_site_params = '7',
                                                 {$table_users} = '7',
                                                 site_pages_constant = '7',
                                                 site_pages_variable = '7'
                                                  ;");
    $admin_roleId = $db->insert_id();
    $db->query("INSERT INTO my_admin_roles SET
                                                 role_title='manager',
                                                 role_descr='Менеджер',
                                                 role_info='0',
                                                 role_tables='0',
                                                 role_fields='0',
                                                 role_modules='0',
                                                 role_about='0',
                                                 role_users='0',
                                                 role_roles='0',
                                                 role_site_setup='0',
                                                 role_site_dynamic='0',
                                                 role_site_data='0',
                                                 my_admin_catalog='0',
                                                 my_admin_fman='0',
                                                 my_site_text_blocks='0',
                                                 role_fman='0',
                                                 my_site_params = '3',
                                                 {$table_users} = '3',
                                                 site_pages_constant = '3',
                                                 site_pages_variable = '7'
                                                  ;");
    $manager_roleId = $db->insert_id();
	 $db->query("INSERT INTO my_admin_roles SET
                                                 role_title='user',
                                                 role_descr='Обычный пользователь',
                                                 role_info='0',
                                                 role_tables='0',
                                                 role_fields='0',
                                                 role_modules='0',
                                                 role_about='0',
                                                 role_users='0',
                                                 role_roles='0',
                                                 role_site_setup='0',
                                                 role_site_dynamic='0',
                                                 role_site_data='0',
                                                 my_admin_catalog='0',
                                                 my_admin_fman='0',
                                                 my_site_text_blocks='0',
                                                 role_fman='0',
                                                 {$table_users}='3',
                                                  site_pages_constant = '0',
                                                 site_pages_variable = '0'
                                                 ;"
     );
    $db->query("TRUNCATE TABLE {$table_users}");
    $db->query("INSERT INTO {$table_users} SET user_who='Администратор', user_login='".sha1('admin')."', user_password='".sha1('monline')."', user_role='".$admin_roleId."';");
    $db->query("INSERT INTO {$table_users} SET user_who='Менеджер', user_login='".sha1('manager')."', user_password='".sha1('manager')."', user_role='".$manager_roleId."';");
  }
  
	#### Вася. //Таблица описания структуры каталов
	$query = "
	CREATE TABLE IF NOT EXISTS `my_admin_catalog` (
		`id` int(10) NOT NULL AUTO_INCREMENT,
		`table_name` varchar(127) NOT NULL COMMENT 'название таблицы',
		`field_descr` varchar(255) NOT NULL COMMENT 'поле \"анонс\" %не личное',
		`field_div` varchar(5) NOT NULL COMMENT 'разделитель записей',
		`drawfunc` varchar(255) NOT NULL COMMENT 'функция для отображения',
		`field_img` varchar(255) NOT NULL COMMENT 'поле с изображением',
		`folder_img` varchar(255) NOT NULL COMMENT 'каталог к изображениям',
		`link_table` varchar(255) NOT NULL COMMENT 'родительская таблица',
		`link_title` varchar(255) NOT NULL COMMENT 'текст ссылки для перехода',
		`link_parent` varchar(127) NOT NULL COMMENT 'поле связи родителя',
		`link_child` varchar(127) NOT NULL COMMENT 'поле связи потомка',
		`show` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'учитывать',
		PRIMARY KEY (`id`),
		KEY `select_table` (`table_name`,`show`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='информация о структуре каталога в админке';";
	$db->query($query);
  
  ////////////////////////////////////////////////////////////
  //создать таблицы сайта
  ///////////////////////////////////////////////////////////
  
  $query =
	"CREATE TABLE IF NOT EXISTS `my_admin_fman` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `path` varchar(200) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;";
  if ($db->query($query)) {
  	$query = 
	"INSERT INTO `my_admin_fman` (`id`, `path`) VALUES
	(1, 'files/'),
	(2, 'pages/templates/'),
	(3, 'addons/ckeditor/config.js');";
	$db->query($query);
  }
  
  $query = 
  "CREATE TABLE IF NOT EXISTS `my_site_emails` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(250) NOT NULL,
      `category` int(11) NOT NULL COMMENT 'категория - если необходимо разбить пользователей на группы',
      `user_id` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;"
  ;
  $db->query($query);
  
  $query = 
  "CREATE TABLE IF NOT EXISTS `my_site_email_data` (
      `id` int(10) NOT NULL AUTO_INCREMENT,
      `item_key` varchar(150) NOT NULL,
      `theme` varchar(100) NOT NULL,
      `message` text NOT NULL,
      `reply_to` varchar(250) NOT NULL,
      `isHTML` mediumint(1) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `item_key` (`item_key`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='данные для рассылок писем' AUTO_INCREMENT=2 ;"
  ;
  $db->query($query);
  
  $query = 
  "CREATE TABLE IF NOT EXISTS `my_site_pages` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `mask` varchar(300) NOT NULL,
      `controller` varchar(100) NOT NULL,
      `action` varchar(100) NOT NULL,
      `alias` varchar(100) NOT NULL,
      `html_title` varchar(70) NOT NULL,
      `html_keywords` varchar(150) NOT NULL,
      `html_description` varchar(200) NOT NULL,
      `page_blocks` varchar(500) NOT NULL,
      `plugins` varchar(500) NOT NULL,
      `short_title` varchar(30) NOT NULL,
      `parent_alias` varchar(100) NOT NULL,
		`block_get_parameters` tinyint(1) default NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `map` (`mask`),
      KEY `alias` (`alias`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='страницы сайта' AUTO_INCREMENT=2 ;"
  ;
  if($db->query($query)){
    $query = 
    "INSERT INTO `my_site_pages` (`mask`, `controller`, `action`, `alias`, `html_title`, `html_keywords`, `html_description`, `page_blocks`, `plugins`, `short_title`, `parent_alias`) VALUES
    ('', 'index', '', 'index', 'framework', 'framework', 'framework', '', '', 'Главная', ''),
    ('login/', 'login', '', 'login', 'framework', 'framework', 'вход пользователя', '', '', 'Вход', 'index'),
    ('registration/', 'registration', '', 'registration', 'framework', 'framework', 'регистрация пользователя', '', '', 'Регистрация', 'index'),
    ('reactivate/', 'reactivate', '', 'reactivate', 'framework', 'framework', 'повторн. отправки активац. кода', '', '', 'Активация', 'index'),
    ('pages/:page/', 'pages', '', 'pages', 'Страницы сайта', 'Страницы сайта', 'Страницы сайта', '', '', 'Страницы сайта', 'index'),
    ('services/:page/', 'pages', '', 'services', 'Услуги', 'Услуги', 'Услуги', '', '', 'Услуги', 'index'),
    ('about/', 'pages', '', 'about', 'О нас', 'О нас', 'О нас', '', '', 'О нас', 'index');"
    ;
    $db->query($query);
  }
  
  $query = 
  "CREATE TABLE IF NOT EXISTS `my_site_pages_dynamic_types` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `descr` varchar(100) NOT NULL,
      `plugin_name` varchar(150) NOT NULL,
      `mask_parameters` varchar(500) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 "
  ;
  if($db->query($query)){
    $query = 
    "INSERT INTO `my_site_pages_dynamic_types` (`id`, `descr`, `plugin_name`, `mask_parameters`) VALUES
    (2, 'Текстовая страница', 'userpage1', ':pageName/')"
    ;
    $db->query($query);
  }
  
  $query = 
  "CREATE TABLE IF NOT EXISTS `my_site_plugin_schemes` (
      `item_key` varchar(50) NOT NULL,
      `descr` varchar(100) NOT NULL,
      `plugin_name` varchar(100) NOT NULL,
      `config_data` text NOT NULL,
      PRIMARY KEY (`item_key`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
  ;
  if(@$db->query($query)){
    $query = 
    "INSERT INTO `my_site_plugin_schemes` (`item_key`, `descr`, `plugin_name`, `config_data`) VALUES
    ('userpage1', 'Динамические страницы - по умолчанию', 'userpage', 'a:2:{s:10:\"table_name\";s:20:\"my_plg_dynamic_pages\";s:10:\"field_name\";s:6:\"field1\";}'),
    ('userdefault1', 'Пользователь по умолчанию', 'userdefault', ''),
    ('userextend', 'Пользователь (расширенная версия)', 'userextend', ''),
    ('geolocation', 'Определение местоположения', 'geolocation', '')"
    ;
    $db->query($query);
  }
  
  $query = 
  "CREATE TABLE IF NOT EXISTS `my_site_siteplugins` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `scheme_id` varchar(100) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 "
  ;
  $db->query($query);
  
  $query = 
  "CREATE TABLE IF NOT EXISTS `my_site_text_blocks` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `item_key` varchar(50) NOT NULL,
      `title` varchar(100) NOT NULL,
      `html` text NOT NULL,
      PRIMARY KEY (`id`),
      KEY `item_key` (`item_key`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 "
  ;
  $db->query($query);
  
  $query =	// СТАТИЧЕСКИЕ СТРАНИЦЫ САЙТА
  "
	CREATE TABLE IF NOT EXISTS `site_pages_constant` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `title` varchar(511) NOT NULL DEFAULT '',
	  `content` text NOT NULL,
	  `alias` varchar(127) NOT NULL,
	  `meta_title` varchar(511) NOT NULL DEFAULT '' COMMENT 'заголовок страницы',
	  `meta_keywords` varchar(511) NOT NULL DEFAULT '',
	  `meta_description` varchar(511) NOT NULL DEFAULT '',
	  `meta_modify` datetime NOT NULL COMMENT 'дата модификации',
	  PRIMARY KEY (`id`),
	  KEY `alias` (`alias`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='страницы сайта со статичным содержимым';";
	$db->query($query);
	
  $query =	// ДОБАВЛЯЕМЫЕ СТАТИЧЕСКИЕ СТРАНИЦЫ САЙТА
  "
	CREATE TABLE IF NOT EXISTS `site_pages_variable` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `title` varchar(511) NOT NULL DEFAULT '',
	  `content` text NOT NULL,
	  `alias` varchar(127) NOT NULL,
	  `meta_title` varchar(511) NOT NULL DEFAULT '' COMMENT 'заголовок страницы',
	  `meta_keywords` varchar(511) NOT NULL DEFAULT '',
	  `meta_description` varchar(511) NOT NULL DEFAULT '',
	  `meta_modify` datetime NOT NULL COMMENT 'дата модификации',
	  PRIMARY KEY (`id`),
	  KEY `alias` (`alias`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='добавляемые страницы сайта со статичным содержимым';";
	$db->query($query);
	
	
	/*================== ТАБЛИЦА КОНТАКТОВ =================*/
	$query ="
	CREATE TABLE IF NOT EXISTS `contacts` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `email_info` varchar(255) NOT NULL COMMENT 'для информир.',
	  `email_admin` varchar(255) NOT NULL COMMENT 'для преупрежд.',
	  `phone` varchar(511) NOT NULL COMMENT 'телефоны',
	  `address` text NOT NULL COMMENT 'адрес',
	  `weight` smallint(6) NOT NULL DEFAULT '20' COMMENT 'вес',
	  `allow_show` enum('1','0') NOT NULL DEFAULT '1' COMMENT 'отображать',
	PRIMARY KEY (`id`),
	KEY `weight` (`weight`),
	KEY `allow_show` (`allow_show`)
	) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Контакты';";
	$db->query($query);
	$query ="
	INSERT INTO `contacts` (`id`, `email_info`, `email_admin`, `phone`, `address`, `weight`, `allow_show`) VALUES
	(1, 'info@localhost', 'test@localhost', '', '', 1, '1');";
	$db->query($query);  
	
/*================== ТАБЛИЦА ОТПРАВЛЕННЫХ E-MAIL =================*/
	$query ="
	CREATE TABLE IF NOT EXISTS `log_send_email` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `name` varchar(255) NOT NULL,
	  `email_to` varchar(128) NOT NULL,
	  `email_from` varchar(128) NOT NULL,
	  `head` varchar(255) NOT NULL,
	  `body` text NOT NULL,
	  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='отправленные e-mail`ы';";
	$db->query($query);
	
    
/*================== ТАБЛИЦА ШАБЛОНОВ ПИСЕМ =================*/
	$query ="
	CREATE TABLE IF NOT EXISTS `tpl_emails` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `key` varchar(63) NOT NULL,
	  `title` varchar(127) NOT NULL,
	  `head` varchar(255) NOT NULL,
	  `content` text NOT NULL,
	  `description` text NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `key` (`key`)
	) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='Данные. Шаблоны писем';";
	$db->query($query);
	$query ="
	INSERT INTO `tpl_emails` (`id`, `key`, `title`, `head`, `content`) VALUES
		(1, 'registr_user', 'Регистрация пользователя (пользов.)', 'Регистрация на сайте SITNAME', '<h2 style=\\\"font-style:italic;\\\">Благодарим Вас за регистрацию на нашем сайте.</h2>\r\n\r\n<div style=\\\"background:#eee;border:1px solid #ccc;padding:5px 10px;\\\">Регистрационные данные: Логин - &#39;{email}&#39;<br />\r\nПароль - &#39;{password}&#39;</div>\r\n\r\n<p>Перейдите по этой ссылке для активации аккаунта: <a href=\\\"{link}\\\" target=\\\"_blank\\\">{link}</a></p>\r\n'),
		(2, 'aktcode_resend', 'Повторный запрос кода активации', 'SITNAME. Повторный запрос кода активации', '<p><br />\r\nВами была повторно запрошена ссылка активации аккаунта на сайте <span style=\\\"color:#B22222;\\\"><em><strong>SITNAME</strong></em></span>.<br />\r\n<br />\r\nСсылка активации - <a href=\\\"{link}\\\" target=\\\"_blank\\\">{link}</a></p>\r\n'),
		(3, 'registr_manag', 'Регистрация пользователя (менедж.)', 'Регистрация на сайте SITNAME', '<h2 style=\\\"font-style:italic;\\\">Благодарим Вас за регистрацию на нашем сайте.</h2>\r\n\r\n<div style=\\\"background:#eee;border:1px solid #ccc;padding:5px 10px;\\\">Регистрационные данные: Логин - &#39;{email}&#39;<br />\r\nПароль - &#39;{password}&#39;</div>\r\n\r\n<p>Перейдите по этой ссылке для активации аккаунта: <a href=\\\"{link}\\\" target=\\\"_blank\\\">{link}</a></p>\r\n');";
	$db->query($query);

/*================== ТАБЛИЦА ПАРАМЕТРОВ =================*/
	$query =" 
	CREATE TABLE `my_site_params` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`key` VARCHAR(63) NOT NULL,
		`title` TINYTEXT NOT NULL,
		`value` TINYTEXT NOT NULL,
		PRIMARY KEY (`id`),
		INDEX `key` (`key`)
	)
	COMMENT='значения настроек для сайта'
	COLLATE='utf8_general_ci'
	ENGINE=MyISAM;";
	$db->query($query);
  
  $query ="
  INSERT INTO `my_site_params` (`key`, `title`, `value`) VALUES ('email_admin', 'E-Mail менеджера', 'manager@localhost');";
	$db->query($query);	
	
	
 /*================== ТАБЛИЦА КЛЮЧЕЙ ДЛЯ АВТОРИЗАЦИИ БЕЗ ПАРОЛЯ =================*/
	$query =" 
	CREATE TABLE `my_admin_tokens` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) UNSIGNED NOT NULL COMMENT 'пользователь [my_admin_users]',
		`token` VARCHAR(64) NOT NULL COMMENT 'ключ',
		`date_losing` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'дата окончания действия',
		PRIMARY KEY (`id`),
		UNIQUE INDEX `user_id` (`user_id`),
		INDEX `date_given` (`date_losing`),
		INDEX `token` (`token`)
	)
	COMMENT='ключи для авторизации без пароля'
	COLLATE='utf8_general_ci'
	ENGINE=MyISAM
	AUTO_INCREMENT=2;";
	$db->query($query);
	
	
	
/*========== ОГРАНИЧЕНИЯ КОЛ-ВА ПОПЫТОК ВВОДА ПАРОЛЯ =========================*/
	$query ="
	CREATE TABLE IF NOT EXISTS `ip_locked` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`user_ip` int(10) unsigned NOT NULL COMMENT 'IP-адрес',
		`date_locked` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'время блокировки',
		PRIMARY KEY (`id`),
		KEY `user_ip` (`user_ip`),
		KEY `date_locked` (`date_locked`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='заблокированные IP-адреса';";
	$db->query($query);

	$query ="
	CREATE TABLE IF NOT EXISTS `user_login_trying` (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`user_ip` int(10) unsigned NOT NULL COMMENT 'IP-адрес',
		`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`status` enum('succes','failure','locked') NOT NULL DEFAULT 'failure' COMMENT 'состояние успешности авторизации',
		`user_id` int(10) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`),
		KEY `user_ip` (`user_ip`),
		KEY `time` (`time`),
		KEY `status` (`status`)
	) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COMMENT='попытки входа пользователей';";
	$db->query($query);

	
	
	
	#### так нужно делать
	//error_reporting($CUR_ERROR_REP);

  header('Location: index.php');
?>
	<a href="/cabinet/">[ перейти в кабинет ]</a>
	
	
	
	
