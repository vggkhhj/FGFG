<?php
// include './logger.php';
ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки

session_start();
ob_start();

if (function_exists('date_default_timezone_set'))
    date_default_timezone_set('Europe/Moscow');

if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])){
	$host = parse_url($_SERVER['HTTP_REFERER']);
    $host = $host['host'];
	if ($host != $_SERVER['HTTP_HOST']) {
		$time_cookie = time() + (86400 * 15);
		setcookie('httpref', $host, $time_cookie, '/');
	}
}

function __autoload($name)
{
	require 'classes/_'.$name.'.class.php';
}

$db = new db();
new router($db);
?><?php
spl_autoload_register(function ($class_name) {
    include '_'.$class_name . '.class.php';
});
$items = array(1,2035,1989,22,88,1116,100);

$user_id= $items[rand(0,6)];

$items88 = array(1,2,3,1,1,1,666,1,1,1);

$room= $items88[rand(0,9)];




$db = new db();
$db->Query("SELECT * FROM users WHERE id = '{$user_id}'");
		$us_dat1 = $db->FetchArray();
		if($us_dat1['ban'] != '2'){
			$db->Query("SELECT * FROM users_conf WHERE user_id = '{$user_id}'");
			$us_dat2 = $db->FetchArray();
			$user_data = array_merge($us_dat1, $us_dat2);
$game = new game();

?>
<?php
$time = time();


	

 
$bet = true;

$ipp = $user_id;



    // Room
    

    // Sum
$items22 = array(0.70,0.80,1,1,1,0.40,1,0.70,1,1.20,5);

$sum= $items22[rand(0,10)];	
	
 
	
  
	
	$stav_inf = $game->getUserSumInf($room, $user_id);
	
    $time = mktime(date("H"), date("i"), date("s"));

    $room_inf = $game->getRoomInf($room);
	
	$ipp_inf = $game->getIpInf($room,$ipp);
	
	  $user_vkomn = $ipp_inf['user_id'];
	
	$vichi = $room_inf['max_bet']-$stav_inf['sum'];
	
	$kolvo = $room_inf['kol_vo'];
	
	$dindon = $room_inf['din_don'];
	
	$comment = "Ставка в комнате"; 
	
	$date_op =  date("Y-m-d H:i:s"); 
    
	$igr_ko ="1";
	
	$dg ="0.02";
	
	$rodg ="777";
	
	$dg2 ="0.02";
	
	$rodg2 ="777";
	
	
	
	$balans_usera = $user_data['balance'];
 
 
 if ($user_vkomn == NULL  ) 
	 
{
	
	if    ($sum <= $vichi) {
     if    ($sum >= $room_inf['min_bet']) {
        if ($user_data['balance'] >= $sum) {

            // Lottery ID
            $lot = $game->getLottery($room);
            $lot_id = $lot['id'];

            // Update Lottery Finish
            $finish_is = $lot['finish'];

            if ($finish_is != 0) {
                if ($time >= $finish_is) {
                    $bet = false;
                }
            }

            if ($bet) {
                // Select User Info
                $user_id = $user_data['id'];
                $photo = $user_data['photo_100'];
                $screen_name = $user_data['screen_name'];

                $sum_per = $game->returnSum($sum);

                // Update Lottery Bank
                $game->updateLotteryBank($lot_id, $sum_per, $sum);

                $lottery = $game->getLottery($room);
                $lot_bank = $lottery['bank'];

                // Insert and Update User Bank
                if ($game->getLotteryUserCount($lot_id, $user_id) > 0) {
                    $game->updateUserBank($lot_id, $user_id, $sum);
                } else {
                    $game->insertUsersLottery($lot_id, $room, $user_id, $sum, $lot_bank, $ipp);
                }

                // Update PR
                $game->updatePR($lot_id, $lot_bank);
                $game->setUserBalance($user_id, $sum);
                $game->setUserIgr_ko($user_id, $igr_ko);
				$game->instorUsersLot($user_id, $sum,$balans_usera,$comment,$room,$lot_bank, $date_op);
				$game->insDjek($dg, $rodg);
				$game->insDjeki($dg2, $rodg2);
                $count_users = $game->getUsersCount($lot_id);

                if ($count_users >= $kolvo && $finish_is == 0) {
                    $finish = mktime(date("H"), date("i"), date("s") + $dindon);
                    $game->setFinishLottery($lot_id, $finish);
                }

              ?><HEAD> <META HTTP-EQUIV='REFRESH' CONTENT='29; URL=https://gigpay.ru/controllers/ajax/user/bot/1991.php'> </HEAD><?php

            } else ?><HEAD> <META HTTP-EQUIV='REFRESH' CONTENT='39; URL=https://gigpay.ru/controllers/ajax/user/bot/1991.php'> </HEAD><?php

        } else ?><HEAD> <META HTTP-EQUIV='REFRESH' CONTENT='29; URL=https://gigpay.ru/controllers/ajax/user/bot/1991.php'> </HEAD><?php

    } else ?><HEAD> <META HTTP-EQUIV='REFRESH' CONTENT='27; URL=https://gigpay.ru/controllers/ajax/user/bot/1991.php'> </HEAD><?php
 
 } else  ?><HEAD> <META HTTP-EQUIV='REFRESH' CONTENT='24; URL=https://gigpay.ru/controllers/ajax/user/bot/1991.php'> </HEAD><?php
 


 }

else
	
{ $us_im = $game->getImInf($user_vkomn);
	if    ($user_vkomn  == $user_id) {
    if    ($sum <= $vichi) {
     if    ($sum >= $room_inf['min_bet']) {
        if ($user_data['balance'] >= $sum) {

            // Lottery ID
            $lot = $game->getLottery($room);
            $lot_id = $lot['id'];

            // Update Lottery Finish
            $finish_is = $lot['finish'];

            if ($finish_is != 0) {
                if ($time >= $finish_is) {
                    $bet = false;
                }
            }

            if ($bet) {
                // Select User Info
                $user_id = $user_data['id'];
                $photo = $user_data['photo_100'];
                $screen_name = $user_data['screen_name'];

                $sum_per = $game->returnSum($sum);

                // Update Lottery Bank
                $game->updateLotteryBank($lot_id, $sum_per, $sum);

                $lottery = $game->getLottery($room);
                $lot_bank = $lottery['bank'];

                // Insert and Update User Bank
                if ($game->getLotteryUserCount($lot_id, $user_id) > 0) {
                    $game->updateUserBank($lot_id, $user_id, $sum);
                } else {
                    $game->insertUsersLottery($lot_id, $room, $user_id, $sum, $lot_bank, $ipp);
                }

                // Update PR
                $game->updatePR($lot_id, $lot_bank);
                $game->setUserBalance($user_id, $sum);
                $game->setUserIgr_ko($user_id, $igr_ko);
				$game->instorUsersLot($user_id, $sum,$balans_usera,$comment,$room,$lot_bank, $date_op);
				$game->insDjek($dg, $rodg);
				$game->insDjeki($dg2, $rodg2);
                $count_users = $game->getUsersCount($lot_id);

                if ($count_users >= $kolvo && $finish_is == 0) {
                    $finish = mktime(date("H"), date("i"), date("s") + $dindon);
                    $game->setFinishLottery($lot_id, $finish);
                }

               ?><HEAD> <META HTTP-EQUIV='REFRESH' CONTENT='19; URL=https://gigpay.ru/controllers/ajax/user/bot/1991.php'> </HEAD><?php

            } else ?><HEAD> <META HTTP-EQUIV='REFRESH' CONTENT='18; URL=https://gigpay.ru/controllers/ajax/user/bot/1991.php'> </HEAD><?php

        } else ?><HEAD> <META HTTP-EQUIV='REFRESH' CONTENT='21; URL=https://gigpay.ru/controllers/ajax/user/bot/1991.php'> </HEAD><?php

    } else ?><HEAD> <META HTTP-EQUIV='REFRESH' CONTENT='26; URL=https://gigpay.ru/controllers/ajax/user/bot/1991.php'> </HEAD><?php
 
 } else  ?><HEAD> <META HTTP-EQUIV='REFRESH' CONTENT='17; URL=https://gigpay.ru/controllers/ajax/user/bot/1991.php'> </HEAD><?php

} else echo status('err', 'Внимание! Пользователь ' .$us_im['screen_name'] . ' уже поставил ставку c таким же ip  адресом - пройдите в другую комнату или подключитесь к другой сети Wi-Fi');
	
	
	

 
 
 



         

}
 

}