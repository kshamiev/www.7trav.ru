<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль вывода статичной справочной информации.
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
return $Tpl_Mod->Fetch($ModSystem->ModulUser, $_REQUEST['filename']);
