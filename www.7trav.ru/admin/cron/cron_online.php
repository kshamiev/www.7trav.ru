<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Обработка сотрудников и клиентов
 *
 * <ol>
 * <li>Инициализация онлайн статуса сотрудников
 * <li>Инициализация онлайн статуса клиентов
 * <li>Удаление неактивных (больше 6 часов) гостей
 * <li>Загрузка статистики посщений разделов
 * </ol>
 *
 * @package Core
 * @subpackage Cron
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 12.11.2008
 */

/**
 * безопасность
 */
if ( !class_exists('DB') ) return;

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $sys_time;
global $file_name;
global $file_log;
global $file_exit;

/**
 * НАСТРОЙКИ
 */

/**
 * РАБОТА
 */
//  неактивные сотрудники
foreach (glob(PATH_ADMIN . '/session/worker/*') as $path)
{
  $id = basename($path);
  $date = date('Y-m-d H:i:s', trim(file_get_contents($path)));
  DB::Set_Query("UPDATE Worker SET StatOnline = 'да', DateOnline = '{$date}' WHERE ID = {$id}");
  unlink($path);
}
$sql = "UPDATE Worker SET StatOnline='нет' WHERE DateOnline < NOW() - INTERVAL " . SESSION_WORKER_TIME . " SECOND";
DB::Set_Query($sql);

//  неактивные клиенты
foreach (glob(PATH_SITE . '/session/client/*') as $path)
{
  $id = basename($path);
  $date = date('Y-m-d H:i:s', trim(file_get_contents($path)));
  DB::Set_Query("UPDATE Client SET StatOnline = 'да', DateOnline = '{$date}' WHERE ID = {$id}");
  unlink($path);
}
$sql = "UPDATE Client SET StatOnline='нет' WHERE DateOnline < NOW() - INTERVAL " . SESSION_CLIENT_TIME . " SECOND";
DB::Set_Query($sql);

//  удаление неактивных гостей
$sql = "DELETE FROM Client WHERE ( Groups_ID = 2 OR Groups_ID IS NULL ) AND DateOnline < NOW() - INTERVAL " . SESSION_REMOVE_CLIENT . " SECOND";
DB::Set_Query($sql);

//  загрузка хитов (истрии посещений разделов сайта пользователями)
if ( file_exists($path = PATH_SITE . '/session/statrazdel.csv') )
{
  rename($path, '/tmp/statrazdel.csv');
  $sql = "
  LOAD DATA INFILE '/tmp/statrazdel.csv'
    INTO TABLE Statistic_Razdel
    FIELDS
      TERMINATED BY ';'
    (Statistic_Host_ID, Name, Date)
  ";
  DB::Set_Query($sql);
}

return 0;
