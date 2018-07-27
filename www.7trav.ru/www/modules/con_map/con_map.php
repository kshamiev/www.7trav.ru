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
//  отступы
$otstup = array();
$otstup[1] = '&nbsp;&nbsp;&nbsp;&nbsp;';
$otstup[2] = $otstup[1] . '&nbsp;&nbsp;&nbsp;&nbsp;';
$otstup[3] = $otstup[2] . '&nbsp;&nbsp;&nbsp;&nbsp;';
$otstup[4] = $otstup[3] . '&nbsp;&nbsp;&nbsp;&nbsp;';
$otstup[5] = $otstup[4] . '&nbsp;&nbsp;&nbsp;&nbsp;';
$otstup[6] = $otstup[5] . '&nbsp;&nbsp;&nbsp;&nbsp;';
$otstup[7] = $otstup[6] . '&nbsp;&nbsp;&nbsp;&nbsp;';
$otstup[8] = $otstup[7] . '&nbsp;&nbsp;&nbsp;&nbsp;';
$otstup[9] = $otstup[8] . '&nbsp;&nbsp;&nbsp;&nbsp;';

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
//  статья раздела
if ( !$cache = $ModSystem->Get_Cache('content.htm') ) { //  кеширование
  $Tpl_Mod = new Templates;
  $Tpl_Mod->Assign('otstup', $otstup);
  $Tpl_Mod->Assign('razdel_list', Site::Get_Map());
  $cache = $Tpl_Mod->Fetch($ModSystem->ModulUser);
  $ModSystem->Set_Cache('content.htm', $cache);
}
return $cache;
