<?php
/**
 * Класс-пейджер
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage classes
 */
  class ac_pager
  {
    public $recordsPerPage; //кол-во записей на странице
    public $currentPage; //здесь будем хранить текущую страницу
    public $pagesCnt; // здесь будем хранить кол-во страниц
    public $recordsCnt; //здесь будем хранить кол-во записей, попавших под выборку
    
    private $query; //запрос
    private $queryQ;
    private $recordsReturned;
    private $tempArray;
    
    public function __construct($queryWillBe = false)
    {
      $this->setRecordsPerPage();
      $this->updateFromBase();
      $this->setQuery($queryWillBe);
      $this->setPagesCnt();
      $this->setCurrentPage();
      $this->recordsReturned = 0;
      $this->db = new DB(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    }
    
    public function setRecordsPerPage($recordsWillBe = 10)
    {
      $this->recordsPerPage = $recordsWillBe;
      $this->setPagesCnt(ceil($this->recordsCnt/$this->recordsPerPage));
    }
    
    public function setPagesCnt($pagesCntWillBe = 0)
    {
      $this->pagesCnt = $pagesCntWillBe;
    }
    
    public function setCurrentPage($currentPageWillBe = 1)
    {
      if (@$currentPageWillBe > 0)
        $this->currentPage = $currentPageWillBe;
      else $this->currentPage = 1;
    }
    
    public function updateFromBase()
    {
      $this->setQuery($this->query);
    }
    
    public function setQuery($queryWillBe = false)
    {
      if ($queryWillBe)
      {
        $this->query = $queryWillBe;
        $this->queryQ = $this->db->query($this->query);
        $this->recordsCnt = $this->db->num_rows($this->queryQ);
        $this->setPagesCnt(ceil($this->recordsCnt/$this->recordsPerPage));
      }
    }
    
    public function getRow($back = false)
    {
      if ($this->query)
      {
        if (($this->recordsReturned < $this->recordsPerPage) && ((($this->currentPage-1)*$this->recordsPerPage+$this->recordsReturned) < $this->recordsCnt))
        {
          $this->recordsReturned++;
          DB::data_seek($this->queryQ, ($this->currentPage - 1)*$this->recordsPerPage + $this->recordsReturned - 1);
          $this->tempArray = DB::fetch_assoc($this->queryQ);
          if ($back === true) {
            $this->recordsReturned--;
            DB::data_seek($this->queryQ, ($this->currentPage - 1)*$this->recordsPerPage + $this->recordsReturned);
          }
          return $this->tempArray;
        } else
        {
          $this->recordsReturned = 0;
          return false;
        }
      } else
      return false;
    }
  }
