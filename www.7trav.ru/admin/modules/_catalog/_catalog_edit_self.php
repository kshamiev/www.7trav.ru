<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Системный абстрактный модуль.
 * 
 * Реализует основную работу с объектами типа Catalog
 * Или объектами типа Каталог.
 * Создание, Изменение, Сохранение.
 * Доступ к зависимым модулям.
 * Работа с кешем.
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
global $op;
global $mod_link;
global $mod_link_blank;
global $Logs;
global $subj_list;

//  Конфигурация объекта
SC::Init($ModSystem->Tbl);

//  Права
$Access = $ModSystem->Access;

//  Шаблон
$Tpl_Mod = new Templates();

//  инициализация обрабатываемого объекта
$Obj = $ModSystem->Obj;
/* @var $Obj Obj_Catalog */

//  инициализация родительского объекта, если он есть
//  В режиме каталога всегда есть родитель, Родитель верхнего уровня 0
if ( count($ModSystem->Parent) ) {
  $ObjParent = new $ModSystem->Parent['Tbl']($ModSystem->Parent['Obj_ID']);
  $ObjParent->Load_Node();
} else {
  $ObjParent = new $ModSystem->Tbl();
}
/* @var $ObjParent Obj_Catalog */

/**
 * Фильтры
 */
//  инициализация фильтра
if ( !$ModSystem->Filter instanceof Filter ) {
  $Filter = new Filter($ModSystem->Tbl);
  //  общая инициализация
  $Filter->Set_All();
  //  сортировка
  $Filter->Set_Sort('Keyl', 'ASC');
  $ModSystem->Filter = $Filter;
} else {
  $Filter = $ModSystem->Filter;
}

/**
 * Инициализация свойств для вывода на редактирование
 */
$fckeditor_prop = array();
$textarea_prop_left = array();
$textarea_prop_right = array();
$Prop_List_Right = array();
$Prop_List_Left = SC::$Prop[$ModSystem->Tbl];
foreach ($Prop_List_Left as $prop => $row) {
  //  убираем УП
  if ( isset(SC::$ConditionUser[$prop]) ) {
    unset($Prop_List_Left[$prop]); continue;
  }
  //  убираем УС
  if ( isset($ObjParent) && $prop == SC::$Link[$ObjParent->Tbl_Name][$Obj->Tbl_Name]['LinkP'] ) {
    unset($Prop_List_Left[$prop]); continue;
  }
  //  fckeditor
  if ( 'fckeditor' == $row['Form'] ) {
    $fckeditor_prop[$prop] = $row['Comment'];
    unset($Prop_List_Left[$prop]); continue;
  }
  //  textarea
  if ( 'textarea' == $row['Form'] ) {
    if ( !$row['Colonka'] || 'right' == $row['Colonka'] ) {
      $textarea_prop_right[$prop] = $row['Comment'];
    } else {
      $textarea_prop_left[$prop] = $row['Comment'];
    }
    unset($Prop_List_Left[$prop]); continue;
  }
  //  сортировка свойтсв на колонки
  if ( 'right' == $row['Colonka'] || ( !$row['Colonka'] && ( 'datetime' == $row['Form'] || 'select' == $row['Form'] || 'radio' == $row['Form'] || 'check' == $row['Form'] || 'checkbox' == $row['Form'] ) ) ) {
    $Prop_List_Right[$prop] = $Prop_List_Left[$prop];
    unset($Prop_List_Left[$prop]); continue;
  }
}

/**
 * Пользовательская инициализация
 */
if ( file_exists($mod_path = 'modules/' . $ModSystem->ModulUser . '/init_' . strtolower($ModSystem->Tbl) . '.php') ) {
  include $mod_path;
}

/**
 * РАБОТА
 */
/**
 * Изменение объекта
 */
while ( 'obj_save' == $op || 'obj_save_ok' == $op || 'obj_add' == $op )
{
  if ( !$Access['E'] ) {
    $subj = $subj_list[50];
    $op = 'not';
    break;
  }
  //  изменение
  $Obj->Edit();
  //  сохранение
  $Obj->Save();
  //  сброс кеша объекта
  $ObjParent->Act_Cache_Clear();
  $Obj->Act_Cache_Clear();
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';сохранение позиции;' . $Obj->ID);
  $subj = $subj_list[2];
  break;
}

/**
 * Создание объекта
 */
while ( 'obj_new' == $op || 'obj_add' == $op ) {
  if ( !$Access['A'] ) {
    $subj = $subj_list[50];
    $op = 'not';
    break;
  }
  if ( 0 < $ObjParent->ID ) {
    $ModSystem->Obj = $Obj = $ObjParent->Create_Child($Filter);
  } else {
    $ModSystem->Obj = $Obj = $ObjParent::Create($Filter);
  }
  $Obj->Load();
  //  завершение
  $Logs->Save(';' . $Worker->Login . ';создание позиции;' . $Obj->ID);
  $subj = $subj_list[1];
  break;
}

/**
 * Пользовательская обработка
 */
if ( file_exists($mod_path = 'modules/' . $ModSystem->ModulUser . '/action_' . strtolower($ModSystem->Tbl) . '.php') ) {
  include $mod_path;
}

/**
 * ВЫВОД
 */
//  редактируемые текстовые свойства (fckeditor)
$Tpl_Mod->Assign_Link('fckeditor_prop', $fckeditor_prop);
//  редактируемые текстовые свойства (textarea)
$Tpl_Mod->Assign_Link('textarea_prop_left', $textarea_prop_left);
$Tpl_Mod->Assign_Link('textarea_prop_right', $textarea_prop_right);
//  редактируемые свойства 1 колонка
$Tpl_Mod->Assign_Link('Prop_List_Left', $Prop_List_Left);
//  редактируемые свойства 2 колонка
$Tpl_Mod->Assign_Link('Prop_List_Right', $Prop_List_Right);
//
$Tpl_Mod->Assign('Filter', $Filter);
$Tpl_Mod->Assign('ModSystem', $ModSystem);
$Tpl_Mod->Assign('Access', $Access);
$Tpl_Mod->Assign('Obj', $Obj);
$Tpl_Mod->Assign('mod_link', $mod_link);
$Tpl_Mod->Assign('mod_link_blank', $mod_link_blank);
$Tpl_Mod->Assign('op', $op);
if ( 0 == count($Prop_List_Right) && 0 == count($textarea_prop_right) ) {
  $ModSystem->Block.= '_one'; 
}
return $Tpl_Mod->Fetch_System($ModSystem);
