<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Расширенная работа с модулями.
 * 
 * <ol>
 * <li>Сброс кеша конфигураций модулей шаблонов при удалении модуля принадлежащего к сайту (по типу зоны). 
 * <li>Изменение комментария таблицы объектов. Комментарии таблиц используются как название модулей в админке так и системе в целом
 * <li>Установка - Снятие авторизованного доступа к файлам объекта. Тип которго (таблицу) обрабатывает целевой модуль.
 * <li>Добавление прав для группы администратора на добавляемый модуль.
 * <li>Сброс конфигурации модуля.
 * </ol>
 * @package Core
 * @subpackage ModSystem
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 13.01.09
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $op;
global $Access;
global $Obj;
/* @var $Obj ModSystem */

/**
 * Сброс кешированных конфигураций модулей шаблонов
 * Изменение комментария таблицы объектов
 * Авторизация доступа к файлам объекта текущего типа
 * Добавление прав администратора на добавляемый модуль
 */
while ( 'obj_remove' == $op || 'obj_save' == $op || 'obj_save_ok' == $op || 'obj_add' == $op || 'obj_new' == $op )
{
  //  Сброс кешированных конфигураций модулей шаблонов
  if ( 'obj_new' != $op && $Obj->Zone_Type_ID ) {
    System_File::Cache_Clear_All('Site_Template');
  }
  if ( 'obj_remove' != $op && 'obj_new' != $op ) {
    //  Изменение комментария таблицы объектов
    //  сброс конфигурации модуля
    if ( $Obj->Tbl ) {
      DB::Set_Query("ALTER TABLE {$Obj->Tbl} COMMENT = " . DB::S($_POST['Prop']['Name']));
      $Obj->Act_Config_Clear('obj');
    }
    //  Авторизация доступа к файлам объекта текущего типа
    if ( $_POST['Prop']['IsProtectedFile'] ) {
      copy(dirname(__FILE__). '/htaccess', PATH_ADMIN . '/img/' . $Obj->Tbl_Name . '/.htaccess');
    } else if ( file_exists($path = PATH_ADMIN . '/img/' . $Obj->Tbl_Name . '/.htaccess') ) {
      unlink($path);
    }
  }
  //  Добавление прав администратора на добавляемый модуль
  if ( 'obj_new' == $op ) {
    $sql = "INSERT INTO Access
      (Groups_ID, ModSystem_ID, V, E, A, R, L, RL, S)
    VALUES
      (1, " . $Obj->ID . ", 1, 1, 1, 1, 1, 1, 1)";
    DB::Set_Query($sql);
  }
  break;
}
