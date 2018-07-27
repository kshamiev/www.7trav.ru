<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Pattern_Comment
 * 
 * Реализует основной функционал работы с объектами:
 * <ol>
 * <li>Создание, Изменение, Сохранение, Удаление
 * <li>Получение связанных дочерних объектов по определенному типу связи
 * <li>Получение не связанных дочерних объектов по определенному типу связи
 * <li>Создание связи между объектами по определенному типу связи
 * <li>Удаление связи между объектами по определенному типу связи
 * <li>Работа с кешем объектов. Проверка на существование объекта
 * <li>Возможность работы через регистр
 * </ol>
 * 
 * @package Core
 * @subpackage Object
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version Pattern_Date
 * @see какие классы смотреить через запятую
 * @link какие скрипты смотреть через запятую
 */
class Pattern_Catalog extends Obj_Catalog
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  /**
   * Левый ключ
   *
   * @var integer
   */
  protected $Keyl = 0;
  /**
   * Правый ключ
   *
   * @var integer
   */
  protected $Keyr = 0;
  /**
   * Уровень вложенности
   *
   * @var integer
   */
  protected $Level = 0;
  //  [BEG] Prop
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Pattern_Class';
  //  [BEG] Link
  //  [END] Link
  /**
   * Конструткор класса
   * Инициализация идентификатора объекта
   * 0 - новый объект, не сохраненый в БД
   * Если $flag_load установлен в true происходит загрузка свойств объекта из БД
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
   * @return Pattern_Class
   */
  public static function Factory($id = 0, $flag_load = false)
  {
    $index = __CLASS__ . (0 < $id ? '_' . $id : '');
    if ( !$result = Registry::Get($index) ) {
      $result = new self($id, $flag_load);
      Registry::Set($index, $result);
    }
    return $result;
  }
}