<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль логирования
 *
 * @package Cms
 * @subpackage Client
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 02.06.2009
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
global $Client;
/* @var $Client Client */

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
$Tpl_Mod = new Templates();
$Tpl_Mod->Assign('Client', $Client);
return $Tpl_Mod->Fetch($ModSystem->ModulUser);
