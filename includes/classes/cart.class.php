<?php
/**
 * Корзина
 * @author Vadox <vadox.k@yandex.ru>
 * @copyright 2013 Vadox
 * @package C4MS
 * @subpackage classes
 */
class Cart{
    /*
     * Массив с товарами
     */
    protected $orderList;
    /*
     * Переменная для проверки существования в ордер листе товара
     */
    protected $alreadyExists = -1;
    /*
     * Таблица с товарами
     */
    protected $db = null;

    function __construct(){
        @session_start();
		$this->tableName = 'catalog_offers';
        $this->db = new DB(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        //Проверяем сессию, формируем ордерлист.
        if (!empty($_SESSION['orderList']))
            $this->orderList = $_SESSION['orderList'];
        else
            $this->orderList = Array();
    }
    /*
     * устанавливаем сессию
     */
    private function setOrderList(){
        $_SESSION['orderList'] = $this->orderList;
    }
    /*
     * функция для добавления нового товара в ордерлист
     */
    public function addNewPurchase($itemId, $count){
        $this->findInKey($itemId);
        if ($this->alreadyExists === -1) {
            $item_good = $this->db->select_array_row("SELECT `co`.*,
			`cp`.`direction_id`,
			`cp`.`image` as `image`, `cv`.`title` as vendor 
			FROM `catalog_offers` `co`
			LEFT JOIN `catalog_products` cp ON cp.id = co.product_id
            LEFT JOIN `catalog_vendors` cv ON cv.id = cp.vendor_id
			WHERE `co`.`id`=".$itemId);
            $cat = $this->db->select_result("SELECT alias FROM `catalog_directions` WHERE id=".$item_good['direction_id']);
            $order_array = array();
			
            //заполняем поля
            $order_array['id'] = $item_good['id'];
            $order_array['title'] = stripslashes($item_good['title']);
			$order_array['vendor'] = stripslashes($item_good['vendor']);
			//$order_array['brand'] = stripslashes($item_good['brand']);
			//$order_array['additional'] = stripslashes($item_good['additional']);
            $order_array['image'] = $item_good['image'];
            $order_array['count'] = $count;
			
			/*if($item_good['action']&&!empty($item_good['new_price']))
				$order_array['price'] = $item_good['new_price'];
            else*/
			$order_array['price'] = $item_good['price'];
			
            $order_array['url'] = HREF_DOMAIN.$cat."/".$item_good['alias']."/";		
			$this->orderList[$itemId] = $order_array;
        } else {
            $this->purchasePlus($itemId,$count);
        }
        $this->setOrderList();
    }
    /*
     * Прибавение одной единицы товара к уже зарегистрированной в ордерлисте
     */
    public function purchasePlus($id,$count=1){
        if ($this->findInKey($id) !== -1)
            if (isset($this->orderList[$this->alreadyExists]))
                $this->orderList[$this->alreadyExists]['count']+=$count;
        $this->setOrderList();
    }
    /*
     * Уменьшение одной единицы товара от уже зарегистрированного товара в ордерлисте
     */
    public function purchaseMinus($id){
        if ($this->findInKey($id) !== -1)
            if ($this->orderList[$this->alreadyExists]['count'] > 0)
                $this->orderList[$this->alreadyExists]['count']--;
        $this->setOrderList();
    }
    /*
     * функция удаления товара из ордерлиста
     */
    public function purchaseRemove($id) {
        if($this->findInKey($id) !== -1) unset($this->orderList[$this->alreadyExists]);
        $this->setOrderList();
    }
    /*
     * функция проверки существования товара с заданной айдихой в ордерлисте
     */
    function findInKey($id){
        if (count($this->orderList) != 0)
            foreach ($this->orderList as $key => $value)
				if ($key ==$id) $this->alreadyExists = $key;
        return $this->alreadyExists;
    }
    /*
     * получить общую сумму
     */
    public function getFullPrice(){
        $fullprice = 0;
        foreach($this->orderList as $key => $value){
            $fullprice += $value['price'] * $value['count'];
        }
        return $fullprice;
    }
    /*
     * получить общее количество
     */
    public function getFullCount() {
        $count = 0;
        foreach($this->orderList as $key => $value){
            $count += $value['count'];
        }
        return $count;
    }
    /*
     * получить текущий ордерлист
     */
    function getOrderList(){
        return $this->orderList;
    }
}