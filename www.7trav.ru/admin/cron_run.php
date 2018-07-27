<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Обертка для выполнения демонов по расписанию.
 *
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

set_time_limit(36000);

if ( isset($_SERVER['argv'][1]) && file_exists($sys_path = PATH_CRON . '/' . $_SERVER['argv'][1]) )
{

  // защита от многопоточного запуска
  if ( file_exists($sys_path_run = PATH_LOG . '/' . $_SERVER['argv'][1] . '.run') )
    return 0;
  file_put_contents($sys_path_run, 'this demon run');

  //  ИНИЦИАЛИЗАЦИЯ
  $sys_time = microtime(1);
  $file_name = array_shift(explode('.', $_SERVER['argv'][1]));
  $file_log = $file_name . '.log';
  $file_exit = $file_name . '.stop';
  Logs::Save_File('начало', $file_log);

  //  ЗАПУСК ДЕМОНА
  include $sys_path;

  //  ЗАВЕРШЕНИЕ
  $sys_time = sprintf("%01.3f", microtime(1) - $sys_time);
  Logs::Save_File('завершение - ' . memory_get_usage() . ' - ' . $sys_time, $file_log);

  // снятие защиты от многопоточного запуска
  unlink($sys_path_run);
}
return 0;