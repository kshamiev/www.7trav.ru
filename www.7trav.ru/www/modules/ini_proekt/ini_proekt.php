<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Инициализирующий модуль проекта.
 * 
 * @package Cms
 * @subpackage Core
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 22.04.2010
 */

/**
 * безопасность
 */
if ( !class_exists('DB') ) return;

global $Tpl_Main;
/* @var $Tpl_Main Templates */
global $Client;
/* @var $Client Client */

/**
 * Инициализация покупателькой корзины
 */
$Basket = $Client->Basket;
$Tpl_Main->Assign('Basket', $Basket);

/**
 * SAPE
 */
if ( !defined('_SAPE_USER') ) {
  define('_SAPE_USER', '06fc028d9ad61dfe0d5c1d2d13899d63');
}
require_once PATH_SITE . '/' . _SAPE_USER . '/sape.php';
$o = array();
$o['charset'] = 'utf-8';
//  $o['force_show_code'] = true;
//  $o['request_uri'] = $_SERVER['REDIRECT_URL'];
$sape = new SAPE_client($o);
$sape_link1 = $sape->return_links();
$Tpl_Main->Assign('sape_link1', $sape_link1);

$sape_context = new SAPE_context();

//
return '';
