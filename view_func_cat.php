<?php 

	/* функция возвращает данные таблицы для каталогизатора 
	* или ничего, если данных нет и она отображается как обычно
	*/
	function cat_getInfoOfTable(){
		global $db;
		$tableName = DB::escape($_GET['tableName']);
			// получить данные о таблице
		$query="SELECT * FROM `my_admin_catalog` WHERE `table_name`='{$tableName}' AND `show`='1'";

		$catSData = $db->select_array_row($query);
		if(!empty($catSData)){
			return $catSData;
		}else{
			return null;
		}
	}
	
	// Вернуть ссылку "ВЕРНУТЬСЯ" при добавлении/редактировании записи
	function u_print_Eback($printPageNumber = true){
		global $db;
		$table_name = DB::escape($_GET['tableName']);
		$get = array();
			// Экранировать полученные данные
		foreach($_GET as $key=>$value){
			$get[DB::escape($key)]=DB::escape($value);
		}

			// изначальные занные: простая таблица - отображается как обычно
		$cont = "view_table.php?tableName=".$table_name;
			// прочитать данные о текущей таблице

		$query="SELECT * FROM `my_admin_catalog` WHERE `table_name`='".$_GET['tableName']."' AND `show`='1'";
		$catSData=$db->select_array_row($query);
			// если это дочерняя таблица
		if (!empty($catSData) && !empty($catSData['link_table'])){ // это дочерняя таблица
			
			if(!empty($get[$catSData['link_child']])){ // УДАЛЯЕМ ЗАПИСЬ
					// мы и получили ссылку
				$parentID=(int)$get[$catSData['link_child']];
			}elseif(!empty($_GET['recordId'])){	// РЕДАКТИРУЕМ ЗАПИСЬ
					// прочитать поле родителя
				$query="SELECT `".$catSData['link_child']."` FROM `".$catSData['table_name']."` WHERE `id` = ".((int) $_GET['recordId']);
				$parentID=$db->select_result($query);
			}else{ // ДОБАВЛЯЕМ ЗАПИСЬ
				if(!empty($_GET[$catSData['link_child']]))
					$parentID=$_GET[$catSData['link_child']];
			}

				// сформировать ссылку

			if(!empty($parentID)){
				$cont .="&table_parent=".$catSData['link_table'];
				$cont .= "&".$catSData['link_child']."=".$parentID;
			}
		}
		if(!empty($_GET['pageNbr']) && $printPageNumber){
			$cont .= "&pageNbr=".$_GET['pageNbr'];
		}
		return $cont;
	}
	
	// Вернуть массив с данными для формирования ссылок "ВЕРНУТЬСЯ" и "ДОБАВИТЬ" при обзоре каталога
	function u_print_Vback(){
		global $db;
			// Экранировать полученные данные
		$get = array();
		// Экранировать полученные данные
		foreach($_GET as $key=>$value){
			$get[DB::escape($key)]=DB::escape($value);
		}
			// ссылка добавления записи
		$oA_add = Array();
		$oA_add['title'] = "add";
		$oA_add['descr'] = "Добавить запись";
		$oA_add['link'] = "add_to_table.php?tableName=".$_GET['tableName']; 
			// прочитать данные о текущей таблице
		$query="SELECT * FROM `my_admin_catalog` WHERE `table_name`='".$_GET['tableName']."' AND `show`='1'";
		$catSData=$db->select_array_row($query);		
			// если это дочерняя таблица
		if (!empty($catSData) && !empty($catSData['link_table']) && !empty($get[$catSData['link_child']])){
				// ссылка "вернуться"
			$oA_back = Array();
			$oA_back['title'] = "back";
			$oA_back['descr'] = "Вернуться";
			$oA_back['link'] = "view_table.php?tableName=".$catSData['link_table'];
				// прочитать данные о родительской таблице
			$query="SELECT * FROM `my_admin_catalog` WHERE `table_name`='".$catSData['link_table']."' AND `show`='1'";
			$catSData_parn=$db->select_array_row($query);	
				// если родительская таблица сама является дочерней
			if (!empty($catSData_parn) && !empty($catSData_parn['link_title'])){
					// прочитать информацию о записи
				$query="SELECT `".$catSData_parn['link_child']."` FROM `".$catSData_parn['table_name']."` WHERE `id`=".intval($get[$catSData['link_child']])."";
				$parnID=$db->select_result($query);
					// добавить данные к ссылке "вернуться"
				$oA_back['link'] .="&table_parent=".$catSData_parn['table_name']."&".$catSData_parn['link_child']."=".$parnID;			
			}
				// добавить данные к ссылке "добавить"
			if(!empty($get[$catSData['link_child']])){
				$oA_add['link'] .="&".$catSData['link_child']."=".intval($get[$catSData['link_child']]);
			}

		}
			// соберём результаты
		$oA['add']=$oA_add;
		if(!empty($oA_back)){
			$oA['back']=$oA_back;
		}
		return $oA;
	}

	function cat_getSelectQuery($catSData){
		global $db;
		$table_name = $catSData['table_name']; // текущая таблица

		$where = '';

		if (!empty($_GET['table_parent'])) {
			$settings['table_parent'] = $_GET['table_parent'];

			// фильтр записей
			$where = (!empty($_GET[$catSData['link_child']])) ? " WHERE `".$catSData['link_child']."` = '".intval($_GET[$catSData['link_child']])."'" : "";
		}
		$group = (!empty($settings['group'])) ? " GROUP BY `".$settings['group']."`" : "";
		#### Сортировка
		if(!empty($_SESSION[$table_name]['sorting'])){
			$order = " ORDER BY ".$_SESSION[$table_name]['sorting'];
		}else{
			$order = (!empty($settings['order'])) ? " ORDER BY `".$settings['order']."`" : "";
		}
		####
		global $search_filter;
		if(!empty($search_filter)){
			$where = (empty($where)) ? " WHERE ".$search_filter." " : $where." AND (".$search_filter.")";
		}

		//если пользователю доступны только свои записи
		if(roleCheckReadTheirOnly($table_name)){
			$user_records = $db->select_result("SELECT GROUP_CONCAT(log_record) FROM my_admin_log WHERE log_table = '{$table_name}' AND log_creator = '{$_SESSION['user']['id']}'");
			if(empty($user_records)) $user_records = "0";
			if(!empty($where)) $where .= " AND ";
			else $where .= " WHERE ";
			$where .= "`id` IN ({$user_records})";
		}

		$query = "SELECT * FROM {$table_name}".$where.$group.$order;

		return $query;
	}

	function cat_getPages($catSData){
		$pA = array();
		$table_name = $catSData['table_name'];

		$recordsPerPage = !empty($_SESSION['setRecordsPerPage_'.$table_name]) ? $_SESSION['setRecordsPerPage_'.$table_name] : 10;
		$query = cat_getSelectQuery($catSData);
		$page_number = !empty($_GET['pageNbr']) ? $_GET['pageNbr'] : 1;

		$cPager = new ac_pager();
		$cPager->setQuery($query);
		$cPager->setRecordsPerPage($recordsPerPage);
		$cPager->setCurrentPage($page_number);

		if ($cPager->recordsCnt > 0){
			$SCRIPT_NAME = $_SERVER['SCRIPT_NAME'];
			$pA['firstPage'] = $SCRIPT_NAME."?tableName=".$table_name."&pageNbr=1";
			$pA['lastPage'] = $SCRIPT_NAME."?tableName=".$table_name."&pageNbr=".$cPager->pagesCnt;
			$pA['currentPage'] = $cPager->currentPage;
			$pA['pagesCnt'] = $cPager->pagesCnt;
			$pA_temp =  Array();
			if ($cPager->pagesCnt > 1)
				for ($i = 1; $i <= $cPager->pagesCnt; $i++)
					$pA_temp[$i] = $SCRIPT_NAME."?tableName=".$table_name."&pageNbr=".$i;
			$pA['pagesLinks'] = $pA_temp;
		}else{
			$pA['pagesLinks'] = Array();
		}

		return $pA;
	}
?>