<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль статей.
 * 
 * Список статей раздела постранично.
 * Выбранная статья целиком.
 * 
 * @package Cms
 * @subpackage Article
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
/* @var $ModSystem ModSystem */
global $Razdel;
/* @var $Razdel Razdel */

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
//  список статей
if ( !$cache = $Razdel->Get_Cache("content.htm") ) {
  $Tpl_Mod = new Templates();
  $Tpl_Mod->Assign_Link('Article', $Razdel);
  $Tpl_Mod->Assign('article_list', $Razdel->Get_Navigation_ChildDescription());
  $cache = $Tpl_Mod->Fetch($ModSystem->ModulUser);
  $Razdel->Set_Cache("content.htm", $cache);
}
return $cache;
