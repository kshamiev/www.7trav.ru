<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Статистика скаченных сотрудниками файлов
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
 * @package Core
 * @subpackage Statistic
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 03.02.2010
 * @see какие классы смотреить через запятую
 * @link какие скрипты смотреть через запятую
 */
class Statistic_DuwnLoadFileWorker extends Obj_Relation
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * ID объекта
   *
   * @var integer
   */
  protected $ObjectID;
  /**
   * Дата
   *
   * @var string
   */
  protected $Date;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Statistic_DuwnLoadFileWorker';

  /**
   * Статистика скаченных файлов сотрудниками.
   * Служит для работы с отношениями родительского объекта.
   * 
   * @var Statistic_DuwnLoadFileWorker
   */
  private $_Statistic_DuwnLoadFileWorker;
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
   * @return Statistic_DuwnLoadFileWorker
   */
  public function Get_Statistic_DuwnLoadFileWorker()
  {
    return $this->_Statistic_DuwnLoadFileWorker;
  }    
  /**
   * Инициализация и/или получение клиента
   * Работает через Регистр
   * Индекс класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id - идентификатор объекта
   * @return Statistic_DuwnLoadFileWorker
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