<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Базовый абстрактный класс для работы с объектами типа отношения.
 * 
 * Тип отношения это - Права доступа, Покупательская корзина
 * Реализует основной функционал работы с объектами:
 * <ol>
 * <li>Создание, Изменение, Сохранение, Удаление.
 * <li>Получение связанных дочерних объектов по определенному типу связи.
 * <li>Получение не связанных дочерних объектов по определенному типу связи.
 * <li>Создание связи между объектами по определенному типу связи.
 * <li>Удаление связи между объектами по определенному типу связи.
 * <li>Работа с кешем объектов. Проверка на существование объекта.
 * <li>Работа со свойстваи объекта через сеттеры и геттеры.
 * </ol>
 * @package Core
 * @subpackage Object
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */
abstract class Obj_Relation
{
  /**
   * Массив содержащий измененные свойства объекта
   *
   * @var array
   */
  private $_Prop_Change = array();
  /**
   * Статус загрузки объекта
   *
   * @var bolean
   */
  private $_Is_Load = false;
  /**
   * Геттер
   * Получение массива измененный свойств объекта.
   * 
   * @return array
   */
  public function Get_Prop_Change()
  {
    return $this->_Prop_Change;
  }
  /**
   * Геттер
   * Получение статуса загрузки объекта.
   * 
   * @return bolean
   */
  public function Get_Is_Load()
  {
    return $this->_Is_Load;
  }
  /**
   * Получение списка объектов передаваемого класса указанного в фильтре типа Obj_Relation
   * Относительно текущего родительского объекта
   * C учетом условий ( сортировка, постраничность )
   *
   * @param Filter $Filter
   * @return array
   */
  public function Get_Relation_Link(Filter $Filter)
  {
    $sql_where = array(1);
    /**
     * УС - Условие связи
     */
    $link = SC::$Rel[$this->Tbl_Name];
    $sql_from = $this->Tbl_Name . ' as c
      INNER JOIN ' . $link['Table'] . ' as o ON c.' . $link['LinkC'] . ' = o.ID';
    $sql_where[$link['LinkP']] = 'c.' . $link['LinkP'] . " = " . $this->ID;
    /**
     * УП - условие пользователя
     */
    if ( isset(SC::$ConditionUser[$link['LinkC']]) ) {
      $sql_where[$link['LinkC']] = 'c.' . $link['LinkC'] . ' = ' . SC::$ConditionUser[$link['LinkC']];
    }
    foreach (SC::$PropAll[$this->Tbl_Name] as $prop => $row) {
      if ( isset(SC::$ConditionUser[$prop]) )
      {
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
    if ( $Filter->Page )
    {
      $sql_limit = 'LIMIT ' . ( ($Filter->Page-1) * $Filter->Page_Item ) . ', ' . $Filter->Page_Item;
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
    ORDER BY
      " . $sql_sort . "
    " . $sql_limit . "
    ";
    $result_row = array();
    $res = &DB::Query($sql);
    while ( false != $row = $res->fetch_assoc() ) $result_row[array_shift($row)] = $row;
    $res->close();
    return $result_row;
  }
  /**
   * Получение списка объектов передаваемого класса указанного в фильтре типа Obj_Relation
   * Относительно текущего родительского объекта
   * И не привязанных к нему
   *
   * @return list
   */
  public function Get_Relation_UnLink()
  {
    $sql_where = array(1);
    /**
     * УС - Условие связи
     */
    $link = SC::$Rel[$this->Tbl_Name];
    $sql_from = $this->Tbl_Name . ' as c
      RIGHT JOIN ' . $link['Table'] . ' as o ON c.' . $link['LinkC'] . ' = o.ID AND c.' . $link['LinkP'] . ' = ' . $this->ID;
    $sql_where[$link['LinkP']] = 'c.' . $link['LinkP'] . " IS NULL";
    /**
     * УП - условие пользователя
     */
    if ( isset(SC::$ConditionUser[$link['LinkC']]) ) {
      $sql_where[$link['LinkC']] = 'o.ID = ' . SC::$ConditionUser[$link['LinkC']];
    }
    foreach (SC::$PropAll[$this->Tbl_Name] as $prop => $row) {
      if ( isset(SC::$ConditionUser[$prop]) )
      {
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
    ORDER BY
      o.Name ASC
    ";
    return DB::Get_Query_Two($sql);
  }
  /**
   * Загрузка отношения с передаваемым объектом
   *
   * @param integer $id - идентификатор дочернего объекта
   * @return array or false
   */
  public function Load($id)
  {
    $link = SC::$Rel[$this->Tbl_Name];
    $sql = "
    SELECT
      *
    FROM {$this->Tbl_Name} as c
    WHERE
      {$link['LinkP']} = {$this->ID}
      AND {$link['LinkC']} = {$id}
    ";
    $row = DB::Get_Query_Row($sql);
    if ( 0 < count($row) ) {
      array_shift($row);
      array_shift($row);
      foreach ($row as $Prop => $Value) $this->$Prop = $Value;
      $this->_Prop_Change = array();
      $this->_Is_Load = true;
      return $row;
    } else {
      return false;
    }
  }
  /**
   * Загрузка отношения из массива
   *
   * @param array $row - массив отношения с конкретным объектом
   * @return void
   */
  public function Load_Row($row)
  {
    foreach ($row as $Prop => $Value) {
      $this->$Prop = $Value;
      unset($this->_Prop_Change[$Prop]);
    }
  }
  /**
   * Редактирование объекта.
   * 
   * Централизованное изменение его свойств
   * Обрабатывает входящие $_POST данные, изменяя свойства объекта согласно правам доступа и их спецификации.
   * 
   * @param string $index - индекс $_POST массива входящих данных
   * @return void 
   */
  public function Edit($index = 'Prop')
  {
    SC::IsInit($this->Tbl_Name);
    $data = $_POST[$index];
    //  пост данные
    foreach (SC::$Prop[$this->Tbl_Name] as $prop => $row)
    {
      //  пропускаем заблокированные либо неразрешенные свойств 
      if ( $row['IsLocked'] || !$row['E'] ) continue;
      //  пост данные относящиесмя к бинарным данным пропускаем
      if ( 'file' == $row['Form'] || 'img' == $row['Form'] ) continue;

      //  checkbox
      if ( 'checkbox' == $row['Form'] )
      {
        if ( isset($data[$prop]) ) {
          $this->__set($prop, implode(',', $data[$prop]));
        } else if ( $row['IsNull'] && 'hidden' != $row['Form'] ) {
          $this->__set($prop, null);
        }
        continue;
      }
      //  check
      else if ( 'check' == $row['Form'] )
      {
        if ( isset($data[$prop]) ) {
          $this->__set($prop, 1);
        } else  if ( 'hidden' != $row['Form'] ) {
          $this->__set($prop, 0);
        }
        continue;
      }
      //  все остальные
      if ( isset($data[$prop]) )
      {
        $data[$prop] = trim($data[$prop]);
        //  проверка на не нулевое значение
        if ( !$row['IsNull'] && !$data[$prop] ) continue;
        //  пароль
        if ( 'passw' == $row['Form'] && strlen($data[$prop]) < 32 && 0 < strlen($data[$prop])) {
          $this->__set($prop, md5($data[$prop])); continue;
        }
        //  Url - особенно поле
        if ( 'Url' == substr($prop, 0, 3) ) {
          $this->__set($prop, System_String::Translit_Url($data[$prop])); continue;
        }
        //  все остальные
        $this->__set($prop, $data[$prop]);
      }
    }
    unset($data);
  }
  /**
   * Создание и/или Сохранение отношений объекта в БД.
   * Реализует механизим абстрактного достпа к созданию или сохранению отношений объекта.
   * 
   * @param integer $id - идетификатор объекта с которым строятся отношения
   * @param integer $flag_op - флаг операции, по умолчанию "0" (0 - автоопределение, 1 - обновление, -1 - добавление)
   * @return bolean
   */
  public final function Save($id, $flag_op = 0)
  {
    $link = SC::$Rel[$this->Tbl_Name];
    //  проверка на тип операции
    if ( 0 == $flag_op ) {
      $sql = "
      SELECT
        COUNT(*)
      FROM " . $this->Tbl_Name . "
      WHERE
        " . $link['LinkP'] . " = " . $this->ID . "
        AND " . $link['LinkC'] . " = " . $id;
      $flag_op = DB::Get_Query_Cnt($sql);
    }
    //  сборка свойств для сохранения в БД
    $sql_update = array();
    foreach ($this->_Prop_Change as $field => $flag)
    {
      if ( !isset(SC::$PropAll[$this->Tbl_Name][$field]) ) {
        unset($this->_Prop_Change[$field]);
      } else {
        $metod = SC::$PropAll[$this->Tbl_Name][$field]['DB'];
        $sql_update[] = $field . '=' . DB::$metod($this->$field);
      }
    }
    //  print '$sql_update<pre>'; print_r($sql_update); print '</pre>';
    if ( !count($sql_update) ) return true;
    /**
     * update
     */
    if ( 0 < $flag_op )
    {
      $sql = "
      UPDATE " . $this->Tbl_Name . "
      SET
        " . implode(', ', $sql_update) . "
      WHERE
        " . $link['LinkP'] . " = " . $this->ID . "
        AND " . $link['LinkC'] . " = " . $id . "
      ";
      DB::Set_Query($sql);
    }
    /**
     * insert
     */
    else
    {
      $sql = "
      INSERT " . $this->Tbl_Name . "
      SET
        " . $link['LinkP'] . " = " . $this->ID . ",
        " . $link['LinkC'] . " = " . $id . ",
        " . implode(', ', $sql_update) . "
      ";
      DB::Set_Query($sql);
    }
    $this->_Prop_Change = array();
    return true;
  }
  /**
   * Удаление отношения объекта.
   * Реализует механизим абстрактного достпа к удалению отношения объекта.
   * 
   * Если он не указан происходит полная очистка (удаление) расширений для текущего объекта
   * (К примеру полная очистка корзины пользователя)
   *
   * @param integer $id - идетификатор объекта с которым строятся отношения
   * @return void
   */
  public final function Remove($id = 0)
  {
    $link = SC::$Rel[$this->Tbl_Name];
    if ( 0 < $id ) {
      $sql = "
      DELETE FROM " . $this->Tbl_Name . "
      WHERE
        " . $link['LinkP'] . " = " . $this->ID . "
        AND " . $link['LinkC'] . " = " . $id . "
      ";
      return DB::Set_Query($sql);
    } else {
      $sql = "
      DELETE FROM " . $this->Tbl_Name . "
      WHERE
        " . $link['LinkP'] . " = " . $this->ID . "
      ";
      return DB::Set_Query($sql);
    }
  }
  /**
   * универсальный геттер позволяющий обернуть все прямые обращения
   * к абстрактным переменным в их персональный геттер
   *
   * @param string $field абстрактное свойство класса
   * @return mixed
   */
  public function __get($field)
  {
    if ( method_exists($this, $method = 'Get_' . $field) ) {
      return $this->$method();
    }
    return $this->$field;
  }
  /**
   * универсальный сеттер позволяющий обернуть все прямые обращения
   * к абстрактным переменным в их персональный сеттер
   *
   * @param string $field абстрактное свойство наследуемого класса
   * @param mixed $value значение этого свойства
   * @return bolean
   */
  public function __set($field, $value)
  {
    //  print 'set: ' . $field . '=' . $value . '<br>';
    if ( method_exists($this, $method = 'Set_' . $field) ) {
      return $this->$method($value);
    }
    //  изменение свойства
    $this->$field = $value;
    $this->_Prop_Change[$field] = true;
    return true;
  }
}