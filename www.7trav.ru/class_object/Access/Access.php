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
 * @subpackage Access
 * @version 30.11.2009
 */
class Access extends Obj_Relation
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Просмотр
   *
   * @var integer
   */
  protected $V;
  /**
   * Изменение
   *
   * @var integer
   */
  protected $E;
  /**
   * Добавление
   *
   * @var integer
   */
  protected $A;
  /**
   * Удаление
   *
   * @var integer
   */
  protected $R;
  /**
   * Связать
   *
   * @var integer
   */
  protected $L;
  /**
   * Отвязать
   *
   * @var integer
   */
  protected $RL;
  /**
   * Админ
   *
   * @var integer
   */
  protected $S;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Access';

  /**
   * Права на модули системы.
   * Служит для работы с отношениями родительского объекта.
   * 
   * @var Access
   */
  private $_Access;
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
   * @return Access
   */
  public function Get_Access()
  {
    return $this->_Access;
  }    
  /**
   * Инициализация и/или получение клиента
   * Работает через Регистр
   * Индекс класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id - идентификатор объекта
   * @return Access
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