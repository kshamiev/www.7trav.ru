<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Главная страница сайта.
 * 
 * @package Cms
 * @subpackage Orders
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
global $Client;
/* @var $Client Client */
global $Basket;
/* @var $Basket Basket */
global $op;
global $subj;

if ( 'Orders-Save_Order' != $op && 0 == count($Client->Basket->Basket) ) {
  header('Location: /basket/'); exit;
}

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
$Tpl_Mod = new Templates;
//  станции метро
$Tpl_Mod->Assign('metro_list', Metro::Get_Metro_All());
//  клиент
if ( 2 != $Client->Groups_ID && !isset($_POST['Metro_ID']) ) {
  $_POST['Metro_ID'] = $Client->Metro_ID;
}
if ( 2 != $Client->Groups_ID && !isset($_POST['Address']) ) {
  $_POST['Address'] = $Client->Address;
}
if ( !isset($_POST['Comment']) ) {
  $_POST['Comment'] = "Удобное время звонка:   \nУдобное время доставки:   \nДругое:  ";
}
$Tpl_Mod->Assign_Link('Client', $Client);
//  статья раздела
$Tpl_Mod->Assign_Link('Article', $Razdel);
//
$Tpl_Mod->Assign_Link('op', $op);
$Tpl_Mod->Assign_Link('subj', $subj);
//
return $Tpl_Mod->Fetch($ModSystem->ModulUser);
