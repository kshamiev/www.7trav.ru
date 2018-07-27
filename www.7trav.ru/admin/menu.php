<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Ядро системы. Административная часть.
 * 
 * Служит оберткой для работы модулей системы в основном и отдельном окне.
 * <ol>
 * <li>Поиск модулей и их подключение.
 * <li>Загрузка прав, проверка и переходы между ними.
 * </ol>
 * @package Core
 * @subpackage Core
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

/**
 * Подключение конфигурации
 */
chdir(dirname(__FILE__));
require_once '../config.php';

/**
 * ЗАГОЛОВКИ
 */
header('Pragma: no-cache');
header('Cache-Control: no_cache, must-revalidate');
//  header('Expires: Mon, 26 jul 2005 05:00:00 GMT');
//  header('Last-Modified: '.date('D, d M Y H:i:s').'GMT');
//  header("Content-Type: text/html; charset=windows-1251");
header("Content-Type: text/html; charset=utf-8");
//  setlocale(LC_ALL,'ru_RU.CP1251');
//  print '<pre>'; print_r(localeconv()); print '</pre>';

/**
 * СЕССИЯ
 */
session_name(md5(DB_NAME . 'admin')); session_start();
//  session_register('Registry');
$Registry = &$_SESSION['Registry'];
if ( !$Registry instanceof Registry ) {
  $Tpl_Main = new Templates;
  die($Tpl_Main->Fetch('_index', 'close_session'));
} else {
  Registry::Set_Instance($Registry);
}

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $op;

//  шаблон
$Tpl_Main = new Templates;

//  пользователь
$Worker = Worker::Factory();
/* @var $Worker Worker */

/**
 * ВЫВОД
 */
//  навигация
if ( 0 < $Worker->ID ) {
  $Tpl_Main->Assign('navigation_array', ModSystem::Get_Navigation($Worker));
}
print $Tpl_Main->Fetch('_index', 'menu'); exit;
