<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Ajax
 * 
 * Выполнение контроллеров с помощью технологии ajax.
 * Возвращает данные в xml формате.
 * 
 * @package Cms
 * @subpackage Core
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 12.03.2010
 */

/**
 * Подключение конфигурации
 */
chdir(dirname(__FILE__));
require_once '../config.php';
global $op;
global $subj;

/**
 * ЗАГОЛОВКИ
 */
header('Pragma: no-cache');
header('Cache-Control: no_cache, must-revalidate');
//  header('Expires: Mon, 26 jul 2005 05:00:00 GMT');
//  header('Last-Modified: '.date('D, d M Y H:i:s').'GMT');
//  header("Content-Type: text/html; charset=utf-8");
header("Content-Type: text/xml; charset=utf-8");
//  setlocale(LC_ALL,'ru_RU.CP1251');
//  setlocale(LC_COLLATE,'ru_RU.CP1251');
setlocale(LC_CTYPE, 'ru_RU.UTF-8');
setlocale(LC_COLLATE, 'ru_RU.UTF-8');
//  setlocale(LC_CTYPE,'ru_RU.UTF-8');
//  print '<pre>'; print_r(localeconv()); print '</pre>';

/**
 * СЕССИЯ
 */
session_name(md5(DB_NAME . 'admin')); session_start();
$Registry = &$_SESSION['Registry'];
if ( !$Registry instanceof Registry ) {
  $Registry = Registry::Get_Instance();
} else {
  Registry::Set_Instance($Registry);
}

/**
 * РАБОТА КОНТРОЛЛЕРА
 */
if ( '' != $op ) {
  $mas = explode('-', $op);
  //  инициализация объекта
  if ( isset($mas[2]) && 0 < $mas[2] ) {
  	if ( false == $Object = Registry::Get($mas[0] . '_' . $mas[2]) ) {
  		$Object = $mas[0]::Factory($mas[2]);
  	}
  } else if ( false == $Object = Registry::Get($mas[0]) ) {
    $Object = $mas[0]::Factory();
  }
  //  инициализация метода
  $value = isset($mas[3]) ? $mas[3] : false ;
  if ( method_exists($Object, $method = 'Action_' . $mas[1]) ) {
    print $Object->$method($value);
  } else if ( method_exists($Object, $mas[1]) ) {
    print $Object->$mas[1]($value);
  } else {
    Logs::Save_File($op, 'error_method.log');
  }
}
exit();
