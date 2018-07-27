<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль снятие статиски посещений
 *
 * @package Cms
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
 * СЕССИЯ
 */
session_name(md5(DB_NAME));
session_start();
$Registry = &$_SESSION['Registry'];
if ( !$Registry instanceof Registry ) {
  $Registry = Registry::Get_Instance();
} else {
  Registry::Set_Instance($Registry);
}

/**
 * КЛИЕНТ
 */
$Client = Client::Factory();
/* @var $Client Client */
if ( !$Client->Groups_ID ) {
  header('Content-Type: image/gif');
  die(file_get_contents('img/1x1.gif'));
}

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
//  Несчитаемая статистика
$ip = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
if ( file_exists('statistic.txt') ) {
  $sys_ip_not_stat = file('statistic.txt');
  foreach ($sys_ip_not_stat as $ip_not) {
    if ( $ip == trim($ip_not) ) {
      header('Content-Type: image/gif');
      die(file_get_contents('img/1x1.gif'));
    }
  }
}

/**
 * РАБОТА
 */
//  первый запрос
if ( !$Client->ID ) {
  $Client->Save();
  setcookie('client_id', $Client->ID, time() + COOKIE_TIME, '/');
  $Client->Set_Timeout();
  $Client->Get_Basket();
}
if ( !$Client->Ip ) {
  $Client->Ip = $ip;
  $Client->Statistic_Host_ID = Statistic_Host::Save_Host($Client);
}
//  ;следующие запросы
Statistic_Host::Save_Hit($Client->Statistic_Host_ID);

/**
 * ВЫВОД
 */
header('Content-Type: image/gif');
die(file_get_contents('img/1x1.gif'));
