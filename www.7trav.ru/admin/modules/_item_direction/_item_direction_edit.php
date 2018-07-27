<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Системный абстрактный модуль.
 * 
 * Реализует основную работу с объектами типа Item
 * Или просто объектами.
 * Создание, Изменение, Сохранение.
 * Доступ к зависимым модулям.
 * Работа с кешем.
 * @package Core
 * @subpackage Object
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 18.02.2010
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $ModSystem;
/* @var $ModSystem ModSystem */
global $Worker;
/* @var $Worker Worker */
global $op;
global $mod_link;
global $mod_link_blank;

//  Конфигурация объекта
SC::Init($ModSystem->Tbl);

//  Права
$Access = $ModSystem->Access;

//  Шаблон
$Tpl_Mod = new Templates;

//  инициализация обрабатываемого объекта и его загрузка для работы
if ( isset($_REQUEST['obj_id']) ) {
  $ModSystem->Obj = $Obj = new $ModSystem->Tbl($_REQUEST['obj_id'], true);
} else if ( is_null($ModSystem->Obj) ) {
  $ModSystem->Obj = $Obj = new $ModSystem->Tbl();
} else {
  $Obj = $ModSystem->Obj;
}
/* @var $Obj Obj_Item */

/**
 * ГОРИЗОНТАЛЬНОЕ ПРАВИЛО (на строки или условия пользователя)
 * проверка безопасности доступа к объекту
 */
if ( 0 < $Obj->ID ) {
  foreach (SC::$ConditionUser as $Prop => $Value) {
    if ( isset(SC::$Prop[$ModSystem->Tbl][$Prop]) && $Value != $Obj->$Prop ) {
      Logs::Save_File("Попытка доступа к запрещенному объекту {$Obj->Tbl_Name} - {$Obj->ID}", 'error_access_obj.log');
      header('location: ' . $mod_link); exit;
    }
  }
}

//  свойства fckeditor и высота окна редактирования
$fckeditor_prop = array();
foreach (SC::$Prop[$ModSystem->Tbl] as $prop => $row) {
  if ( 'fckeditor' == $row['Form'] ) {
    $fckeditor_prop[$prop] = $row['Comment'];
  }
}

/**
 * РАБОТА
 */

/**
 * ВЫВОД
 */
//  Дочерние модули с учетом прав доступа
$Link_List = $ModSystem->Get_ModSystem_Link_Access($Worker);
$Tpl_Mod->Assign('Link_List', $Link_List);
// навигация пуста для обычных объектов
$Tpl_Mod->Assign('Path', array());
//  редактируемые текстовые свойства (fckeditor)
$Tpl_Mod->Assign_Link('fckeditor_prop', $fckeditor_prop);
//
$Tpl_Mod->Assign('ModSystem', $ModSystem);
$Tpl_Mod->Assign('Access', $Access);
$Tpl_Mod->Assign('Obj', $Obj);
$Tpl_Mod->Assign('mod_link', $mod_link);
$Tpl_Mod->Assign('mod_link_blank', $mod_link_blank);
$Tpl_Mod->Assign('op', $op);
return $Tpl_Mod->Fetch_System($ModSystem);