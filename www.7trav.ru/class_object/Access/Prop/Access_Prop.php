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
 * @version 02.01.2010
 */
class Access_Prop extends Obj_Relation
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
   * Редактирование
   *
   * @var integer
   */
  protected $E;
  /**
   * Сортировка
   *
   * @var integer
   */
  protected $Sort;
  /**
   * Видимость в списке
   *
   * @var integer
   */
  protected $IsVisible;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Access_Prop';

  /**
   * Права на свойства объекта.
   * Служит для работы с отношениями родительского объекта.
   * 
   * @var Access_Prop
   */
  private $_Access_Prop;
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
   * @return Access_Prop
   */
  public function Get_Access_Prop()
  {
    return $this->_Access_Prop;
  }    
  /**
   * Получение списка объектов передаваемого класса указанного в фильтре типа Obj_Relation
   * Относительно текущего родительского объекта
   * C учетом условий ( сортировка, постраничность )
   *
   * @param Filter $Filter
   * @param $modsystem_id
   * @return array
   */
  public function Get_Modsystem_Link(Filter $Filter, $modsystem_id)
  {
    $sql_where = array(1);
    /**
     * УС - Условие связи
     */
    $link = SC::$Rel[$this->_Tbl_Name];
    $sql_from = $this->_Tbl_Name . ' as c
      INNER JOIN ' . $link['Table'] . ' as o ON c.' . $link['LinkC'] . ' = o.ID';
    $sql_where[$link['LinkP']] = 'c.' . $link['LinkP'] . " = " . $this->ID;
    /**
     * УП - условие пользователя
     */
    if ( isset(SC::$ConditionUser[$link['LinkC']]) ) {
      $sql_where[$link['LinkC']] = 'c.' . $link['LinkC'] . ' = ' . SC::$ConditionUser[$link['LinkC']];
    }
    foreach (SC::$Prop[$this->_Tbl_Name] as $prop => $row) {
      if ( isset(SC::$ConditionUser[$prop]) ) {
        $sql_where[$prop] = 'c.' . $prop . ' = ' . SC::$ConditionUser[$prop];
      }
    }
    /**
     * Видимые свойства
     */
    $sql_prop = 'c.' . implode(', c.', array_keys($Filter->Sort_Prop));
    /**
     * Сортировка
     */
    $sort = $Filter->Sort['Prop'];
    if ( 'ID' == $sort || 'Name' == $sort ) {
      $sql_sort = 'o.' . $sort . ' ' . $Filter->Sort['Value'];
    } else {
      $sql_sort = 'c.' . $sort . ' ' . $Filter->Sort['Value'];
    }
    /**
     * Постраничность
     */
    $sql_limit = '';
    if ( $Filter->Page ) {
      $sql_limit = 'LIMIT ' . (($Filter->Page - 1) * $Filter->Page_Item) . ', ' . $Filter->Page_Item;
    }
    /**
     * КОЛИЧЕСВТО ОБЪЕКТОВ
     */
    $sql = "
    SELECT
      COUNT(*)
    FROM " . $sql_from . "
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND o.ModSystem_ID = {$modsystem_id}
    ";
    $Filter->Count = DB::Get_Query_Cnt($sql);
    /**
     * ОБЪЕКТЫ
     */
    $sql = "
    SELECT
      o.ID, o.Name as _Name, " . $sql_prop . "
    FROM " . $sql_from . "
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND o.ModSystem_ID = {$modsystem_id}
      ORDER BY
      " . $sql_sort . "
    " . $sql_limit . "
    ";
    $result_row = array();
    $res = &DB::Query($sql);
    while ( false != $row = $res->fetch_assoc() )
      $result_row[array_shift($row)] = $row;
    $res->close();
    return $result_row;
  }
  /**
   * Получение списка объектов передаваемого класса указанного в фильтре типа Obj_Relation
   * Относительно текущего родительского объекта
   * И не привязанных к нему
   *
   * @param $modsystem_id
   * @return list
   */
  public function Get_Modsystem_UnLink($modsystem_id)
  {
    $sql_where = array(1);
    /**
     * УС - Условие связи
     */
    $link = SC::$Rel[$this->_Tbl_Name];
    $sql_from = $this->_Tbl_Name . ' as c
      RIGHT JOIN ' . $link['Table'] . ' as o ON c.' . $link['LinkC'] . ' = o.ID AND c.' . $link['LinkP'] . ' = ' . $this->ID;
    $sql_where[$link['LinkP']] = 'c.' . $link['LinkP'] . " IS NULL";
    /**
     * УП - условие пользователя
     */
    if ( isset(SC::$ConditionUser[$link['LinkC']]) ) {
      $sql_where[$link['LinkC']] = 'o.ID = ' . SC::$ConditionUser[$link['LinkC']];
    }
    foreach (SC::$Prop[$this->_Tbl_Name] as $prop => $row) {
      if ( isset(SC::$ConditionUser[$prop]) ) {
        $sql_where[$prop] = 'c.' . $prop . ' = ' . SC::$ConditionUser[$prop];
      }
    }
    /**
     * ОБЪЕКТЫ
     */
    $sql = "
    SELECT
      o.ID, o.Name
    FROM " . $sql_from . "
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND o.ModSystem_ID = {$modsystem_id}
      AND o.IsLocked = 0
    ORDER BY
      o.Name ASC
    ";
    return DB::Get_Query_Two($sql);
  }
  /**
   * Установка прав доступа на свойства объекта который обрабатывает выбранный модуль модуль для выбранной нруппы.
   * 
   * Сброс кеша конфигарции свойств для текущей пары группа - объектный модуль.
   * 
   * @param ModSystem $ModSystem - Модуль системы
   * @param integer $flag_op - Флаг операции (-1 = новая запись, 1 = обновление)
   * @return bolean
   */
  public function Save_ModSystem(ModSystem $ModSystem, $flag_op)
  {
    if ( !$ModSystem->Tbl ) {
      $ModSystem->Load_Prop('Tbl');
    }
    if ( !$ModSystem->Tbl ) return true;
    $sql = "SELECT V, E FROM Access WHERE Groups_ID = {$this->ID} AND ModSystem_ID = {$ModSystem->ID}";
    $access = DB::Get_Query_Row($sql);
    if ( 0 == count($access) ) return false;
    //  Сброс кеша конфигарции свойств для текущей пары группа - объектный модуль
    if ( file_exists($path = PATH_CLASS_CONFIG . '/' . str_replace('_', '/', $ModSystem->Tbl) . '/Prop_' . $this->ID . '.ini') ) {
      unlink($path);
    }
    //  инициализация
    if ( !$access['V'] ) {
      //  удаление прав
      $this->Remove_ModSystem($ModSystem);
      return true;
    }
    $sql_set = 'V = 1';
    if ( $access['E'] ) {
      $sql_set .= ', E = 1';
    } else {
      $sql_set .= ', E = 0';
    }
    //  установка прав доступа на свойства объекта
    $sort = 10;
    $sql = "SELECT ID, IsVisible FROM ModSystem_Prop WHERE ModSystem_ID = {$ModSystem->ID} AND IsLocked = 0 ORDER BY Sort ASC";
    foreach (DB::Get_Query($sql) as $row) {
      if ( 0 < $flag_op ) {
        $sql = "UPDATE Access_Prop SET {$sql_set} WHERE Groups_ID = {$this->ID} AND ModSystem_Prop_ID = {$row['ID']}";
      } else {
        $sql = "INSERT Access_Prop SET Groups_ID = {$this->ID}, ModSystem_Prop_ID = {$row['ID']}, Sort = {$sort}, IsVisible = {$row['IsVisible']}, {$sql_set}";
      }
      DB::Set_Query($sql);
      $sort = $sort + 10;
    }
    //  Сброс кеша конфигарции свойств для текущей пары группа - объектный модуль
    if ( file_exists($path = PATH_CLASS_CONFIG . '/' . str_replace('_', '/', $ModSystem->Tbl) . '/Prop_' . $this->ID . '.ini') ) {
      unlink($path);
    }
    return true;
  }
  /**
   * Удаление прав на все свойства модуля
   * 
   * @param ModSystem $ModSystem - Модуль системы
   * @return void
   */
  public function Remove_ModSystem(ModSystem $ModSystem)
  {
    if ( !$ModSystem->Tbl ) {
      $ModSystem->Load_Prop('Tbl');
    }
    if ( !$ModSystem->Tbl ) return true;
    $sql = "
    DELETE Access_Prop AS ap FROM Access_Prop AS ap
      INNER JOIN ModSystem_Prop AS mp ON ap.ModSystem_Prop_ID = mp.ID
    WHERE
      mp.ModSystem_ID = {$ModSystem->ID} 
      AND ap.Groups_ID = {$this->ID}
    ";
    DB::Set_Query($sql);
    //  Сброс кеша конфигарции свойств для текущей пары группа - объектный модуль
    if ( file_exists($path = PATH_CLASS_CONFIG . '/' . str_replace('_', '/', $ModSystem->Tbl) . '/Prop_' . $this->ID . '.ini') ) {
      unlink($path);
    }
  }
  /**
   * Удаление прав на одно свойство таблицы
   * 
   * @param integer $modsystem_prop_id - идентификатор свойства таблицы системы 
   * @return void
   */
  public static function Remove_ModSystem_Prop($modsystem_prop_id)
  {
    $sql = "DELETE FROM Access_Prop WHERE ModSystem_Prop_ID = {$modsystem_prop_id}";
    DB::Set_Query($sql);
  }
  /**
   * Инициализация и/или получение клиента
   * Работает через Регистр
   * Индекс класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id - идентификатор объекта
   * @return Access_Prop
   */
  public static function Factory($id = 0)
  {
    $index = __CLASS__ . (0 < $id ? '_' . $id : '');
    if ( Registry::Is_Exists($index) ) {
      $result = Registry::Get($index);
    } else {
      $result = new self($id);
      Registry::Set($index, $result);
    }
    return $result;
  }
}