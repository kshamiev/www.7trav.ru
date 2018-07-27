<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль каталога продукции.
 * 
 * Постраничный вывод продукции с условием (или без) выбранного каталога.
 * 
 * @package WareHouse
 * @subpackage Goods
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 11.05.2010
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
global $page;

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
SC::Init('Goods');
/**
 * Фильтры
 */
if ( !$ModSystem->Filter instanceof Filter ) {
  if ( !isset($_POST['search']) || !$_POST['search'] = trim($_POST['search']) ) {
    header("Location: " . HTTPH); exit;
  }
  $Filter = new Filter('Goods', 1, 20, 11);
  $ModSystem->Filter = $Filter;
} else {
  if ( !$Client->Search ) {
    header("Location: " . HTTPH); exit;
  }
  $Filter = $ModSystem->Filter;
}
//
if ( isset($page) && $page ) {
  $Filter->Page = $page;
}
//
if ( isset($_POST['search']) && $_POST['search'] = trim($_POST['search']) ) {
  $Client->Search = $_POST['search'];
  $Filter->Page = 1;
}

$Tpl_Mod = new Templates();
$Tpl_Mod->Assign_Link('Razdel', $Razdel);
$Tpl_Mod->Assign_Link('Filter', $Filter);
$Tpl_Mod->Assign_Link('Basket', $Basket);
$Tpl_Mod->Assign_Link('Client', $Client);
$goods_list = Goods::Get_Goods_Search_List($Filter);
$Tpl_Mod->Assign_Link('goods_list', $goods_list);
$Tpl_Mod->Assign('page_list', $Filter->Get_Page_List());
return $Tpl_Mod->Fetch($ModSystem->ModulUser);
