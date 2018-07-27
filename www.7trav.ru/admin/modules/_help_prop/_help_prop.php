<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль вывода справочной информации свойств.
 *
 * @package Core
 * @subpackage System
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 24.05.2010
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $ModSystem;
/* @var $ModSystem ModSystem */

//  Шаблон
$Tpl_Mod = new Templates;

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
$ModSystem_Prop = new ModSystem_Prop($_REQUEST['prop_id']);
$ModSystem_Prop->Load_Prop('Content');
$Tpl_Mod->Assign('ModSystem_Prop', $ModSystem_Prop);
return $Tpl_Mod->Fetch_System($ModSystem);
