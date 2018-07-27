<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Базовый абстрактный класс для работы с простыми объектами.
 *
 * Реализует основной функционал работы с объектами:
 * <ol>
 * <li>Создание, Изменение, Сохранение, Удаление.
 * <li>Получение связанных дочерних объектов по определенному типу связи.
 * <li>Получение не связанных дочерних объектов по определенному типу связи.
 * <li>Создание связи между объектами по определенному типу связи.
 * <li>Удаление связи между объектами по определенному типу связи.
 * <li>Работа с кешем объектов. Проверка на существование объекта.
 * <li>Работа со свойстваи объекта через сеттеры и геттеры.
 * <li>Сортировка объектов между собой (при наличии поля Direction).
 * </ol>
 * @package Core
 * @subpackage Object
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */
abstract class Obj_Item
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
   * Список папок расположения кеша
   *
   * @var array
   */
  private $_PathCache = array();
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
   * Получение кеша объекта с учетом времени его жизни.
   * 
   * @param string $file_name - файл кеша.
   * @param string $cache_time - время жизни кеша в секундах.
   * @return string (htm) or array (ini) or false
   */
  public function Get_Cache($file_name, $cache_time = CACHE_TIME)
  {
    //  путь до кеша
    if ( 0 == count($this->_PathCache) ) {
      $this->_PathCache($this->ID);
    }
    $path = PATH_CACHE . '/' . $this->Tbl_Name . '/' . implode('/', $this->_PathCache) . '/' . $this->ID . '/' . LANG_PREFIX . '/' . $file_name;
    //
    if ( file_exists($path) && time() - filemtime($path) < $cache_time ) {
      if ( 'ini' == substr($file_name, -3) ) {
        return parse_ini_file($path, true);
      } else {
        return file_get_contents($path);
      }
    }
    return false;
  }
  /**
   * Сохранение кеша объекта.
   *
   * @param string $file_name - файл кеша.
   * @param string $cache - сохраняемый кеш.
   * @return void
   */
  public function Set_Cache($file_name, $cache)
  {
    //  путь до кеша
    if ( 0 == count($this->_PathCache) ) {
      $this->_PathCache($this->ID);
    }
    //  инициализация папки кеша
    $path = PATH_CACHE . '/' . $this->Tbl_Name;
    if ( !is_dir($path) ) mkdir($path);
    foreach ($this->_PathCache as $folder) {
      $path.= '/' . $folder;
      if ( !is_dir($path) ) @mkdir($path, 0777);
    }
    //  конечное хранилище кеша объекта
    $path.= '/' . $this->ID;
    if ( !is_dir($path) ) @mkdir($path, 0777);
    //  языковой кеш
    $path.= '/' . LANG_PREFIX;
    if ( !is_dir($path) ) @mkdir($path, 0777);
    //
    file_put_contents($path . '/' . $file_name, $cache);
  }
  /**
   * Получение объектов текущего класса.
   * 
   * Реализует механизим абстрактного достпа к получению объектов наследуемых классов
   * Получение списка объектов передаваемого класса указанного в фильтре типа Obj_Item
   * C учетом условий ( фильтры, УП, поиск, сортировка, постраничность )
   *
   * @param Filter $Filter
   * @return array
   */
  public final static function Get_Object(Filter $Filter)
  {
    $sql_where = array(1);
    $Tbl = $Filter->Tbl;
    //  загрузка конфигурации объекта
    SC::IsInit($Tbl);
    /**
     * фильтры
     */
    $sql_where = array_merge($sql_where, $Filter->Get_Sql_Filter());
    /**
     * УП - условие пользователя
     */
    foreach (SC::$PropAll[$Tbl] as $prop => $row)
    {
      if ( isset(SC::$ConditionUser[$prop]) ) {
        if ( $prop == $Tbl . '_ID' ) {  //  Прямое УП на таблицу ( если таблица сама является условием пользователя - УП )
          $sql_where['ID'] = 'c.ID = ' . SC::$ConditionUser[$prop];
        } else {
          $sql_where[$prop] = 'c.' . $prop . ' = ' . SC::$ConditionUser[$prop];
        }
      }
    }
    /**
     * Поиск
     */
    $sql_where = array_merge($sql_where, $Filter->Get_Sql_Search());
    /**
     * УРС - Условие рекурсивной связи на верхний уровень
     * ( здесь можно поставить УП выше УРС на верхний уровень ) по умолчанию связь затирает УП
     * !isset($sql_where[$this->Tbl_Name . '_ID'])
     */
    if ( isset(SC::$Link[$Tbl][$Tbl]) && !isset(SC::$Link[$Tbl][$Tbl]['LinkC']) )
    {
      $prop = SC::$Link[$Tbl][$Tbl]['LinkP'];
      $sql_where[$prop] = 'c.' . $prop . ' IS NULL';
    }
    /**
     * Видимые свойства
     */
    $sql_prop = 'c.' . implode(', c.', array_merge(array('ID'), array_keys($Filter->Sort_Prop)));
    /**
     * Сортировка
     */
    $sql_sort = $Filter->Sort['Prop'] . ' ' . $Filter->Sort['Value'];
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
    FROM " . $Tbl . " as c
    WHERE
      " . implode(' AND ', $sql_where) . "
    ";
    $Filter->Count = DB::Get_Query_Cnt($sql);
    /**
     * ОБЪЕКТЫ
     */
    $sql = "
    SELECT
      " . $sql_prop . "
    FROM " . $Tbl . " as c
    WHERE
      " . implode(' AND ', $sql_where) . "
    ORDER BY
      c." . $sql_sort . "
    " . $sql_limit . "
    ";
    //  return DB::Get_Query_Obj_List($sql, $Tbl);
    $result_row = array();
    $res = &DB::Query($sql);
    /* @var $res mysqli_result */
    while ( false != $row = $res->fetch_assoc() )
    {
      $id = array_shift($row);
      $result_row[$id] = $row;
    }
    $res->close();
    //
    return $result_row;
  }
  /**
   * Получение списка объектов передаваемого класса указанного в фильтре типа Obj_Item
   * Относительно текущего родительского объекта
   * C учетом условий ( фильтры, УП, поиск, сортировка, постраничность )
   *
   * @param Filter $Filter
   * @return array
   */
  public final function Get_Object_Link(Filter $Filter)
  {
    $sql_where = array(1);
    $Tbl = $Filter->Tbl;
    //  загрузка конфигурации объекта
    SC::IsInit($this->Tbl_Name);
    /**
     * фильтры
     */
    $sql_where = array_merge($sql_where, $Filter->Get_Sql_Filter());
    /**
     * УП - условие пользователя
     */
    foreach (SC::$PropAll[$Tbl] as $prop => $row)
    {
      if ( isset(SC::$ConditionUser[$prop]) ) {
        if ( $prop == $Tbl . '_ID' ) {  //  Прямое УП на таблицу ( если таблица сама является условием пользователя - УП )
          $sql_where['ID'] = 'c.ID = ' . SC::$ConditionUser[$prop];
        } else {
          $sql_where[$prop] = 'c.' . $prop . ' = ' . SC::$ConditionUser[$prop];
        }
      }
    }
    /**
     * Поиск
     */
    $sql_where = array_merge($sql_where, $Filter->Get_Sql_Search());
    /**
     * УС - Условие связи
     */
    $link = SC::$Link[$this->Tbl_Name][$Tbl];
    //  один ко многим  ( здесь можно поставить УП выше УС )
    //  по умолчанию переход по связи затирает УП !isset($sql_where[$link['LinkP']])
    if ( !isset($link['LinkC']) ) {
      $sql_from = $Tbl . ' as c';
      $sql_where[$link['LinkP']] = 'c.' . $link['LinkP'] . " = " . $this->ID;
    } else {  //  многие ко многим
      $sql_from = $this->Tbl_Name . '_Link_' . $Tbl . ' as p
        INNER JOIN ' . $Tbl . ' as c ON c.ID = p.' . $link['LinkC'] . ' AND p.' . $link['LinkP'] . ' = ' . $this->ID;
    }
    /**
     * Видимые свойства
     */
    $sql_prop = 'c.' . implode(', c.', array_merge(array('ID'), array_keys($Filter->Sort_Prop)));
    /**
     * Сортировка
     */
    $sql_sort = $Filter->Sort['Prop'] . ' ' . $Filter->Sort['Value'];
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
      " . $sql_prop . "
    FROM " . $sql_from . "
    WHERE
      " . implode(' AND ', $sql_where) . "
    ORDER BY
      c." . $sql_sort . "
    " . $sql_limit . "
    ";
    //  return DB::Get_Query_Obj_List($sql, $Tbl);
    $result_row = array();
    $res = &DB::Query($sql);
    /* @var $res mysqli_result */
    while ( false != $row = $res->fetch_assoc() )
    {
      $id = array_shift($row);
      $result_row[$id] = $row;
    }
    $res->close();
    //
    return $result_row;
  }
  /**
   * Получение списка объектов передаваемого класса указанного в фильтре типа Obj_Item
   * Относительно текущего родительского объекта
   * И не привязанных к нему
   * C учетом условий ( УП )
   *
   * @param Filter $Filter
   * @return list
   */
  public final function Get_Object_UnLink(Filter $Filter)
  {
    $sql_where = array();
    $Tbl = $Filter->Tbl;
    //  загрузка конфигурации объекта
    SC::IsInit($this->Tbl_Name);
    /**
     * УП - условие пользователя
     */
    foreach (SC::$PropAll[$Tbl] as $prop => $row)
    {
      if ( isset(SC::$ConditionUser[$prop]) ) {
        if ( $prop == $Tbl . '_ID' ) {  //  Прямое УП на таблицу ( если таблица сама является условием пользователя - УП )
          $sql_where['ID'] = 'c.ID = ' . SC::$ConditionUser[$prop];
        } else {
          $sql_where[$prop] = 'c.' . $prop . ' = ' . SC::$ConditionUser[$prop];
        }
      }
    }
    /**
     * УС - Условие связи
     */
    $link = SC::$Link[$this->Tbl_Name][$Tbl];
    //  один ко многим  ( здесь можно поставить УП выше УС )
    //  по умолчанию переход по связи затирает УП !isset($sql_where[$link['LinkP']])
    if ( !isset($link['LinkC']) )
    {
      $sql_from = $Tbl . ' as c';
      //
      /*
      $sql_where[$link['LinkP']] = 'c.' . $link['LinkP'] . " != " . $this->ID;
      $sql_where = '( ' . implode(' AND ', $sql_where) . ' ) OR ' . $link['LinkP'] . ' IS NULL';
      */
      //  перепривязка
      $sql_where[$link['LinkP']] = 'c.' . $link['LinkP'] . " != " . $this->ID;
      //  ни к чему не привязанные
      $sql_where[$link['LinkP']] = 'c.' . $link['LinkP'] . " IS NULL";
      $sql_where = implode(' AND ', $sql_where);
    }
    //  многие ко многим
    else
    {
      $sql_from = $this->Tbl_Name . '_Link_' . $Tbl . ' as p
        RIGHT JOIN ' . $Tbl . ' as c ON p.' . $link['LinkC'] . ' = c.ID AND p.' . $link['LinkP'] . ' = ' . $this->ID;
      //
      $sql_where[$link['LinkP']] = 'p.' . $link['LinkP'] . ' IS NULL';
      $sql_where = implode(' AND ', $sql_where);
    }
    /**
     * ОБЪЕКТЫ
     */
    $sql = "
    SELECT
      c.ID, c.Name
    FROM " . $sql_from . "
    WHERE
    {$sql_where}
    ORDER BY
      c.Name ASC
    ";
    //  return DB::Get_Query_Obj_List($sql, $Tbl);
    return DB::Get_Query_Two($sql);
  }
  /**
   * Сортировка объекта относительно других
   *
   * @param bolean $direction - напрвление сортировки
   * @return bolean
   */
  public function Act_Sortig($direction)
  {
    $this->Direction = DB::Get_Query_Cnt("SELECT Direction FROM {$this->Tbl_Name} WHERE ID = {$this->ID}");
    // начало и конец
    if ( $direction ) {
      $sql = "SELECT ID FROM {$this->Tbl_Name} WHERE Direction = " . ( $this->Direction + 1 );
      $sort = $this->Direction + 1;
    } else {
      $sql = "SELECT ID FROM {$this->Tbl_Name} WHERE Direction = " . ( $this->Direction - 1 );
      $sort = $this->Direction - 1;
    }
    $id = DB::Get_Query_Cnt($sql); if ( !$id ) return true;
    //
    $sql = "UPDATE {$this->Tbl_Name} SET Direction = {$sort} WHERE ID = {$this->ID}";
    DB::Set_Query($sql);
    $sql = "UPDATE {$this->Tbl_Name} SET Direction = {$this->Direction} WHERE ID = {$id}";
    DB::Set_Query($sql);
    //
    $this->Direction = $sort;
    return true;
  }
  /**
   * Сохранение загруженного файла.
   * С реализацией обработки картинки в процессе сохранения (поворот и ресайз)
   * Для ресайза и поворота картинки:
   * $resize['X'=>25, 'Y'=>25, 'R'=>-1(-90)|1(90)|2(180)]
   *
   * @param array $file - информация о файле ('tmp_name', 'name', 'type', error)
   * @param array $resize - resize image
   * @return bolean
   */
  public function Act_File_Upload($file, $resize = array())
  {
    //  файл не загружен или загружен с ошибками
    if ( !is_uploaded_file($file['tmp_name']) || 0 != $file['error'] ) {
      return false;
    }
    if ( 0 == count($this->_PathCache) ) {
      $this->_PathCache($this->ID);
    }
    $path = PATH_ADMIN . '/img/' . strtolower($this->Tbl_Name);
    if ( !is_dir($path) ) mkdir($path);
    foreach ($this->_PathCache as $folder) {
      $path.= '/' . $folder;
      if ( !is_dir($path) ) mkdir($path, 0777);
    }
    $path.= '/' . $this->ID;
    if ( !is_dir($path) ) mkdir($path, 0777);
    //  коррекция имени файла
    $filename = System_String::Translit_File($file['name']);
    while ( file_exists($path . '/' . $filename) )
    {
      $m = explode('.', $filename);
      $m[0].= '_';
      $filename = implode('.', $m);
    }
    //  размер файла
    $file['size'] = filesize($file['tmp_name']);
    //  resize
    if ( 0 < count($resize) && 'image' == substr($file['type'], 0, 5) )
    {
      settype($resize['R'], "integer");
      settype($resize['X'], "integer");
      settype($resize['Y'], "integer");
      if ( $resize['X'] || $resize['Y'] || $resize['R'] ) {
        //  exec('convert -resize [100]x[200] '.$imgs['tmp_name'].' -> ../img/goods/imgs/'.$goods_id.'.'.$ext);
        System_Image::Resize($file['tmp_name'], $path . '/' . $filename, $resize['X'], $resize['Y'], $resize['R']);
      } else {
        copy($file['tmp_name'], $path . '/' . $filename);
      }
      //  chmod($path . '/' . $filename, 0666);
    }
    else
    {
      copy($file['tmp_name'], $path . '/' . $filename);
      //  chmod($path . '/' . $filename, 0666);
    }
    return str_replace(PATH_ADMIN . '/img/', '', $path) . '/' . $filename;
  }
  /**
   * Очистка кеша объекта
   *
   * @param string $ext - префикс-раширение файла кеша
   */
  public function Act_Cache_Clear($ext = '')
  {
    if ( 0 == count($this->_PathCache) ) {
      $this->_PathCache($this->ID);
    }
    if ( '' == $ext ) {
      System_File::File_Remove(PATH_CACHE . '/' . $this->Tbl_Name . '/' . implode('/', $this->_PathCache) . '/' . $this->ID);
    } else {
      System_File::File_Remove(PATH_CACHE . '/' . $this->Tbl_Name . '/' . implode('/', $this->_PathCache) . '/' . $this->ID, $ext);
    }
  }
  /**
   * Создание объекта наследуемого класса.
   * 
   * Реализует механизим абстрактного достпа к созданию объекта.
   * C учетом условий ( фильтры, УП )
   *
   * @param Filter $Filter
   * @return Obj_Item
   * @todo если инциализация конфигурации происходит в фильтре то отсюда можно это убрать 
   */
  public static function Create(Filter $Filter)
  {
    //  загрузка конфигурации объекта
    SC::IsInit($Filter->Tbl);
    //
    $Obj = new $Filter->Tbl();
    /* @var $Obj Obj_Item */
    /**
     * фильтры
     */
    foreach ($Filter->Filter as $prop => $row )
    {
      if ( isset($row['Value']) && $row['Value'] ) {
        $Obj->__set($prop, $row['Value']);
      }
    }
    /**
     * УП - условие пользователя
     */
    foreach (SC::$PropAll[$Filter->Tbl] as $prop => $row)
    {
      if ( isset(SC::$ConditionUser[$prop]) ) {
        $Obj->__set($prop, SC::$ConditionUser[$prop]);
      }
    }
    //  сохранение объекта и возврат на него указателя
    $Obj->Save();
    return $Obj;
  }
  /**
   * Создание объекта наследуемого класса.
   * Через объектообразуйщий объект
   * C учетом условий ( фильтры, УП )
   *
   * @param Filter $Filter
   * @return Obj_Item
   */
  public function Create_Advanced(Filter $Filter)
  {
    //  загрузка конфигурации объекта
    SC::IsInit($Filter->Tbl);
    //
    DB::Set_Query("INSERT INTO {$Filter->Tbl} (ID) VALUES ($this->ID)");
    $Obj = new $Filter->Tbl($this->ID);
    /* @var $Obj Obj_Item */
    /**
     * фильтры
     */
    foreach ($Filter->Filter as $prop => $row )
    {
      if ( isset($row['Value']) && $row['Value'] ) {
        $Obj->__set($prop, $row['Value']);
      }
    }
    /**
     * УП - условие пользователя
     */
    foreach (SC::$PropAll[$Filter->Tbl] as $prop => $row)
    {
      if ( isset(SC::$ConditionUser[$prop]) ) {
        $Obj->__set($prop, SC::$ConditionUser[$prop]);
      }
    }
    //  сохранение объекта и возврат на него указателя
    $Obj->Save();
    return $Obj;
  }
  /**
   * Создание связи с передаваемым объектом
   * УС - Условие связи
   *
   * @param object $Obj
   * @return bolean
   */
  public function Create_Link($Obj)
  {
    //  загрузка конфигурации объекта
    SC::IsInit($this->Tbl_Name);
    //
    $link = SC::$Link[$this->Tbl_Name][$Obj->Tbl_Name];
    //  один ко многим  ( здесь можно поставить УП выше УС )
    //  по умолчанию переход по связи затирает УП !isset($sql_where[$link['LinkP']])
    if ( !isset($link['LinkC']) )
    {
      $sql = "
      UPDATE " . $Obj->Tbl_Name . "
      SET
        " . $link['LinkP'] . " = " . $this->ID . "
      WHERE
        ID = " . $Obj->ID . "
      ";
      DB::Set_Query($sql);
    }
    //  многие ко многим
    else
    {
      $sql = 'INSERT INTO ' . $this->Tbl_Name . '_Link_' . $Obj->Tbl_Name . '
        (' . $link['LinkP'] . ', ' . $link['LinkC'] . ') 
      VALUES
        (' . $this->ID . ', ' . $Obj->ID . ')
      ';
      DB::Set_Query($sql);
    }
    return true;
  }
  /**
   * Загрузка объекта целиком или частично через массив
   *
   * Если массив пустой или не указан загрузка происходит из БД.
   * Если указан то загружается из него.
   *
   * @param array $row - массив данных свойств объекта
   * @return array or bolean
   */
  public function Load($row = array())
  {
    //  полная загрузка объекта
    if ( 0 == count($row) )
    {
      /**
       * Механизм проверки загрузки объекта через методы инициализации (Init_)
       * Если такого объекта нет или запрос был неправильный.
       */
      if ( 0 == $this->ID ) return false;
      $row = DB::Get_Query_Row("SELECT * FROM {$this->Tbl_Name} WHERE ID = {$this->ID}");
      if ( 0 < count($row) ) {
        foreach ($row as $Prop => $Value) $this->$Prop = $Value;
        $this->_Prop_Change = array();
        $this->_Is_Load = true;
        return $row;
      } else {
        return false;
      }
    }
    //  частичная или полная загрузка объекта через массив
    foreach ($row as $Prop => $Value) {
      $this->$Prop = $Value;
      unset($this->_Prop_Change[$Prop]);
    }
    unset($row);
    return true;
  }
  /**
   * Загрузка свойств объекта.
   *
   * Загружаемые свойства задаются через запятую
   * Их колмчество обционально, но не менее одного
   *
   * @return void
   */
  public function Load_Prop()
  {
    if ( func_num_args() )
    {
      $prop_list = func_get_args();
      $row = DB::Get_Query_Row("SELECT " . implode(', ' , $prop_list) . " FROM {$this->Tbl_Name} WHERE ID = {$this->ID}");
      foreach ($row as $Prop => $Value) {
        $this->$Prop = $Value;
        unset($this->_Prop_Change[$Prop]);
      }
    }
  }
  /**
   * Загрузка свойств объекта.
   *
   * Загружаемые свойства задаются через запятую
   * Их колмчество обционально, но не менее одного
   *
   * @return void
   */
  public function Load_Prop_Language()
  {
    if ( func_num_args() )
    {
      $prop_list = func_get_args();
      $row = DB::Get_Query_Row("SELECT " . implode(', ' , $prop_list) . " FROM {$this->Tbl_Name}_Language WHERE {$this->Tbl_Name}_ID = {$this->ID} AND Language = " . LANG_ID);
      foreach ($row as $Prop => $Value) {
        $this->$Prop = $Value;
        unset($this->_Prop_Change[$Prop]);
      }
    }
  }
  /**
   * Загрузка свойств объекта. Используя систему кеша.
   *
   * Загружаемые свойства задаются через запятую
   * Их колмчество обционально, но не менее одного
   *
   * @return void
   */
  public function Load_Prop_Cache()
  {
    if ( func_num_args() )
    {
      $prop_list = func_get_args();
      $index = implode('' , $prop_list);
      if ( !$row = $this->Get_Cache('prop_' . $index . '.ini') ) {
        $row = DB::Get_Query_Row("SELECT " . implode(', ' , $prop_list) . " FROM {$this->Tbl_Name} WHERE ID = {$this->ID}");
        $cache = System_File::Create_Ini($row, 1);
        $this->Set_Cache('prop_' . $index . '.ini', $cache);
      }
      foreach ($row as $Prop => $Value) {
        $this->$Prop = $Value;
        unset($this->_Prop_Change[$Prop]);
      }
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
//      print $prop . '<>';
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
    //  бинарные данные
    foreach ($_FILES as $prop => $row)
    {
      if ( !isset(SC::$Prop[$this->Tbl_Name][$prop]) ) continue;
      //  удаление
      if ( isset($data[$prop]['Rem']) && $this->$prop ) {
        if ( file_exists($path = PATH_SITE . '/img/' . $this->$prop) ) {
          unlink($path);
        }
        $this->__set($prop, null);
      }
      //  загрузка и созранение через форму
      else {
        //  загрузка файла
        if ( isset($data[$prop]['Edit']) ) {
          $value = $this->Act_File_Upload($row, $data[$prop]['Edit']);
        } else {
          $value = $this->Act_File_Upload($row);
        }
        if ( $value ) $this->__set($prop, $value);
      }
    }
    unset($data);
  }
  /**
   * Создание и/или Сохранение бъекта в БД.
   * Реализует механизим абстрактного достпа к сохранению объекта
   *
   * @return bolean
   */
  public final function Save()
  {
    //  загрузка конфигурации объекта
    SC::IsInit($this->Tbl_Name);
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
    if ( 0 < count($sql_update) )
    {
      //  изменение объекта
      if ( 0 < $this->ID )
      {
        $sql = "
        UPDATE " . $this->Tbl_Name . "
        SET
          " . implode(', ', $sql_update) . "
        WHERE
          ID = " . $this->ID . "
        ";
        DB::Set_Query($sql);
      }
      //  создание объекта
      else
      {
        if ( isset(SC::$PropAll[$this->Tbl_Name]['Direction']) ) {
          $sql = "SELECT MAX(Direction) FROM {$this->Tbl_Name}";
          $direction = DB::Get_Query_Cnt($sql);
          $sql_update[] = 'Direction=' . $direction + 1;
        }
        $this->ID = DB::Ins_Query("INSERT " . $this->Tbl_Name . " SET " . implode(', ', $sql_update));
        //  $this->Load();
      }
      $this->_Prop_Change = array();
    }
    //  создание объекта
    else if ( !$this->ID )
    {
      if ( isset(SC::$PropAll[$this->Tbl_Name]['Direction']) ) {
        $sql = "SELECT MAX(Direction) FROM {$this->Tbl_Name}";
        $direction = DB::Get_Query_Cnt($sql) + 1;
        $this->ID = DB::Ins_Query("INSERT INTO " . $this->Tbl_Name . " (Direction) VALUES({$direction})");
      } else {
        $this->ID = DB::Ins_Query("INSERT INTO " . $this->Tbl_Name . " (ID) VALUES(NULL)");
      }
      //  $this->Load();
    }
    return true;
  }
  /**
   * Удаление текущего объекта.
   * Реализует механизим абстрактного достпа к удалению объекта
   *
   * @return bolean
   */
  public final function Remove()
  {
    //  загрузка конфигурации объекта
    SC::IsInit($this->Tbl_Name);
    //  коррекция сортировки если есть
    if ( isset(SC::$PropAll[$this->Tbl_Name]['Direction']) ) {
      $sql = "SELECT Direction FROM {$this->Tbl_Name} WHERE ID = {$this->ID}";
      $direction = DB::Get_Query_Cnt($sql);
    }
    //  удаление
    if ( !DB::Query_Ignore('DELETE FROM ' . $this->Tbl_Name . ' WHERE ID = ' . $this->ID) ) {
      return false;
    }
    //  коррекция сортировки если есть
    if ( isset(SC::$PropAll[$this->Tbl_Name]['Direction']) ) {
      $sql = "UPDATE {$this->Tbl_Name} SET Direction = Direction - 1 WHERE Direction > {$direction}";
      DB::Set_Query($sql);
    }
    //  путь до кеша
    if ( 0 == count($this->_PathCache) ) {
      $this->_PathCache($this->ID);
    }
    $path = PATH_ADMIN . '/img/' . strtolower($this->Tbl_Name) . '/' . implode('/', $this->_PathCache) . '/' . $this->ID;
    if ( is_dir($path) ) {
      System_File::Folder_Remove($path);
    }
    $this->ID = 0;
    $this->_Prop_Change = array();
    $this->_Is_Load = false;
    return true;
  }
  /**
   * Удаление связи с передаваемым объектом
   * УС - Условие связи
   *
   * @param object $Obj
   * @return bolean
   */
  public function Remove_Link($Obj)
  {
    //  загрузка конфигурации объекта
    SC::IsInit($this->Tbl_Name);
    //
    $link = SC::$Link[$this->Tbl_Name][$Obj->Tbl_Name];
    //  один ко многим  ( здесь можно поставить УП выше УС )
    //  по умолчанию переход по связи затирает УП !isset($sql_where[$link['LinkP']])
    if ( !isset($link['LinkC']) )
    {
      $sql = "
      UPDATE " . $Obj->Tbl_Name . "
      SET
        " . $link['LinkP'] . " = NULL
      WHERE
        ID = " . $Obj->ID . "
      ";
      DB::Set_Query($sql);
    }
    //  многие ко многим
    else
    {
      $sql = '
      DELETE FROM ' . $this->Tbl_Name . '_Link_' . $Obj->Tbl_Name . '
      WHERE
        ' . $link['LinkP'] . ' = ' . $this->ID . '
        AND ' . $link['LinkC'] . ' = ' . $Obj->ID . '
      ';
      DB::Set_Query($sql);
    }
    return true;
  }
  /**
   * Алгоритм построения расположения кеша.
   *
   * Пострение оптимизированной структуры расположения кеша.
   * Для быстрого досутпа в файловой системе.
   * Максимальное значение кеш-индекса int(11)
   *
   * @param integer $id - идентификатор объекта
   * @param integer $depth - глубина прохода или вложенности каталогов
   * @param integer $count - верхняя граница кеш-индекса (кратное 100)
   */
  private function _PathCache($id, $depth = 1, $count = 100000000)
  {
    $step = intval($id / $count);
    if ( $step < 1 ) {
      $this->_PathCache[$depth] = 100;
      if ( 100 != $count ) {
        $this->_PathCache($id, $depth + 1, $count / 100);
      }
    } else {
      $this->_PathCache[$depth] = 100 + $step;
      if ( 100 != $count ) {
        $this->_PathCache($id % $count, $depth + 1, $count / 100);
      }
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
    if ( $value !== $this->$field ) {
      $this->$field = $value;
      $this->_Prop_Change[$field] = true;
      return true;
    }
  }
}