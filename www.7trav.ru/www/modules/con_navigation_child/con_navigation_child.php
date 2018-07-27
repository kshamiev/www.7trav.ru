<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль навигации по дочерним разделам сайта от текущего.
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
global $Razdel;
/* @var $Razdel Razdel */

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
if ( !$cache = $Razdel->Get_Cache('con_child.htm') ) {
  $Tpl_Mod = new Templates();
  $Tpl_Mod->Assign('Article', $Razdel);
  $Tpl_Mod->Assign('navigation_list', $Razdel->Get_Navigation_Child());
  $cache = $Tpl_Mod->Fetch($ModSystem->ModulUser);
  $Razdel->Set_Cache('con_child.htm', $cache);
}
return $cache;
