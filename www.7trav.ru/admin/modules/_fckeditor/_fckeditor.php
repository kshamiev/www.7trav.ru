<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Системный абстрактный модуль. Редактирование контента объектов.
 * 
 * Расширенный текстовой редактор.
 * Для редактирования больших текстов.
 * @package Core
 * @subpackage Object
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 * @see FCKeditor
 * @todo сбрасывать здесь кеш.
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $ModSystem;
/* @var $ModSystem ModSystem */
global  $op;
global $mod_link;
global $subj_list;

//  Шаблон
$Tpl_Mod = new Templates;

//  Конфигурация объекта
SC::Init($ModSystem->Tbl);

//  права
$Access = $ModSystem->Access;

//  инициализация обрабатываемого объекта
if ( !is_null($ModSystem->Obj) ) {
  $Obj = $ModSystem->Obj;
} else if ( isset($_REQUEST['obj_id']) ) {
  $Obj = new $ModSystem->Tbl($_REQUEST['obj_id']);
} else {
  $subj = $subj_list[58];
  $Tpl_Mod->Assign('subj', $subj);
  return $Tpl_Mod->Fetch('_blank');
}
/* @var $Obj Obj_Item */

//  размещение файлов для FCK_Editor
$path = strtolower($ModSystem->Tbl) . '/' . $_REQUEST['obj_id'];
Registry::Set('fck_editor_path', $path);

/**
 * РАБОТА
 */
if ( 'obj_save' == $op || 'obj_save_ok' == $op )
{
  if ( !$Access['E'] )
  {
    $subj = '! Вы не имеете прав на данную операцию !';
    $op = 'not';
    break;
  }
  $Obj->$_REQUEST['prop_edit'] = $_POST['content'];
  $Obj->Save();
  $Obj->Load_Prop('Name');
  $Obj->Act_Cache_Clear();
  $subj = $subj_list[2];
}
else
{
  $Obj->Load_Prop('Name', $_REQUEST['prop_edit']);
}
$Tpl_Mod->Assign('op', $op);

/**
 * ВЫВОД
 */
//  список возможных полей для релактирования
$prop_list = array();
foreach (SC::$Prop[$Obj->Tbl_Name] as $prop => $row)
{
  if ( 'fckeditor' == $row['Form'] ) {
    $prop_list[$prop] = $row['Comment'];
  }
}
$Tpl_Mod->Assign('prop_list', $prop_list);
//  редактируемое поле
$Tpl_Mod->Assign('prop_edit', $_REQUEST['prop_edit']);
//
$Tpl_Mod->Assign_Link('ModSystem', $ModSystem);
$Tpl_Mod->Assign('Obj', $Obj);
$Tpl_Mod->Assign('Access', $Access);
$Tpl_Mod->Assign('mod_link', $mod_link);
return $Tpl_Mod->Fetch_System($ModSystem);