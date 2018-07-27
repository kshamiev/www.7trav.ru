<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Административный модуль без настроек.
 * 
 * Бланк адмнистративного модуля.
 * 
 * @package Core
 * @subpackage Core
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 17.03.2010
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
$Tpl_Mod->Assign('subj', 'Модуль не определен либо не имеет настроек (отсутствует)');
return $Tpl_Mod->Fetch_System($ModSystem);
