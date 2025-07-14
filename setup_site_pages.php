<?php
require_once ('common.php');
$MA_pageTitle = 'Управление страницами сайта';

$model = Model::getInstance();

$result = $model->getContentPageParams('SitePagesList');

if(!isset($_GET['action'])){
  $MA_content .= "<table width=95% border=0 cellspacing=1>";
  $MA_content .= "<tr><th>Псевдоним</th><th>Заголовок</th><th>Маска адреса</th><th>Контролер</th><th>Действие</th><th>Родитель</th></tr>";
  $cnt = 0;
  foreach($result['rows'] as $fa){
    $trClass = ++$cnt%2 ? 'F0F0F0' : 'FFF' ;
    $MA_content .=
        "<tr style='background-color:#" . $trClass . ";'><td><a href='setup_site_pages.php?action=edit&id=" . $fa['id'] . "'>" .
        $fa['alias'] .
        "</a></td><td><a href='setup_site_pages.php?action=edit&id=" . $fa['id'] . "'>" .
        $fa['short_title'] .
        "</a></td><td><a href='setup_site_pages.php?action=edit&id=" . $fa['id'] . "'>" .
        $fa['mask'] .
        "</a></td><td><a href='setup_site_pages.php?action=edit&id=" . $fa['id'] . "'>" .
        $fa['controller'] .
        "</a></td><td><a href='setup_site_pages.php?action=edit&id=" . $fa['id'] . "'>" .
        $fa['action'] .
        "</a></td><td><a href='setup_site_pages.php?action=edit&id=" . $fa['id'] . "'>" .
        $fa['parent_alias'] .
        "</a></td><td><a href='setup_site_pages.php?action=del&id=" . $fa['id'] . "'><b>[Удалить]</b></a></td></tr>";

  }
  $MA_content .= "</table>";
  $MA_content .= "<br><a href='setup_site_pages.php?action=add'><b>[Добавить новую]</b></a>";
}else
  switch($_GET['action']){
    case 'edit':
    case 'add':
      //прочитать плагины
      $plugins = Array();
      $pluginList = $model->getPluginShemes();
      foreach($pluginList as $plEl){
        $plugins[] = Array('value'=>$plEl['item_key'], 'option'=>$plEl['item_key']);
      }

      //страницы
      $pages = $model->getSitePages();
      //контролеры
      include_once DIR_KERNEL . 'controller.class.php';
      include_once DIR_PAGES . 'site_controller.class.php';
      $controllers = Array();
      $dir = opendir(DIR_CTR);
      while(false !== ($file = readdir($dir))){
        if(strpos($file, '.class.php')){
          include_once DIR_CTR . $file;
          $ctr = substr($file, 0, strpos($file, '.class.php'));
          $controllers[] = Array('value'=>$ctr, 'option'=>$ctr . 'Controller');
        }
      }

      //действия
      $methods = Array();
      foreach($controllers as $ctr){
        $methods[$ctr['value']] = get_class_methods($ctr['option']);
      }

      //блоки страниц
      $blocks = Array();
      $dir = opendir(DIR_BLOCKS);
      //блоки с контролерами
      while(false !== ($file = readdir($dir))){
        if($file !== '..' && $file !== '.'){
          $blocks[] = $file;
        }
      }

      $dir = opendir(DIR_TPL_BLOCKS);
      //блоки-элементы страниц
      while(false !== ($file = readdir($dir))){
        if($file !== '..' && $file !== '.'){
          $blocks[] = substr($file, 0, strpos($file, '.tpl'));
        }
      }

      //подгoтовка правил для полей формы
      $formData = Array(
          'alias'=>Array('title'=>'Псевдоним', 'type'=>'string', 'required'=>true, 'max'=>30),
          'short_title'=>Array('title'=>'Заголовок', 'type'=>'string', 'required'=>true, 'max'=>60),
          'mask'=>Array('title'=>'Маска адреса', 'type'=>'string', 'max'=>120),
          'controller'=>Array('title'=>'Контролер', 'type'=>'string', 'max'=>100),
          'action'=>Array('title'=>'Действие', 'type'=>'string', 'max'=>100),
          'action_input'=>Array('title'=>'Действие (вручную)', 'type'=>'string', 'max'=>100),
          'parent_alias'=>Array('title'=>'Родительская страница', 'type'=>'string', 'max'=>100),
          'parent_alias_input'=>Array('title'=>'Родительская страница (вручную)', 'type'=>'string', 'max'=>100),
        //'plugins'=>Array('title'=>'Плагины страницы', 'type'=>'string', 'max'=>500),
          'plugins_input'=>Array('title'=>'Плагины страницы (вручную)', 'type'=>'string', 'max'=>500),
        //'blocks'=>Array('title'=>'Блоки страницы', 'type'=>'string', 'max'=>500),
          'blocks_input'=>Array('title'=>'Блоки страницы (вручную)', 'type'=>'string', 'max'=>500),
          'html_title'=>Array('title'=>'Заголовок HTML', 'type'=>'string', 'required'=>true, 'max'=>70),
          'html_keywords'=>Array('title'=>'Ключевые слова HTML', 'type'=>'string', 'required'=>true, 'max'=>150),
          'html_description'=>Array('title'=>'Описание HTML', 'type'=>'string', 'required'=>true, 'max'=>200),
      );
      //если форма передана - обработать данные
      if(isset($_POST['_submit'])){
        $form = new FormProcessor();
        //если проверка не прошла - получить ошибки
        if(!$form->validateForm($formData))
          $errors = $form->getCheckErrors();
        //получить обработанные значения полей формы
        $fields = $form->getFilteredFields();
        //провести дальнейшую обработку значений
        if(!isset($fields['mask'])) $fields['mask'] = "";
        if(!isset($fields['action'])) $fields['action'] = "";
        if(!isset($fields['parent_alias'])) $fields['parent_alias'] = "";
        //действия - могут быть указаны через комбобокс или вручную, если установлен флажок

        $fields['action'] = isset($_POST['action_checkbox']) ? $fields['action_input'] : $fields['action'];
        //родитель - может быть указан через комбобокс или вручную, если установлен флажок

        $fields['parent_alias'] = isset($_POST['parent_alias_checkbox']) ? $fields['parent_alias_input'] : $fields['parent_alias'];
        //плагины
        //если установлен флажок "вручную" - просто скопировать строку
        if(isset($_POST['plugins_checkbox'])){
          $fields['plugins'] = $fields['plugins_input'];
        }else
          //флажок не установлен, нужно использовать данные комбобокса
          //если комбобокс не пустой
          if(!empty($_POST['plugins'])){
            $allowedPlugins = Array();
            //комбо с мультивыбором, поэтому возрвращает массив
            //для каждого элемента комбобокса нужна обработка
            foreach($_POST['plugins'] as $pl){
              //проверка на допустимость имени плагина
              $thisAllowed = false;
              foreach($plugins as $ap)
                if($ap['value']===$pl)
                  $thisAllowed = true;
              if($thisAllowed)
                $allowedPlugins[] = $pl;
            }
            //если в результате массив (значений больше одного)
            if(is_array($allowedPlugins))
              $fields['plugins'] = implode(PAGE_GROUP_DELIMITER, $allowedPlugins);
            //в результате вернуло одно значение, либо пустоту (если были недопустимые значения)
            else
              $fields['plugins'] = $allowedPlugins;
          }else{
            //комбик пустой, значение обнулили
            $fields['plugins'] = '';
          }

        //если установлен флажок "вручную" для блоков страниц - просто скопировать строку
        $fields['page_blocks'] = '';
        if(isset($_POST['blocks_checkbox'])){
          $fields['page_blocks'] = $fields['blocks_input'];
        }else{
          //если вручную не установлено, нужно разобрать данные комбобоксов
          //если не пуст главный комбобокс (блоки первого уровня)
          if(!empty($_POST['blocks'])){
            $allowedBlocks = Array();
            //проверить все элементы комбобокса на допустимость имен блоков
            foreach($_POST['blocks'] as $pl)
              //проверка на допустимость имени плагина
              if(in_array($pl, $blocks))
                $allowedBlocks[] = $pl;
            //сохранить результат - если массив то форматировать, иначе просто скопировать строку
            if(is_array($allowedBlocks))
              $fields['page_blocks'] = implode(PAGE_GROUP_DELIMITER, $allowedBlocks);
            else
              $fields['page_blocks'] = $allowedBlocks;
          }else//комбик пустой - значение обнулили
            $fields['page_blocks'] = '';

          //обработать сгруппированные блоки
          //если счетчик групп хранит положительное значение
          if(!empty($_POST['group_count']))
            if(intval($_POST['group_count'])>0){
              $groups = Array();
              //обработать количество групп, указанное счетчиком
              for($i=1;$i<=intval($_POST['group_count']);$i++){
                $inputName = 'group' . $i . '_input';
                $comboName = 'group' . $i . '_combo';
                //обработать инпут - имя группы
                if(empty($_POST[$inputName])) continue;
                $thisGroupName = preg_replace('/[^a-z0-9\_]/', '', $_POST[$inputName]);
                //обработать комбобокс с блоками внутри группы
                if(empty($_POST[$comboName])) continue;
                $allowedBlocks = Array();
                foreach($_POST[$comboName] as $cb)
                  if(in_array($cb, $blocks)) $allowedBlocks[] = $cb;
                if(is_array($allowedBlocks)){
                  array_unshift($allowedBlocks, $thisGroupName);
                  $groups[] = implode(PAGE_BLOCK_DELIMITER, $allowedBlocks);
                }
                else
                  $groups[] = $thisGroupName . PAGE_BLOCK_DELIMITER . $allowedBlocks;
              }
              if(!empty($fields['page_blocks']))
                $fields['page_blocks'] .= ';' . implode(PAGE_GROUP_DELIMITER, $groups);
              else
                $fields['page_blocks'] = $groups;
            }
        }

        //если ошибок проверки нет - передать значения в базу
        if(empty($errors)){
          //удалить лишние значения перед вставкой в базу
          unset($fields['action_input']);
          unset($fields['parent_alias_input']);
          unset($fields['blocks_input']);
          unset($fields['plugins_input']);
          //вставка или обновление
          if($_GET['action'] === 'edit')
            $model->updateSitePages($fields, intval($_GET['id']));
          else
            if($_GET['action'] === 'add')
              $model->updateSitePages($fields);
          header(Util::getThisAddressGet());
        }

      }else
        if($_GET['action'] === 'edit'){
          //если режим редактирования - получить все страницы сайта и найти страницу с указанным ИДом
          $sitePages = $model->getSitePages();
          foreach($sitePages as $sp)
            if($sp['id'] == intval($_GET['id'])){
              $fields = $sp;
              break;
            }
        }

      ob_start();
      ?>
      <form action=<?php echo Util::getThisAddressGet() ?> method=post>
        <table>
          <?php if(!empty($errors)):?>
            <tr>
              <td colspan=3>
                <ul>
                  <?php foreach($errors as $fl):?>
                    <li><?php echo $fl?></li>
                  <?php endforeach?>
                </ul>
              </td>
            </tr>
          <?php endif?>
          <tr valign="top">
            <td align="right">Псевдоним:</td>
            <td colspan=3><input name="alias" id="alias" value="<?php echo $fields['alias']?>" size="60" maxlength="30" type="text"></td>
          </tr>
          <tr valign="top">
            <td align="right">Заголовок:</td>
            <td colspan=3><input name="short_title" id="short_title" value="<?php echo $fields['short_title']?>" size="60" maxlength="30" type="text"></td>
          </tr>
          <tr valign="top">
            <td align="right">Маска адреса:</td>
            <td colspan=3><input name="mask" id="mask" value="<?php echo $fields['mask']?>" size="60" maxlength="150" type="text"></td>
          </tr>
          <tr valign="top">
            <td align="right">Контролер:</td>
            <td>
              <select name="controller" id="controller" onchange="changeActionsList()">
                <?php foreach($controllers as $b):?>
                  <option value="<?php echo $b['value']?>" <?php echo strtolower($b['value'])===strtolower($fields['controller']) ? 'selected' : '' ?> >
                    <?php echo $b['option']?>
                  </option>
                <?php endforeach?>
              </select>
            </td>
            <td><input type=checkbox name=controller_checkbox id=controller_checkbox onclick="changeState(this, 'controller_input', 'controller')"> вручную</td>
            <td><input name=controller_input id=controller_input value="<?php echo $fields['controller']?>" disabled ></td>
          </tr>
          <tr valign="top">
            <td align="right">Действие:</td>
            <td>
              <select name="action" id="action">
                <option value=""></option>
                <?php foreach($methods['index'] as $b):?>
                  <option value="<?php echo $b?>" <?php echo $b===$fields['action'] ? 'selected' : '' ?> >
                    <?php echo $b?>
                  </option>
                <?php endforeach?>
              </select>
            </td>
            <td><input type=checkbox name=action_checkbox id=action_checkbox onclick="changeState(this, 'action_input', 'action')"> вручную</td>
            <td><input name=action_input id=action_input value="<?php echo $fields['action']?>" disabled ></td>
          </tr>
          <tr valign="top">
            <td align="right">Родительская страница:</td>
            <td>
              <select name="parent_alias" id="parent_alias">
                <option value=""></option>
                <?php foreach($pages as $b):?>
                  <option value="<?php echo $b['alias']?>" <?php echo $b['alias']==$fields['parent_alias'] ? 'selected' : '' ?> >
                    <?php echo $b['short_title']?>
                  </option>
                <?php endforeach?>
              </select>
            </td>
            <td><input type=checkbox name=parent_alias_checkbox id=parent_alias_checkbox onclick="changeState(this, 'parent_alias_input', 'parent_alias')"> вручную</td>
            <td><input name=parent_alias_input id=parent_alias_input value="<?php echo $fields['parent_alias']?>" disabled ></td>
          </tr>
          <tr valign="top">
            <td align="right">Плагины страницы:</td>
            <td>
              <select name="plugins[]" id="plugins" multiple="multiple">
                <?php $thisPlugins = explode(PAGE_GROUP_DELIMITER, $fields['plugins'])?>
                <?php foreach($plugins as $b):?>
                  <option value="<?php echo $b['value']?>" <?php echo in_array($b['value'], $thisPlugins) ? 'selected' : '' ?> >
                    <?php echo $b['option']?>
                  </option>
                <?php endforeach?>
              </select>
            </td>
            <td><input type=checkbox name=plugins_checkbox id=plugins_checkbox onclick="changeState(this, 'plugins_input', 'plugins')"> вручную</td>
            <td><input name=plugins_input id=plugins_input value="<?php echo $fields['plugins']?>" disabled ></td>
          </tr>
          <tr valign="top">
            <td align="right">Блоки страницы:</td>
            <?php
            $plainBlocks = Array();
            $groupBlocks = Array();
            $groupBlockNames = Array();
            if(!empty($fields['page_blocks'])){
              $blocks1 = explode(PAGE_GROUP_DELIMITER, $fields['page_blocks']);
              foreach($blocks1 as $b1)
                if(strpos($b1, PAGE_BLOCK_DELIMITER)>-1){
                  $blocks2 = explode(PAGE_BLOCK_DELIMITER, $b1);
                  $groupName = $blocks2[0];
                  unset($blocks2[0]);
                  $groupBlockNames[] =$groupName;
                  $groupBlocks[] = $blocks2;
                }else
                  $plainBlocks[] = $b1;
            }
            ?>
            <td>
              <select name="blocks[]" id="blocks" multiple="multiple">
                <?php foreach($blocks as $b):?>
                  <option value="<?php echo $b?>" <?php echo @in_array($b, $plainBlocks) ? 'selected' : '' ?> >
                    <?php echo $b?>
                  </option>
                <?php endforeach?>
              </select>
              <br>
              <input type=button value="+" onClick="addGroup()">
              <input type=button value="-" onClick="hideGroup()">
              <?php for($i=1;$i<31;$i++):?>
                <div id=group<?php echo $i?> style="display:<?php echo (count($groupBlocks) > 0 && $i-1<count($groupBlocks) )? 'block' : 'none' ?>;">
                  <?php echo $i?>. <input name="group<?php echo $i?>_input" id="group<?php echo $i?>_input" value="<?php echo isset($groupBlockNames[$i-1]) ? $groupBlockNames[$i-1] : ''?>" size=13>
                  <br>
                  <select name="group<?php echo $i?>_combo[]" id="group<?php echo $i?>_combo" multiple="multiple">
                    <?php foreach($blocks as $b):?>
                      <option value="<?php echo $b?>" <?php echo (isset($groupBlocks[$i-1]) && in_array($b, $groupBlocks[$i-1])) ? 'selected' : '' ?> >
                        <?php echo $b?>
                      </option>
                    <?php endforeach?>
                  </select>
                  <br><br>
                </div>
              <?php endfor?>
              <input type=hidden name=group_count id=group_count value="<?php echo  count($groupBlocks)?>">
              <script language="javascript">
                <!--
                //группы блоков страницы
                function addGroup(){
                  var i = document.getElementById('group_count').value;
                  i++;
                  document.getElementById('group' + i).style.display = '';
                  document.getElementById('group_count').value = i;
                }
                function hideGroup(){
                  var i = document.getElementById('group_count').value;
                  document.getElementById('group' + i).style.display = 'none';
                  if(i>0)i--;
                  document.getElementById('group_count').value = i;
                }
                //переключатель ручной/авторежим
                function changeState(checkbox, target1, target2){
                  document.getElementById(target1).disabled = !checkbox.checked;
                  document.getElementById(target2).disabled = checkbox.checked;
                }
                //обновляет список действий при изменении контролера
                var actionsList = Array(
                    <?php
                      $cnt1 = 0;
                      foreach($methods as $mt){
                        $cnt2 = 0;
                        echo 'Array("" ';
                        if(!empty($mt)){
                          echo(', ');
                          foreach($mt as $mtRow){
                            echo '"' . $mtRow . '"';
                            if(++$cnt2<count($mt)) echo ', ';
                          }
                        }

                        if(++$cnt1<count($methods)) echo '),';
                        else echo ')';
                      }
                    ?>
                );
                var currentAction = '<?php echo $fields['action'] ?>';
                function changeActionsList(){
                  var action = document.getElementById('action');
                  var controller = document.getElementById('controller');
                  action.length = 0;
                  var newActions = actionsList[controller.selectedIndex];
                  var selectedIndex = 0;
                  for(i=0;i<newActions.length;i++){
                    var newElement = document.createElement('option');
                    newElement.text = newActions[i];
                    if(newActions[i] == currentAction)
                      selectedIndex = i;
                    action.add(newElement, null);
                  }
                  action.selectedIndex = selectedIndex;
                }
                changeActionsList();
                // -->
              </script>
            </td>
            <td><input type=checkbox name=blocks_checkbox id=blocks_checkbox onclick="changeState(this, 'blocks_input', 'blocks')"> вручную</td>
            <td><input name=blocks_input id=blocks_input value="<?php echo $fields['page_blocks']?>" disabled ></td>
          </tr>
          <tr valign="top">
            <td align="right">Заголовок HTML (Title):</td>
            <td colspan=3><input name="html_title" id="html_title" value="<?php echo $fields['html_title']?>" size="60" maxlength="70" type="text"></td>
          </tr>
          <tr valign="top">
            <td align="right">Ключевые слова HTML (META Keywords):</td>
            <td colspan=3><input name="html_keywords" id="html_keywords" value="<?php echo $fields['html_keywords']?>" size="60" maxlength="150" type="text"></td>
          </tr>
          <tr valign="top">
            <td align="right">Описание HTML (META Description):</td>
            <td colspan=3><input name="html_description" id="html_description" value="<?php echo $fields['html_description']?>" size="60" maxlength="200" type="text"></td>
          </tr>
          <tr>
            <td></td>
            <td><input name="_submit" id="_submit" value="Сохранить" type="submit"></td>
          </tr>
        </table>
      </form>
      <b><a href="setup_site_pages.php">[ Вернуться назад ]</a></b>
      <?php
      $MA_content .= ob_get_contents();
      ob_end_clean();
      break;
    case 'del':
      $model->deleteSitePage(intval($_GET['id']));
      header(Util::getThisAddress());
      break;
  }

//Подключаем шаблон
include $MA_theme;