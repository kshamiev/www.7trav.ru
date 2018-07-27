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
global $sys_lang_list;
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
session_name(md5(DB_NAME . 'admin')); session_start();
//  session_register('Registry');
$Registry = &$_SESSION['Registry'];
if ( !$Registry instanceof Registry ) {
  $Registry = Registry::Get_Instance();
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

/**
 * РАБОТА МОДУЛЯ
 */
//	вход
if ( 'login' == $op )
{
  if ( !$subj = $Worker->Init_Login() )
  {
    //  запомнить меня
    if ( isset($_POST['Memory']) )
    {
      setcookie("Login", $_POST['Login'], time() + COOKIE_TIME, '/');
      setcookie("Passw", $_POST['Passw'], time() + COOKIE_TIME, '/');
    }
    header('location: main.php'); exit;
  }
}

/**
 * ВЫВОД
 */
//  логин и пароль
$Login = '';
if ( isset($_COOKIE['Login']) ) $Login = $_COOKIE['Login'];
$Tpl_Main->Assign('Login', $Login);
$Passw = '';
if ( isset($_COOKIE['Passw']) ) $Passw = $_COOKIE['Passw'];
$Tpl_Main->Assign('Passw', $Passw);
$Tpl_Main->Assign('Worker', $Worker);
$Tpl_Main->Assign_Link('sys_lang_list', $sys_lang_list);
print $Tpl_Main->Fetch('_index', 'index');
if ( '' != $subj ) print '<script language="JavaScript" type="text/javascript">alert(\''.$subj.'\');</script>';
exit;