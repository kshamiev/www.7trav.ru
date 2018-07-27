<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Ядро построения и инициализации системы.
 *
 * Формирование объектных классов.
 * Конфигурирование их свойств и файлов конфигурации.
 *
 * @package Core
 * @subpackage System
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 02.02.2010
 * @todo Сделать возможность рефаторинга модуля из модуля.
 */
class System_Factory
{
  /**
   * Массив сопоставлений типа поля в таблице методу его обработки в SQL запросах
   *
   * @var array
   */
  protected static $PropTypeDB;
  /**
   * Массив сопоставлений типа поля в таблице типу в php скриптах
   *
   * @var array
   */
  protected static $PropTypePhp;
  /**
   * Массив начальных префиксов для имен таблиц,
   * которые запрещены для обработки в системе или системно
   *
   * @var array
   */
  protected static $TblPrefixExcept;
  /**
   * Инициализация соответствий методов и типов обработки
   *
   * @return void
   */
  protected static function Init_Prop_Type()
  {
    self::$PropTypeDB = array(
    //  строки
    'char' => 'S',
    'varchar' => 'S',
    'tinytext' => 'T',
    'text' => 'T',
    'mediumtext' => 'T',
    'longtext' => 'T',
    'enum' => 'S',
    'set' => 'S',
    //  числа
    'bigint' => 'I',
    'mediumint' => 'I',
    'int' => 'I',
    'smallint' => 'I',
    'tinyint' => 'I',
    'bit' => 'I',
    'float' => 'F',
    'double' => 'F',
    'decimal' => 'F',
    'real' => 'F',
    //  дата и время
    'timestamp' => 'D',
    'datetime' => 'D',
    'date' => 'D',
    'time' => 'D'
    );
    self::$PropTypePhp = array(
    //  строки
    'char' => 'string',
    'varchar' => 'string',
    'tinytext' => 'string',
    'text' => 'string',
    'mediumtext' => 'string',
    'longtext' => 'string',
    'enum' => 'string',
    'set' => 'string',
    //  числа
    'bigint' => 'integer',
    'mediumint' => 'integer',
    'int' => 'integer',
    'smallint' => 'integer',
    'tinyint' => 'integer',
    'bit' => 'integer',
    'float' => 'float',
    'double' => 'float',
    'decimal' => 'float',
    'real' => 'float',
    //  дата и время
    'timestamp' => 'string',
    'datetime' => 'string',
    'date' => 'string',
    'time' => 'string'
    );
  }
  /**
   * Инициализация начальных префиксов в именах таблиц,
   * которые запрещены для обработки системой.
   *
   * @return void
   */
  protected static function Init_Tbl_Prefix_Except()
  {
    self::$TblPrefixExcept = array('_', 'Cache');
  }
  public static function Get_Class_Empty_System()
  {
    if ( file_exists($file = PATH_LOG . '/class_empty_system.log') ) {
      unlink($file);
    }
    $sql = "SELECT Tbl, ID FROM ModSystem WHERE Tbl IS NOT NULL";
    $table_list = DB::Get_Query_Two($sql);
    self::_Class_Empty_System(PATH_CLASS_OBJECT, $table_list);
    //
    /*
    header("Content-Disposition: attachment; filename = " . basename($file));
    header("Content-Length: " . filesize($file));
    $fp = fopen($file, "rb");
    $file = fread($fp, filesize($file));
    fclose($fp);
    print $file;
    */
  }
  private static function _Class_Empty_System($path, &$table_list, $class_path = '')
  {
    $flag_exists = false;
    if ( '' != $class_path ) $class_path.= '_';
    $fp_dir = opendir($path);
    while ( false != $name = readdir($fp_dir) )
    {
      if ( '.' == $name || '..' == $name ) continue;
      if ( is_dir($path . '/' . $name) )
      {
        $flag = self::_Class_Empty_System($path . '/' . $name, $table_list, $class_path . $name);
        if ( isset($table_list[$class_path . $name]) ) {
          unset($table_list[$class_path . $name]);
          $flag_exists = true;
        } else if ( false == $flag) {
          Logs::Save_File($path . '/' . $class_path . $name, 'class_empty_system.log');
        }
      }
    }
    return $flag_exists;
  }
  /**
   * Создание документации на БД
   *
   * @return string - относительный путь до сгенерированного файла документации
   */
  public static function Act_Generate_Documentation_DB()
  {
    $razdel_name_old = '';
    $razdel_name = '';
    $table_name_old = '';
    $table_name = '';
    //  Начало
    $fp = fopen(PATH_ADMIN . '/templates/_documentation/db.htm', 'w');
    $Tpl = new Templates;
    $navigation_list = array();
    $sql = '
    SELECT
      DISTINCT mg.Name as Razdel, m.Name, m.Tbl
    FROM ModSystem as m
      INNER JOIN ModSystem_Groups as mg ON mg.ID = m.ModSystem_Groups_ID
    WHERE
      m.Tbl IS NOT NULL
    ORDER BY
      mg.Name ASC, m.Tbl ASC
    ';
    $res_table = &DB::Query($sql);
    while ( false != $table = $res_table->fetch_assoc() )
    {
      $navigation_list[array_shift($table)][$table['Tbl']] = $table['Name'];
    }
    $res_table->close();
    $Tpl->Assign_Link('navigation_list', $navigation_list);
    $tpl = $Tpl->Fetch('_documentation', 'db_beg');
    fputs($fp, $tpl);
    //
    $sql = '
    SELECT
      DISTINCT mg.Name as Razdel, m.ID, m.Name, m.Modul, m.Tbl, m.Content
    FROM ModSystem as m
      INNER JOIN ModSystem_Groups as mg ON mg.ID = m.ModSystem_Groups_ID
    WHERE
      m.Tbl IS NOT NULL
    ORDER BY
      mg.Name ASC, m.Tbl ASC
    ';
    $res_table = &DB::Query($sql);
    while ( false != $table = $res_table->fetch_assoc() )
    {
      //  раздел
      $razdel_name = System_String::Translit_Url($table['Razdel']);
      if ( $razdel_name != $razdel_name_old )
      {
        $Tpl = new Templates;
        $Tpl->Assign_Link('razdel_name', $razdel_name);
        $Tpl->Assign_Link('razdel_name_old', $razdel_name_old);
        $Tpl->Assign_Link('table', $table);
        $tpl = $Tpl->Fetch('_documentation', 'db_razdel');
        fputs($fp, $tpl);
        $razdel_name_old = $razdel_name;
      }
      //  таблица
      $table_name = $table['Tbl'];
      $sql = "
      SELECT
        ID, Name, Prop, Form, TypeFull, Content
      FROM ModSystem_Prop
      WHERE
        ModSystem_ID = {$table['ID']}
      ORDER BY
        Sort ASC
      ";
      $prop_list = DB::Get_Query($sql);
      $Tpl = new Templates;
      $Tpl->Assign_Link('table_name', $table_name);
      $Tpl->Assign_Link('table_name_old', $table_name_old);
      $Tpl->Assign_Link('table', $table);
      $Tpl->Assign_Link('prop_list', $prop_list);
      $tpl = $Tpl->Fetch('_documentation', 'db_tbl');
      fputs($fp, $tpl);
      $table_name_old = $table_name;
    }
    $res_table->close();
    //  Завершение
    $Tpl = new Templates;
    $tpl = $Tpl->Fetch('_documentation', 'db_end');
    fputs($fp, $tpl);
    fclose($fp);
    return 'templates/_documentation/db.htm';
  }
  /**
   * Импорт архитектры БД в систему.
   *
   * Таблица = Модуль
   * <ol>
   * <li>Инициализация таблицы соостветсвий типов столбцов с  методами-обработчиками БД.
   * (varchar(150) => S, int(11) => I, datetime => D)
   * <li>Инициализация префиксов таблиц не подлежащих обработке. Скрытых.
   * <li>Чтение всех таблиц БД в массив.
   * <li>Сброс флага существования таблицы, связей и свойств.
   * (таблица = модуль)
   * <li>Обработка таблиц по порядку:
   * <li>Пропускаем скрытые таблицы.
   * <li>Обрабатываем связи многие ко многим.
   * Пропуская связи к таблицам расширения.
   * <li>Читаме - загружаем все свойства таблицы.
   * <li>Обрабатываем связи один ко многим.
   * Пропуская связи к таблицам расширения и от них, а также не родительские связи к таблицам отношений.
   * <li>Проверка струтуры свойств.
   * Отсутствие поля 'Name' при наличии поля 'ID' - идентификатора объекта является ошибкой.
   * <li>Анализ и установка модуля по умолчанию для обработки объектов данного типа таблицы.
   * На основании свойств и имени таблицы.
   * <li>Сохраняем или изменяем ели есть модуль в БД. Проставляя флаг существования.
   * <li>Добавляем новые данные о модуле - таблице в общий массив таблиц.
   * <li>Импорт свойств таблицы.
   * <li>Обработка связей по порядку:
   * <li>Проверка на существование связи на основании существования таблиц.
   * <li>Анализ блокировки связи при сохранение (Внутренняя связь между таблицами типа каталог).
   * <li>Сохраняем или изменяем ели есть связь между модулями в БД.
   * <li>Удаление несуществующих таблиц, связей  и свойтсв.
   * </ol>
   *
   * @return void
   */
  public static function Import_Structure()
  {
    self::Init_Prop_Type();
    self::Init_Tbl_Prefix_Except();
    //  массив всех таблиц
    $table_array = DB::Get_Query_One('SHOW TABLES');
    $table_array = array_flip($table_array);
    //  сброс флага существования для дальнешего удаления несуществующих элементов
    DB::Set_Query('UPDATE ModSystem SET IsExist = 0 WHERE Tbl IS NOT NULL');
    DB::Set_Query('UPDATE ModSystem_Link SET IsExist = 0 WHERE FieldP IS NOT NULL');
    DB::Set_Query('UPDATE ModSystem_Prop SET IsExist = 0');
    //
    if ( file_exists(PATH_ROOT . '/link.txt') ) {
      unlink(PATH_ROOT . '/link.txt');
    }
    //  РАБОТА
    $res_table = &DB::Query('SHOW TABLE STATUS');
    while ( false != $table = $res_table->fetch_assoc() )
    {
      //  Скрытые таблицы и таблицы кеша не обрабатываются
      foreach (self::$TblPrefixExcept as $tbl_prefix)
      {
        if ( $tbl_prefix == substr($table['Name'], 0, strlen($tbl_prefix)) ) {
          continue 2;
        }
      }
      /**
       * СВЯЗЬ МНОГИЕ КО МНОГИМ (КРОСС ТАБЛИЦЫ)
       */
      $row = explode('_Link_', $table['Name']);
      if ( 1 < count($row) )
      {
        $prop_list = DB::Get_Query_One('SHOW COLUMNS FROM ' . $table['Name']);
        $fp = fopen(PATH_ROOT . '/link.txt', 'a+');
        //  if ( in_array('Direction', $prop_list) );
        fputs($fp, $row[0] . ";" . $row[1] . ';' . $prop_list[0] . ';' . $prop_list[1] . "\n");
        fclose($fp);
        continue;
      }
      /**
       * СВОЙСТВА
       */
      $prop_list = array();
      $res = &DB::Query('SHOW FULL COLUMNS FROM ' . $table['Name']);
      $fp = fopen(PATH_ROOT . '/link.txt', 'a+');
      $flag_link_relation = 0;
      while ( false != $row = $res->fetch_assoc() )
      {
        $prop_list[$row['Field']] = $row;
        //  Связи один ко многим (исключаем связи с таблицами расширений объектов)
        if ( '_ID' == substr($row['Field'], -3) ) {
          //  обработка связей с таблицами отношений
          //  ставим флаг запрещающий далнейшее построение связей к таблицам отношений
          if ( !isset($prop_list['ID']) )
          {
            if ( $flag_link_relation < 2 ) {
              fputs($fp, preg_replace('~(_[A-Z]{1})?_ID$~si', '', $row['Field']) . ';' . $table['Name'] . ';' . $row['Field'] . ";\n");
            }
            $flag_link_relation++;
            continue;
          }
          else
          {
            fputs($fp, preg_replace('~(_[A-Z]{1})?_ID$~si', '', $row['Field']) . ';' . $table['Name'] . ';' . $row['Field'] . ";\n");
          }
        }
      }
      unset($flag_link_relation);
      fclose($fp);
      $res->close();
      //  проверка струтуры свойств
      if ( isset($prop_list['ID']) && !isset($prop_list['Name']) ) {
        Logs::Save_File('есть поле ID нет поля Name в таблице ' . $table['Name'], 'error_system_factory.log');
      }
      /**
       * ОБЪЕКТ
       */
      // тип объектов содержащихся в таблице
      if ( !isset($prop_list['ID']) ) {
        $table['Modul'] = '_relation';
      } else if ( isset($prop_list['Keyl']) && isset($prop_list['Keyr']) && isset($prop_list['Level']) ) {
        $table['Modul'] = '_catalog';
      } else if ( isset($prop_list['Direction']) ) {
        $table['Modul'] = '_item_direction';
      } else {
        $table['Modul'] = '_item';
      }
      //  записываем конфигурацию объекта в БД
      $sql = "SELECT ID FROM ModSystem WHERE Tbl = '{$table['Name']}'";
      if ( !$mod_id = DB::Get_Query_Cnt($sql) ) {
        $sql = "INSERT INTO ModSystem
          (Name, Modul, ModulUser, Tbl)
        VALUES
          (" . DB::S($table['Comment']) . ", '" . $table['Modul'] . "', 'adm_" . strtolower($table['Name']) . "', '" . $table['Name'] . "')";
        $mod_id = DB::Ins_Query($sql);
      } else {
        $sql = "UPDATE ModSystem
        SET
          Name = " . DB::S($table['Comment']) . ",
          Modul = '{$table['Modul']}',
          IsExist = 1
        WHERE
          ID = " . $mod_id;
        DB::Set_Query($sql);
        $sql = "UPDATE ModSystem SET IsExist = 1 WHERE Tbl = '{$table['Name']}'";
        DB::Set_Query($sql);
      }
      //  занесение в общий массив обрабатываемых таблиц
      $table_array[$table['Name']] = array();
      $table_array[$table['Name']]['ID'] = $mod_id;
      $table_array[$table['Name']]['Comment'] = $table['Comment'];
      $table_array[$table['Name']]['Modul'] = $table['Modul'];
      //  импорт свойств
      self::_Import_Prop($prop_list, $table, $mod_id);
    }
    $res_table->close();
    /**
     * СВЯЗИ
     */
    $fp = fopen(PATH_ROOT . '/link.txt', 'r');
    while ( !feof($fp) )
    {
      $row = fgetcsv($fp, 1024, ';');
      if ( !trim($row[0]) ) continue;
      //  такое возможно - это связь один ко многим
      if ( !isset($table_array[$row[0]]) || !isset($table_array[$row[1]]) ) {
        //  $sql = "DELETE FROM ModSystem_Link WHERE ModSystem_P_ID = " . $table_array[$row[0]]['ID'];
        Logs::Save_File('ошибочная связь ' . $row[0] . '_' . $row[1], '/error_system_factory.log');
        continue;
      }
      $sql = "SELECT COUNT(*) FROM ModSystem_Link WHERE ModSystem_P_ID = " . $table_array[$row[0]]['ID'] . " AND ModSystem_C_ID = " . $table_array[$row[1]]['ID'];
      if ( !DB::Get_Query_Cnt($sql) )
      {
        $IsLocked = 0;
        $sql = "SELECT COUNT(*) FROM ModSystem_Prop WHERE Prop = 'Direction' AND ModSystem_ID = {$table_array[$row[1]]['ID']}";
        if ( '_catalog' == $table_array[$row[1]]['Modul'] || 0 < DB::Get_Query_Cnt($sql) ) {
          $IsLocked = 1;
        }
        $sql = "INSERT INTO ModSystem_Link (
          ModSystem_P_ID,
          ModSystem_C_ID,
          FieldP,
          FieldC,
          IsLocked
        ) VALUES (
        {$table_array[$row[0]]['ID']},
        {$table_array[$row[1]]['ID']},
          '{$row[2]}',
          " . DB::S($row[3]) . ",
          {$IsLocked}
        )";
          $link_id = DB::Ins_Query($sql);
      }
      else
      {
        $sql = "UPDATE ModSystem_Link
        SET
          FieldP = '" . $row[2] . "',
          FieldC = " . DB::S($row[3]) . ",
          IsExist = 1
        WHERE
          ModSystem_P_ID = " . $table_array[$row[0]]['ID'] . "
          AND ModSystem_C_ID = " . $table_array[$row[1]]['ID'] . "
        ";
        DB::Set_Query($sql);
      }
    }
    fclose($fp);
    unlink(PATH_ROOT . '/link.txt');
    // удаления несуществующих элементов
    DB::Set_Query('DELETE FROM ModSystem WHERE IsExist = 0 AND Tbl IS NOT NULL');
    DB::Set_Query('DELETE FROM ModSystem_Link WHERE IsExist = 0 AND FieldP IS NOT NULL');
    DB::Set_Query('DELETE FROM ModSystem_Prop WHERE IsExist = 0');
  }
  /**
   * Импорт свойств таблицы (объектов)
   * $prop_list - массив свойств
   * $table - массив содержащий инофрмацию о таблице (объекта) в БД
   * $mod_id - идентификатор модуля обрабатывающий объект (таблицв в БД)
   * 
   * Столбец таблицы = Свойство класса.
   * <ol>
   * <li>Анализируем были ли изменены свойства.
   * <li>Сохраняем результат анализа в флаг.
   * От котого зависит будет ли обновлена сортировка все обарбываемых свойств.
   * <li>Далее обрабатываем свойства по порядку:
   * <li>Получаем чистый тип столбца без параметров (varchar(150) => varchar).
   * <li>Инициализируем метод обработчик свойства в БД.
   * Свойства с не определенным обработчиком не обрабатываются.
   * <li>Устанавливаем:
   * Сортировку.<br>
   * Блокировку (Связь на себя. Идентификатор. Связующие идентификаторы отношений. 'Keyl', 'Keyr', 'Level', 'Direction')<br>
   * Флаг не нулевого значение.<br>
   * Видимость в табличном представлении ('Name'. Все свойства таблиц отношений).<br>
   * Форму отображения свойства на основании метода обработчика и имени свойства.<br>
   * <li>Проверяем существование свойства по его имени и модулю.
   * <li>По результату проверки изменяем либо добавляем свойство в БД.
   * </ol>
   * @param array $prop_list - массив свойств таблицы
   * @param array $table - информация о таблице 
   * @param integer $mod_id - идентификатор модуля обрабатывающего таблицу
   * @return void
   */
  private static function _Import_Prop($prop_list, $table, $mod_id)
  {
    $Sort = 0;
    //  Анализ изменния самих свойств, для инициализации их сортировки
    //  при удалении
    $sql = "
    SELECT
      COUNT(*)
    FROM ModSystem_Prop
    WHERE
      ModSystem_ID = {$mod_id}
    ";
    $prop_count_old = DB::Get_Query_Cnt($sql);
    //  при добавлении и изменении
    $sql = "
    SELECT
      COUNT(*)
    FROM ModSystem_Prop
    WHERE
      ModSystem_ID = {$mod_id}
      AND Prop IN ('" . implode("', '", array_keys($prop_list)) . "')
    ";
    $prop_count_new = DB::Get_Query_Cnt($sql);
    if ( $prop_count_new == count($prop_list) && $prop_count_old == count($prop_list) ) {
      $flag_sort = false;
    } else {
      $flag_sort = true;
    }
    //
    foreach ($prop_list as $Prop => $Data)
    {
      /**
       * Тип свойства и Метод обработки
       */
      $Data['TypeFull'] = $Data['Type'];
      $arr = explode('(', $Data['Type']);
      $Data['Type'] = array_shift($arr);
      $DB = self::$PropTypeDB[$Data['Type']];
      if ( !$DB ) {
        Logs::Save_File('не определенный тип ' . $Data['Type'] . ' поля ' . $Prop . ' в таблице ' . $table['Name'], 'error_system_factory.log');
        continue;
      }
      /**
       * Сортировка
       */
      $Sort++;
      /**
       * Заблокированные свойства для стандартной обработки
       * 1 Связь на себя
       * 2 Связующие идентификаторы отношений
       * 3 Идентификатор и алгоритмичные поля
       */
      $Form = '';
      if ( false !== strpos($Prop, $table['Name'] . '_') ) {
        $IsLocked = 1;
        $Form = 'hidden';
      } else if ( '_relation' == $table['Modul'] && $Sort < 3 ) {
        $IsLocked = 1;
        $Form = 'hidden';
      } else if ( 'ID' == $Prop || 'Keyl' == $Prop || 'Keyr' == $Prop || 'Level' == $Prop || 'Direction' == $Prop ) {
        $IsLocked = 1;
        $Form = 'hidden';
      } else {
        $IsLocked = 0;
      }
      /**
       * Обязательное поле для заполнения
       */
      if ( 'YES'  == trim($Data['Null']) ) {
        $IsNull = 1;
      } else {
        $IsNull = 0;
      }
      /**
       * Видимость в списке
       */
      $IsVisible = 0;
      if ( '_relation' == $table['Modul'] || 'Name' == $Prop ) $IsVisible = 1;
      /**
       * Форма отображения свойства
       */
      //  целочисленный тип поля
      if ( 'I' == $DB )
      {
        if ( '_ID' == substr($Prop, -3) ) {
          $Form = 'select';
        } else if ( 'tinyint(1)' == $Data['TypeFull'] ) {
          $Form = 'check';
        } else if ( '' == $Form ) {
          $Form = 'text';
        }
      }
      //  число с плавающей точкой
      else if ( 'F' == $DB )
      {
        $Form = 'text';
      }
      //  перечисления
      else if ( 'enum' == $Data['Type'] )
      {
        if ( $IsNull )
        {
          $Form = 'select';
        }
        else
        {
          $Form = 'radio';
        }
      }
      //  множества
      else if ( 'set' == $Data['Type'] )
      {
        $Form = 'checkbox';
      }
      //  тексты
      else if ( 'T' == $DB )
      {
        if ( false !== strpos($Prop, 'Content') )
        {
          $Form = 'fckeditor';
        }
        else
        {
          $Form = 'textarea';
        }
      }
      //  строки
      else if ( 'S' == $DB )
      {
        if ( false !== strpos($Prop, 'Passw') )
        {
          $Form = 'passw';
        }
        else if ( false !== strpos($Prop, 'File') )
        {
          $Form = 'file';
        }
        else if ( false !== strpos($Prop, 'Img') )
        {
          $Form = 'img';
        }
        else
        {
          $Form = 'text';
        }
      }
      //  Дата и время
      else if ( 'D' == $DB )
      {
        $Form = 'datetime';
      }
      /**
       * ЗАНЕСЕНИЕ В БД
       */
      $prop_id = DB::Get_Query_Cnt("SELECT ID FROM ModSystem_Prop WHERE ModSystem_ID = {$mod_id} AND Prop = '{$Prop}'");
      //  существующее свойство
      if ( 0 < $prop_id )
      {
        $sql_sort = '';
        if ( true == $flag_sort ) {
          $sql_sort = "Sort = " . $Sort . ",";
        }
        $sql = "UPDATE ModSystem_Prop
        SET
          Name = " . DB::S($Data['Comment']) . ",
          Type = '" . $Data['Type'] . "',
          TypeFull = " . DB::S($Data['TypeFull']) . ",
          IsNull = " . $IsNull . ",
          {$sql_sort}
          DB = '" . $DB . "',
          IsExist = 1
        WHERE
          ID = " . $prop_id . "
        ";
          DB::Set_Query($sql);
      }
      //  новое свойство
      else
      {
        $flag_change = true;
        //
        $sql = "INSERT INTO ModSystem_Prop
          (
          ModSystem_ID,
          Name,
          Prop,
          Type,
          TypeFull,
          Form,
          IsVisible,
          IsNull,
          DB,
          Sort,
          IsLocked
          )
        VALUES
          (
          " . $mod_id . ",
          " . DB::S($Data['Comment']) . ",
          '" . $Prop . "',
          '" . $Data['Type'] . "',
          " . DB::S($Data['TypeFull']) . ",
          '" . $Form . "',
          " . $IsVisible . ",
          " . $IsNull . ",
          '" . $DB . "',
          " . $Sort . ",
          " . $IsLocked . "
          )";
        $prop_id = DB::Ins_Query($sql);
      }
    }
  }
  /**
   * Экспорт архитектуры БД в приложение.
   * 
   * Структура расположения всех файлов согласной философии Zend
   * <ol>
   * <li>Инициализация таблицы соостветсвий типов столбцов с  методами-обработчиками БД.
   * (varchar(150) => S, int(11) => I, datetime => D)
   * <li>Получение всех модулей которые обрабатывают таблицы.
   * <li>Создание класса для каждой таблицы (которую обрабатывает модуль) из соотвествующего для ее типа шаблона если его нет.
   * <li>(Каталог, Отношение, Объект)
   * <li>Экспорт конфигурации таблицы (Obj.ini)
   * <li>Рефакторинг свойств класса согласно столбцам таблицы.
   * <li>Экспорт конфигурации свойств таблицы (Prop.ini)
   * <li>Исключая столбцы 'ID', 'Keyl', 'Keyr', 'Level'
   * <li>Экспорт конфигурации для таблиц отношений.
   * <li>Как со стороны родителя так и потомка (Rel_Groups.ini, Rel_ModSystem.ini)
   * <li>Рефакторинг свойств дочерних объектов класса согласно дочерним связям таблицы.
   * <li>Экспорт конфигурации дочерних связей таблицы (Link.ini)
   * </ol>
   *
   * @return void
   */
  public static function Export_Structure()
  {
    self::Init_Prop_Type();
    //  РАБОТА
    $sql = "
    SELECT
      DISTINCT m.*
    FROM ModSystem as m
      INNER JOIN ModSystem_Prop as mp ON mp.ModSystem_ID = m.ID
    ORDER BY
      Tbl ASC
    ";
    $res_table = &DB::Query($sql);
    while ( false != $table = $res_table->fetch_assoc() )
    {
      //  инициализация пути расположения класса. Создание каталога класса
      $path_class = PATH_CLASS_OBJECT;
      $path_class_config = PATH_CLASS_CONFIG;
      foreach (explode('_', $table['Tbl']) as $folder)
      {
        $path_class.= '/' . $folder;
        if ( !is_dir($path_class) ) mkdir($path_class);
        $path_class_config.= '/' . $folder;
        if ( !is_dir($path_class_config) ) mkdir($path_class_config);
      }
      $path_class.= '/' . $table['Tbl'] . '.php';
      //  удаление конфигурационных файлов
      $file_list = glob($path_class_config . '/*.ini');
      foreach ($file_list as $file) {
        unlink($file);
      }
      /**
       * ЭКСПОРТ ОБЪЕКТА - ТАБЛИЦЫ
       */
      self::Export_Obj($table['ID'], $table);
      /**
       * ЭКСПОРТ ВСЕХ СВОЙСТВ И ОТНОШЕНИЙ
       */
      self::Export_PropAll($table['ID'], $table);
      /**
       * ЭКСПОРТ СВЯЗЕЙ
       */
      if ( '_relation' != $table['Modul'] ) {
        self::Export_Link($table['ID'], $table);
      }
    }
    $res_table->close();
    /**
     * Перемещение объектнообразованнх классво внутрь объектнообразующего
     */
    //  $class_list = glob(PATH_CLASS . '/*{0,1,2,3,4,5,6,7,8,9}', GLOB_BRACE + GLOB_ONLYDIR);
  }
  /**
   * ЭКСПОРТ ОБЪЕКТА - ТАБЛИЦЫ
   * 
   * @param integer $modsystem_id - Идентификатор модуля.
   * @param array $table - Информация о модуле.
   */
  public static function Export_Obj($modsystem_id, $table = array())
  {
    //  Инициализация
    if ( 0 == count($table) ) {
      $table = DB::Get_Query_Row('SELECT ID, Tbl, Modul, Name FROM ModSystem WHERE ID = ' . $modsystem_id);
    }
    $path_class = PATH_CLASS_OBJECT . '/' . str_replace('_', '/', $table['Tbl']) . '/' . $table['Tbl'] . '.php';
    $path_class_config = PATH_CLASS_CONFIG . '/' . str_replace('_', '/', $table['Tbl']);
    //
    $sql = "SHOW TABLE STATUS WHERE Name = '{$table['Tbl']}'";
    $table_info = DB::Get_Query_Row($sql);
    $fp = fopen($path_class_config . '/Obj.ini', 'w');
    //  fputs($fp, 'ID=' . $table['ID'] . "\n");
    fputs($fp, 'Tbl=' . $table['Tbl'] . "\n");
    fputs($fp, 'Comment="' . $table['Name'] . '"' . "\n");
    fputs($fp, 'Engine=' . $table_info['Engine'] . "\n");
    fputs($fp, "\n");
    fclose($fp);
    //  создание объектного класса при его отсутствии
    if ( !file_exists($path_class) ) {
      //  загрузка шаблона класса
      if ( '_item' == $table['Modul'] ) {
        $class_pattern_name = 'Obj_Item_Pattern.php';
      } else if ( '_catalog' == $table['Modul'] ) {
        $class_pattern_name = 'Obj_Catalog_Pattern.php';
      } else if ( '_relation' == $table['Modul'] ) {
        $class_pattern_name = 'Obj_Relation_Pattern.php';
      }
      $class = file_get_contents(PATH_CLASS . '/Obj/' . $class_pattern_name);
      //  парсинг шаблона класса
      if ( '_item' == $table['Modul'] ) {
        $class = str_replace('Pattern_Item', $table['Tbl'], $class);
      } else if ( '_catalog' == $table['Modul'] ) {
        $class = str_replace('Pattern_Catalog', $table['Tbl'], $class);
      } else if ( '_relation' == $table['Modul'] ) {
        $class = str_replace('Pattern_Relation', $table['Tbl'], $class);
      }
      //
      $class = str_replace('Pattern_Class', $table['Tbl'], $class);
      $class = str_replace('Pattern_Comment', $table['Name'], $class);
      $class = str_replace('Pattern_Date', date('d.m.Y'), $class);
      file_put_contents($path_class, $class);
    }
  }
  /**
   * ЭКСПОРТ СВОЙСТВ И ОТНОШЕНИЙ
   * 
   * @param integer $modsystem_id - Идентификатор модуля.
   * @param array $table - Информация о модуле.
   */
  public static function Export_PropAll($modsystem_id, $table = array())
  {
    //  Инициализация
    if ( 0 == count($table) ) {
      $table = DB::Get_Query_Row('SELECT ID, Tbl, Modul FROM ModSystem WHERE ID = ' . $modsystem_id);
    }
    $table = DB::Get_Query_Row('SELECT * FROM ModSystem WHERE ID = ' . $modsystem_id);
    $path_class = PATH_CLASS_OBJECT . '/' . str_replace('_', '/', $table['Tbl']) . '/' . $table['Tbl'] . '.php';
    $path_class_config = PATH_CLASS_CONFIG . '/' . str_replace('_', '/', $table['Tbl']);
    //
    $sql = "
    SELECT
      *
    FROM ModSystem_Prop
    WHERE
      ModSystem_ID = " . $table['ID'] . "
      AND Prop NOT IN ('ID', 'Keyl', 'Keyr', 'Level')
    ORDER BY
      Sort ASC
    ";
    $prop_list = DB::Get_Query($sql);
    if ( '_relation' == $table['Modul'] ) {
      $tbl_parent = preg_replace('~(_[A-Z]{1})?_ID$~si', '', $prop_list[0]['Prop']);
      $tbl_child = preg_replace('~(_[A-Z]{1})?_ID$~si', '', $prop_list[1]['Prop']);
      //  от 1 родителя
      $fp = fopen($path_class_config . '/Rel.ini', 'w');
      fputs($fp, 'Table=' . $tbl_child . "\n");
      fputs($fp, 'LinkP=' . $prop_list[0]['Prop'] . "\n");
      fputs($fp, 'LinkC=' . $prop_list[1]['Prop'] . "\n");
      fclose($fp);
      //  от 2 родителя
      if ( $tbl_parent != $tbl_child ) { // если отношения между одной и той же таблицей то этот механизм не имеет смысла.
        $fp = fopen($path_class_config . '/Rel_' . $tbl_child . '.ini', 'w');
        fputs($fp, 'Table=' . $tbl_parent . "\n");
        fputs($fp, 'LinkP=' . $prop_list[1]['Prop'] . "\n");
        fputs($fp, 'LinkC=' . $prop_list[0]['Prop'] . "\n");
        fclose($fp);
      }
      /**
       * @deprecated
       */
      $fp = fopen($path_class_config . '/Rel_' . $tbl_parent . '.ini', 'w');
      fputs($fp, 'Table=' . $tbl_child . "\n");
      fputs($fp, 'LinkP=' . $prop_list[0]['Prop'] . "\n");
      fputs($fp, 'LinkC=' . $prop_list[1]['Prop'] . "\n");
      fclose($fp);
      //
      unset($prop_list[0]);
      unset($prop_list[1]);
    }
    $fp = fopen($path_class_config . '/Prop.ini', 'w');
    foreach($prop_list as $prop)
    {
      //  if ( $prop['IsLocked'] ) continue;  //  возможно нужно перерабатывать
      fputs($fp, '[' . $prop['Prop'] . ']' . "\n");
      fputs($fp, 'ID="' . $prop['ID'] . '"' . "\n");
      fputs($fp, 'Comment="' . $prop['Name'] . '"' . "\n");
      fputs($fp, 'Type="' . $prop['Type'] . '"' . "\n");
      fputs($fp, 'Form=' . $prop['Form'] . "\n");
      fputs($fp, 'IsVisible=' . $prop['IsVisible'] . "\n");
      fputs($fp, 'IsNull=' . $prop['IsNull'] . "\n");
      fputs($fp, 'IsLocked=' . $prop['IsLocked'] . "\n");
      fputs($fp, 'IsFilter=' . $prop['IsFilter'] . "\n");
      fputs($fp, 'Colonka=' . $prop['Colonka'] . "\n");
      fputs($fp, 'DB=' . $prop['DB'] . "\n");
      if ( $prop['Content'] ) {
        fputs($fp, 'Content=1' . "\n");
      } else {
        fputs($fp, 'Content=0' . "\n");
      }
      fputs($fp, "\n");
    }
    fclose($fp);
    //  корректировка классов
    $prop_make = '';
    foreach ($prop_list as $prop)
    {
      if ( '_' == substr($prop['Prop'], 0, 1) ) {
        $access = 'private';
      } else {
        $access = 'protected';
      }
      $prop_make.= "\n  /**\n";
      $prop_make.= "   * {$prop['Name']}\n";
      $prop_make.= "   *\n";
      $prop_make.= "   * @var " . self::$PropTypePhp[$prop['Type']] . "\n";
      $prop_make.= "   */\n";
      $prop_make.= "  {$access} $" . $prop['Prop'] . ";";
    }
    $class = file_get_contents($path_class);
    $class = preg_replace('~  //  \[BEG\] Prop(.*?)  //  \[END\] Prop~si', "  //  [BEG] Prop{$prop_make}\n  //  [END] Prop", $class);
    file_put_contents($path_class, $class);
  }
  /**
   * ЭКСПОРТ СВОЙСТВ ДЛЯ ГРУППЫ
   * 
   * @param integer $modsystem_id - Идентификатор модуля.
   * @param integer $modsystem_id - Идентификатор группы.
   * @param string $path - Путь к конфигурационному файлу.
   */
  public static function Export_Prop($modsystem_id, $groups_id, $path)
  {
    $sql = "
    SELECT
      mp.Prop,
      mp.Name as Comment,
      mp.Type,
      mp.Form,
      ap.IsVisible,
      mp.IsNull,
      mp.IsFilter,
      mp.Colonka,
      mp.DB,
      ap.E
    FROM Access_Prop as ap
      INNER JOIN ModSystem_Prop as mp ON ap.ModSystem_Prop_ID = mp.ID
    WHERE
      ap.Groups_ID =  " . $groups_id . "
      AND mp.ModSystem_ID = " . $modsystem_id . "
    ORDER BY ap.Sort
    ";
    $prop_list = DB::Get_Query($sql);
    if ( 0 == count($prop_list) ) {
      $Access_Prop = new Access_Prop($groups_id);
      $Access_Prop->Save_ModSystem(new ModSystem($modsystem_id), -1);
    }
    $prop_list = DB::Get_Query($sql);
    System_File::Create_Ini($prop_list, 3, $path);
    unset($prop_list);
  }
  /**
   * ЭКСПОРТ СВЯЗЕЙ
   * 
   * @param integer $modsystem_id - Идентификатор модуля.
   * @param array $table - Информация о модуле.
   */
  public static function Export_Link($modsystem_id, $table = array())
  {
    //  Инициализация
    if ( 0 == count($table) ) {
      $table = DB::Get_Query_Row('SELECT ID, Tbl FROM ModSystem WHERE ID = ' . $modsystem_id);
    }
    //  $table = DB::Get_Query_Row('SELECT * FROM ModSystem WHERE ID = ' . $modsystem_id);
    $path_class = PATH_CLASS_OBJECT . '/' . str_replace('_', '/', $table['Tbl']) . '/' . $table['Tbl'] . '.php';
    $path_class_config = PATH_CLASS_CONFIG . '/' . str_replace('_', '/', $table['Tbl']);
    //
    $sql = "
    SELECT
      m.Name,
      m.Tbl,
      l.FieldP,
      l.FieldC
    FROM ModSystem_Link as l
      INNER JOIN ModSystem as m ON l.ModSystem_C_ID = m.ID AND l.ModSystem_P_ID = {$table['ID']}
    WHERE
      m.Tbl IS NOT NULL
    ORDER BY
      m.Name ASC
    ";
    $link_list = DB::Get_Query($sql);
    //
    $fp = fopen($path_class_config . '/Link.ini', 'w');
    foreach($link_list as $link)
    {
      fputs($fp, '[' . $link['Tbl'] . ']' . "\n");
      fputs($fp, 'LinkP=' . $link['FieldP'] . "\n");
      if ( $link['FieldC'] ) {
        fputs($fp, 'LinkC=' . $link['FieldC'] . "\n");
      }
      fputs($fp, 'Comment="' . $link['Name'] . '"' . "\n");
      fputs($fp, "\n");
    }
    fclose($fp);
    //  корректировка классов
    $link_make = '';
    foreach ($link_list as $link)
    {
      $link_make.= "\n  /**\n";
      $link_make.= "   * {$link['Name']}\n";
      $link_make.= "   *\n";
      $link_make.= "   * @var " . $link['Tbl'] . "\n";
      $link_make.= "   */\n";
      $link_make.= "  protected $" . $link['Tbl'] . ";";
    }
    $class = file_get_contents($path_class);
    $class = preg_replace('~  //  \[BEG\] Link(.*?)  //  \[END\] Link~si', "  //  [BEG] Link{$link_make}\n  //  [END] Link", $class);
    file_put_contents($path_class, $class);
  }
  /**
   * Общий факторинг-рефакторинг системы
   */
  public static function Factory()
  {
    self::Import_Structure();
    self::Export_Structure();
    System_File::Folder_Empty_Remove();
    System_File::File_Arhiv_Remove();
    System_File::Folder_System_Create();
  }
}