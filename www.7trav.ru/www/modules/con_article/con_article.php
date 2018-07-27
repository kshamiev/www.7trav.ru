<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль статей.
 * 
 * Список статей раздела постранично.
 * Выбранная статья целиком.
 * 
 * @package Cms
 * @subpackage Article
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 16.03.2009
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
global $obj_id;

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
if ( isset($_REQUEST['obj_id']) && 0 < $_REQUEST['obj_id'] ) { //  статья
  $Article = new Article($_REQUEST['obj_id']);
  // защита невидимых статей и заодно от перегрузок сервера
  if ( $Article->Get_Cache('stop.log') ) {
    header('HTTP/1.1 404 Not Found');
    exit();
  }
  //  кеширование
  if ( !$cache = $Article->Get_Cache('content.htm') ) {
    $Article->Load();
    if ( 'да' != $Article->IsVisible ) {
      $Article->Set_Cache('stop.log', date('d.m.Y H;i'));
      header('HTTP/1.1 404 Not Found');
      exit();
    }
    $Tpl_Mod = new Templates();
    $Tpl_Mod->Assign_Link('Article', $Article);
    $Tpl_Mod->Assign('article_list', $Razdel->Get_Article_List());
    $cache = $Tpl_Mod->Fetch($ModSystem->ModulUser);
    $Article->Set_Cache('content.htm', $cache);
  }
  $Razdel->Set_Seo($Article->Seo);
  return $cache;
} else {  //  список статей
  if ( !$cache = $Razdel->Get_Cache("content.htm") ) {
    $Tpl_Mod = new Templates();
    $Tpl_Mod->Assign_Link('Article', $Razdel);
    $Tpl_Mod->Assign('article_list', $Razdel->Get_Article_List());
    $cache = $Tpl_Mod->Fetch($ModSystem->ModulUser);
    $Razdel->Set_Cache("content.htm", $cache);
  }
  return $cache;
}
