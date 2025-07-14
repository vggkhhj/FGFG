<?php
/**
 * Конструктор SELECTов
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage classes
 */
class MysqlSelect{
    protected $mask = Array(
      'select' => ' SELECT %s ',
      'from' => ' FROM %s ',
      'where' => ' WHERE %s ',
      'group' => ' GROUP BY %s ',
      'order' => ' ORDER BY %s ',
      'limit' => ' LIMIT %s '
    );
    
    protected $query = Array(
      'select' => '',
      'from' => '',
      'where' => '',
      'group' => '',
      'order' => '',
      'limit' => ''
    );
    
    public function __construct($from = '', $where = ''){
      if(!empty($from))
        $this->from($from);
      if(!empty($where))
        $this->where($where);
    }
    
    public function select($str){
      $this->query['select'] = sprintf($this->mask['select'], $str);
    }
    
    public function from($str){
      $this->query['from'] = sprintf($this->mask['from'], $str);
    }
    
    public function where($str){
      $this->query['where'] = sprintf($this->mask['where'], $str);
    }
    
    public function order($str){
      $this->query['order'] = sprintf($this->mask['order'], $str);
    }
    
    public function group($str){
      $this->query['group'] = sprintf($this->mask['group'], $str);
    }
    
    public function limit($str, $str2 = ''){
      $this->query['limit'] = sprintf($this->mask['limit'], $str . ( empty($str2) ? '' : ',' . $str2));
    }
    
    public function get(){
    	$result =implode(' ', $this->query); 
    	if(DEBUG_MODE)
    	  var_dump($result);
      return $result;
    }
}