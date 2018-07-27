<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Работа с правми доступа на cвойства объектов.
 * <ol>
 * <li>Добавление, изменение, удаление прав на свойства объекта.
 * Происходит автоматически при работе с правами на объектные модкули.
 * </ol>
 * @package Core
 * @subpackage Access
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $op;
global $Access;
global $Obj;
/* @var $Obj Access */

//  Конфигурация объекта
SC::Init('Access_Prop', 'Groups');

/**
 * Удаление прав на свойства
 */
while ( 'obj_remove' == $op ) {
  //  Удаление прав на свойства объекта
  if ( 'ModSystem' == SC::$Rel['Access']['Table'] ) {
    $Access_Prop = new Access_Prop($Obj->ID);
    $Access_Prop->Remove_ModSystem(new ModSystem($_REQUEST['obj_id']));
  } else {
    $Access_Prop = new Access_Prop($_REQUEST['obj_id']);
    $Access_Prop->Remove_ModSystem(new ModSystem($Obj->ID));
  }
  break;
}

/**
 * Добавление прав на свойства
 */
while ( 'obj_add' == $op ) {
  //  Добавление прав на свойства объекта
  if ( 'ModSystem' == SC::$Rel['Access']['Table'] ) {
    $Access_Prop = new Access_Prop($Obj->ID);
    $Modules = new ModSystem($_REQUEST['obj_id']);
    $Access_Prop->Save_ModSystem($Modules, -1);
  } else {
    $Access_Prop = new Access_Prop($_REQUEST['obj_id']);
    $Modules = new ModSystem($Obj->ID);
    $Access_Prop->Save_ModSystem($Modules, -1);
  }
  break;
}

/**
 * Изменение прав на свойства
 */
while ( 'obj_save' == $op ) {
  //  Изменение прав на свойства объекта
  if ( 'ModSystem' == SC::$Rel['Access']['Table'] ) {
    $Access_Prop = new Access_Prop($Obj->ID);
    $Modules = new ModSystem($_REQUEST['obj_id']);
    $Access_Prop->Save_ModSystem($Modules, 1);
  } else {
    $Access_Prop = new Access_Prop($_REQUEST['obj_id']);
    $Modules = new ModSystem($Obj->ID);
    $Access_Prop->Save_ModSystem($Modules, 1);
  }
  break;
}
