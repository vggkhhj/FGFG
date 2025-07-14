<?php
//TODO: переделать запросы под класс DB
  class ac_pager
  {
    public $recordsPerPage; //кол-во записей на странице
    public $currentPage; //здесь будем хранить текущую страницу
    public $pagesCnt; // здесь будем хранить кол-во страниц
    public $recordsCnt; //здесь будем хранить кол-во записей, попавших под выборку
    public $zebra; //при выводе четного ряда - true, нечетного false
    
    private $query; //запрос
    private $queryQ;
    private $recordsReturned;
    private $db;
    
    public function __construct($queryWillBe = false)
    {
      $this->setRecordsPerPage();
      $this->updateFromBase();
      $this->setQuery($queryWillBe);
      $this->setPagesCnt();
      $this->setCurrentPage();
      $this->setZebra();
      $this->recordsReturned = 0;
      $this->db = new DB(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    }
    
    public function setZebra($zebraWillBe = false)
    {
      $this->zebra = $zebraWillBe;
    }
    
    public function setRecordsPerPage($recordsWillBe = 10)
    {
      $this->recordsPerPage = $recordsWillBe;
      if ($this->recordsCnt != 0 && $this->recordsPerPage != 0)
        $this->setPagesCnt(ceil($this->recordsCnt/$this->recordsPerPage));
      else
        $this->setPagesCnt(0);
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
      $this->setZebra(false);
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
    
    public function getRow()
    {
      if ($this->query)
      {
        if (($this->recordsReturned < $this->recordsPerPage) && ((($this->currentPage-1)*$this->recordsPerPage+$this->recordsReturned) < $this->recordsCnt))
        {
          if ($this->zebra)  $this->setZebra(false); else $this->setZebra(true);
          $this->recordsReturned++;
          DB::data_seek($this->queryQ, ($this->currentPage - 1)*$this->recordsPerPage + $this->recordsReturned - 1);
          return DB::fetch_assoc($this->queryQ);
        } else
        {
          $this->recordsReturned = 0;
          return false;
        }
      } else
      return false;
    }
  }
