<?php
/**
 * Создает капчу
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage lib
 */

//TODO: добавить deny from all #### не очень хорошая идея...
		

require_once '../../config.php';

	#### сообщений точно не нужно выводить
	$CUR_ERROR_REP=error_reporting();
	error_reporting(0);

require_once DIR_ADD . 'securimage/securimage.php';

$colors = Array(
  '#b70505', '#051eb7', '#006b0d', '#ff6600', '#6600ff', 
  '#29708d', '#54792f', '#4a3826'
);
$bgColors = Array(
  '#e3daed', '#dfafaf', '#dfafd9', '#bbafdf', '#afd3df', '#afdfb0', '#dedfaf', '#dfc7af'
);
$lineColors = Array(
  '#80BFFF', '#da80ff', '#ff80aa', '#80ffa4', '#f5ff80', '#ffaa80'
);
$arcColors = Array(
  '#8080ff', '#ff80fe', '#ff8098', '#80d4ff', '#80ff92', '#f5ff80', '#ffbf80'
);

$img = new Securimage();
if(SECURIMAGE_TTF){
  $path = DIR_ADD . 'securimage/fonts/';
  $files = getDirListing($path);
  $img->ttf_file = $path . $files[mt_rand(0, count($files)-1)];
}
else{
  $path = DIR_ADD . 'securimage/gdfonts/';
  $files = getDirListing($path);
  $img->use_gd_font = true;
  $img->gd_font_file = $path . $files[mt_rand(0, count($files)-1)];
}
$img->text_color = defined('SECURIMAGE_TEXT_COLOR') ? SECURIMAGE_TEXT_COLOR : $colors[mt_rand(0, count($colors)-1)];
$img->image_bg_color = defined('SECURIMAGE_BG_COLOR') ? SECURIMAGE_BG_COLOR : $bgColors[mt_rand(0, count($bgColors)-1)];
$img->line_color = defined('SECURIMAGE_LINE_COLOR') ? SECURIMAGE_LINE_COLOR : $lineColors[mt_rand(0, count($lineColors)-1)];
$img->line_distance = defined('SECURIMAGE_LINE_DISTANCE') ? SECURIMAGE_LINE_DISTANCE : mt_rand(5, 10);
$img->line_thickness = defined('SECURIMAGE_LINE_THICKNESS') ? SECURIMAGE_LINE_THICKNESS : mt_rand(1, 2);
$img->draw_angled_lines = defined('DRAW_ANGLED_LINES') ? DRAW_ANGLED_LINES : mt_rand(0, 1);
$img->arc_line_colors = defined('SECURIMAGE_ARC_LINE_COLORS') ? SECURIMAGE_ARC_LINE_COLORS : $arcColors[mt_rand(0, count($lineColors)-1)];
if(defined('SECURIMAGE_WIDTH')) $img->image_width = SECURIMAGE_WIDTH;
if(defined('SECURIMAGE_HEIGHT')) $img->image_height = SECURIMAGE_HEIGHT;
if(defined('SECURIMAGE_CODE_LENGTH')) $img->code_length = SECURIMAGE_CODE_LENGTH;
if(defined('SECURIMAGE_CHARSET')) $img->charset = SECURIMAGE_CHARSET;
if(defined('SECURIMAGE_FONT_SIZE')) $img->font_size = SECURIMAGE_FONT_SIZE;
if(defined('SECURIMAGE_TEXT_TRANSPARENCY_PERCENTAGE')) $img->text_transparency_percentage = SECURIMAGE_TEXT_TRANSPARENCY_PERCENTAGE;
if(defined('SECURIMAGE_DRAW_LINES')) $img->draw_lines = SECURIMAGE_DRAW_LINES;
$img->show(); // alternate use:  $img->show('/path/to/background.jpg');

function getDirListing($dirName){
  $dh = opendir($dirName);
  $files = Array();
  while(($fileName = readdir($dh)) == true)
    if($fileName != '.' && $fileName != '..')
      $files[] = $fileName;
  
  return $files;
}

/*
function mt() {
 	list($usec, $sec) = explode(' ', microtime());
 	return (float) $sec + ((float) $usec * 100000);
}

function multiWave($img1, $img2){
  // случайные параметры (можно поэкспериментировать с коэффициентами):
  // частоты
  $rand1 = mt_rand(700000, 1000000) / 15000000;
  $rand2 = mt_rand(700000, 1000000) / 15000000;
  $rand3 = mt_rand(700000, 1000000) / 15000000;
  $rand4 = mt_rand(700000, 1000000) / 15000000;
  // фазы
  $rand5 = mt_rand(0, 3141592) / 1000000;
  $rand6 = mt_rand(0, 3141592) / 1000000;
  $rand7 = mt_rand(0, 3141592) / 1000000;
  $rand8 = mt_rand(0, 3141592) / 1000000;
  // амплитуды
  $rand9 = mt_rand(400, 600) / 100;
  $rand10 = mt_rand(400, 600) / 100;

  for($x = 0; $x < $width; $x++){
    for($y = 0; $y < $height; $y++){
      // координаты пикселя-первообраза.
      $sx = $x + ( sin($x * $rand1 + $rand5) + sin($y * $rand3 + $rand6) ) * $rand9;
      $sy = $y + ( sin($x * $rand2 + $rand7) + sin($y * $rand4 + $rand8) ) * $rand10;

      // первообраз за пределами изображения
      if($sx < 0 || $sy < 0 || $sx >= $width - 1 || $sy >= $height - 1){
        $color = 255;
        $color_x = 255;
        $color_y = 255;
        $color_xy = 255;
      }else{ // цвета основного пикселя и его 3-х соседей для лучшего антиалиасинга
        $color = (imagecolorat($img, $sx, $sy) >> 16) & 0xFF;
        $color_x = (imagecolorat($img, $sx + 1, $sy) >> 16) & 0xFF;
        $color_y = (imagecolorat($img, $sx, $sy + 1) >> 16) & 0xFF;
        $color_xy = (imagecolorat($img, $sx + 1, $sy + 1) >> 16) & 0xFF;
      }

      // сглаживаем только точки, цвета соседей которых отличается
      if($color == $color_x && $color == $color_y && $color == $color_xy){
        $newcolor=$color;
      }else{
        $frsx = $sx - floor($sx); //отклонение координат первообраза от целого
        $frsy = $sy - floor($sy);
        $frsx1 = 1 - $frsx;
        $frsy1 = 1 - $frsy;

        // вычисление цвета нового пикселя как пропорции от цвета основного пикселя и его соседей
        $newcolor = floor( $color    * $frsx1 * $frsy1 +
        $color_x  * $frsx  * $frsy1 +
        $color_y  * $frsx1 * $frsy  +
        $color_xy * $frsx  * $frsy );
      }
      imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newcolor, $newcolor, $newcolor));
    }
  }
}

function getRandomStr($iLow = 5, $iHi = 0){
  mt_srand();
  $iHi = ($iHi<$iLow) ? $iLow : $iHi;
  $len = mt_rand($iLow, $iHi);
  $str = "";
  $pattern = "123456789abcdefghijkmnpqrstuvwxyz";
  for($i=0;$i<$len;$i++)
  $str .= substr($pattern, mt_rand(0,strlen($pattern)), 1);
  return $str;
}

header("Content-type: image/png");

// создаем изображение
$im=imagecreate(101, 26);

// Выделяем цвет фона (белый)
$w=imagecolorallocate($im, 255, 255, 255);

// Выделяем цвет для фона (светло-серый)
$g1=imagecolorallocate($im, 192, 192, 192);

// Выделяем четыре случайных темных цвета для символов
$cl1=imagecolorallocate($im,rand(0,128),rand(0,128),rand(0,128));
$cl2=$cl1;//imagecolorallocate($im,rand(0,128),rand(0,128),rand(0,128));
$cl3=$cl1;//imagecolorallocate($im,rand(0,128),rand(0,128),rand(0,128));
$cl4=$cl1;//imagecolorallocate($im,rand(0,128),rand(0,128),rand(0,128));

// Выделяем цвет для более темных помех (темно-серый)
$g2=$cl1;
imagecolorallocate($im, 64,64,64);

// Рисуем сетку
//for ($i=0;$i<=100;$i+=5) imageline($im,$i,0,$i,25,$g1);
//for ($i=0;$i<=25;$i+=5) imageline($im,0,$i,100,$i,$g1);

// Выводим каждую цифру по отдельности, немного смещая случайным образом
for($i = 0; $i < strlen($_SESSION[CAPTCHA_KEY]); $i++)
  imagestring($im, 8, $i*12 + rand(0,10), 5+rand(-5,5), substr($_SESSION[CAPTCHA_KEY], $i, 1), $cl1);

// Выводим пару случайных линий тесного цвета, прямо поверх символов.
// Для увеличения количества линий можно увеличить,
// изменив число выделенное красным цветом
for ($i = 0; $i < 5; $i++)
  imageline($im, rand(5,50), rand(10,25), rand(5,60), rand(10,25), $g2);


// Коэфициент увеличения/уменьшения картинки
$k = 3;

// Создаем новое изображение, увеличенного размера
$im1 = imagecreatetruecolor(101*$k, 26*$k);

// Копируем изображение с изменением рамеров в большую сторону
imagecopyresized($im1, $im, 0, 0, 0, 0, 101*$k, 26*$k, 101, 26);


// Создаем новое изображение, нормального размера
$im2 = imagecreatetruecolor(101, 26);

// Копируем изображение с изменением рамеров в меньшую сторону
imagecopyresampled($im2, $im1, 0, 0, 0, 0, 101, 26, 101*$k, 26*$k);

// Генерируем изображение
imagepng($im2);

// Освобождаем память
imagedestroy($im2);
imagedestroy($im1);
imagedestroy($im);
*/

	#### так нужно делать
	error_reporting($CUR_ERROR_REP);
