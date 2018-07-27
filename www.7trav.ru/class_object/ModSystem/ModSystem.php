<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Класс контроллер низкого уровня
 * Обеспечивает взаимодействие с моделью
 * и является оберткой для всех модулей системы
 *
 * @package Core
 * @subpackage ModSystem
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 12.03.2009
 */
class ModSystem extends Obj_Item
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Группа модуля
   *
   * @var integer
   */
  protected $ModSystem_Groups_ID;
  /**
   * Принадлежность к типу зоны
   *
   * @var integer
   */
  protected $Zone_Type_ID;
  /**
   * Название
   *
   * @var string
   */
  protected $Name;
  /**
   * Системный модуль
   *
   * @var string
   */
  protected $Modul;
  /**
   * Пользовательский модуль
   *
   * @var string
   */
  protected $ModulUser;
  /**
   * Обрабатываемая таблица
   *
   * @var string
   */
  protected $Tbl;
  /**
   * В навигации
   *
   * @var string
   */
  protected $IsVisible;
  /**
   * Описание пользователя
   *
   * @var string
   */
  protected $Description;
  /**
   * Высота окна редак-ия
   *
   * @var integer
   */
  protected $EditHeight;
  /**
   * Описание разработчика
   *
   * @var string
   */
  protected $Content;
  /**
   * Включение прав на файлы
   *
   * @var integer
   */
  protected $IsProtectedFile;
  /**
   * Существование модуля
   *
   * @var integer
   */
  protected $IsExist;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'ModSystem';
  //  [BEG] Link
  /**
   * Конфигурация шаблонов
   *
   * @var Site_Template_Config
   */
  protected $Site_Template_Config;
  /**
   * Права доступа
   *
   * @var Access
   */
  protected $Access;
  /**
   * Права на свойства объектов
   *
   * @var Access_Prop
   */
  protected $Access_Prop;
  /**
   * Разделы
   *
   * @var Razdel
   */
  protected $Razdel;
  /**
   * Свойства
   *
   * @var ModSystem_Prop
   */
  protected $ModSystem_Prop;
  /**
   * Связи
   *
   * @var ModSystem_Link
   */
  protected $ModSystem_Link;
  /**
   * Статистика скаченных клиентами файлов
   *
   * @var Statistic_DuwnLoadFileClient
   */
  protected $Statistic_DuwnLoadFileClient;
  /**
   * Статистика скаченных сотрудниками файлов
   *
   * @var Statistic_DuwnLoadFileWorker
   */
  protected $Statistic_DuwnLoadFileWorker;
  //  [END] Link
  /**
   * Текущий шаблон модуля
   *
   * @var string
   */
  public $Block;
  /**
   * Фильтр
   *
   * @var Filter
   */
  public $Filter = null;
  /**
   * Экземпляр обрабатываемого объекта
   *
   * @var mixed
   */
  public $Obj = null;
  /**
   * Иерархический путь модуля
   * История как до него добрались
   *
   * @var array
   */
  public $Path = array();
  /**
   * Непосредственный родитель
   *
   * @var array
   */
  public $Parent = array();
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
   * Инициализация модуля через таблицу которую он обрабатывает
   * 
   * @param $Tbl - Имя таблицы
   * @return bolean
   */
  public function Init_Tbl($Tbl)
  {
    $sql = "SELECT ID, Name, Modul FROM {$this->_Tbl_Name} WHERE Tbl = '{$Tbl}'";
    $row = DB::Get_Query_Row($sql);
    if ( 0 < count($row) ) {
      $this->Tbl = $Tbl;
      return $this->Load($row);
    }
    return false; 
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
   * Получение прав на модуль
   *
   * @return array
   */
  public function Get_Access()
  {
    //  получение прав на модуль
    if ( is_null($this->Access) )
    {
      if ( 0 < $this->ID ) {
        $sql = "SELECT V, E, A, R, L, RL, S FROM Access WHERE Groups_ID = " . Registry::Get('Worker')->Groups_ID . " AND ModSystem_ID = " . $this->ID;
        $this->Access = DB::Get_Query_Row($sql);
      } else {
        $this->Access = array();
      }
    }
    return $this->Access;
  }
  /**
   * Получение административных модулей согласно правам сотрудника
   *
   * @param Worker $Worker
   * @return array
   */
  public static function Get_Navigation(Worker $Worker)
  {
    $sql = "
    SELECT
      m.ID, m.Name, mg.Name
    FROM ModSystem as m
      INNER JOIN Access as a ON m.ID = a.ModSystem_ID AND a.Groups_ID = " . $Worker->Groups_ID . "
      LEFT JOIN ModSystem_Groups as mg ON mg.ID = m.ModSystem_Groups_ID
    WHERE
      IsVisible = 'да'
    ORDER BY
      mg.Direction ASC, m.Name ASC
    ";
    $res = &DB::Query($sql);
    /* @var $res mysqli_result */
    $result_array = array();
    while ( false != $row = $res->fetch_row() )
    {
      $result_array[array_pop($row)][$row[0]] = $row[1];
    }
    $res->close();
    return $result_array;
  }
  /**
   * Получение списка групп которые имеют право на модуль
   *
   * @param ModSystem $ModSystem - Моудль
   * @return list - Список групп которые имеют право на модуль
   */
  public function Get_Groups_Access($modsystem_id)
  {
    $sql = "
    SELECT
      g.ID, g.Name
    FROM Groups as g
      INNER JOIN Access as a ON a.Groups_ID = g.ID AND a.ModSystem_ID = {$modsystem_id}
    WHERE
      Status = 'открыта'
    ORDER BY
      g.Name
    ";
    return DB::Get_Query_Two($sql);
  }
  /**
   * Получение всех доступных для сайта модулей.
   * Сгруппированных по типам зон и отсортироыванных по названию.
   * 
   * @return array
   */
  public static function Get_ModSystem_Site()
  {
    $result = array();
    $sql = "SELECT Zone_Type_ID, ID, Name FROM ModSystem WHERE 1 < Zone_Type_ID ORDER BY Name ASC";
    $sql = "SELECT Zone_Type_ID, ID, Name FROM ModSystem WHERE Zone_Type_ID IS NOT NULL ORDER BY Name ASC";
    $res = &DB::Query($sql);
    /* @var $res mysqli_result */
    //  while ( false != $row = $res->fetch_assoc() ) $result[array_shift($row)][] = $row;
    while ( false != $row = $res->fetch_row() ) $result[$row[0]][$row[1]] = $row[2];
    $res->close();
    return $result;
  }
  /**
   * Получение дочерних модулей с учетом прав доступа сотрудников
   * 
   * @param Worker $Worker - Сотрудник
   * @return array - список модулей по дочерней связи
   */
  public function Get_ModSystem_Link_Access(Worker $Worker)
  {
    $sql = "
    SELECT
      m.ID, m.Name
    FROM ModSystem as m
      INNER JOIN ModSystem_Link as mm ON mm.ModSystem_C_ID = m.ID AND mm.ModSystem_P_ID = {$this->ID}
      INNER JOIN Access as a ON a.ModSystem_ID = m.ID AND a.Groups_ID = {$Worker->Groups_ID}
    WHERE
      mm.IsLocked = 0
    ORDER BY
      mm.Sort ASC
      ";
    return DB::Get_Query($sql);
  }
  /**
   * Сброс конфигурационных файлов объектных модулей.
   * 
   * @param string $flag - 'obj' тип объекта 'prop' все свойства '' и то и то
   */
  public function Act_Config_Clear($flag = '')
  {
    $flag = strtolower($flag);
    if ( !$this->Tbl ) {
      $this->Load_Prop('Tbl');
    }
    if ( !$this->Tbl ) return true;
    if ( ( 'obj' == $flag || '' == $flag ) && file_exists($path = PATH_CLASS_CONFIG . '/' . str_replace('_', '/', $this->Tbl) . '/Obj.ini') ) {
      unlink($path);
    } else if ( ( 'prop' == $flag || '' == $flag ) ) {
      $file_list = glob(PATH_CLASS_CONFIG . '/' . str_replace('_', '/', $this->Tbl) . '/Prop*.ini');
      foreach ($file_list as $file) unlink($file);
    } 
  }
  /**
   * Создание и/или получение объекта
   * Работает через Регистр
   *
   * @param itneger $id
   * @return ModSystem
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