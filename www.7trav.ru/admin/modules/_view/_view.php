<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Системный абстрактный модуль.
 * 
 * Реализует механизм просмотра объектов любого типа.
 * @package Core
 * @subpackage Object
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $ModSystem;
/* @var $ModSystem ModSystem */
global $mod_link;
global $subj_list;

//  Шаблон
$Tpl_Mod = new Templates;

//  Конфигурация объекта
SC::Init($ModSystem->Tbl);

//  инициализация обрабатываемого объекта
if ( isset($_REQUEST['obj_id']) ) {
  $Obj = new $ModSystem->Tbl($_REQUEST['obj_id'], true);
}
else {
  $subj = $subj_list[58];
  $Tpl_Mod->Assign('subj', $subj);
  return $Tpl_Mod->Fetch('_blank');
}

/**
 * ГОРИЗОНТАЛЬНОЕ ПРАВИЛО (на строки или условия пользователя)
 * проверка безопасности доступа к объекту
 */
if ( 0 < $Obj->ID ) {
  foreach (SC::$ConditionUser as $Prop => $Value) {
    if ( isset(SC::$Prop[$ModSystem->Tbl][$Prop]) && $Value != $Obj->$Prop ) {
      Logs::Save_File("Попытка доступа к запрещенному объекту {$Obj->Tbl_Name} - {$Obj->ID}", 'error_access_obj.log');
      $subj = $subj_list[55];
      $Tpl_Mod->Assign('subj', $subj);
      return $Tpl_Mod->Fetch('_blank');
    }
  }
}

/**
 * Фильтры
 */
//  инициализация фильтра
$Filter = new Filter($ModSystem->Tbl);
$Filter->Set_All();

/**
 * Инициализация свойств для вывода на просмотр
 */
$Prop_List = SC::$Prop[$ModSystem->Tbl];
foreach ($Prop_List as $prop => $row) {
  //  убираем fckeditor из загрузки, но не из видимости свойства
  if ( 'fckeditor' == $row['Form'] ) {
    unset($Prop_List[$prop]); continue;
  }
  //  убираем заблокированные свойства
  if ( $row['IsLocked'] ) {
    unset($Prop_List[$prop]); continue;
  }
  //  убираем УП
  if ( isset(SC::$ConditionUser[$prop]) ) {
    unset($Prop_List[$prop]); continue;
  }
  //  свойства свойства пароли
  if ( 'passw' == $row['Form'] ) {
    unset($Prop_List[$prop]);
  }
}

/**
 * Пользовательская инициализация
 */
if ( file_exists($mod_path = 'modules/' . $ModSystem->ModulUser . '/init_' . strtolower($ModSystem->Tbl) . '.php') ) {
  include $mod_path;
}

/**
 * ВЫВОД
 */
$Tpl_Mod->Assign_Link('Prop_List', $Prop_List);
$Tpl_Mod->Assign('Filter', $Filter);
$Tpl_Mod->Assign_Link('ModSystem', $ModSystem);
$Tpl_Mod->Assign_Link('Obj', $Obj);
return $Tpl_Mod->Fetch_System($ModSystem);
