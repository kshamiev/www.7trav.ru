<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Класс содержащий конфигурации объектов
 * Их свойтсва, связи, пути к ним
 *
 * @package Core
 * @subpackage SC
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 16.02.2009
 */
final class SC
{
  /**
   * Условия пользователя
   *
   * @var array ('Groups'=>2, ...)
   */
  public static $ConditionUser = array();
  /**
   * Массив содержащий конфигурацию объекта
   *
   * @var array
   */
  public static $Obj = array();
  /**
   * Массив содержащий конфигурацию всех свойств объекта за исключением системных (ID, Nested Set, ID отношений)
   *
   * @var array
   */
  public static $PropAll = array();
  /**
   * Массив содержащий конфигурацию свойств объекта для конкретной группы с учетом ее прав
   *
   * @var array
   */
  public static $Prop = array();
  /**
   * Массив содержащий конфигурацию связей объекта
   *
   * @var array
   */
  public static $Link = array();
  /**
   * Массив содержащий конфигурацию отношений объекта
   *
   * @var array
   */
  public static $Rel = array();
  /**
   * Проверка на существование типа объекта
   *
   * @param string $class_name - имя класса-таблицы
   * @return bolean
   */
  public static function IsExist($class_name)
  {
    if ( !is_dir(PATH_CLASS_OBJECT . '/' . str_replace('_', '/', $class_name)) ) {
      return false;
    }
    return true;
  }
  /**
   * Проверка и загрузки конфигурации объекта в случае отсутсвия
   *
   * @param string $class_name
   * @param string $relation_name - родительская таблица
   * @return bolean
   */
  public static function IsInit($class_name, $relation_name = '')
  {
    if ( !isset(self::$Obj[$class_name]) ) return self::Init($class_name, $relation_name);
    return true;  //  надо от этого уходить ( от завязки на возвращаемый результат, возможно он нигде уже и не используется )
  }
  /**
   * Загрузка конфигурации объекта
   *
   * @param string $class_name
   * @param string $relation_name - родительская таблица
   * @return bolean
   * @todo нужно еще оптимизировать
   */
  public static function Init($class_name, $relation_name = '')
  {
    if ( '' != $relation_name ) $relation_name = '_' . $relation_name;
    //  путь до конфигурационных файлов
    $path = PATH_CLASS_CONFIG . '/' . str_replace('_', '/', $class_name);
    //  получение группы !пользователя!
    if ( true == Registry::Is_Exists('Worker') ) {
      $groups_id = Registry::Get('Worker')->Groups_ID;
    } else if ( true == Registry::Is_Exists('Client') ) {
      $groups_id = Registry::Get('Client')->Groups_ID;
    }
    //$ModSystem = new ModSystem();
    //$ModSystem->Init_Tbl($class_name);
    //  загрузка и инициализация типа объекта
    if ( !file_exists($path1 = $path . '/Obj.ini') ) {
      $table = DB::Get_Query_Row("SELECT ID, Tbl, Modul, Name FROM ModSystem WHERE Tbl = '{$class_name}'");
      System_Factory::Export_Obj($table['ID'], $table);
    }
    self::$Obj[$class_name] = parse_ini_file($path1, true);
    //  загрузка и инициализация свойств для группы с учетом прав доступа
    if ( isset($groups_id) ) {
      if ( !file_exists($path1 = $path . '/Prop_' . $groups_id . '.ini') ) {
        $table = DB::Get_Query_Row("SELECT ID, Tbl, Modul, Name FROM ModSystem WHERE Tbl = '{$class_name}'");
        System_Factory::Export_Prop($table['ID'], $groups_id, $path1);
      }
      self::$Prop[$class_name] = parse_ini_file($path1, true);
    }
    //  загрузка и инициализация всех свойств
    if ( !file_exists($path1 = $path . '/Prop.ini') ) {
      $table = DB::Get_Query_Row("SELECT ID, Tbl, Modul, Name FROM ModSystem WHERE Tbl = '{$class_name}'");
      System_Factory::Export_PropAll($table['ID'], $table);
    }
    self::$PropAll[$class_name] = parse_ini_file($path1, true);
    //  загрузка и инициализация связей
    if ( file_exists($path1 = $path . '/Link.ini') ) {  
      self::$Link[$class_name] = parse_ini_file($path1, true);
    }
    //  загрузка и инициализация отношений
    if ( file_exists($path1 = $path . '/Rel' . $relation_name . '.ini') ) {
      self::$Rel[$class_name] = parse_ini_file($path1);
    }
    return true;
  }
}