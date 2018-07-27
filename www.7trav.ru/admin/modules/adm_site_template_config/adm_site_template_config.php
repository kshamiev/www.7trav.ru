<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль работы с конфигурацией шаблона
 * 
 * Конфигурирование модулей в зонах шаблона
 * 
 * @package Cms
 * @subpackage Site
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
global $Logs;
global $subj_list;

//  Конфигурация объекта
SC::Init($ModSystem->Tbl, $ModSystem->Parent['Tbl']);
SC::Init($ModSystem->Parent['Tbl']);

//  Шаблон
$Tpl_Mod = new Templates;

//  права
$Access = $ModSystem->Access;

//  инициализация обрабатываемого объекта
if ( is_null($ModSystem->Obj) ) {
  $ModSystem->Obj = $Obj = new $ModSystem->Tbl($ModSystem->Parent['Obj_ID']);
} else {
  $Obj = $ModSystem->Obj;
}
/* @var $Obj Site_Template_Config */

$Tpl_Site = new Site_Template($ModSystem->Parent['Obj_ID']);

/**
 * Фильтры
 */
//  инициализация фильтра
if ( !$ModSystem->Filter instanceof Filter ) {
  $Filter = new Filter($ModSystem->Tbl);
  $ModSystem->Filter = $Filter;
}
else
{
  $Filter = $ModSystem->Filter;
}

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * конфигурирование
 */
while ( 'save' == $op )
{
  if ( !$Access['E'] ) {
    $subj = $subj_list[50];
    break;
  } else if ( !isset($_POST['Templates']) ) {
    $subj = $subj_list[51];
    break;
  }
  else
  {
    $Obj->Set_Configuration($_POST['Templates']);
    //  сброс кеша конфигурации
    $Tpl_Site->Act_Cache_Clear('config.ini');
    //  завершение
    $Logs->Save(';' . $Worker->Login . ';конфигурирование щаблона;' . $Obj->ID);
    $subj = $subj_list[2];
    break;
  }
}

/**
 * ВЫВОД
 */
$Tpl_Mod->Assign_Link('Obj', $Obj);
$Tpl_Mod->Assign('zone_list', $Tpl_Site->Get_Zone_Link());
$Tpl_Mod->Assign('modsystem_list', ModSystem::Get_ModSystem_Site());
$Tpl_Mod->Assign('template_list', $Obj->Get_Configuration());
$Tpl_Mod->Assign_Link('Filter', $Filter);
$Tpl_Mod->Assign('Access', $Access);
$Tpl_Mod->Assign_Link('ModSystem', $ModSystem);
$Tpl_Mod->Assign_Link('mod_link', $mod_link);
return $Tpl_Mod->Fetch_System($ModSystem);
