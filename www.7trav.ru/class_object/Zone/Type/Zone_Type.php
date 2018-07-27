<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Cms
 */

/**
 * Краткое описание
 * 
 * Полное описание
 * 
 * @package Cms
 * @subpackage Zone
 * @version 29.01.2010
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 */
class Zone_Type extends Obj_Item
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Название
   *
   * @var string
   */
  protected $Name;
  /**
   * Тип зоны
   *
   * @var string
   */
  protected $Type;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Zone_Type';

  //  [BEG] Link
  /**
   * Зоны шаблона
   *
   * @var Zone
   */
  protected $Zone;
  /**
   * Модули системы
   *
   * @var ModSystem
   */
  protected $ModSystem;
  //  [END] Link

  /**
   * Конструткор класса
   * Инициализация объекта
   * $id == 0 - новый объект, не сохраненый в БД
   * Если $flag_load установлен в true и 0 < $id происходит загрузка свойств объекта из БД
   *
   * @param integer $id - идентификатор объекта
   * @param bolean $flag_load - флаг загрузки объекта
   */
  public function __construct($id = 0, $flag_load = false)
  {
    if ( 0 < $id ) {
      $this->ID = $id;
    }
    if ( 0 < $id && $flag_load ) {
      $this->Load();
    }
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
   * Инициализация и/или получение клиента
   * Работает через Регистр
   * Индекс класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id - идентификатор объекта
   * @param bolean $flag_load - флаг загрузки объекта
   * @return Zone_Type
   */
  public static function Factory($id = 0, $flag_load = false)
  {
    $index = __CLASS__ . (0 < $id ? '_' . $id : '');
    if ( Registry::Is_Exists($index) )
    {
      $result = Registry::Get($index);
    }
    else
    {
      $result = new self($id, $flag_load);
      Registry::Set($index, $result);
    }
    return $result;
  }
}