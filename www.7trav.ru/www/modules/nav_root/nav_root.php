<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль навигации по главный разделам сайта.
 * 
 * @package Cms
 * @subpackage Navigation
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 16.03.2009
 */

/**
 * безопасность
 */
if ( !class_exists('DB') ) return;

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $ModSystem;
/* @var $ModSite ModSite */
global $Razdel_Root;
/* @var $Razdel_Root Razdel */

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
if ( !$cache = $ModSystem->Get_Cache('child.htm') ) {
  $Tpl_Mod = new Templates();
  $Tpl_Mod->Assign('navigation_list', $Razdel_Root->Get_Navigation_Child());
  $cache = $Tpl_Mod->Fetch($ModSystem->ModulUser);
  $ModSystem->Set_Cache('child.htm', $cache);
}
return $cache;