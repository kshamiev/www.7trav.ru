<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Ядро системы. Административная часть.
 * 
 * <ol>
 * <li>Реализует левое меню навигации по модулям и логирование.
 * <li>Служит контейнером для системы (iframe)
 * </ol>
 * @package Core
 * @subpackage Core
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 * @link windows.php
 */

/**
 * Подключение конфигурации
 */
chdir(dirname(__FILE__));
require_once '../config.php';
global $op;

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
session_name(md5(DB_NAME . 'admin'));
session_start();
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
//  шаблон
$Tpl_Main = new Templates();

//  ;льзователь
$Worker = Worker::Factory();
/* @var $Worker Worker */
if ( 0 == $Worker->ID ) {
  Registry::Unset_Instance();
  session_unset(); session_destroy();
  die($Tpl_Main->Fetch('_index', 'close_session'));
}
//	выход ; таймауту
if ( SESSION_WORKER_TIME < $Worker->Timeout )
{
  Registry::Unset_Instance();
  session_unset(); session_destroy();
  die($Tpl_Main->Fetch('_index', 'close_session'));
}
//	инициализация онлайн статуса
else
{
  $Worker->Set_Timeout();
}

/**
 * РАБОТА МОДУЛЯ
 */
//  перезагрузка
if ( 'reload' == $op )
{
  $subj = $Worker->Init_Reload();
}
//	выход
else if ( 'logout' == $op )
{
  Registry::Unset_Instance();
  session_unset(); session_destroy();
  $Tpl_Main->Assign('exit', true);
  die($Tpl_Main->Fetch('_index', 'close_session'));
}

/**
 * ВЫВОД
 */
//  логин и пароль
$Tpl_Main->Assign('Worker', $Worker);
print $Tpl_Main->Fetch('_index', 'main');
if ( '' != $subj ) print '<script language="JavaScript" type="text/javascript">alert(\''.$subj.'\');</script>';
exit;