<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Расширенная работа с зонами шаблонов
 * 
 * Сброс кешированных конфигураций модулей шаблонов
 * @package Cms
 * @subpackage Zone
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

global $op;
global $Access;
global $Obj;
/* @var $Obj Zone */

/**
 * Сброс кешированных конфигураций модулей шаблонов
 */
while ( 'obj_remove' == $op || 'obj_save' == $op || 'obj_save_ok' == $op )
{
  $Tpl_Site = new Site_Template($Obj->Site_Template_ID);
  $Tpl_Site->Act_Cache_Clear('config.ini');
  break;
}