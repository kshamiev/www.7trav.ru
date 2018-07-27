<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Шаблонный класс для работы объектами системы типа простой объект
 * 
 * @package Core
 * @subpackage Cron
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 30.11.2009
 */
class Cron extends Obj_Item
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Описание
   *
   * @var string
   */
  protected $Name;
  /**
   * Демон
   *
   * @var string
   */
  protected $Demon;
  /**
   * Минуты
   *
   * @var string
   */
  protected $Minute;
  /**
   * Часы
   *
   * @var string
   */
  protected $Hour;
  /**
   * Дни
   *
   * @var string
   */
  protected $Day;
  /**
   * Месяцы
   *
   * @var string
   */
  protected $Month;
  /**
   * Недели
   *
   * @var string
   */
  protected $Week;
  /**
   * Статус скрипта
   *
   * @var string
   */
  protected $IsActiv;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Cron';

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
   * Получение объектной таблицы класса
   * 
   * @return string - имя обрабатываемой таблицы класса
   */
  public function Get_Tbl_Name()
  {
    return $this->_Tbl_Name;
  }
  /**
   * Функция проверки запуска по дате и времени в формате crontab
   *
   * @param string $date_this
   * @param string $date_cron
   * @return bolean
   */
  public static function Act_Check_Date($date_this, $date_cron)
  {
    //  любое допустимое значение либо точное совпадение
    if ( '*' == $date_cron || $date_this == $date_cron ) return true;
    if ( false !== strpos($date_cron, '-') ) { //  диапазон
      $mas = explode('-', $date_cron);
      if ( $mas[0] <= $date_this && $date_this <= $mas[1] ) return true;
      return false;
    } else if ( false !== strpos($date_cron, '/') ) { //  кратное
      $mas = explode('/', $date_cron);
      if ( $date_this % $mas[1] ) return false;
      return true;
    } else if ( false !== strpos($date_cron, ',') ) { //  список
      $mas = explode(',', $date_cron);
      if ( in_array($date_this, $mas) ) return true;
      return false;
    } else {
      return false;
    }
  }
  /**
   * Инициализация и/или получение клиента
   * Работает через Регистр
   * Индекс класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id - идентификатор объекта
   * @param bolean $flag_load - флаг загрузки объекта
   * @return Cron
   */
  public static function Factory($id = 0, $flag_load = false)
  {
    $index = __CLASS__ . (0 < $id ? '_' . $id : '');
    if ( Registry::Is_Exists($index) ) {
      $result = Registry::Get($index);
    } else {
      $result = new self($id, $flag_load);
      Registry::Set($index, $result);
    }
    return $result;
  }
}