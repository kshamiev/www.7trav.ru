<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Cms
 */

/**
 * Покупательская корзина
 * 
 * Шаблонный класс для работы объектами системы типа отношения или расширения
 * <lo>
 * <li>Добавление товара в корзину
 * <li>Удаление товара из корзины.
 * <li>Загрузка, Перерасчет, Очистка всей корзины.
 * </lo>
 * 
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @package Cms
 * @subpackage Basket
 * @version 11.03.2010
 */
class Basket extends Obj_Relation
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Название товара
   *
   * @var string
   */
  protected $Name;
  /**
   * Цена продажи
   *
   * @var float
   */
  protected $Price;
  /**
   * Цена закупочная
   *
   * @var float
   */
  protected $PriceBase;
  /**
   * Количество
   *
   * @var integer
   */
  protected $Cnt;
  /**
   * Ссылка
   *
   * @var string
   */
  protected $Url;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Basket';
  /**
   * Покупательская корзина.
   * Служит для работы с отношениями родительского объекта.
   * 
   * @var Basket
   */
  private $_Basket = array();
  /**
   * Полная стоимость товаров в покупателькой корзине
   *
   * @var float
   */
  private $_Summa = 0;
  /**
   * Конструткор класса
   * Инициализация идентификатора объекта
   * $id - идентификатор родительского объекта
   *
   * @param integer $id
   */
  public function __construct($id)
  {
    $this->ID = $id;
  }
  public function Init_Client()
  {
    $Client = Client::Factory();
    if ( 0 == $Client->ID ) {
      return false;
    }
    if ( 0 < $this->ID && $this->ID != $Client->ID ) {
      DB::Set_Query("UPDATE Basket SET Client_ID = {$Client->ID} WHERE Client_ID = {$this->ID}");
    }
    $this->ID = $Client->ID;
    $this->Load_Basket();
    return true;
    /*
    if ( 0 < $this->ID ) {
      DB::Set_Query("UPDATE Basket SET Client_ID = {$client_id} WHERE Client_ID = {$this->ID}");
    }
    $this->ID = $client_id;
    $this->Load_Basket();
    */
  }
  /**
   * Получение объектной таблицы класса
   * 
   * @return string - имя обрабатываемой таблицы класса
   */
  public function Get_Tbl_Name()
  {
    return $this->_Tbl_Name;
  }
  /**
   * Возвращает отношения с родительским объектом
   * 
   * @return Basket
   */
  public function Get_Basket()
  {
    return $this->_Basket;
  }    
  /**
   * Возвращает сумму покупательской корзины
   * 
   * @return float - сумма покупательской корзины
   */
  public function Get_Summa()
  {
    return $this->_Summa;
  }    
  /**
   * Добавление товара в корзину
   *
   * @param integer goods_id - идентификатор товара
   * @return float - сумма корзины
   */
  public function Action_Goods_Add($goods_id)
  {
    $goods_id = intval($goods_id);
    if ( 0 == $this->ID ) {
      if ( !$this->Init_Client() ) {
        Logs::Save_File('error', 'error_init_basket.log');
        return $this->_Summa; 
      }
    }
    //
    if ( isset($this->_Basket[$goods_id]) )
    {
      $sql = "UPDATE Basket SET Cnt = Cnt + 1 WHERE Client_ID = {$this->ID} AND Goods_ID = {$goods_id}";
      DB::Set_Query($sql);
      //
      $this->_Basket[$goods_id]->Cnt++;
    }
    else
    {
      $Goods = new Goods($goods_id);
      $Goods->Load_Prop('Name', 'Url', 'PriceBase', 'Price');
      //
      $sql = "INSERT INTO Basket (
        Client_ID,
        Goods_ID,
        Name,
        PriceBase,
        Price,
        Url
      ) VALUES (
        {$this->ID},
        {$Goods->ID},
        " . s($Goods->Name) . ",
        " . f($Goods->PriceBase) . ",
        " . f($Goods->Price) . ",
        " . s($Goods->Url) . "
      )";
      DB::Set_Query($sql);
      //
      $Basket = new self($goods_id);
      $Basket->Name = $Goods->Name;
      $Basket->Url = $Goods->Url;
      $Basket->PriceBase = $Goods->PriceBase;
      $Basket->Price = $Goods->Price;
      $Basket->Cnt = 1;
      $this->_Basket[$goods_id] = $Basket;
    }
    $this->_Summa+= $this->_Basket[$goods_id]->Price;
    return $this->_Summa;
  }
  /**
   * Удаление товаров из корзины
   *
   * @param array $goods_list
   */
  public function Action_Goods_Remove()
  {
    if ( !isset($_POST['goods_list_rem']) ) return 'Выберите товар для удаления из корзины';
    foreach ($_POST['goods_list_rem'] as $goods_id)
    {
      $goods_id = intval($goods_id);
      $sql = "DELETE FROM Basket WHERE Client_ID = {$this->ID} AND Goods_ID = {$goods_id}";
      DB::Set_Query($sql);
      //
      $this->_Summa-= $this->_Basket[$goods_id]->Price * $this->_Basket[$goods_id]->Cnt;
      unset($this->_Basket[$goods_id]);
    }
  }
  /**
   * Перерасчет корзины
   *
   * @param array $goods_list
   */
  public function Action_Reorder()
  {
    $this->_Summa = 0;
    foreach ($_POST['goods_list'] as $goods_id => $goods_cnt)
    {
      $goods_id = intval($goods_id);
      $goods_cnt = intval($goods_cnt);
      if ( 0 < $goods_cnt ) {
        $sql = "UPDATE Basket SET Cnt = {$goods_cnt} WHERE Client_ID = {$this->ID} AND Goods_ID = {$goods_id}";
        DB::Set_Query($sql);
        //
        $this->_Basket[$goods_id]->Cnt = $goods_cnt;
        $this->_Summa+= $this->_Basket[$goods_id]->Price * $goods_cnt;
      } else {
        $sql = "DELETE FROM Basket WHERE Client_ID = {$this->ID} AND Goods_ID = {$goods_id}";
        DB::Set_Query($sql);
        //
        unset($this->_Basket[$goods_id]);
      }
    }
  }
  /**
   * Полная очистка корзины
   *
   */
  public function Action_Clear()
  {
    if ( DB::Set_Query("DELETE FROM Basket WHERE Client_ID = {$this->ID}") ) {
      $this->_Basket = array();
      $this->_Summa = 0;
      return true;
    }
    return false;
  }
  /**
   * Загрузка покупательской корзины клиента
   *
   */
  public function Load_Basket()
  {
    if ( 0 == $this->ID ) return false;
    $this->_Basket = array();
    $this->_Summa = 0;
    //  загрузка корзины пользователя
    $sql = "SELECT Goods_ID, Name, PriceBase, Price, Cnt, Url FROM Basket WHERE Client_ID = {$this->ID}";
    foreach (DB::Get_Query($sql) as $row)
    {
      $id = array_shift($row);
      $Basket = new self($id);
      $Basket->Load_Row($row);
      $this->_Basket[$id] = $Basket;
      $this->_Summa+= $row['Price'] * $row['Cnt'];
    }
  }
  /**
   * Создание и/или получение объекта
   * Работает через Регистр
   * Индекс: класс объекта + [_{$id} - если 0 < $id]
   *
   * @param itneger $id - идентификатор объекта
   * @return Basket
   */
  public static function Factory($id = 0)
  {
    $index = __CLASS__ . (0 < $id ? '_' . $id : '');
    if ( !$result = Registry::Get($index) ) {
      $result = new self($id);
      Registry::Set($index, $result);
    }
    return $result;
  }
}