<?php
  require_once ('common.php');
  $MA_pageTitle = 'Типы динамических страниц';

  $model = Model::getInstance();
  $result = $model->getDynamicPagesTypes();
  if(!isset($_GET['action'])){
	  $MA_content .= "<table width=95% border=0 cellspacing=1>";
	  $cnt = 0;
	  foreach($result as $fa){
	  	$trClass = ++$cnt%2 ? 'F0F0F0' : 'FFF' ;
	    $MA_content .= 
	      "<tr style='background-color:#" . $trClass . ";'><td><a href='setup_site_dynamic_pages_types.php?action=edit&id=" . $fa['id'] . "'>" . 
	      $fa['descr'] .
	      "</a></td><td><a href='setup_site_dynamic_pages_types.php?action=edit&id=" . $fa['id'] . "'>" . 
	      $fa['plugin_name'] .
	      "</a></td><td><a href='setup_site_dynamic_pages_types.php?action=del&id=" . $fa['id'] . "'><b>[Удалить]</b></a></td></tr>";
	    
	  }
	  $MA_content .= "</table>";
	  $MA_content .= "<br><a href='setup_site_dynamic_pages_types.php?action=add'><b>[Добавить новый тип]</b></a>";
  }else
    switch($_GET['action']){
    	case 'edit':
    	case 'add':
    		//прочитать плагины
    		$plugins = Array();
    		$pluginList = $model->getPluginShemes();
    		foreach($pluginList as $plEl)
    		  $plugins[] = Array('value'=>$plEl['item_key'], 'option'=>$plEl['item_key']);
    		//страницы
    		$pages = $model->getSitePages();
    		//подгoтовка правил для полей формы
    		$formData = Array(
    		  'form'=>Array(
    		    'method'=>'post',
    		    'action'=>Util::getThisAddressGet()
    		    ),
    		  'controls'=>Array(
    		    'descr'=>Array(
    		      'title'=>'Описание', 'type'=>'string', 'required'=>true, 'max'=>50, 
    		      'control_type'=>'text'
    		      ),
            'plugin_name'=>Array(
              'title'=>'Использовать плагин', 'type'=>'string', 'required'=>true, 'max'=>100, 
              'control_type'=>'select', 'select_options'=>$plugins
    		      ),
            'mask_parameters'=>Array(
              'title'=>'Маска параметров', 'type'=>'string', 'max'=>500, 
              'control_type'=>'text'
              )
    		  )
    		);
    		if($_GET['action'] === 'edit')
    		  $formData['values'] = $model->getDynamicPagesTypes(intval($_GET['id']));
    		$form = new FormProcessor();
    		$form->createForm($formData);
    		if($_GET['action'] === 'edit')
    		  $updateMode = sprintf('`id`="%d"', intval($_GET['id']));
    		else
    		  $updateMode = null;
    		if($form->checkFormSubmit(TABLE_SITE_PAGES_DYNAMIC_TYPES, $updateMode))
    		  header("Location: " . Util::getThisAddressGet());
    		ob_start();
    		$form->printForm();
    		$MA_content .= ob_get_clean();
    		$MA_content .= "<b><a href='" . Util::getThisAddress() . "'>[ Вернуться назад ]</a></b>";
    		ob_end_clean();
    	  break;
      case 'del':
        $model->deleteDynamicPageType(intval($_GET['id']));
        header("Location: " . Util::getThisAddress());
        break;
    }
  
  //Подключаем шаблон
  include $MA_theme;