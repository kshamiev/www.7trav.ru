<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Расширенная работа с разделами сайта.
 * 
 * Инициализация фильтра по модулям раздела.
 * Корректировка прав доступа к главным разделам (страницам) сайтов.
 * @package Cms
 * @subpackage Razdel
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $Filter;
global $ObjParent;
/* @var $ObjParent Razdel */

/**
 * Инициализация фильтра по модулям раздела.
 * Корректировка прав доступа к главным разделам (страницам) сайтов.
 */
if ( !$ModSystem->Filter instanceof Filter ) {
  $Filter->Add_Filter('ModSystem_ID', Razdel::Get_ModSystem_Content());
}
if ( 0 == $ObjParent->ID && 0 < $Worker->Site_ID ) {
  $Site = new Site($Worker->Site_ID);
  $Site->Load_Prop('Host');
  $Filter->Set_Filter('UrlRoot', $Site->Host);
} else {
  $Filter->Rem_Filter('UrlRoot');
}
