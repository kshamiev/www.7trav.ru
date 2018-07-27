<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Главная страница сайта.
 * 
 * @package Cms
 * @subpackage Razdel
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
//  главня страница
if ( !$cache = $Razdel->Get_Cache('content.htm') ) { //  кеширование
  $Tpl_Mod = new Templates;
  $Tpl_Mod->Assign_Link('Article', $Razdel);
  //  хиты продаж
  $Tpl_Mod->Assign('goods_top_list', Goods::Get_TopSale());
  //  новинки магазина
  $Tpl_Mod->Assign('goods_new_list', Goods::Get_Vitrina());
  //
  $cache = $Tpl_Mod->Fetch($ModSystem->ModulUser);
  $Razdel->Set_Cache('content.htm', $cache);
}
return $cache;
