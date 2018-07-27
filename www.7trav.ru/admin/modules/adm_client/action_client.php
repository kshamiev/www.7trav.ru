<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Работа с клиентами.
 * 
 * Корректировка доступа к клиентам (УП).
 * Решение коллизии системы, чтобы можно было работать с клиентами.
 * 
 * @package Cms
 * @subpackage Client
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

//  Корректировка доступа к клиентам (УП)
unset(SC::$ConditionUser['Groups_ID']);
