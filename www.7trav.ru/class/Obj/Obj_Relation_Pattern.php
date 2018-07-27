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
class Pattern_Relation extends Obj_Relation
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Pattern_Class';
  /**
   * Pattern_Comment.
   * Служит для работы с отношениями родительского объекта.
   * 
   * @var Pattern_Class
   */
  private $_Pattern_Class;
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
   * @return Pattern_Class
   */
  public function Get_Pattern_Class()
  {
    return $this->_Pattern_Class;
  }    
  /**
   * Создание и/или получение объекта
   * Работает через Регистр
   * Индекс: класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id - идентификатор объекта
   * @return Pattern_Class
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