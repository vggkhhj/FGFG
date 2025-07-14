//Запрос
function sendGetRequest(url)
{
    var myRequest = null;
    try
    {
        myRequest=new XMLHttpRequest();
    } catch (trymicrosoft)
      {
          try
          {
              myRequest=new ActiveXObject('Msxml2.XMLHTTP');
          } catch (othermicrosoft)
            {
               try
               {
                 myRequest = new ActiveXObject('Microsoft.XMLHTTP');
               } catch (failed)
                 {
                   myRequest = null;
                 }
            }
      }
     myRequest.open("GET", url, true);
     myRequest.onreadystatechange = function myFunc() {return false;};
     myRequest.send(null);
}
//Запрос

//Выбор имени для таблиц
function setNameForTable(currentBox)
{
    var currentId = currentBox.id.replace('tableBox_','');
    var boxI = currentBox.getAttribute('boxI');
    if (currentBox.checked)
    {
        var saveTableAs = document.getElementById('saveTableAs');
            saveTableAs.value = document.getElementById('tableName_'+currentId).value;
        document.getElementById('currentId').value = currentId;
        document.getElementById('selectedIcon').value = '';
        unselectAllIcons();
        showPreview('');

        document.getElementById('addWindow').style.left = '200px';
        var yPos = boxI*30;
        document.getElementById('addWindow').style.top =  yPos+'px';
        document.getElementById('addWindow').style.display = 'block';
        changeAlbum(document.getElementById('iconsAlbum').value);
    }
    
    if (!currentBox.checked)
    {
        if (window.confirm('Отключить таблицу?'))
        {
            var url = 'remove_table.php?tableName=' + currentId;
            if (window.confirm('Удалить соответствующие базе поля?'))
              url = url + "&delFields=1";
            today = new Date;
            url = url + "&today="+today.getTime();
            sendGetRequest(url);
            alert('Данные удалены');
        }
        else currentBox.checked = true;
    }
}

function saveTableName()
{
    var currentId = document.getElementById('currentId').value;
    document.getElementById('tableName_'+currentId).value = document.getElementById('saveTableAs').value;
    var currentIcon = document.getElementById('selectedIcon').value;
    document.getElementById('tableIcon_'+currentId).value = currentIcon;
        document.getElementById('addWindow').style.display = 'none';
}

function cancelSaving()
{
    var currentId = document.getElementById('currentId').value;
    var currentBox = document.getElementById('tableBox_' + currentId);
        currentBox.checked = false;
        
    document.getElementById('addWindow').style.display = 'none';
}

function unselectAllIcons()
{
    for (var i = 1; i < 11 ; i++)
      document.getElementById('icon' + i).style.border = '1px solid #555';
}

function selectIcon(imageName)
{
    unselectAllIcons();
    var albumName = document.getElementById('iconsAlbum').value;
    document.getElementById('icon' + imageName).style.border = '1px solid red';
    document.getElementById('selectedIcon').value = albumName+ '/' +imageName;
}

function showPreview(imageName)
{
    var albumName = document.getElementById('iconsAlbum').value;
    document.getElementById('previewImage').src = 'css/icons/' + albumName + '/' + imageName + '.png';
}

function previewLastSelected()
{
    var imageName = document.getElementById('selectedIcon').value;
    document.getElementById('previewImage').src = 'css/icons/' + imageName + '.png';
}

function changeAlbum(album) { 
	unselectAllIcons();
	for (var i = 1; i < 11 ; i++) {
	  document.getElementById('icon' + i).src = 'css/icons/'+ album + '/' + i + '.gif';
	}
}
//Выбор имени для таблиц

//Настройка полей каждой таблицы
function checkCorrection()
{
    return true;
}

function onOffField(currentBox)
{
    var currentId = currentBox.id.replace('fieldBox_','');
    var currentFieldBlockData = document.getElementById('fieldBlockData_'+currentId);
    if (currentBox.checked)
      currentFieldBlockData.style.display = 'block';
    else
      currentFieldBlockData.style.display = 'none';
}

function editProp(currentFieldName) {
	var fieldType = document.getElementById('fieldType_' + currentFieldName).value;
    
    allPropsInvisible(currentFieldName);
    //Выбираем какие настройки для какого поля отобразить
    if (fieldType == 'text') document.getElementById('textProp_' + currentFieldName).style.display = 'block';
    if (fieldType == 'textarea') document.getElementById('textareaProp_' + currentFieldName).style.display = 'block';
    if (fieldType == 'checkbox') document.getElementById('checkboxProp_' + currentFieldName).style.display = 'block';
    if (fieldType == 'radio') document.getElementById('radioProp_' + currentFieldName).style.display = 'block';
    if (fieldType == 'date') document.getElementById('dateProp_' + currentFieldName).style.display = 'block';
    if (fieldType == 'link') document.getElementById('linkProp_' + currentFieldName).style.display = 'block';
    if (fieldType == 'file') document.getElementById('fileProp_' + currentFieldName).style.display = 'block';
}

function allPropsInvisible(currentFieldName) {
    document.getElementById('textProp_' + currentFieldName).style.display = 'none';
    document.getElementById('textareaProp_' + currentFieldName).style.display = 'none';
    document.getElementById('checkboxProp_' + currentFieldName).style.display = 'none';
    document.getElementById('radioProp_' + currentFieldName).style.display = 'none';
    document.getElementById('dateProp_' + currentFieldName).style.display = 'none';
    document.getElementById('linkProp_' + currentFieldName).style.display = 'none';
    document.getElementById('fileProp_' + currentFieldName).style.display = 'none';

}
//Настройка полей каждой таблицы

//Настройка модулей
function modulesActivation(currentModule)
{
    if (!currentModule.checked)
    {
      if (window.confirm('Отключить модуль?'))
      {
        var currentModuleName = currentModule.id.replace('moduleBox_','');
        var url = 'uninstall_modules.php?moduleName=' + currentModuleName;
        today = new Date;
        url = url + "&today="+today.getTime();
        sendGetRequest(url);
        alert('Данные удалены');
      }
      else currentModule.checked = true;
    }
}
//Настройка модулей

//Загрузка файла
function uploadFile()
{
  document.getElementById('uploadForm').submit();
  document.getElementById('uploadImageWindow').style.display='none'; return false;
}
//Загрузка файла


//Назначение действий элементам
$(document).ready(function(){
	//добавить новую копию для файла
	$('.addCopyBtn').click(function(){
		rel = $(this).attr('rel');
		randomValue = getrandom('1','100');
									/*
                    					0 - width
                    					1 - height
                    					2 - action
                    					3 - prefix
                    					4 - r
                    					5 - g
                    					6 - b
                    				*/
		w_h = "w: <input type='text' name='fileFieldDefault["+rel+"]["+randomValue+"][0]' value='' size='8'> h: <input type='text' name='fileFieldDefault["+rel+"]["+randomValue+"][1]' value='' size='8'> | ";
		rgb = "r: <input type='text' name='fileFieldDefault["+rel+"]["+randomValue+"][4]' value='' size='4'> g: <input type='text' name='fileFieldDefault["+rel+"]["+randomValue+"][5]' value='' size='4'> b: <input type='text' name='fileFieldDefault["+rel+"]["+randomValue+"][6]' value='' size='4'> | ";
		s = "действие: <SELECT name='fileFieldDefault["+rel+"]["+randomValue+"][2]'><OPTION value='crop'>Подрезать</OPTION><OPTION value='fit'>Вписать</OPTION><OPTION value='letterbox'>Вписать на фон</OPTION></SELECT> | ";
		prefix = "префикс: <input type='text' name='fileFieldDefault["+rel+"]["+randomValue+"][3]' value='' size='8'> ";
		controls = "<span class='spanBtn removeCopyBtn'>-</span><br></span>";		
		$('#imageCopies_'+rel).append("<span>"+w_h+rgb+s+prefix+controls+"</span>");
		return false();
	});
	//удалить копию для файла
	$('.removeCopyBtn').live('click', function(){
		$(this).parent().remove();
	})
})

//генереция случайного числа
function getrandom(min_random, max_random) {
    var range = max_random - min_random + 1;
    return Math.floor(Math.random()*range) + min_random;
}