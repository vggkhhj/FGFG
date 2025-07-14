<?php
//--------------------------- КАТАЛОГИЗАТОР -------------------------------

	// функция вывода записей таблицы в виде каталога
	function printRecords_Catalog($RA, $catSData) {
		global $db;

		$table_name = $catSData['table_name']; // текущая таблица

		// получим данные каталогизатора
		$query="
			SELECT `link_title`, `link_table`, `link_parent`, `link_child`, `table_name`
				FROM `my_admin_catalog`
				WHERE `link_table`='$table_name' AND `show`='1'";

		$subCatData=$db->select_array($query);
		// установим настройки каталогизатора
		$struct[$table_name]=$catSData;
		if(!empty($subCatData)){ // если есть дочерние таблицы
			foreach($subCatData as $tmp_rec){
				$tmp_rec['link_table']=$tmp_rec['table_name'];
				$struct[$table_name]['subcats'][]=$tmp_rec;
			}
		}else{
			$struct[$table_name]['subcats'] = array();
		}
		//ядро каталогизатора
		$settings = $struct[$table_name];
		$settings['drawfunc'] = (!empty($settings['drawfunc'])) ? $settings['drawfunc'] : "catRec_common";


		//--------- функция формирует запрос на выборку записей
		$query = cat_getSelectQuery($catSData);
		//--------- пейджер ---------
		$page_number = !empty($_GET['pageNbr']) ? $_GET['pageNbr'] : 1;
		$on_page = !empty($_SESSION['setRecordsPerPage_'.$table_name]) ? $_SESSION['setRecordsPerPage_'.$table_name] : 10;
		$start_record = ($page_number - 1) * $on_page;
		$limit = " LIMIT {$start_record}, {$on_page}";

		$recsArray=$db->select_array($query.$limit);

		$cont = '';
		if (count($recsArray) > 0){
			$catRA = array();
			$catRA['settings'] = $settings;
			foreach ($recsArray as $tmp){
				$links_sub = array();
				if (!empty($settings['subcats'])) {
					foreach ($settings['subcats'] as $key=>$subcat) {
						$links_sub[$key]['default'] = !empty($subcat['default']) ? $subcat['default'] : "";
						$links_sub[$key]['title'] = $subcat['link_title'];
						$links_sub[$key]['table'] = $subcat['link_table'];
						$links_sub[$key]['href'] = HREF_DOMAIN."cabinet/view_table.php?tableName=".$subcat['link_table']."&table_parent=".$table_name."&".$subcat['link_child']."=".$tmp[$subcat['link_parent']];
						//$subcat['link_child']."=".$tmp[$subcat['link_parent']];
					}
				}
				$link_parent = "";
				if(!empty($_GET['pageNbr'])){
					$link_parent .= '&pageNbr='.$_GET['pageNbr'];
				}
				if(!empty($settings['link_table'])){
					$link_parent .= "&table_parent=".$settings['link_table'];
					if(!empty($_GET[$settings['link_child']])){
						$link_parent .= "&".$settings['link_child']."=".$_GET[$settings['link_child']];
					}
				}

				$tmp['links_sub'] = $links_sub;
				$tmp['links_edit'] = HREF_DOMAIN."cabinet/add_to_table.php?tableName=".$table_name."&recordId=".$tmp['id'].$link_parent;
				if(roleCheckDelete($table_name, $tmp['id']))
					$tmp['links_delete'] = HREF_DOMAIN."cabinet/view_table.php?tableName=".$table_name."&recordId=".$tmp['id']."&del=true".$link_parent;
				
				$catRA['records'][] = $tmp;
			}
			$cont .= call_user_func($settings['drawfunc'], $catRA, $table_name);
		}else{
			$cont .= "Нет записей";
		}
		return $cont;
	}

	//тема каталогизатора
	function catRec_common($RA, $table_name) {
		global $db;
		
		$cont = '';
		$settings = $RA['settings'];
		$maxcols = 4;
		$curcol = 1;
		
	####	Данные для особого отображение записей		
		switch($table_name){
			case 'DEMO':{
				$statues=ArrayTransform_la_By($db->select_array("SELECT * FROM `order_status`"));
			}break;
		}
		
		if (!empty($settings['field_img'])) { // ВЫВОД С ИЗОБРАЖЕНИЯМИ
			$cont .= "<table id='".$settings['drawfunc']."' class='catRec_table' width='90%' border='0' align='center' cellpadding='0' cellspacing='5'>";
			$cont .= "<tr>";
			foreach ($RA['records'] as $value) {
				$href_subcats = "";
				$href_default = $value['links_edit'];
				if (!empty($value['links_sub'])) {
					foreach ($value['links_sub'] as $sublink) {
						$href_subcats .= "<a href='".$sublink['href']."'>".$sublink['title']."</a><br>";
						if ($sublink['default']) $href_default = $sublink['href'];
					}
				}			
				$href_edit = "<a href='".$value['links_edit']."'>[&nbsp;изменить&nbsp;]</a>&nbsp";
				$href_delete = "<a onclick=\"return window.confirm('Удалить запись?');\" href='".$value['links_delete']."' class='fn_del'>[&nbsp;удалить&nbsp;]</a>";
				
				$folder_img = (!empty($settings['field_img'])) ? $settings['folder_img'] : HREF_FILES_USER;
				if ($value[$settings['field_img']])
					$href_default = "<img height='140' width='140' border='0' src='".$folder_img.$value[$settings['field_img']]."'/><br>";
				else
					$href_default = "<img height='140' border='0' src='".HREF_IMG."no_photo.jpg' border='0'/><br>";
				if (!empty($settings['field_descr'])) {
					// "анонс" записи
					if (!empty($tmp_descr)){
			####	Особое отображение записей			
						switch($table_name){
							default:{
								$first = true;
								foreach ($tmp_descr as $rec) {
									$rec=trim($rec);
									if (!$first) $href_default .= $settings['field_div'];
									$href_default .= stripslashes($value[$rec]);
									$first = false;
								}
							}break;
						}
					}
					else {
						$href_default .= stripslashes($value[$settings['field_descr']])."<br>";
					}
					$href_default .= "<br>";
				}	
			
				//#### настоим ссылки + отображение
				switch($table_name){
					case 'DEMO': 
						$href_setAdd= "<a href='adds.php?add=test&param=". $value['id']."[&nbsp;аддонс&nbsp;]</a><br>";
						break;
				}
				
				$cont .= "<td valign='top' align='center'>";
				$cont .= $href_default.$href_edit.$href_delete."<br>".$href_subcats.$href_setAdd;
				$cont .= "<div style='height: 20px;'></div>";
				$cont .= "</td>";
				if ($curcol < $maxcols)
					$curcol++;
				else {
					$cont .= "</tr><tr>";
					$curcol = 1;
				}
			}
			$cont .= "</tr>";
			$cont .= "</table>";
			
		}else { // ВЫВОД ЗАПИСЕЙ СТРОКОЙ
			$cont .= "<table id='".$settings['drawfunc']."' class='catRec_table' width='90%' border='0' align='center' cellpadding='0' cellspacing='5'>";
			foreach ($RA['records'] as $value) {
				$href_subcats = "";
				$href_default = $value['links_edit'];
				if (!empty($value['links_sub'])) {
					foreach ($value['links_sub'] as $sublink) {
						$href_subcats .= "<a href='".$sublink['href']."'>".$sublink['title']."</a> ";
						if ($sublink['default']) $href_default = $sublink['href'];
					}
				}

				if (!empty($value['links_edit']))
					$href_edit = "<a href='".$value['links_edit']."'>[&nbsp;изменить&nbsp;]</a>&nbsp";

				if (!empty($value['links_delete']))
					$href_delete = "<a onclick=\"return window.confirm('Удалить запись?');\" href='".$value['links_delete']."' class='fn_del'>[&nbsp;удалить&nbsp;]</a>";
				else $href_delete = '';

				if ($_GET['tableName'] != 'goods_categories') {
					$href_default = "<a href='".$href_default."'>";
				}
				else $href_default = "<a>";
					// "анонс" записи
				if (!empty($settings['field_descr'])) {
					$tmp_descr=explode(',',$settings['field_descr']);
					if (!empty($tmp_descr)){
			####	Особое отображение записей			
						switch($table_name){
							default:{
								$first = true;
								foreach ($tmp_descr as $rec) {
									$rec=trim($rec);
									if (!$first) $href_default .= $settings['field_div'];
									$href_default .= stripslashes($value[$rec]);
									$first = false;
								}
							}break;
						}
					}
					else {
						$href_default .= stripslashes($value[$settings['field_descr']]);
					}
				}
				
				//#### настоим ссылки + отображение
				switch($table_name){
					// пример выше
				}
				
				$href_default .= "</a>";
				$bgcol = ($curcol) ? " class='fill'" : "";
				$cont .= "<tr".$bgcol.">";
				$cont .= "<td width='50' align='center'>{$value['id']}</td>";
				$cont .= "<td valign='top' align='left'>";
				$cont .= $href_default;
				$cont .= "</td>";
				$cont .= "<td valign='top' align='center' width='200'>";
				$cont .= $href_edit.$href_delete;
				$cont .= "</td>";

				$cont .= "<td valign='top' align='center'>";
				$cont .= $href_subcats;
				$cont .= "</td>";
				$cont .= "</tr>";
				$curcol = ($curcol) ? 0 : 1;
			}
			$cont .= "</table>";
		}
		return $cont;
	}

	
	
?>