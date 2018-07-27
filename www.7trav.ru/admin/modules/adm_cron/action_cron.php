<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Кеширование расписания запуска демонов.
 * 
 * Кеширование расписания запуска демонов.
 * @package Core
 * @subpackage Cron
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

global $op;
global $Access;

/**
 * Кеширование расписания запуска демонов.
 */
while ( $op )
{
  if ( !$Access['E'] ) {
    break;
  }
  $sql = "SELECT * FROM Cron WHERE IsActiv = 'включен'";
  $fp = fopen(PATH_ADMIN . '/cron/cron.ini', 'w');
  foreach (DB::Get_Query($sql) as $row)
  {
    fputs($fp, '[' . $row['Demon'] . ']' . "\n");
    fputs($fp, 'Week="' . $row['Week'] . '"' . "\n");
    fputs($fp, 'Month="' . $row['Month'] . '"' . "\n");
    fputs($fp, 'Day="' . $row['Day'] . '"' . "\n");
    fputs($fp, 'Hour="' . $row['Hour'] . '"' . "\n");
    fputs($fp, 'Minute="' . $row['Minute'] . '"' . "\n");
  }
  fclose($fp);
  break;
}
