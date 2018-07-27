<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль изменения профиля
 *
 * @package Cms
 * @subpackage Client
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 12.06.2009
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
global $Razdel;
/* @var $Razdel Razdel */
global $op;
global $subj;

if ( 2 == $Client->Groups_ID ) {
  header('Location: /registration/'); exit;
}

/**
 * ВЫВОД
 */
//  статья раздела
$Tpl_Mod = new Templates;
$Tpl_Mod->Assign('Article', $Razdel);
//  станции метро
$Tpl_Mod->Assign('metro_list', Metro::Get_Metro_All());
//  клиент
$Tpl_Mod->Assign('Client', $Client);
//
$Tpl_Mod->Assign_Link('op', $op);
$Tpl_Mod->Assign_Link('subj', $subj);
//
return $Tpl_Mod->Fetch($ModSystem->ModulUser);
