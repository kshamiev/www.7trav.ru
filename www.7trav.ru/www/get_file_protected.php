<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Авторизовання отдача файлов Клиентам.
 * 
 * <ol>
 * <li>Отдача файлов, с учетом прав доступа.
 * <li>Счетчик скаченных - запрошенных файлов.
 * </ol>
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

global $subj_list;

/**
 * Сессия
 */
session_name(md5(DB_NAME));
session_start();
//  session_register('Registry');
$Registry = &$_SESSION['Registry'];
if ( !$Registry instanceof Registry ) {
  die($subj_list[59]);
} else {
  Registry::Set_Instance($Registry);
}
$Worker = Client::Factory();
if ( 0 == $Worker->ID ) {
  die($subj_list[59]);
}
SC::$ConditionUser = $Client->Condition;

/**
 * Запрос
 */
$mas = explode('/', substr($_SERVER["REQUEST_URI"], 5));
$Tbl = $mas[0];
$ID = $mas[1];

/**
 * Проверка прав доступа
 */
//  Вертикальные права (права на модуль)
$ModSystem = new ModSystem();
$ModSystem->Init_Tbl($Tbl);
if ( !$ModSystem->Access['V'] ) {
  die($subj_list[59]);
}
//  Горизонтальные права (права на объект)
SC::Init($Tbl);
$Obj = new $Tbl($ID, true);
foreach (SC::$Prop[$Tbl] as $Prop => $row) {
  if ( isset($Worker->Condition[$Prop]) && $Worker->Condition[$Prop] != $Obj->$Prop ) {
    die($subj_list[59]);
  }
}

/**
 * Счетчик
 */
$sql = "INSERT INTO Statistic_DuwnLoadFileClient SET Client_ID = {$Worker->ID}, ModSystem_ID = {$ModSystem->ID}, ObjectID = {$ID}";
DB::Set_Query($sql);

/**
 * Отдача файла
 */
$link = PATH_ADMIN . $_SERVER["REQUEST_URI"];
header("Content-Disposition: attachment; filename = " . basename($link));
header("Content-Length: " . filesize($link));
$fp = fopen($link, "rb");
$file_name = fread($fp, filesize($link));
fclose($fp);
print $file_name;
exit();
