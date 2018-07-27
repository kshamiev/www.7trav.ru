<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль вывода справочной информации модуля.
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
$Tpl_Mod->Assign('ModSystem', $ModSystem);
return $Tpl_Mod->Fetch_System($ModSystem);
