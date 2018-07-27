<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Статистика хостов
 * 
 * @package Core
 * @subpackage Statistic
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 12.03.2009
 */
class Statistic_Host extends Obj_Item
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Клиент
   *
   * @var integer
   */
  protected $Client_ID;
  /**
   * IP адрес
   *
   * @var string
   */
  protected $Ip;
  /**
   * Хост
   *
   * @var string
   */
  protected $Name;
  /**
   * От куда пришел
   *
   * @var string
   */
  protected $Ref;
  /**
   * Глубина цвета
   *
   * @var integer
   */
  protected $Color;
  /**
   * Ширина
   *
   * @var integer
   */
  protected $Width;
  /**
   * Высота
   *
   * @var integer
   */
  protected $Height;
  /**
   * Дата
   *
   * @var string
   */
  protected $Date;
  /**
   * Операционная система
   *
   * @var string
   */
  protected $OS;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Statistic_Host';

  //  [BEG] Link
  /**
   * Статистика разделов
   *
   * @var Statistic_Razdel
   */
  protected $Statistic_Razdel;
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
   * Получение количества присутсвующего народу на сайте
   * Гостей, Клиентов и Сотрудников
   *
   * @return array('guest'=>int, 'client'=>int, 'worker'=>int);
   */
  public static final function Get_Online_Status()
  {
    $online_count = array();
    $online_count['guest'] = DB::Get_Query_Cnt("SELECT COUNT(*) FROM Client WHERE Groups_ID = 2 AND StatOnline = 'да'");
    $online_count['client'] = DB::Get_Query_Cnt("SELECT COUNT(*) FROM Client WHERE Groups_ID != 2 AND StatOnline = 'да'");
    $online_count['worker'] = DB::Get_Query_Cnt("SELECT COUNT(*) FROM Worker WHERE StatOnline = 'да'");
    return $online_count;
  }
  /**
   * Сохранение статистики хостов (заходов)
   * $ip - Ip адрес посетителя
   *
   * @param string $ip
   * @return integer
   */
  public static function Save_Host(Client $Client)
  {
    $sql = "
    INSERT INTO Statistic_Host
      (
      Client_ID,
      Ip,
      Name,
      Ref,
      Color,
      Width,
      Height,
      OS
      )
    VALUES
      (
      " . $Client->ID . ",
      " . DB::S($Client->Ip) . ",
      '" . HOST . "',
      " . DB::S($_GET['referrer_page']) . ",
      " . DB::I($_GET['color']) . ",
      " . DB::I($_GET['x_scale']) . ",
      ". DB::I($_GET['y_scale']) . ",
      " . DB::S($_SERVER["HTTP_USER_AGENT"]) . "
      )
    ";
    return DB::Ins_Query($sql);
  }
  /**
   * Сохранение статистики хитов (кликов)
   *
   * @param integer $id
   */
  public static function Save_Hit($id)
  {
    $fp = fopen(PATH_SITE . '/session/statrazdel.csv', 'a');
    fputs($fp, "{$id};{$_GET['zapros_page']};" . date('Y-m-d H;m:i') . "\n");
    fclose($fp);
  }
  /**
   * Создание и/или получение объекта
   * Работает через Регистр
   *
   * @param itneger $id
   * @return Statistic_Host
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