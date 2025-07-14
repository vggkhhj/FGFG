<?php
/**
 * Следит за активностью пользователя
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage classes
 */
class UserWatch{
  protected static $tableName = 'user_watch';
  protected static $addrFieldName = 'remote_addr';

  
  public static function canActAgain($fieldName, $secondsPassed, $remoteAddr = ''){
    $db = new DB(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    $remoteAddr = empty($remoteAddr) ? $_SERVER['REMOTE_ADDR'] : $remoteAddr;
    $now = time();
    //найти такой айпи в базе
    $res = $db->query(
      sprintf(
        "SELECT * FROM `%s` WHERE `%s`='%s' ",
        self::$tableName, self::$addrFieldName, $remoteAddr
      )
    );
    if(@$db->num_rows($res)>0){
      //если айпи есть - обновить последнюю попытку доступа
      $db->query(
        sprintf(
          "UPDATE `%s` SET `%s`=%d WHERE `%s`='%s' ",
          self::$tableName, $fieldName, $now, self::$addrFieldName, $remoteAddr
        )
      );
      //проверить собственно прошедшее время
      $fa = $db->fetch_array($res);
      if($now > intval($fa[$fieldName]) + $secondsPassed)
        return true;
      else
        return false;
    }
    else{
      //если айпи нет - записать новый, установить последнюю попытку доступа
      $db->query(
        sprintf(
          "INSERT INTO `%s` SET `%s`='%s', `%s`='%s' ",
          self::$tableName, self::$addrFieldName, $remoteAddr, $fieldName, $now
        )
      );
      //если первая запись айпи - всегда позволять доступа
      return true;
    }
  }
}