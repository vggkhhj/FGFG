<?php
/**
 * Блок страницы
 * Предоставляет базовый функционал блока страницы
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage kernel
 */
abstract class BlockAbstract{
  protected $controllerRef = null;
  
  public function __construct(&$controller){
    $this->controllerRef = $controller;
    $this->main();
  }
  
  abstract public function main();
}