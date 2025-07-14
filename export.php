<?php
	if(empty($_GET['tableName'])) exit('Ќе выбрана таблица');
	
	require_once ('common.php');

	$table_name= DB::escape($_GET['tableName']);
	
		//провер€ем права на доступ к это таблице
	if ($_SESSION['user']['roles'][$table_name] == 0 && $_SESSION['user']['roles']['role_title'] != 'root'){
		header('Location: index.php');
		exit('ƒоступ запрещЄн');
	}
  
	if ($_SESSION['user']['roles'][$table_name] >= 1 && $_SESSION['user']['roles'][$table_name] <= 3 && $_SESSION['user']['roles']['role_title'] != 'root') {
    $QS = "SELECT ".$table_name.".* FROM ".$table_name.", my_admin_log WHERE my_admin_log.log_record=".$table_name.".id AND my_admin_log.log_table='".$table_name."' AND my_admin_log.log_creator='".$_SESSION['user']['id']."' ORDER BY id;";
	} else $QS = "SELECT * FROM {$table_name} ORDER BY id;";
	
	$tQ = $db->select_array($QS);
	if(empty($tQ)) exit('Ќеверные данные таблицы');
	
	$ctype = "application/vnd.ms-excel; format=attachment;";
	header("Pragma: public");
	header("Expires: 0");
	header("Accept-Ranges: bytes");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: $ctype");
	header("Content-Disposition: attachment; filename=data.xls");
	header("Content-Transfer-Encoding: binary");
	
	$export = '';
		// добавим верх документа
	$export.= formatExcelHead();
  
	$export .= "<table cellspacing='0' cellpadding='0' border='1'>";
	foreach ($tQ as $tA) {
			// выведем заголовки
		if(!isset($cfirst) && $cfirst=true) $export.=formatTableHead($tA);
	 
      $export .= "<tr>";
      foreach ($tA as $tAK => $tAV) {
        $export .= "<td>".strip_tags($tAV,"<b>")."</td>";
      }
      $export .= "</tr>";
	}
	$export .= "</table>";
		// добавим низ документа
	$export.=formatExcelFooter();

	echo $export;

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/  

/** ‘ормирует строку заголовков таблицы */
function formatTableHead($rowArray){
	global $table_name,$db;
	$query="SELECT `field_name`, `field_descr` FROM `my_admin_fields`  WHERE `field_table`='$table_name'";
	$headDescrs=Util::dbToArray($db->select_array($query),'field_name');
	$OUT='<tr>';
	foreach($rowArray as $name=>$value){
		$OUT.='<td>'.(!empty($headDescrs[$name])?$headDescrs[$name]['field_descr']:$name).'</td>';
	}
	$OUT.='</tr>';
	return $OUT;
}

/** ‘ормирует верх документа */
function formatExcelHead(){
return <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>
	<head>
		<meta http-equiv=Content-Type content="text/html; charset=utf-8">
	</head>
	<body>
EOF;
}

/** ‘ормирует низ документа */
function formatExcelFooter(){
return <<<EOF
	</body>
</html>
EOF;
}
