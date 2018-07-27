<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Для корректной записи в БД
 *
 * Запись целых чисел в БД
 * @param integer $int
 * @return integer or NULL
 */
function i($int)
{
  if ( 0 === $int || '0' === $int ) return 0;
  $int = intval($int);
  if ( $int ) return $int;
  else return 'NULL';
}

/**
 * Для корректной записи в БД
 *
 * Запись дробных чисел в БД
 * @param float $float
 * @return float or NULL
 */
function f($float)
{
  if ( 0 === $float || '0' === $float ) return 0;
  $float = str_replace(',', '.', $float);
  $float = floatval($float);
  if ( $float ) return $float;
  else return 'NULL';
}

/**
 * Для корректной записи в БД
 *
 * Запись строк в БД
 * @param string $str
 * @return string or NULL
 */
function s($str)
{
  if ( 0 === $str || '0' === $str ) return 0;
  $str = trim($str);
  if ( $str ) return "'" . DB::$DB->real_escape_string($str) . "'"; return 'NULL';
}

/**
 * Для корректной записи в БД
 *
 * Запись текстов в БД
 * @param string $str
 * @return string | NULL
 */
function t($str)
{
  if ( 0 === $str || '0' === $str ) return 0;
  $str = trim($str);
  if ( $str ) return "'" . DB::$DB->real_escape_string($str) . "'"; return 'NULL';
}

/**
 * Для корректной записи в БД
 *
 * Запись дат в БД
 * @param string $str
 * @return string | NULL
 */
function d($str)
{
  $str = trim($str);
  if ( $str ) return "'" . DB::$DB->real_escape_string($str) . "'"; return 'NULL';
}

/**
 * Работа с БД
 * Объект вносящий дополнительный уровень косвенности при работе с БД
 * позволяет собирать статистику об эффективности работы с БД
 *
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @package Core
 * @subpackage DB
 * @version 12.11.2008
 */
final class DB
{
  /**
   * Объект mysqli работы с БД
   *
   * @var mysqli
   */
  public static $DB;
  /**
   * Интерфейс для работы с хранимыми процедурами
   *
   * @var DB_Procedure
   */
  public static $Procedure;
  /**
   * Метод 0 уровня для одиночных запросов
   * Выполнение запроса и возвращение дескриптора на результат
   * В случае ошибки возвращает false
   * Ошибки запросов фиксируются в файл-логе error_db.log
   *
   * @return mysqli_result
   */
  public static function &Query($sql)
  {
    if ( !FLAG_RUN )
    {
      $sys_time = microtime(1);
      $res = self::$DB->query($sql);
      $sys_time = sprintf("%01.3f", microtime(1) - $sys_time);
      Logs::Save_File($sys_time, 'sql.log');
      Logs::Save_File($sql, 'sql.log');
    }
    else
    {
      $res = self::$DB->query($sql);
    }
    if ( !$res )
    {
      return self::Error_DB($sql, DB::$DB->error);
    }
    return $res;
  }
  /**
   * Метод 0 уровня для мультизапросов
   * Выполнение запроса и возвращение дескриптора на результат
   * В случае ошибки возвращает false
   * Ошибки запросов фиксируются в файл-логе error_db.log
   *
   * @return mysqli_result
   */
  public static function &Query_Multi($sql)
  {
    if ( !FLAG_RUN )
    {
      $sys_time = microtime(1);
      $res = self::$DB->multi_query($sql);
      $sys_time = sprintf("%01.3f", microtime(1) - $sys_time);
      Logs::Save_File($sys_time, 'sql.log');
      Logs::Save_File($sql, 'sql.log');
    }
    else
    {
      $res = self::$DB->multi_query($sql);
    }
    if ( !$res )
    {
      return self::Error_DB($sql, DB::$DB->error);
    }
    return $res;
  }
  /**
   * Метод 0 уровня для хранимых процедур
   * Выполнение запроса и возвращение дескриптора на результат
   * В случае ошибки возвращает false
   * Ошибки запросов фиксируются в файл-логе error_db.log
   *
   * @return mysqli_result
   */
  public static function &Query_Real($sql)
  {
    if ( !FLAG_RUN )
    {
      $sys_time = microtime(1);
      $res = self::$DB->real_query($sql);
      $sys_time = sprintf("%01.3f", microtime(1) - $sys_time);
      Logs::Save_File($sys_time, 'sql.log');
      Logs::Save_File($sql, 'sql.log');
    }
    else
    {
      $res = self::$DB->real_query($sql);
    }
    if ( !$res )
    {
      return self::Error_DB($sql, DB::$DB->error);
    }
    return $res;
  }
  /**
   * передача запроса в БД ( метод 0 уровня )
   *
   * @return bolean
   */
  public static function Query_Ignore($sql)
  {
    if ( self::$DB->query($sql) ) return true; return false;
  }
  /**
   * Общий запрос к БД
   * Получение данных в виде ассоциативного массива
   *
   * @param string $sql запрос
   * @return array
   */
  public static function Get_Query($sql)
  {
    $result = array();
    if ( !$res = &self::Query($sql) ) return false;
    /* @var $res mysqli_result */
    while ( false != $row = $res->fetch_assoc() ) $result[] = $row;
    $res->close();
    return $result;
  }
  /**
   * выбор из бд одной строки ( ассоциативный )
   *
   * @param string $sql запрос
   * @return array
   */
  public static function Get_Query_Row($sql)
  {
    if ( !$res = &self::Query($sql) ) return false;
    /* @var $res mysqli_result */
    $row = $res->fetch_assoc();
    $res->close();
    if ( is_array($row) ) return $row; return array();
  }
  /**
   * Выборка из бд одного поля ( список )
   *
   * @param string $sql запрос
   * @return list
   */
  public static function Get_Query_One($sql)
  {
    $result = array();
    if ( !$res = &self::Query($sql) ) return false;
    /* @var $res mysqli_result */
    while ( false != $row = $res->fetch_row() ) $result[] = $row[0];
    $res->close();
    return $result;
  }
  /**
   * Выборка из бд двух полей ( ассоциативный )
   * $sql - запрос
   *
   * @param string $sql
   * @return array
   */
  public static function Get_Query_Two($sql)
  {
    $result = array();
    if ( !$res = &self::Query($sql) ) return false;
    /* @var $res mysqli_result */
    while ( false != $row = $res->fetch_row() ) $result[$row[0]] = $row[1];
    $res->close();
    return $result;
  }
  /**
   * Получение результата работы для агрегирующих функций
   *
   * @param string $sql запрос
   * @return string or integer
   */
  public static function Get_Query_Cnt($sql)
  {
    if ( !$res = &self::Query($sql) ) return false;
    /* @var $res mysqli_result */
    $row = $res->fetch_row();
    $res->close();
    return isset($row[0]) ? $row[0] : false;
  }
  /**
   * Выборка одного объекта указанного типа
   *
   * Конструктор вызывается после выполнения запроса.
   * Тоесть параметры отрабатывают в конструкторе после инициализации свойств полученными данными из запроса.
   *
   * @param string $sql
   * @param string $class_type
   * @param array $param
   * @return object
   */
  public static function Get_Query_Obj($sql, $class_type, $param = array())
  {
    if ( !$res = &self::Query($sql) ) return false;
    //  конструктор вызывается после выполнения запроса !
    if ( 0 < count($param) ) {
      $result = $res->fetch_object($class_type, $param);
    } else {
      $result = $res->fetch_object($class_type);
    }
    $res->close();
    return $result;
  }
  /**
   * Выборка более одного объекта указанного типа
   *
   * Конструктор вызывается после выполнения запроса.
   * Тоесть параметры отрабатывают в конструкторе после инициализации свойств полученными данными из запроса.
   *
   * @param string $sql
   * @param string $class_type
   * @param array $param
   * @return object
   */
  public static function Get_Query_Obj_List($sql, $class_type, $param = array())
  {
    if ( !$res = &self::Query($sql) ) return false;
    //  конструктор вызывается после выполнения запроса !
    $result = array();
    if ( 0 < count($param) ) {
      while ( false != $Obj = $res->fetch_object($class_type, $param) ) $result[$Obj->ID] = $Obj;
    } else {
      while ( false != $Obj = $res->fetch_object($class_type) ) $result[$Obj->ID] = $Obj;
    }
    $res->close();
    return $result;
  }
  /**
   * Получение вариантов значений для полей SET & ENUM в виде списка
   *
   * @param string $tbl таблица
   * @param string $col столбец
   * @return list
   */
  public static function Get_EnumSet_Value($tbl, $col)
  {
    $result = array();
    if ( !$res = &self::Query("DESCRIBE " . $tbl . " " . $col) ) return false;
    /* @var $res mysqli_result */
    $row = $res->fetch_row();
    $res->close();
    $mas = explode("','", substr($row[1], strpos($row[1], "'") + 1, -2));
    sort($mas);
    return $mas;
  }
  /**
   * Изменения в бд (delete, update, insert)
   * $sql - запрос
   *
   * @param string $sql
   * @return bolean
   */
  public static function Set_Query($sql)
  {
    if ( self::Query($sql) ) return true; return false;
  }
  /**
   * Добавление в бд (insert)
   * $sql - запрос
   * возвращает ID добавленной записи (insert) в текущей трназакции
   *
   * @param string $sql
   * @return integer
   */
  public static function Ins_Query($sql)
  {
    if ( self::Query($sql) ) return DB::$DB->insert_id; return false;
  }
  /**
   * Блокировка таблицы на запись
   *
   * @param string $tbl
   * @return bolean
   */
  public static function Table_Lock_Write($tbl)
  {
    return self::Query('LOCK TABLE ' . $tbl . ' WRITE');
  }
  /**
   * Блокировка таблицы на чтение
   *
   * @param string $tbl
   * @return bolean
   */
  public static function Table_Lock_Read($tbl)
  {
    return self::Query('LOCK TABLE ' . $tbl . ' READ');
  }
  /**
   * Снятие всех блокировок в текущей транзакции ( сессии )
   *
   * @return bolean
   */
  public static function Table_Unlock()
  {
    return self::Query('UNLOCK TABLES');
  }
  protected static function Table_Rename($tbl_name_old, $tbl_name_new)
  {
    $sql = "RENAME TABLE `{$tbl_name_old}` TO `{$tbl_name_new}`";
  }
  protected static function Alter_Field_Rename($tbl_name, $field_old, $field_new)
  {
    $sql = "ALTER TABLE `{$tbl_name}` CHANGE `{$field_old}` `{$field_new}` INT(11) NOT NULL DEFAULT '1' COMMENT 'Валюта'";
  }
  protected static function Alter_Index($tbl_name, $index_old, $index_new)
  {
    $sql = "ALTER TABLE `{$tbl_name}` DROP INDEX `{$index_old}`, ADD INDEX `{$index_new}` (`{$index_new}`)";
  }
  /**
   * Парсинг строк для последующего табличного ипорта
   *
   * @param array $row
   * @return array
   */
  protected static function Row_Import_Parsing($row)
  {
    foreach ($row as $k => $v)
    {
      $v = trim($v);
      if ( !$v )
      {
        $row[$k] = '\N';
      } else
      if ( !preg_match('([0-9|,|.]+)s', $v) )
      {
        $row[$k] = str_replace(',', '.', $row[$k]);
      } else {
        $row[$k] = str_replace('\\', '\\\\', $row[$k]);
        $row[$k] = str_replace("\n", '\\\n', $row[$k]);
        $row[$k] = str_replace("\t", '\\\t', $row[$k]);
        $row[$k] = iconv("WINDOWS-1251", "UTF-8", $row[$k]);
      }
    }
    return $row;
  }
  /**
   * Для корректной записи в БД
   *
   * Запись целых чисел в БД
   * @param integer $int
   * @return integer or NULL
   */
  public static function I($int)
  {
    if ( 0 === $int || '0' === $int ) return 0;
    $int = intval($int);
    if ( $int ) return $int;
    else return 'NULL';
  }
  /**
   * Для корректной записи в БД
   *
   * Запись дробных чисел в БД
   * @param float $float
   * @return float or NULL
   */
  public static function F($float)
  {
    if ( 0 === $float || '0' === $float ) return 0;
    $float = str_replace(',', '.', $float);
    $float = floatval($float);
    if ( $float ) return $float;
    else return 'NULL';
  }
  /**
   * Для корректной записи в БД
   *
   * Запись строк в БД
   * @param string $str
   * @return string or NULL
   */
  public static function S($str)
  {
    if ( 0 === $str || '0' === $str ) return 0;
    $str = trim($str);
    if ( $str ) return "'" . DB::$DB->real_escape_string(trim($str)) . "'"; return 'NULL';
  }
  /**
   * Для корректной записи в БД
   *
   * Запись текстов в БД
   * @param string $str
   * @return string | NULL
   */
  public static function T($str)
  {
    if ( 0 === $str || '0' === $str ) return 0;
    $str = trim($str);
    if ( $str ) return "'" . DB::$DB->real_escape_string(trim($str)) . "'"; return 'NULL';
  }
  /**
   * Для корректной записи в БД
   *
   * Запись дат в БД
   * @param string $str
   * @return string | NULL
   */
  public static function D($str)
  {
    $str = trim($str);
    if ( $str ) return "'" . DB::$DB->real_escape_string(trim($str)) . "'"; return 'NULL';
  }
  //  
  /**
   * Перевод бинарных данных в формат бинарного SQL (0xFFFF...)
   * 
   * @param string $phrase
   */
  private static function _Sql_Hex3($phrase)
  {
    $rph = "0x";
    if ( 0 < strlen($phrase) )
    {
      for ($i=0; $i<strlen($phrase); $i++)
      {
        $chr = dechex(ord($phrase[$i]));
        if ( strlen($chr) < 2 ) {
          $chr = "0" . $chr;
        }
        $rph.= $chr;
      }
    }
    return $rph;
  }
  /**
   * Ведение логов ошибочных запросов
   *
   * @param string $err1 ошибочный запрос
   * @param string $err2 ответ сервера
   * @return bolean
   */
  public static function Error_DB($err1, $err2)
  {
    $fp = fopen(PATH_LOG . '/error_db.log', 'a+');
    fputs($fp, date("[d.m.Y H:i:s] ") . " ошибка базы данных!\n");
    fputs($fp, "страница сайта: " . HTTPL . "\n");
    fputs($fp, "откуда пришли: " . HTTPH . "\n");
    fputs($fp, "запрос: " . $err1 . "\n");
    fputs($fp, "ответ сервера: " . $err2 . "\n\n");
    fclose($fp); chmod(PATH_LOG . '/error_db.log', 0666);
    return false;
  }
}

//  Инициализация объекта mysqli
/* create a connection object which is not connected */
DB::$DB = mysqli_connect(DB_HOST, DB_LOGIN, DB_PASSW, DB_NAME);
DB::$DB->set_charset('utf8');
//  DB::$DB = mysqli_init();
/* set connection options */
//  DB::$DB->options(MYSQLI_INIT_COMMAND, "SET AUTOCOMMIT=1");
//  DB::$DB->options(MYSQLI_INIT_COMMAND, "SET CHARACTER SET UTF8");
//  DB::$DB->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
/* connect to server */
//  DB::$DB->real_connect(DB_HOST, DB_LOGIN, DB_PASSW, DB_NAME);
//  DB::$DB->select_db(DB_NAME);
/* check connection */
if ( mysqli_connect_errno() ) {
  die("mysqli - Невозможно соединится с сервером или выбрать БД.<br>\n Причина: " . mysqli_connect_error());
}

//  Инициализация интерфеса для работы с хранимыми процедурами
DB::$Procedure = new DB_Procedure;

//  mysql_query('SET CHARACTER SET UTF8');
//  mysql_query('SET CHARACTER SET cp1251_koi8');
//  mysql_query('set names cp1251');
//  mysql_query("SET CHARACTER SET DEFAULT", DB::$DB_Link);