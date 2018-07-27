<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Ядро системы. Административная часть.
 * 
 * Служит оберткой для работы модулей системы в основном и отдельном окне.
 * <ol>
 * <li>Поиск модулей и их подключение
 * <li>Загрузка прав, проверка и переходы между ними
 * <li>Переход к модулю через таблицу которую он обслуживает
 * </ol>
 * @package Core
 * @subpackage Core
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 18.05.2010
 */

/**
 * Подключение конфигурации
 */
chdir(dirname(__FILE__));
require_once '../config.php';
global $op;
global $subj;

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
if (! $Registry instanceof Registry) {
  $Tpl_Main = new Templates();
  die($Tpl_Main->Fetch('_index', 'close_session'));
} else {
  Registry::Set_Instance($Registry);
}

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
//  доступ к объектному модулю через указание обрабатываемой им таблицы
if (isset($_REQUEST['tbl_name'])) {
  $sql = "SELECT ID FROM ModSystem WHERE Tbl = '" . preg_replace('~(_[A-Z]{1})?_ID$~si', '', $_REQUEST['tbl_name']) . "'";
  $_REQUEST['mod_id'] = DB::Get_Query_Cnt($sql);
}
//  инициализация id модуля
if (isset($_REQUEST['mod_id'])) {
  $mod_id = $_REQUEST['mod_id'];
} else {
  $mod_id = 1;
}

//  шаблон
$Tpl_Main = new Templates();

//  пользователь
$Worker = Worker::Factory();
//  выход по таймауту
if ( !$Worker->ID || SESSION_WORKER_TIME < $Worker->Timeout ) {
  Registry::Unset_Instance();
  session_unset(); session_destroy();
  die($Tpl_Main->Fetch('_index', 'close_session'));
} else {  //  инициализация онлайн статуса
  $Worker->Set_Timeout();
}
//  заносим в конфигурайию УП для удобства дальнейщего использования
SC::$ConditionUser = $Worker->Condition;

/**
 * ИНИЦИАЛИЗАЦИЯ МОДУЛЯ
 */
//  флаг первичной работы модуля
$mod_flag_first = false;

/**
 * переход по родительской связи к потомку
 */
if ( isset($_REQUEST['mod_child_id']) )
{
  // родитель
  $ModSystem = ModSystem::Factory($mod_id, true);

  $parent = array();
  $parent['ID'] = $ModSystem->ID;
  $parent['Tbl'] = $ModSystem->Tbl;
  $parent['Obj_ID'] = $ModSystem->Obj->ID;
  $parent['Obj_Name'] = $ModSystem->Obj->Name;

  $path = $ModSystem->Path;
  $path[$parent['ID'] . $parent['Obj_ID']] = $parent;

  //  потомок
  Registry::Unset_Index('ModSystem_' . $_REQUEST['mod_child_id']);
  $ModSystem = ModSystem::Factory($_REQUEST['mod_child_id'], true);
  $ModSystem->Parent = $parent;
  $ModSystem->Path = $path;
  //
  $mod_id = $_REQUEST['mod_child_id'];
  //  Registry::Set('mod_id', $mod_id);
  unset($path); unset($parent); $mod_flag_first = true;
}
/**
 * переход по родительской связи к родителю
 * выполняется только для объектов типа каталог
 */
else if ( isset($_REQUEST['mod_parent_id']) )
{
  // родитель
  $ModSystem = ModSystem::Factory($_REQUEST['mod_parent_id'], true);
  
  $path = array(); $parent = array();
  foreach ($ModSystem->Path as $key => $row)
  {
    if ( $_REQUEST['mod_parent_id'] . $_REQUEST['obj_id'] == $key )
    {
      $parent = $row;
      $path[$key] = $row;
      //  потомок
      //  Registry::Unset_Index('ModSystem_' . $_GET['mod_parent_id']);
      $ModSystem = ModSystem::Factory($_REQUEST['mod_parent_id'], true);
      $ModSystem->Parent = $parent;
      $ModSystem->Path = $path;
      break;
    }
    $parent = $row;
    $path[$key] = $row;
  }
  //
  $mod_id = $_REQUEST['mod_parent_id'];
  //  Registry::Set('mod_id', $mod_id);
  unset($path); unset($parent); $mod_flag_first = true;
}
/**
 * текущий модуль
 */
else
{
  $mod_flag_first = false;
  //  сброс модуля либо модуль работает первый раз
  if ( 'mod_clear' == $op ) {
    Registry::Unset_Index('ModSystem_' . $mod_id);
    $mod_flag_first = true;
  }
  //  модуль работает первый раз
  else if ( !Registry::Is_Exists('ModSystem_' . $mod_id) ) {
    $mod_flag_first = true;
  }
  //  потомок
  $ModSystem = ModSystem::Factory($mod_id, true);
}

//  проверка права на просмотр
if ( !$ModSystem->Access['V'] )
{
  $Tpl_Main->Assign('subj', 'Вы не имеете прав на запрошенный модуль');
  die($Tpl_Main->Fetch('_blank'));
}

/**
 * Поиск модуля и определение его подключения
 */
if ( isset($_REQUEST['block']) ) {
  $ModSystem->Block = System_File::File_Name_Filter($_REQUEST['block']);
} else {
  $ModSystem->Block = '';
}

if ( !file_exists($mod_path = 'modules/' . $ModSystem->ModulUser . '/' . $ModSystem->ModulUser . $ModSystem->Block . '.php') )
{
  if ( !file_exists($mod_path = 'modules/' . $ModSystem->Modul . '/' . $ModSystem->Modul . $ModSystem->Block . '.php') )
  {
    if ( !file_exists($mod_path = 'modules/' . $ModSystem->Block . '/' . $ModSystem->Block . '.php') )
    {
      //  модуль не найден
      $Tpl_Main->Assign('subj', 'Модуль не определен либо не имеет настроек (отсутствует)');
      die($Tpl_Main->Fetch('_blank'));
    }
  }
}

//  ссылка на модуль (для форм и ссылок на себя)
$mod_link = 'window.php?mod_id=' . $mod_id;
$mod_link_blank = 'window.php?mod_id=' . $mod_id;

//  Логировнаие операций
$Logs = new Logs($ModSystem->ID);

/**
 * РАБОТА МОДУЛЯ
 */
$Tpl_Main->Assign('zone_content', include $mod_path);

/**
 * ВЫВОД
 */
if ( '!' == substr($subj, 0, 1) ) {
  $subj = ' <font color="#ff0000">' . $subj . '</font>';
}
if ( file_exists(PATH_LOG . '/error_db.log') ) {
  $subj.= '&nbsp;&nbsp;<font color="#ff0000">! ОШИБКА БД !</font>';
}
$Tpl_Main->Assign('time', $Logs->Display());
//  $Tpl->Assign('memory', memory_get_usage() . ' - ' . memory_get_peak_usage());
$Tpl_Main->Assign('memory', memory_get_peak_usage());
$Tpl_Main->Assign('subj', $subj);
$Tpl_Main->Assign('op', $op);
$Tpl_Main->Assign('ModSystem', $ModSystem);
print $Tpl_Main->Fetch('_index', 'window');
exit;
