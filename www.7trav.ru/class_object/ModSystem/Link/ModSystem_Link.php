<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Шаблонный класс для работы объектами системы типа отношения или расширения
 * 
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @package Core
 * @subpackage ModSystem
 * @version 30.11.2009
 */
class ModSystem_Link extends Obj_Relation
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Поле родитеськой связи
   *
   * @var string
   */
  protected $FieldP;
  /**
   * Поле дочерней связи
   *
   * @var string
   */
  protected $FieldC;
  /**
   * Блокировка связи
   *
   * @var integer
   */
  protected $IsLocked;
  /**
   * Существование связи
   *
   * @var integer
   */
  protected $IsExist;
  /**
   * Сортировка
   *
   * @var integer
   */
  protected $Sort;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'ModSystem_Link';

  /**
   * Связи модуля системы.
   * Служит для работы с отношениями родительского объекта.
   * 
   * @var ModSystem_Link
   */
  private $_ModSystem_Link;
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
   * @return ModSystem_Link
   */
  public function Get_ModSystem_Link()
  {
    return $this->_ModSystem_Link;
  }    
  /**
   * Инициализация и/или получение клиента
   * Работает через Регистр
   * Индекс класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id - идентификатор объекта
   * @return ModSystem_Link
   */
  public static function Factory($id = 0)
  {
    $index = __CLASS__ . (0 < $id ? '_' . $id : '');
    if ( Registry::Is_Exists($index) )
    {
      $result = Registry::Get($index);
    }
    else
    {
      $result = new self($id);
      Registry::Set($index, $result);
    }
    return $result;
  }
}