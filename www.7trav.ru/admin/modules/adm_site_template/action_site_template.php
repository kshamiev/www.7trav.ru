<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль удаляет кеш конфигурации удаляемого шаблона
 *
 * @package Cms
 * @subpackage Site
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $op;
global $Access;

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * Сброс кешированных конфигураций модулей шаблонов
 */
while ( 'obj_remove' == $op )
{
  if ( !$Access['R'] ) {
    break;
  }
  $Tpl_Site = new Site_Template($Obj->ID);
  $Tpl_Site->Act_Cache_Clear('config.ini');
  break;
}