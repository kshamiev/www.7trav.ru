<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль по умолчанию или стартовый модуль.
 * 
 * Модуль работающий при входе в систему.
 * 
 * @package Core
 * @subpackage System
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $ModSystem;
/* @var $ModSystem ModSystem */
global $mod_link;

//  Шаблон
$Tpl_Mod = new Templates;

/**
 * ВЫВОД
 */
$Site = new Site();
$Site->Init_Host(HOST);
$Tpl_Mod->Assign('online_count', Statistic_Host::Get_Online_Status());
$Tpl_Mod->Assign_Link('Site', $Site);
$Tpl_Mod->Assign('ModSystem', $ModSystem);
$Tpl_Mod->Assign('mod_link', $mod_link);
return $Tpl_Mod->Fetch_System($ModSystem);
