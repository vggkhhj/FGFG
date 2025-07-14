<?php
/**
 * Класс пейджер
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage classes
 */
  class Pager{
    public $limits = Array(5, 10, 20);
    const ARG_LIMIT = 'pageLimit';
    const ARG_PAGE_NUM = 'pageNumber';
    private $pageSize;
    private $submitAddress;
    private $rowCount;
    private $pageCount;
    private $thisPageNum;
    private $prevPageNum;
    private $nextPageNum;
    private $startRow;
    private $endRow;
    private $rangeStartPage;
    private $rangeSize;
    private $lastRangePage;
    
    private $gotSubmit = 0;
    
    public function __construct($limits = 0, $iDoInit = false, $iRowCount = 0){
      if(!empty($limits))
        $this->limits = $limits;
      if($iDoInit) $this->init($iRowCount);
    }
    
    public function setSubmitAddress($iSubmitAddress){
      $this->submitAddress = $iSubmitAddress;
    }
    
    public function getPageSubmit(){
      //количество элементов на одной странице
      if(isset($_GET[self::ARG_LIMIT]))
        $this->pageSize = intval($_GET[self::ARG_LIMIT]);
      if(!in_array($this->pageSize, $this->limits))
        $this->pageSize = $this->limits[0];
      //номер текущей страницы
      if(isset($_GET[self::ARG_PAGE_NUM]) && @intval($_GET[self::ARG_PAGE_NUM])>0)
        $this->thisPageNum = intval($_GET[self::ARG_PAGE_NUM]);
      else 
        $this->thisPageNum = 1;
      //if($this->thisPageNum > $this->pageCount) $this->thisPageNum = $this->pageCount-1;
      //начальная и конечная строки на текущей странице
      $this->startRow = $this->thisPageNum * $this->pageSize - $this->pageSize;// + 1;
      $this->endRow = $this->startRow + $this->pageSize;
      //********выдавать по 10 страниц за раз
      //сразу надо проверить, наберется ли страниц до конца десятка
      //пробуем выбрать из базы количество рядов, необходимых для завершения десятка страниц:
      //находим ближайший десяток
      for($i=0;$i<10;$i++)
        if( ($this->thisPageNum + $i) % 10 == 0 )
        {
          $rangeEndPage = $this->thisPageNum + $i;
          break;
        }
      $this->rangeSize = $rangeEndPage * $this->pageSize - $this->startRow;//необходимое количество рядов до конца десятка
      $this->rangeStartPage = $rangeEndPage - 9 > 0 ? $rangeEndPage - 9 : 1;
      $this->gotSubmit = 1;
    }
    
    public function getRangeSize(){
      return $this->rangeSize;
    }
    
    public function init($iRowCount){
      if(!$this->gotSubmit) $this->getPageSubmit();
      $this->submitAddress = isset($iSubmitAddress) ? $iSubmitAddress : $_SERVER['SCRIPT_NAME'];
      $this->rowCount = $iRowCount;//сколько рядов передано
      $actualRowCount = $this->rowCount<$this->rangeSize ? $this->rowCount : $this->rangeSize;//реально используемое количество рядов - до конца диапазона
      $this->pageCount = $actualRowCount>1 ? ceil($actualRowCount/$this->pageSize) : 1;//количество страниц рассчитывается исходя из реального количества рядов
      $this->lastRangePage = $this->thisPageNum + $this->pageCount - 1;
      $this->prevPageNum = $this->thisPageNum - 1;
      $this->nextPageNum = $this->thisPageNum + 1;
      
      $out = Array();
      //помещаем ... перед ярлыками страниц
      if($this->hasPrevPage($this->rangeStartPage))
          $out['prev_range'] = Array('_GET'=>$this->generatePageLink( Array(self::ARG_PAGE_NUM => $this->rangeStartPage - 1) ), '_SAVE_GET'=>true);
      //сами ярлыки страниц
      $out['shortcuts'] = Array();
      for($i = $this->rangeStartPage; $i <= $this->lastRangePage; $i++)
        if($this->thisPageNum != $i)
          $out['shortcuts'][$i] = Array('_GET'=>$this->generatePageLink( Array(self::ARG_PAGE_NUM => $i) ), '_SAVE_GET'=>true);
        else
          $out['shortcuts'][$i] = $i;
      //помещаем ... после ярлыков страниц
      if($this->hasNextPage($this->lastRangePage))
          $out['next_range'] = Array('_GET'=>$this->generatePageLink( Array(self::ARG_PAGE_NUM => $this->lastRangePage + 1) ), '_SAVE_GET'=>true);
      //следующая-предыдущая страницы
      if($this->hasPrevPage())
        $out['prev_page'] = Array('_GET'=>$this->getPageLink("prev") , '_SAVE_GET'=>true);
      if($this->hasNextPage())
        $out['next_page'] = Array('_GET'=>$this->getPageLink("next") , '_SAVE_GET'=>true);
      //прочие нужные данные
      //ссылки на смену пределов
      $out['limits'] = Array();
      foreach($this->limits as $lim)
        $out['limits'][$lim] = Array('_GET'=>$this->generatePageLink(Array(self::ARG_LIMIT=>$lim, self::ARG_PAGE_NUM=>1)) , '_SAVE_GET'=>true);
  
      return $out;
    }
    
    public function getRowCount(){
      return $this->rowCount;
    }
    public function getPageSize(){
      return $this->pageSize;
    }
    public function getStartRow(){
      return $this->startRow;
    }
    public function getEndRow(){
      return $this->endRow;
    }
    public function getPageLink($iPageOrder){
      $page = Array(self::ARG_PAGE_NUM => $this->{$iPageOrder . "PageNum"});
      return $this->generatePageLink( $page );
    }
    public function generatePageLink($arg){
      return Array(
        self::ARG_PAGE_NUM => ( !empty($arg[self::ARG_PAGE_NUM]) ? intval($arg[self::ARG_PAGE_NUM]) : $this->thisPageNum ) ,
        self::ARG_LIMIT => ( !empty($arg[self::ARG_LIMIT]) ? intval($arg[self::ARG_LIMIT]) : $this->pageSize )
      );
    }
    //определяют наличие предыдущей-следующей страницы в пределах текущего диапазона (десятка)
    public function hasNextPage($pageNum = 0){
      $hasNextPage = false;
      $pageNum = $pageNum>0 ? $pageNum : $this->thisPageNum;
      //если не последняя в диапазоне - то точно есть следующая
      if($pageNum < $this->lastRangePage) $hasNextPage = true;
      //если последняя в диапазоне, и диапазон закончился на 0 - то тоже должна быть следующая
      else
        if( ($pageNum) % 10 == 0 ) $hasNextPage = true;
      return $hasNextPage;
    }
    public function hasPrevPage($pageNum = 0){
      $pageNum = $pageNum>0 ? $pageNum : $this->thisPageNum;
      return ($pageNum - 1 > 0) ? true : false;
    }
    public function getSubmitAddress(){
      return $this->submitAddress;
    }
    public function printPager($iLinkTempl, $iPlainTempl){
      $out = "";
      //помещаем ... перед ярлыками страниц
      if($this->hasPrevPage($this->rangeStartPage))
          $out .= sprintf(
            '<a href="%s">...</a> &nbsp;', 
            $this->generatePageLink( Array(self::ARG_PAGE_NUM => $this->rangeStartPage - 1) )
          );
      //сами ярлыки страниц
      for($i = $this->rangeStartPage; $i <= $this->lastRangePage; $i++)
        if($this->thisPageNum != $i)
          $out .= sprintf($iLinkTempl, $this->generatePageLink( Array(self::ARG_PAGE_NUM => $i) ), $i);
        else
          $out .= sprintf($iPlainTempl, $i);
      //помещаем ... после ярлыков страниц
      if($this->hasNextPage($this->lastRangePage))
          $out .= sprintf(
            '<a href="%s">...</a> &nbsp;', 
            $this->generatePageLink( Array(self::ARG_PAGE_NUM => $this->lastRangePage + 1) )
          );
      return $out;
    }
    
    
  }