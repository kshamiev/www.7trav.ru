<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Главная страница сайта.
 * 
 * @package Cms
 * @subpackage Basket
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
global $Basket;
/* @var $Basket Basket */

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
//  статья раздела
$Tpl_Mod = new Templates();
$Tpl_Mod->Assign_Link('Article', $Razdel);
$Tpl_Mod->Assign_Link('Basket', $Basket);
return $Tpl_Mod->Fetch($ModSystem->ModulUser);
