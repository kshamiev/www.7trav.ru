<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package System
 */

/**
 * Акции
 * 
 * Реализует основной функционал работы с объектами:
 * <ol>
 * <li>Создание, Изменение, Сохранение, Удаление.
 * <li>Получение связанных дочерних объектов по определенному типу связи.
 * <li>Получение не связанных дочерних объектов по определенному типу связи.
 * <li>Создание связи между объектами по определенному типу связи.
 * <li>Удаление связи между объектами по определенному типу связи.
 * <li>Работа с кешем объектов. Проверка на существование объекта.
 * <li>Возможность работы через регистр
 * </ol>
 * 
 * @package System
 * @subpackage Object
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 30.04.2010
 * @see какие классы смотреить через запятую
 * @link какие скрипты смотреть через запятую
 */
class Action extends Obj_Item
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Заголовок
   *
   * @var string
   */
  protected $Name;
  /**
   * Дата начала
   *
   * @var string
   */
  protected $DateBeg;
  /**
   * Дата завершения
   *
   * @var string
   */
  protected $DateEnd;
  /**
   * Маленькая картинка
   *
   * @var string
   */
  protected $Imgs;
  /**
   * Большая картинка
   *
   * @var string
   */
  protected $Imgb;
  /**
   * Видимость на сайте
   *
   * @var string
   */
  protected $IsVisible;
  /**
   * Краткое описание новости
   *
   * @var string
   */
  protected $Description;
  /**
   * Полное описание новости
   *
   * @var string
   */
  protected $Content;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Action';
  //  [BEG] Link
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
   * Проверка на существование объекта
   *
   * @param $id - идентификатор объекта
   */
  public static function Is_Exists($id)
  {
    return DB::Get_Query_Cnt('SELECT COUNT(*) FROM ' . __CLASS__ . ' WHERE ID = ' . $id);
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
   * @return Action
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