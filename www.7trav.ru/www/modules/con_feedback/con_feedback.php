<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Обратная связь.
 * 
 * @package Cms
 * @subpackage Feedback
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
global $op;
global $subj;

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
//  статья раздела
$Tpl_Mod = new Templates;
$Tpl_Mod->Assign_Link('Article', $Razdel);
$Tpl_Mod->Assign_Link('op', $op);
$Tpl_Mod->Assign_Link('subj', $subj);
return $Tpl_Mod->Fetch($ModSystem->ModulUser);
