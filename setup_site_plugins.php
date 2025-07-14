<?php

require_once ('common.php');
$MA_pageTitle = 'Настройка плагинов';

$model = Model::getInstance();
$allowedPlugins = $model->getPluginShemes();
$pluginSchemes = Util::dbToArray($allowedPlugins, 'item_key');

////////////////////////////////////////////////////////////
//если действие не задано - отобразить список схем
////////////////////////////////////////////////////////////
if(!isset($_GET['scheme'])){
  $MA_content .= "<table width=100% border=0 cellspacing=1>";
  $cnt = 0;
  foreach($allowedPlugins as $fa){
    $trClass = ++$cnt%2 ? 'F0F0F0' : 'FFF' ;
    $MA_content .=
        "<tr style='background-color:#" . $trClass . ";'><td><a href='setup_site_plugins.php?scheme=" . $fa['item_key'] . "'>" . 
    $fa['item_key'] .
        "</a></td><td><a href='setup_site_plugins.php?scheme=" . $fa['item_key'] . "'>" . 
    $fa['plugin_name'] .
        "</a></td><td><a href='setup_site_plugins.php?scheme=" . $fa['item_key'] . "'>" . 
    Util::fitString($fa['descr'], 50) .
        "</a></td><td><a href='setup_site_plugins.php?scheme=" . $fa['item_key'] . "'><b>[Настроить]</b></a> " .
        "<a href='setup_site_plugins.php?scheme=install&item=" . $fa['item_key'] . "'><b>[Установить]</b></a> " .
        "<a href='setup_site_plugins.php?scheme=delete&item=" . $fa['item_key'] . "'><b>[Удалить]</b></a>" .
        "</td></tr>";
  }
  $MA_content .= "</table>";
  $MA_content .= "<br><b><a href='setup_site_plugins.php?scheme=new'>[Добавить новую схему]</a></b>";
  ////////////////////////////////////////////////////////////
  //действите задано - надо определить, какое именно
  ////////////////////////////////////////////////////////////
}else

switch($_GET['scheme']){
  ////////////////////////////////////////////////////////////
  //установка схемы - создание необходимых таблиц
  ////////////////////////////////////////////////////////////
  case 'install':
    $scheme = $pluginSchemes[$_GET['item']];
    $pluginObj =$model->getPluginAdminObject($scheme['plugin_name']);
    $pluginObj->installScheme($_GET['item']);
    header('Location: ' . Util::getThisAddress());
    break;
  ////////////////////////////////////////////////////////////
  //удаление схемы
  ////////////////////////////////////////////////////////////
  case 'delete':
    $pluginObj =$model->deletePluginScheme($_GET['item']);
    header('Location: ' . Util::getThisAddress());
    break;
  ////////////////////////////////////////////////////////////
  //прочие варианты (новый или редактирование)
  ////////////////////////////////////////////////////////////
  default:
    //есть первый или второй сабмит - необходимо подключить схему, 
    //получить ее плагин и его настройки и админчасть
    if(isset($_POST['setup_plugins_submit_1']) || isset($_POST['setup_plugins_submit_2'])){
      //определить имя плагина - либо задано через форму, либо плагин текущей схемы
      @session_start();
      if(isset($_SESSION['fields']['plugin_name'])){
        $plugin = $_SESSION['fields']['plugin_name'];
      }else
      if(isset($_POST['plugin_name'])){
        $plugin = $_POST['plugin_name'];
      }else
        $plugin = $pluginSchemes[$_GET['scheme']]['plugin_name'];
      
      //подключить класс админа для выбранного плагина
      $pluginObj = $model->getPluginAdminObject($plugin);
      if(empty($pluginObj)){
        //для плагина не создана админчасть
        $MA_content .= "Не найден модуль администрирования для плагина " . $_GET['plugin'];
      }
      else{

      ////////////////////////////////////////////////////////////
      //второй сабмит - обработка результатов
      ////////////////////////////////////////////////////////////
      if(isset($_POST['setup_plugins_submit_2'])){
        //сохранить результаты заполнения прошлой формы
        @session_start();
        $resultFields = $_SESSION['fields'];
        unset($_SESSION['fields']);
        $pluginObj->submitForm($resultFields);
        if($_GET['scheme'] !== 'new')
          $id = $_GET['scheme'];
        $model->newPluginScheme($resultFields, $id);
        header('Location: ' . Util::getThisAddress());
      }else
      ////////////////////////////////////////////////////////////
      //первый сабмит - форма №2
      ////////////////////////////////////////////////////////////
      if(isset($_POST['setup_plugins_submit_1'])){
        //сохранить результаты заполнения прошлой формы
        @session_start();
        $_SESSION['fields'] = Array(
            'item_key'=>$_POST['item_key'], 
            'plugin_name'=>$_POST['plugin_name'], 
            'descr'=>$_POST['descr']
            );
        
        ob_start();
        ?>
<form action="<?php echo Util::getThisAddressGet()?>" method="post">
<table width="100%">
<?php
$content = ob_get_contents();
ob_end_clean();
$pluginObj->setupForm($content, $_GET['scheme']);
ob_start();
?>
</table>
<input type=submit name=setup_plugins_submit_2 value=Сохранить></form>
<?php
$content .= ob_get_contents();
ob_end_clean();
$MA_content .= $content;
      }
    }
      ////////////////////////////////////////////////////////////
      //сабмита не было - вывод формы
      ////////////////////////////////////////////////////////////
    }else{
      @session_start();
      unset($_SESSION['fields']);
      $pluginData = Array();
      if($_GET['scheme'] !== 'new'){
        $pluginData = $pluginSchemes[$_GET['scheme']];
      }
      $pluginList = $model->getPluginNames();
       
      ob_start();
      ?>
<form action="<?php echo Util::getThisAddressGet()?>" method="post">
<table width="100%">
	<tr>
		<td>Имя схемы:</td>
		<td><input type=text name=item_key
			value="<?php echo $pluginData['item_key']?>"></td>
	</tr>
	<tr>
		<td>Плагин:</td>
		<td>
		  <select name=plugin_name>
		  <?php foreach($pluginList as $pl):?>
		    <option value="<?php echo $pl?>" <?php echo ($pl===$pluginData['plugin_name']) ? 'selected' : '' ?> >
		      <?php echo $pl?>
		    </option>
		  <?php endforeach?>
		  </select>
		</td>
	</tr>
  <tr>
    <td>Описание:</td>
    <td><input type=text name=descr
      value="<?php echo $pluginData['descr']?>"></td>
  </tr>
</table>
<input type=submit name=setup_plugins_submit_1 value="Далее>"></form>
      <?php
      $content .= ob_get_contents();
      ob_end_clean();
      $MA_content .= $content;
    }
    $MA_content .= "<br><br><b><a href='setup_site_plugins.php'>[Вернуться назад]</a></b>";
}

//Подключаем шаблон
include $MA_theme;