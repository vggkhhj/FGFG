<?php
/**
 * Таймер для замера скорости выполнения операций
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage classes
 */
class OperationTimer{
  private $startTime;
  private $endTime;
  private $lastMicroseconds;
  
  public function operationStart(){
    $this->startTime = microtime(true);
  }
  
  public function operationEnd(){
    $this->endTime = microtime(true);
    $this->lastMicroseconds = $this->endTime - $this->startTime;
  }
  
  public function getLastTime(){
    return round($this->lastMicroseconds, 5);
  }
}