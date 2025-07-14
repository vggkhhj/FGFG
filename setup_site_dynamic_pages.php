<?php
  require_once ('common.php');
  $MA_pageTitle = 'Управление динамическими разделами';

  $model = Model::getInstance();
  $result = $model->getContentPageParams('SiteDynamicPagesList');
  if(!isset($_GET['action'])){
	  $MA_content .= "<table width=95% border=0 cellspacing=1>";
	  $MA_content .= "<tr><th>Заголовок</th><th>Адрес</th><th>Тип</th><th>Родитель</th></tr>";
	  $cnt = 0;
	  foreach($result['rows'] as $fa){
	  	$trClass = ++$cnt%2 ? 'F0F0F0' : 'FFF' ;
	    $MA_content .= 
	      "<tr style='background-color:#" . $trClass . ";'><td><a href='" . Util::getThisAddress() . "?action=edit&id=" . $fa['id'] . "'>" . 
	      $fa['short_title'] .
	      "</a></td><td><a href='" . Util::getThisAddress() . "?action=edit&id=" . $fa['id'] . "'>" . 
	      $fa['mask'] . 
        "</a></td><td><a href='" . Util::getThisAddress() . "?action=edit&id=" . $fa['id'] . "'>" . 
        $fa['descr'] .
	      "</a></td><td><a href='" . Util::getThisAddress() . "?action=edit&id=" . $fa['id'] . "'>" . 
	      $fa['parent_alias'] .
	      "</a></td><td><a href='" . Util::getThisAddress() . "?action=del&id=" . $fa['id'] . "'><b>[Удалить]</b></a></td></tr>";
	    
	  }
	  $MA_content .= "</table>";
	  $MA_content .= "<br><a href='" . Util::getThisAddress() . "?action=add'><b>[Добавить новую]</b></a>";
  }else
    switch($_GET['action']){
    	case 'edit':
    	case 'add':
    		//страницы
    		$pages = $model->getSitePages();
    		$pagesOptions = Array();
    		foreach($pages as $p)
    		  $pagesOptions[] = Array('option'=>$p['short_title'], 'value'=>$p['alias']);
    		$pages = $pagesOptions;
    		//типы страниц
    		$types = $model->getDynamicPagesTypes();
    		$typesOptions = Array();
    		foreach($types as $tp)
    		  $typesOptions[] = Array('option'=>$tp['descr'], 'value'=>$tp['plugin_name']);
    		$types = $typesOptions;
    		//подгoтовка правил для полей формы
    		$formData = Array(
    		  'form'=>Array(
    		    'name'=>'setup_site_dynamic_pages_form',
    		    'id'=>'setup_site_dynamic_pages_form',
    		    'method'=>'post',
    		    'action'=>Util::getThisAddressGet()
    		    ),
    		  'controls'=>Array(
            'short_title'=>Array('title'=>'Заголовок', 'type'=>'string', 'required'=>true, 'max'=>60,
              'control_type'=>'text', 'size'=>50
              ),
            'mask'=>Array('title'=>'Адрес', 'type'=>'string', 'max'=>120, 'required'=>true,
              'control_type'=>'text', 'size'=>50
              ),
            'plugins'=>Array('title'=>'Тип страницы', 'type'=>'string', 'max'=>100, 'required'=>true,
              'control_type'=>'select', 'select_options'=>$types
              ),
            'parent_alias'=>Array('title'=>'Родительская страница', 'type'=>'string', 'max'=>100, 'required'=>true,
    		      'control_type'=>'select', 'select_options'=>$pages
    		      ),
    		    'html_title'=>Array('title'=>'Заголовок HTML', 'type'=>'string', 'max'=>70,
    		      'control_type'=>'text', 'size'=>60
    		      ),
    		    'html_keywords'=>Array('title'=>'Ключевые слова HTML', 'type'=>'string', 'max'=>150,
    		      'control_type'=>'text', 'size'=>60
    		      ),
    		    'html_description'=>Array('title'=>'Описание HTML', 'type'=>'string', 'max'=>200,
    		      'control_type'=>'text', 'size'=>60
    		      ),
    		    )
    		);

        if($_GET['action'] === 'edit'){
          $dynamicPages = $result;
          $dynamicPages = Util::dbToArray($dynamicPages['rows'], 'id');
          $formData['values'] = $dynamicPages[$_GET['id']];
          $alias = $dynamicPages[$_GET['id']]['alias'];
          //$formData['values']['text_block'] = $model->getTextBlock($formData['values']['alias']);
        }
    		$form = new FormProcessor(); 
    		$form->createForm($formData);
        if($fields = $form->checkFormSubmit()){
        	unset($fields['alias']);
        	$textBlock = $fields['text_block'];
        	unset($fields['text_block']);
          //вставка или обновление
          if($_GET['action'] === 'edit'){
            $model->updateSitePages($fields, intval($_GET['id']));
            //$model->setTextBlock($alias, $textBlock);
          }else
          if($_GET['action'] === 'add'){
	          //задать все нужные поля по умолчанию
	          $fields['controller'] = 'Empty';
	          //неявно дописать параметры адреса
	          $typesKey = Util::dbToArray($model->getDynamicPagesTypes(), 'plugin_name');
	          $thisType = $typesKey[$fields['plugins']];
	          $fields['mask'] = Util::glueWithSlash($fields['mask'], $thisType['mask_parameters']);
	          
	          $fields['alias'] = 'page' . $model->getNextNewId(TABLE_SITE_PAGES);
            $model->updateSitePages($fields);
            
            //$model->setTextBlock($fields['alias'], $textBlock);
          }
        	//перезагрузить
          header("Location: " . Util::getThisAddressGet());
        }
        ob_start();
        $form->printForm();
        $MA_content .= ob_get_clean();
        $MA_content .= "<b><a href='" . Util::getThisAddress() . "'>[ Вернуться назад ]</a></b>";
        ob_end_clean();
    	  break;
      case 'del':
        $model->deleteSitePage(intval($_GET['id']));
        header('Location: ' . Util::getThisAddress());
        break;
    }
  
  //Подключаем шаблон
  include $MA_theme;