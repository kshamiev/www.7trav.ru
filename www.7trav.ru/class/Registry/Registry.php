<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Реализация шаблона Registry
 * Generic storage class helps to manage global data.
 * 
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @package Core
 * @subpackage Registry
 * @version 16.02.2009
 */
class Registry extends ArrayObject
{
  /**
   * Registry object provides storage for shared objects.
   *
   * @var object Registry
   */
  private static $_Registry = null;
  /**
   * Retrieves the default registry instance.
   *
   * @return Registry
   */
  public static function Get_Instance()
  {
    if ( self::$_Registry === null ) {
      self::Set_Instance(new self());
    }
    return self::$_Registry;
  }
  /**
   * Set the default registry instance to a specified instance.
   *
   * @param Registry $registry An object instance of type Registry, or a subclass.
   * @return void
   * @throws Exception if registry is already initialized.
   */
  public static function Set_Instance(Registry $Registry)
  {
    if ( self::$_Registry !== null ) {
      Logs::Save_File('Registry is already initialized', 'error_registry.log');
    }
    self::$_Registry = $Registry;
  }
  /**
   * Unset the default registry instance.
   * Primarily used in tearDown() in unit tests.
   * @returns void
   */
  public static function Unset_Instance()
  {
    self::$_Registry = null;
  }
  /**
   * Returns TRUE if the $index is a named value in the registry,
   * or FALSE if $index was not found in the registry.
   *
   * @param  string $index
   * @return boolean
   */
  public static function Is_Exists($index)
  {
    if ( self::$_Registry === null ) {
      return false;
    }
    return self::$_Registry->offsetExists($index);
  }
  /**
   * getter method, basically same as offsetGet().
   *
   * This method can be called from an object of type Registry, or it
   * can be called statically.  In the latter case, it uses the default
   * static instance stored in the class.
   *
   * @param string $index - get the value associated with $index
   * @return mixed
   * @throws Exception if no entry is registerd for $index.
   */
  public static function Get($index)
  {
    if ( !self::$_Registry->offsetExists($index) ) {
      return false;
    }
    return self::$_Registry->offsetGet($index);
  }
  /**
   * setter method, basically same as offsetSet().
   *
   * This method can be called from an object of type Registry, or it
   * can be called statically.  In the latter case, it uses the default
   * static instance stored in the class.
   *
   * @param string $index The location in the ArrayObject in which to store
   *   the value.
   * @param mixed $value The object to store in the ArrayObject.
   * @return void
   */
  public static function Set($index, &$value)
  {
    self::$_Registry->offsetSet($index, $value);
  }
  /**
   * Удаление из регистра оп индексу
   * 
   * @param string $index
   */
  public static function Unset_Index($index)
  {
    $instance = self::Get_Instance();
    /*@var $instance Registry*/
    if ( !$instance->offsetExists($index) ) {
      return;
    }
    $instance->offsetUnset($index);
  }
/**
 * @param string $index
 * @return mixed
 *
 * Workaround for http://bugs.php.net/bug.php?id=40442 (ZF-960).
 */
/*
  private function _Offset_Exists($index)
  {
    return array_key_exists($index, $this);
  }
  */
}