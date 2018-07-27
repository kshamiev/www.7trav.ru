<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Реализует возможность изменение комментария свойства.
 * 
 * <ol>
 * <li>Изменение комментария столбца таблицы при изменении названия свойства.
 * <li>Удаление прав на столбец таблицы при его блокировке
 * <li>Сброс конфигурации свойств модуля.
 * </ol>
 * @package Core
 * @subpackage ModSystem
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.10
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $op;
global $Access;
global $ObjParent;
/* @var $ObjParent ModSystem */

/**
 * Изменение комментария столбца таблицы
 * Удаление прав на столбец таблицы при его блокировке
 * 
 */
while ( 'obj_save' == $op || 'obj_save_ok' == $op )
{
  //  Изменение комментария столбца таблицы.
  //  Сброс конфигурации свойств модуля.
  $ObjParent->Load_Prop('Tbl');
  DB::Set_Query("ALTER TABLE {$ObjParent->Tbl} CHANGE {$Obj->Prop} {$Obj->Prop} {$Obj->TypeFull} COMMENT " . DB::S($_POST['Prop']['Name']));
  $ObjParent->Act_Config_Clear('prop');
  //  Удаление прав на столбец таблицы при его блокировке
  if ( $Obj->IsLocked ) {
    Access_Prop::Remove_ModSystem_Prop($Obj->ID);
  }
  break;
}
