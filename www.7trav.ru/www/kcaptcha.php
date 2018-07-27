<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Формирование картинки Kcaptcha для Сотрудников
 * 
 * <ol>
 * <li>Служит для решения проблемы спама.Или механического добавления информации
 * </ol>
 * @package Cms
 * @subpackage Core
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @see Kcaptcha
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
session_name(md5(DB_NAME)); session_start();
$Registry = &$_SESSION['Registry'];
if ( !$Registry instanceof Registry ) {
  $Registry = Registry::Get_Instance();
} else {
  Registry::Set_Instance($Registry);
}

/**
 * ИНИЦИАЛИЗАЦИЯ
 */

//  пользователь
$Client = Client::Factory();

/**
 * ВЫВОД
 */
$Captcha = new Kcaptcha();
$Client->Keystring = $Captcha->getKeyString(); exit;
