<?php
// include './logger.php';[id10]
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



class game extends db
{

    /* MAKE BET */
    /**
     * @param $room
     * @return array|null
     */
    function getLottery($room)
    {
        $this->Query("SELECT * FROM lottery WHERE room = '{$room}' AND status = '1'");
        return $this->FetchArray();
    }
	/**
     * @param $room
     * @return array|null
     */
    function getIpInf($room,$ipp)
    {
        $this->Query("SELECT * FROM lottery_users WHERE ipp = '{$ipp}' AND room = '{$room}'");
      return $this->FetchArray();
    }
	/**
     * @param $room
     * @return array|null
     */
    function getImInf($user_vkomn)
    {
        $this->Query("SELECT * FROM users WHERE id = '{$user_vkomn}' ");
      return $this->FetchArray();
    }
 /**
     * @param $room
     * @return array|null
     */
    function getUserSumInf($room, $user_id)
    {
        $this->Query("SELECT * FROM lottery_users WHERE user_id = '{$user_id}' AND room = '{$room}'");
      return $this->FetchArray();
    }
    /**
     * @param $room
     * @return array|null
     */
    function getRoomInf($room)
    {
        $this->Query("SELECT * FROM packages WHERE id = '{$room}'");
        return $this->FetchArray();
    }

    /**
     * @param $id
     * @param $finish
     */
    function setFinishLottery($id, $finish)
    {
        $this->Query("UPDATE lottery SET finish = '{$finish}' WHERE id = '{$id}' AND status = '1'");
    
	}

    /**
     * @param $id
     * @return int
     */
    function getUsersCount($id)
    {
        $this->Query("SELECT * FROM lottery_users WHERE lot_id = '{$id}'");
        return $this->NumRows();
    }

    /**
     * @param $id
     * @param $sum
     */
    function setUserBalance($id, $sum)
    {
        $this->Query("UPDATE users_conf SET balance = balance - '{$sum}' WHERE user_id = '{$id}'");
    }
      /**
     * @param $id
     * @param $sum
     */
    function setUserIgr_ko($id, $igr_ko)
    {
        $this->Query("UPDATE users_conf SET igr_ko = igr_ko + '{$igr_ko}' WHERE user_id = '{$id}'");
    }
    /**
     * @param $id
     * @return array|null
     */
    function getUserBalance($id)
    {
        $this->Query("SELECT * FROM users_conf WHERE user_id = '{$id}'");
        return $this->FetchArray();
    }

    /**
     * @param $id
     * @param $room
     * @param $user_id
     * @param $photo
     * @param $screen_name
     * @param $user_pr
     * @param $sum
     */
    function insertUsersLottery($id, $room, $user_id, $sum, $bank , $ipp)
    {
        $user_pr = $sum / $bank * 100;
        $this->Query("INSERT INTO lottery_users (lot_id, room, user_id, pr, sum, ipp) VALUES ('{$id}', {$room},'{$user_id}','{$user_pr}', '{$sum}','{$ipp}')");
    }
     /*запись в историю!*
     * @param $id
     * @param $room
     * @param $user_id
     * @param $photo
     * @param $screen_name
     * @param $user_pr
     * @param $sum
     */
    function instorUsersLot($user_id, $sum,	$balans_usera ,$comment,$room,$lot_bank, $date_op)
    {
        
        $this->Query("INSERT INTO history_users (user_id, sum,	balans_usera,comment,room,bank, date_op) VALUES ('{$user_id}', {$sum},{$balans_usera},'{$comment}','{$room}', '{$lot_bank}', '{$date_op}')");
    }
    /**
     * @param $id
     * @param $sum_per
     * @param $sum
     */
    function updateLotteryBank($id, $sum_per, $sum)
    {
        $this->Query("UPDATE lottery SET sum = sum + '{$sum_per}', bank = bank + {$sum} WHERE id = '{$id}' AND status = '1'");
    }

    /**
     * @param $id
     * @param $bank
     */
    function updatePR($id, $bank)
    {
        $this->Query("SELECT * FROM lottery_users WHERE lot_id = {$id}");
        $users = $this->FetchAll();
        foreach ($users as $user) {
            $user_id = $user['user_id'];
            $user_p = $user['sum'] / $bank * 100;
            $this->Query("UPDATE lottery_users SET pr = '{$user_p}' WHERE lot_id = '{$id}' AND user_id = '{$user_id}'");
        }

    }

    /**
     * @param $id
     * @param $user_id
     * @return int
     */
    function getLotteryUserCount($id, $user_id)
    {
        $this->Query("SELECT * FROM lottery_users WHERE lot_id = {$id} AND user_id = '{$user_id}'");
        return $this->NumRows();
    }

	/**
     * @param $id
     * @param $user_id
     * @param $sum
     */
    function insDjeki($dg2, $rodg2)
    {
        $this->Query("UPDATE lottery SET bank = bank + '{$dg2}' WHERE room = '{$rodg2}' ");
    }

	/**
     * @param $id
     * @param $user_id
     * @param $sum
     */
    function insDjek($dg, $rodg)
    {
        $this->Query("UPDATE lottery SET sum = sum + '{$dg}' WHERE room = '{$rodg}' ");
    }

    /**
     * @param $id
     * @param $user_id
     * @param $sum
     */
    function updateUserBank($id, $user_id, $sum)
    {
        $this->Query("UPDATE lottery_users SET sum = sum + '{$sum}' WHERE lot_id = '{$id}' AND user_id = '{$user_id}'");
    }

    /**
     * @param $sum
     * @return mixed
     */
    function returnSum($sum)
    {
        $sumComm = $sum - ($sum * 0.2);
        return $sumComm;
    }

    /* BET ONLINE */

    /**
     * @return int
     */
    function getCountLottery()
    {
        $this->Query("SELECT * FROM lottery WHERE status = '1'");
        return $this->NumRows();
    }

    /**
     * @return array
     */
    function getAllLottery()
    {
        $this->Query("SELECT * FROM lottery WHERE status = '1'");
        return $this->FetchAll();
    }

    /**
     * @param $id
     * @return array
     */
    function getUsersList($id)
    {
        $users = array();
        $this->Query("SELECT * FROM lottery_users WHERE lot_id = '{$id}'");
        if ($this->NumRows() > 0) {
            $lot = $this->FetchAll();
            foreach ($lot as $item) {
                $this->Query("SELECT * FROM users WHERE id = '{$item['user_id']}'");
                $user = $this->FetchArray();
                $users[$item['user_id']] = array("photo" => $user['photo_100'], "pr" => $item['pr'], "screen_name" => $user['screen_name'], "sum" => $item['sum']);
            }
        }

        return $users;
    }

    /**
     * @return array
     */
    function getNewUsers()
    {
        $new_users = array();
        $this->Query("SELECT * FROM auth ORDER BY id DESC LIMIT 50");
        if ($this->NumRows() > 0) {
            $us = $this->FetchAll();
            foreach ($us as $use) {
                $this->Query("SELECT * FROM users WHERE id = '{$use['user_id']}'");
                if ($this->NumRows() > 0) {
                    $user = $this->FetchArray();
                    $new = array("screen_name" => $user['screen_name'],
                        "photo" => $user['photo_100'],
                        "vk_id" => $user['uid']);

                    $new_users[$use['id']] = $new;
                }

            }
        }
        return $new_users;
    }

    /**
     * @param $users
     * @param $pr
     * @return bool|null
     */
    public function winner_rand($users, $pr)
    {
        if (!is_array($users) || count($users) < 1) return false;
        $sum = 0;
        $result = null;
        do {
            foreach ($users as $i => $data) {
                $sum += $pr[$i];
                if (rand(0, $sum) < $pr[$i]) {
                    $result = $data;
                }
            }
        } while (is_null($result));
        return $result;
    }

    /**
     * @param $users
     * @return mixed
     */
    function randwin($users)
    {
        shuffle($users);
        $input = $users;
        $rand_keys = array_rand($input, 1);

        $winner_id = $input[$rand_keys];
        return $winner_id;
    }

    /**
     * @param $id
     * @return bool|null
     */
    public function getWinner($id)
    {
        $win = array("id" => array(), "pr" => array());
        $this->Query("SELECT * FROM lottery_users WHERE lot_id = '{$id}'");
        if ($this->NumRows() > 0) {
            $lotte = $this->FetchAll();
            $num = 0;
            foreach ($lotte as $item) {
                array_push($win["id"], $item['user_id']);
                array_push($win["pr"], $item['pr']);
                $num++;
            }
        }

        return $this->winner_rand($win["id"], $win["pr"]);
//        return $this->randwin($win["id"]);
    }

    /**
     * @param $id
     * @return int
     */
    public function winCount($id)
    {
        $this->Query("SELECT * FROM lottery_winner");
        if ($this->NumRows() > 0) {
            $this->Query("SELECT * FROM lottery_winner WHERE lot_id = '{$id}'");
            if ($this->NumRows() > 0) {
                return true;
            } else return false;
        } else return false;
    }

    /**
     * @param $id
     * @param $winner
     * @return array|null
     */
    function getLotUser($id, $winner)
    {
        $this->Query("SELECT * FROM lottery_users WHERE lot_id = '{$id}' AND user_id = '{$winner}'");
        return $this->FetchArray();
    }

    /**
     * @param $id
     * @return array|null
     */
    function getWinUser($id)
    {
        $this->Query("SELECT * FROM lottery_winner WHERE lot_id = '{$id}'");
        return $this->FetchArray();
    }

    /**
     * @param $id
     * @return array|null
     */
    function getUserInfo($id)
    {
        $this->Query("SELECT * FROM users WHERE id = '{$id}'");
        return $this->FetchArray();
    }

    /**
     * @param $room
     */
    function addLottery($room)
    {
        $this->Query("SELECT * FROM lottery WHERE room = '{$room}' AND status = '1'");
        $count = $this->NumRows();
        if ($count == 0) {
            $this->Query("INSERT INTO lottery (room) VALUES ('{$room}')");
        }
    }

    /**
     * @param $id
     */
    function addStat($id)
    {
        $this->Query("SELECT * FROM lottery WHERE id = '{$id}' AND status = '2'");
        $count = $this->NumRows();
        if ($count != 0) {
            $this->Query("UPDATE stats SET lottery = lottery + 1 WHERE id = '1'");
        }
    }

    function checkLottery($id)
    {
        $this->Query("SELECT * FROM lottery WHERE id = '{$id}'");
        if($this->NumRows() > 0){
            return true;
        } else return false;
    }

    /**
     * @param $id
     */
    function endLottery($id)
    {
        $this->Query("SELECT * FROM lottery WHERE id = '{$id}' AND status = '2'");
        $count = $this->NumRows();
        if ($count == 0) {
            $this->Query("UPDATE lottery SET status = '2' WHERE id = '{$id}'");
        }
    }

    /**
     * @param $id
     * @param $comm
     * @param $user_id
     */
    function loseGame($id, $comm, $user_id)
    {
        $time = time();
        $this->Query("SELECT * FROM lottery_users WHERE lot_id = '{$id}'");
        if ($this->NumRows() > 0) {
            $lot = $this->FetchAll();
            foreach ($lot as $item) {
                if ($item['user_id'] != $user_id) {
                    $this->Query("INSERT INTO history (user_id, sum, type, comment, date_op) VALUES ('{$item['user_id']}','{$item['sum']}','1','Ставка в лотерее','{$time}')");
                    $this->Query("SELECT * FROM users_ref WHERE user_id = '{$item['user_id']}'");
                    $user_data = $this->FetchArray();
                    if ($user_data['ref_1'] != "0") {
                        $ref_1 = ($comm * 0.02);
                        $this->Query("UPDATE users_conf SET balance = balance + '{$ref_1}' WHERE user_id = '" . $user_data['ref_1'] . "'");
                        $this->Query("UPDATE users_ref SET to_ref_1 = to_ref_1 + '{$ref_1}' WHERE id = '{$item['user_id']}'");
                    }
                    if ($user_data['ref_2'] != "0") {
                        $ref_2 = ($comm * 0.01);
                        $this->Query("UPDATE users_conf SET balance = balance + '{$ref_2}' WHERE user_id = '" . $user_data['ref_2'] . "'");
                        $this->Query("UPDATE users_ref SET to_ref_2 = to_ref_2 + '{$ref_2}' WHERE id = '{$item['user_id']}'");
                    }
                    if ($user_data['ref_3'] != "0") {
                        $ref_3 = ($comm * 0.001);
                        $this->Query("UPDATE users_conf SET balance = balance + '{$ref_3}' WHERE user_id = '" . $user_data['ref_3'] . "'");
                        $this->Query("UPDATE users_ref SET to_ref_3 = to_ref_3 + '{$ref_3}' WHERE id = '{$item['user_id']}'");
                    }
                }
            }
        }
    }

    public function clean()
    {
        $this->lotteryUsersClean();
        $this->lotteryClean();
        $this->authClean();
        $this->historyClean();
        $this->bonusClean();
        $this->insertPaymentsClean();
    }

    private function lotteryUsersClean()
    {
        $this->Query("SELECT * FROM lottery_users");
        $lottery = $this->FetchAll();
        foreach ($lottery as $item) {
            $lotID = $item['lot_id'];
            $this->Query("SELECT status FROM lottery WHERE id = '{$lotID}'");
            $status = $this->FetchRow();
            if ($status == 2) {
                $this->Query("DELETE FROM lottery_users WHERE lot_id = '{$lotID}'");
            }
        }
    }

    private function lotteryClean()
    {
        $this->Query("SELECT * FROM lottery WHERE status = '2'");
        if ($this->NumRows() > 0) {
            $this->Query("DELETE FROM lottery WHERE status = '2'");
        }
    }

    private function authClean()
    {
        $time = time() - 604800;
        $this->Query("SELECT * FROM auth WHERE time < '{$time}'");
        if ($this->NumRows() > 0) {
            $this->Query("DELETE FROM auth WHERE time < '{$time}'");
        }
    }

    private function historyClean()
    {
        $time = time() - 604800;
        $this->Query("SELECT * FROM history WHERE date_op < '{$time}'");
        if ($this->NumRows() > 0) {
            $this->Query("DELETE FROM history WHERE date_op < '{$time}'");
        }
    }

    private function bonusClean()
    {
        $time = time() - 172800;

        $this->Query("SELECT * FROM bonus WHERE date_add < '{$time}'");
        if ($this->NumRows() > 0) {
            $this->Query("DELETE FROM bonus WHERE date_add < '{$time}'");
        }
    }

    private function insertPaymentsClean()
    {
        $time = time() - 172800;

        $this->Query("SELECT * FROM inserts WHERE status = '1' AND date_op < '{$time}'");
        if ($this->NumRows() > 0) {
            $this->Query("DELETE FROM inserts WHERE status = '1' AND date_op < '{$time}'");
        }

        $this->Query("SELECT * FROM inserts_ops WHERE status = '1' AND date_op < '{$time}'");
        if ($this->NumRows() > 0) {
            $this->Query("DELETE FROM inserts_ops WHERE status = '1' AND date_op < '{$time}'");
        }

        $this->Query("SELECT * FROM ins WHERE status = '3'");
        if ($this->NumRows() > 0) {
            $this->Query("DELETE FROM ins WHERE status = '3'");
        }

        $this->Query("SELECT * FROM payments WHERE status = '3'");
        if ($this->NumRows() > 0) {
            $this->Query("DELETE FROM payments WHERE status = '3'");
        }

    }

}