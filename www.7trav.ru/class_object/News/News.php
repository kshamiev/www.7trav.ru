<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Cms
 */

/**
 * Шаблонный класс для работы объектами системы типа простой объект
 * 
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @package Cms
 * @subpackage News
 * @version 30.11.2009
 */
class News extends Obj_Item
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Раздел
   *
   * @var integer
   */
  protected $Razdel_ID;
  /**
   * Заголовок
   *
   * @var string
   */
  protected $Name;
  /**
   * Титул (title)
   *
   * @var string
   */
  protected $Title;
  /**
   * Ключи (keywords)
   *
   * @var string
   */
  protected $Keywords;
  /**
   * Дата новости
   *
   * @var string
   */
  protected $Date;
  /**
   * Маленькая картинка
   *
   * @var string
   */
  protected $Imgs;
  /**
   * Видимость на сайте
   *
   * @var string
   */
  protected $IsVisible;
  /**
   * Описание (description)
   *
   * @var string
   */
  protected $Description;
  /**
   * Новость
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
  private $_Tbl_Name = 'News';
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
   * Получение seo объекта.
   * 
   * Title, Keywords, Description
   * Использует систему кеширования.
   * 
   * @return string
   */
  public function Get_Seo()
  {
    if ( !$cache = $this->Get_Cache('seo.htm') ) {
      if ( false == $this->Is_Load ) {
        $this->Load_Prop('Title', 'Description', 'Keywords');
      }
      $cache = '<title>' . htmlspecialchars($this->Title) . '. Купить в интернет магазине Ларец Лекаря</title>' . "\n";
      $cache.= '<meta name="description" content="' . htmlspecialchars($this->Description) . '">' . "\n";
      $cache.= '<meta name="keywords" content="' . htmlspecialchars($this->Keywords) . '">' . "\n";
      $this->Set_Cache('seo.htm', $cache);
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
   * @return News
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