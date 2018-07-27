<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Шаблонный класс для работы объектами системы типа простой объект
 * 
 * @package Core
 * @subpackage Site
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 30.11.2009
 */
class Site_Template extends Obj_Item
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Сайт
   *
   * @var integer
   */
  protected $Site_ID;
  /**
   * Шаблон
   *
   * @var string
   */
  protected $Name;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Site_Template';
  
  //  [BEG] Link
  /**
   * Зоны шаблона
   *
   * @var Zone
   */
  protected $Zone;
  /**
   * Конфигурация шаблонов
   *
   * @var Site_Template_Config
   */
  protected $Site_Template_Config;
  /**
   * Разделы
   *
   * @var Razdel
   */
  protected $Razdel;
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
   * Возвращает зоны текущего шаблона
   * 
   * @return array
   */
  public function Get_Zone_Link()
  {
    $sql = "SELECT ID, Name, Zone_Type_ID FROM Zone WHERE Site_Template_ID = {$this->ID} OR Site_Template_ID IS NULL ORDER BY Sort ASC";
    return DB::Get_Query($sql);
  }
  /**
   * Получение полной конфигурации шаблона для сайта
   * Использует систему кеширования
   *
   * @return array
   */
  public function Get_Config()
  {
    if ( !$cache = $this->Get_Cache('config.ini') ) {
      //  CONCAT (zt.Type, SUBSTRING(m.ModulUser, LOCATE('_', m.ModulUser))) AS ModulUser
      $sql = "
      SELECT
        z.ID as Zone_ID,
        z.Zone,
        m.ID as Modul_ID,
        m.ModulUser
      FROM Site_Template_Config as t
        INNER JOIN ModSystem as m ON m.ID = t.ModSystem_ID
        INNER JOIN Zone as z ON z.ID = t.Zone_ID
      WHERE
        t.Site_Template_ID = {$this->ID}
      ORDER BY
        z.Sort
      ";
      $mod_list = DB::Get_Query($sql);
      $cache = System_File::Create_Ini($mod_list, 2);
      $this->Set_Cache('config.ini', $cache);
      $cache = $this->Get_Cache('config.ini');
      unset($mod_list);
    }
    return $cache;
  }
  /**
   * Инициализация и/или получение клиента
   * Работает через Регистр
   * Индекс класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id - идентификатор объекта
   * @param bolean $flag_load - флаг загрузки объекта
   * @return Tpl
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