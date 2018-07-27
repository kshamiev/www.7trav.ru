<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Шаблонный класс для работы объектами системы типа простой объект
 * 
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @package Core
 * @subpackage Site
 * @version 10.11.2009
 */
class Site extends Obj_Item
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Название проекта
   *
   * @var string
   */
  protected $Name;
  /**
   * Хост
   *
   * @var string
   */
  protected $Host;
  /**
   * Расположение сайта
   *
   * @var string
   */
  protected $Path;
  /**
   * Обратный почтовый адрес
   *
   * @var string
   */
  protected $Email;
  /**
   * Описание
   *
   * @var string
   */
  protected $Description;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Site';

  //  [BEG] Link
  /**
   * Шаблоны сайта
   *
   * @var Site_Template
   */
  protected $Site_Template;
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
   * Инициализация Сайта на основе его хоста
   *
   * @param string $host
   * @return bolean
   */
  public function Init_Host($host)
  {
    $row = DB::Get_Query_Row("SELECT * FROM Site WHERE Host = " . DB::S($host));
    if ( count($row) < 2 ) {
      return false;
    }
    $this->Load($row);
    $this->Is_Load = true;
    return true;
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
   * Получекние карты сайта
   *
   * @return array
   */
  public static function Get_Map()
  {
    $sql = "
    SELECT
      Name,
      SUBSTRING(UrlRoot, POSITION('/' IN UrlRoot)) as UrlRoot,
      Level
    FROM Razdel
    WHERE
      IsIndex = 1
      AND IsAccess = 1
    ORDER BY
      Keyl ASC
    ";
    return DB::Get_Query($sql);
  }
  /**
   * Инициализация и/или получение клиента
   * Работает через Регистр
   * Индекс класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id - идентификатор объекта
   * @param bolean $flag_load - флаг загрузки объекта
   * @return Site
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