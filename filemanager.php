<?php
require_once ('common.php');
if ($_SESSION['user']['roles']['role_fman'] != '7')
	header("Location: index.php");
	 
$html = "";
$MA_pageTitle = 'Файловый менеджер';
$html .= '';



// редактор файлов
if (isset($_GET['edit']) && ($path = $_GET['edit'])) {
	// предварительно открываем файл и проверяем, кодировался ли он в UTF-8
	$file = file_get_contents($_SERVER['DOCUMENT_ROOT'].$_GET['edit']);
	$isUTF = mb_detect_encoding($file, 'UTF-8', true);
	
	// если было редактирование
	if ($_POST['edit_submit']) {
		$newContent = $_POST['content'];
		if ($isUTF) $newContent = iconv('cp1251', 'UTF-8', $newContent);
		$newContent = html_entity_decode(stripslashes($newContent), ENT_NOQUOTES, ($isUTF) ? 'UTF-8' : 'cp1251');
		file_put_contents($_SERVER['DOCUMENT_ROOT'].$_GET['edit'], $newContent);
		$file = $newContent;
		$edited = true;
	}
	
	if ($isUTF) $file = iconv('UTF-8', 'cp1251', $file);
	$html .= '<h4>Редактирование '.$path.'</h4>';
	if ($edited) $html .= '<p>Изменения сохранены.</p>';
	$html .= '<form name="fileEditor" method="post"><div><textarea name="content" rows="25" cols="80">'.htmlentities($file, ENT_NOQUOTES, 'cp1251').'</textarea></div>';
	$html .= '<p><input type="submit" name="edit_submit" value="Сохранить" /></p></form>';
	$html .= '<p><a href="filemanager.php"><b>Вернуться</b></a></p>';
}



// загрузчик файлов
elseif (isset($_GET['upload']) && ($path = $_GET['upload'])) {
	// тут при желании можно поместить в <form> и дописать обработчик, чтобы работало без яваскрипта
	$html .= '<h4>Загрузить в '.$path.'</h4>';
	$html .= '<p><input id="file_upload" name="file_upload" type="file" /></p>';
	$html .= '<p><input id="upload_submit" name="upload_submit" value="Загрузить" type="submit" /></p>';
	$html .= '<p><a href="filemanager.php"><b>Вернуться</b></a></p>';
	// дописываем яваскрипты
	ob_start();
	?>
	
	<link type="text/css" rel="stylesheet" href="<?php echo HREF_ADD ?>uploadify/uploadify.css">
	<script type="text/javascript" src="<?php echo HREF_ADD ?>uploadify/swfobject.js"></script>
	<script type="text/javascript" src="<?php echo HREF_ADD ?>uploadify/jquery.uploadify.min.js"></script>
	<script type="text/javascript">
	$(function() {
		$('#file_upload').uploadify({
			'swf'   : '<?php echo HREF_ADD ?>uploadify/uploadify.swf',
			'uploader'     : '<?php echo HREF_ADD ?>uploadify/uploadify.php',
			'checkScript': '<?php echo HREF_ADD ?>uploadify/check.php',
			'cancelImg'  : '<?php echo HREF_ADD ?>uploadify/uploadify-cancel.png',
			'formData'     : {'folder': '<?php echo $path ?>'},
			'multi'      : true,
			'auto'       : false
		});
		$('#upload_submit').click(function () {
			$('#file_upload').uploadify('upload','*');
			return false;
		});
	});
	</script>
	
	<?php
	$html .= ob_get_contents();
	ob_end_clean();
}



// просто дерево
else {
	
	// если запрос на удаление
	if (!empty($_GET['del'])) {
		unlink($_SERVER['DOCUMENT_ROOT'].$_GET['del']);
	}
	
	
	// если запрос на скачивание
	if(!empty($_GET['download'])) {
		$file = $_SERVER['DOCUMENT_ROOT'] . $_GET['download'];
		$fsize=filesize($file);
		Header("HTTP/1.1 200 OK");
		Header("Connection: close");
		if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
			Header('Content-Type: application/force-download');
		else
			Header('Content-Type: application/octet-stream');
		
		Header("Accept-Ranges: bytes");
		Header("Content-Disposition: Attachment; filename=\"" . pathinfo($file, PATHINFO_BASENAME)."\"");
		Header("Content-Length: ".$fsize);
		
		readfile($file);	
	}	
	
	// выбираем настройки файлменеджера
	$settings = array();
	$settingsQ = $db->select_array("SELECT * FROM `my_admin_fman` ORDER BY `path`");
	if (!empty($settingsQ)) {
		foreach ( $settingsQ as $tmp)
			$settings[] = DIR_ROOT.$tmp['path'];
		$fullAccess = false;
	}
	// если не выбраны настройки - даем полный доступ ко всем папкам
	else $fullAccess = true;
	
	$siteroot = scanPath(DIR_ROOT, $fullAccess);
	
	// выводим дерево
	$html .= '<div id="filemanagerTree">';
	$html .= printTree($siteroot);
	$html .= '</div>';
	
	// дописываем яваскрипты
	ob_start();
	?>
	
	<script type="text/javascript">
	$('#filemanagerTree a.delFile').click(function () {
		return window.confirm('Удалить файл ' + $(this).parents('.title').find('.name').text() + ' ?');
	});
	$('#filemanagerTree .title').hover(function () {
		$(this).addClass('hovered');
		$(this).find('.actions:first').show();
	}, function () {
		$(this).removeClass('hovered');
		$(this).find('.actions:first').hide();
	});
	$('#filemanagerTree a.foldLink').toggle(function () {
		$(this).parents('div.row:first').find('div.folderContent:first').hide();
		$(this).parents('div.title:first').addClass('collapsed').removeClass('expanded');
		return false;
	}, function () {
		$(this).parents('div.row:first').find('div.folderContent:first').show();
		$(this).parents('div.title:first').addClass('expanded').removeClass('collapsed');
		return false;
	});
	$('#filemanagerTree a.foldLink').click();
	$('#filemanagerTree .actions').hide();
	</script>
	
	<?php
	$html .= ob_get_contents();
	ob_end_clean();
}



$MA_content .= $html;
include ($MA_theme);