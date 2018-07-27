<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Ручной запуск демонов.
 * 
 * Ручной запуск демонов.
 * @package Core
 * @subpackage Cron
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $ModSystem;
/* @var $ModSystem ModSystem */
global $subj_list;

//  Шаблон
$Tpl_Mod = new Templates();

//  права
$Access = $ModSystem->Access;

//  инициализация обрабатываемого объекта
if ( isset($_REQUEST['obj_id']) )
{
  $Obj = new $ModSystem->Tbl($_REQUEST['obj_id'], true);
} else
{
  $subj = $subj_list[58];
  $Tpl_Mod->Assign('subj', $subj);
  return $Tpl_Mod->Fetch('_blank');
}

/**
 * ГОРИЗОНТАЛЬНОЕ ПРАВИЛО (на строки или условия пользователя)
 * проверка безопасности доступа к объекту
 */
if ( 0 < $Obj->ID )
{
  foreach (SC::$ConditionUser as $Prop => $Value)
  {
    if ( isset(SC::$Prop[$ModSystem->Tbl][$Prop]) && $Value != $Obj->$Prop )
    {
      Logs::Save_File("Попытка доступа к запрещенному объекту {$Obj->Tbl_Name} - {$Obj->ID}", 'error_access_obj.log');
      $subj = $subj_list[60];
      $Tpl_Mod->Assign('subj', $subj);
      return $Tpl_Mod->Fetch('_blank');
    }
  }
}

/**
 * РАБОТА МОДУЛЯ
 */
set_time_limit(36000);

if ( file_exists($sys_path = PATH_CRON . '/' . $Obj->Demon) )
{
  //  ИНИЦИАЛИЗАЦИЯ
  $sys_time = microtime(1);
  $file_name = array_shift(explode('.', $Obj->Demon));
  $file_log = $file_name . '.log';
  $flag_exit = $file_name . '.stop';
  Logs::Save_File('начало', $file_log);
  
  //  ЗАПУСК ДЕМОНА
  $Tpl_Mod->Assign('print', include $sys_path);
  
  //  ЗАВЕРШЕНИЕ
  $sys_time = sprintf("%01.3f", microtime(1) - $sys_time);
  Logs::Save_File('завершение - ' . memory_get_usage() . ' - ' . $sys_time, $file_log);
} else
{
  $Tpl_Mod->Assign('print', $subj_list[61]);
}

/**
 * ВЫВОД
 */
return $Tpl_Mod->Fetch_System($ModSystem);
