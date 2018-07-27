<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Менеджер запуска демонов по расписанию.
 *
 * @package Core
 * @subpackage Core
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 06.05.2009
 */

/**
 * Подключение конфигурации
 */
chdir(dirname(__FILE__));
require_once '../config.php';

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
$week = date('w');
$month = date('n');
$day = date('j');
$hour = date('G');
$minute = date('i') * 1;

//  $sql = "SELECT * FROM Cron WHERE IsActiv = 'включен'";
//  $sys_cron_list = DB::Get_Query($sql);
if ( !file_exists($path = PATH_ADMIN . '/cron/cron.ini') )
{
  $sql = "SELECT Demon, Week, Month, Day, Hour, Minute FROM Cron WHERE IsActiv = 'включен'";
  System_File::Create_Ini(DB::Get_Query($sql), 3, PATH_ADMIN . '/cron/cron.ini');
}
foreach (parse_ini_file($path, true) as $sys_demon => $sys_cron)
{
  //  Logs::Save_File($sys_cron['Demon'] . ' - ' . $sys_cron['Minute'], 'cron.log');
  //  ПРОВЕРКА ДАТЫ И ВРЕМЕНИ ЗАПУСКА
  if ( false == Cron::Act_Check_Date($week, $sys_cron['Week']) ) continue;
  if ( false == Cron::Act_Check_Date($month, $sys_cron['Month']) ) continue;
  if ( false == Cron::Act_Check_Date($day, $sys_cron['Day']) ) continue;
  if ( false == Cron::Act_Check_Date($hour, $sys_cron['Hour']) ) continue;
  if ( false == Cron::Act_Check_Date($minute, $sys_cron['Minute']) ) continue;
  //  Logs::Save_File($sys_cron['Demon'] . ' runing', 'cron.log');
  //  ЗАПУСК
  exec(PATH_PHP . ' ' . PATH_ADMIN . '/cron_run.php ' . $sys_demon . ' > /dev/null 2>&1 &');
}
return 0;