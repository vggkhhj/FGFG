<?php
function MA_afterSave_user ($rId, $tableName, $isNew = false) {
	global $db;
	
	// Записать Alias (НЕ УНИКАЛЬНЫЙ)
	switch($tableName){
		case 'news':
		case 'site_pages_variable':
		case 'prods_categs':
		case 'prods':
		case 'complects_categs':
		case 'complects':
			$record  = $db->select_array_row("SELECT * FROM `".$tableName."` WHERE `id`=".$rId);
			if(count($record)>0 && ($isNew || $record['alias']=='') ){
				$tmpAlias = makeAlias($record['title'],100);
				$db->query("UPDATE `".$tableName."` SET `alias`='".$tmpAlias."' WHERE `id`=".$rId);
					// проверить алиас на уникальность
				$tmp  = $db->select_array_row("SELECT * FROM `".$tableName."` WHERE `alias` LIKE '".$tmpAlias."'");		
				if($db->num_rows()>1){
					$db->query("UPDATE `".$tableName."` SET `alias`='".$tmpAlias.($rId % 100).date('s',time())."' WHERE `id`=".$rId);
				}
			}
			break;
	}
	
	switch($tableName){
		case 'prods':{
			$record  = $db->select_array_row("SELECT * FROM `".$tableName."` WHERE `id`=".$rId);
			if (!empty($record)){
				
			}
		}break;
	}
	
	
}


function MA_afterDelete_user($rId, $tableName,$deletedArray) {
	
	
	#### перенаправление на нужную страницу
	header('Location:'.u_print_Eback());exit;
}


/*=======================================================================*/

// Пользовательская функция вывода записей таблицы (страница)
function MA_printRecords_user($RA) {
	$cont = '';
		// получить данные таблицы для каталогизатора 
	$catSData=cat_getInfoOfTable();
	if(!empty($catSData)){ // КАТАЛОГИЗАТОР
			// подключить код каталогизатора
		require_once 'view_catalog.php';
		$cont .= printRecords_Catalog($RA,$catSData);
	}else{ // СТАНДАТРНЫЙ ВЫВОД
		if (count($RA) > 0){ 
			$cont .= "<table width='90%' border='0' align='center' cellpadding='2' cellspacing='2'>";
			foreach ($RA as $RAKey => $RAValue) {
				$cont .= "<tr align='left' valign='top'";
				if ($RAValue['zebra']) $cont .= " class='fill'";
				$cont .= ">";
				if(!empty($RAValue['id'])){
					$cont .= "<td width='30' align='center' >{$RAValue['id']}</td>";
				}
				$cont .= "<td><a href='".$RAValue['links']['edit']."' class='links'>".$RAValue['descr']."</a></td>";
				$cont .= "<td width='200' align='center'>";
				$cont .= "<a href='".$RAValue['links']['edit']."' class='links'>[ изменить ]</a>";
				if (!empty($RAValue['links']['delete']))
					$cont .= "<a href='".$RAValue['links']['delete']."' class='links' onclick=\"return window.confirm('Удалить запись?');\">[ Удалить ]</a>";
				$cont .= "</td>";
				$cont .= "</tr>";
			}
			$cont .= "</table>";
		} 
		else $cont .= "Нет записей";
	}
	return $cont;
}

function MA_print_VIEW_TABLE_user($_rA, $_pA, $_oA, $_sA, $_tableName){
	global $db;
	$cont='';
	#### Добавить сортировку по весу, если поле существует
	$row = $db->select_array_row("SELECT * FROM {$_tableName} LIMIT 1");
	if(isset($row['weight'])){
		$_rA[0]['descrArray']['weight']='';
	}
	// Вызов функции для отображения вариантов сортировки, если есть что сортировать и есть поля, по которым сортировать
	if (count($_rA) > 0){
		if (count($_rA[0]['descrArray']) > 0){
			$cont .= MA_printSorting($_rA[0]['descrArray'], $_tableName);


		}
		#### Особое отображение записей
		if(!empty($_rA[0]['table_name']) && $_rA[0]['table_name']=='site_pages_variable'){
			foreach($_rA as $j=>$hand){
				$thAlias=($db->select_result("SElECT `alias` FROM `site_pages_variable` WHERE `id`='".$hand['id']."'"));
				$_rA[$j]['descr'].=', '.HREF_DOMAIN.'pages/'.$thAlias.'/';
			}
		}
	}

	$cont .= MA_printRecords($_rA);
	$cont .= MA_printPages($_pA, $_tableName);
	$cont .= MA_printOperations($_oA);
	$cont .= MA_printSearch($_sA);
  return $cont;
}

function MA_printSorting_user($dA, $table_name) {
//	#### не показывать строку сортировки при поиске
//	global $search_filter;
//	if(!empty($search_filter)) return;
	
	$cont = '';
	$cont .= "<table width='90%' border='0' align='center' cellpadding='2' cellspacing='2' class='printSorting'><tr><td>";
	$cont .= "Сортировать записи: ";
	if (strpos($_SESSION[$table_name]['sorting'], ' DESC'))
		$sortDirection = "&#8593;";
	else $sortDirection = "&#8595;";
	####$cont .= "<a href='view_table.php?tableName=".$table_name."&sortBy=id'>id ";
	$cont .= "<a href='".u_print_Eback()."&sortBy=id'>id ";
	if (preg_match("/^id(([[:space:]])|($))/i", $_SESSION[$table_name]['sorting']))
		$cont .= $sortDirection;
	$cont .= "</a>";
	foreach($dA as $dA_key => $dA_value) {
		####$cont .= " :: <a href='view_table.php?tableName=".$table_name."&sortBy=".$dA_key."'>".fieldDescrByFieldName($dA_key)." ";
		$cont .= " :: <a href='".u_print_Eback()."&sortBy=".$dA_key."'>".fieldDescrByFieldName($dA_key)." ";
		if (preg_match("/^".$dA_key."(([[:space:]])|($))/i", $_SESSION[$table_name]['sorting']))
			$cont .= $sortDirection;
		$cont .= "</a>";
	}
	$cont .= "</td></tr></table>";
	
	return $cont;
}

function MA_printPages_user($PA, $tableName) {

	// получить данные таблицы для каталогизатора
	$catSData=cat_getInfoOfTable();
	$parent = '';

	if(!empty($catSData['link_table']) && !empty($_GET[$catSData['link_child']])){
		$parent .= "&table_parent=".$catSData['link_table'];
		if(!empty($_GET[$catSData['link_child']])){
			$parent .= "&".$catSData['link_child']."=".$_GET[$catSData['link_child']];
		}
		$PA = cat_getPages($catSData);
	}

	$cont = '';
	$cont .= "<table width='90%' border='0' align='center' cellpadding='2' cellspacing='2' class='printPages'>";
	$cont .= "<tr><td>Страницы:</td></tr><tr><td>";

	if (count($PA['pagesLinks']) > 0) {
		foreach ($PA['pagesLinks'] as $PAKey => $PAValue) {
			if ($PAKey != $PA['currentPage'])
				$cont .= "<a href='".$PAValue.$parent."' class='links'>".$PAKey."</a>&nbsp;&nbsp; ";
			else
				$cont .= "<b>".$PAKey."</b>&nbsp;&nbsp; ";
		}
	} else $cont .= "<b>1</b>";
	$recordsPerPage10 = "<a href='view_table.php?tableName=".$tableName."&setRecordsPerPage=10".$parent."'>10</a>";
	$recordsPerPage20 = "<a href='view_table.php?tableName=".$tableName."&setRecordsPerPage=20".$parent."'>20</a>";
	$recordsPerPage50 = "<a href='view_table.php?tableName=".$tableName."&setRecordsPerPage=50".$parent."'>50</a>";
	$recordsPerPage100 = "<a href='view_table.php?tableName=".$tableName."&setRecordsPerPage=100".$parent."'>100</a>";
	$setRecordsPerPage = $_SESSION['setRecordsPerPage_'.$tableName];
	switch ($setRecordsPerPage) {
		default:
		case '10': $recordsPerPage10 = "<b>10</b>"; break;
		case '20': $recordsPerPage20 = "<b>20</b>"; break;
		case '50': $recordsPerPage50 = "<b>50</b>"; break;
		case '100': $recordsPerPage100 = "<b>100</b>"; break;
	}
	$cont .= "<tr><td>По ".$recordsPerPage10." ".$recordsPerPage20." ".$recordsPerPage50." ".$recordsPerPage100." на странице</td></tr>";
	$cont .= "</table>";
	return $cont;
}

/** Возвращает уникальный alias для таблицы #### Вася. 3-03-22/new wrap global
* $table - название таблицы
* $stringIn - строка, из которой формируется alias
* $fieldOut - название поля таблицы, в которое записывается alias
* $oldAlias - готовый, уже сформированный alias (претендент на запись) */
function get_uniqAlias($table,$stringIn,$fieldOut='alias',$oldAlias=''){
	global $db;
	if(empty($oldAlias)){
		$oldAlias=FormProcessor::makeUrlKey($stringIn);
	}
	$matchLine=$db->select_array_row("SELECT `id` FROM `$table` WHERE `$fieldOut` LIKE '$oldAlias';");
	if(!empty($matchLine)){
			// повторим попытку
		$newAlias=FormProcessor::makeUrlKey($oldAlias).(preg_match('/[0-9]$/',$oldAlias)?'':'-').date('s');
		return get_uniqAlias($table,$newAlias,$fieldOut,$newAlias);
	}else{
		return $oldAlias;
	}
}






