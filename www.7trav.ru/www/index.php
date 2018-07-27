<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Ядро системы. Клиентская часть.
 *
 * @package Cms
 * @subpackage Core
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

//  статистика вы;лнения
$sys_time = microtime(1);

/**
 * Подключение конфигурации
 */
chdir(dirname(__FILE__));
require_once '../config.php';
global $op;
global $subj;

/**
 * РАЗБОР ВХОДЯЩЕГО ЗАПРОСА
 */
require 'init_zapros.php';
global $sys_http_zapros;
global $obj_id;
global $page;
global $sort;

/**
 * ЗАГОЛОВКИ
 */
header('Pragma: no-cache');
header('Cache-Control: no_cache, must-revalidate');
//  header('Expires: Mon, 26 jul 2005 05:00:00 GMT');
//  header('Last-Modified: '.date('D, d M Y H:i:s').'GMT');
//  header("Content-Type: text/html; charset=utf-8");
header("Content-Type: text/html; charset=utf-8");
//  setlocale(LC_ALL,'ru_RU.CP1251');
//  setlocale(LC_COLLATE,'ru_RU.CP1251');
setlocale(LC_CTYPE,'ru_RU.UTF-8');
setlocale(LC_COLLATE,'ru_RU.UTF-8');
//  setlocale(LC_CTYPE,'ru_RU.UTF-8');
//  print '<pre>'; print_r(localeconv()); print '</pre>';

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
//  Клиент
$Client = Client::Factory();
/* @var $Client Client */
if ( !$Client->ID ) {
  $Client->Groups_ID = 2;
	if ( isset($_COOKIE['client_id']) && 0 < Client::Is_Exists($_COOKIE['client_id']) ) {
    $Client = new Client($_COOKIE['client_id'], true);
    Registry::Set('Client', $Client);
    setcookie('client_id', $Client->ID, time() + COOKIE_TIME, '/');
    $Client->Set_Timeout();
  }
} else if ( 2 == $Client->Groups_ID && SESSION_REMOVE_CLIENT < $Client->Timeout ) { //  это когда гость удален из БД а сессия еще висит
  $Client->Init_Logout();
} else {
  $Client->Set_Timeout();
}

//  заносим в конфигурайию УП для удобства дальнейщего использования
SC::$ConditionUser = $Client->Condition;

//  Запрошенный раздел
$Razdel = new Razdel();
$Razdel->Init_Url($sys_http_zapros);

//  Корневой раздел
$Razdel_Root = new Razdel(RAZDEL_MAIN_ID);

//  Шаблон раздела
$Tpl_Site = new Site_Template($Razdel->Site_Template_ID);

//  Шаблонизатор
$Tpl_Main = new Templates('templates_main');

/**
 * РАБОТА КОНТРОЛЛЕРА
 */
if ( '' != $op ) {
  $mas = explode('-', $op);
  //  инициализация объекта
  if ( isset($mas[2]) && 0 < $mas[2] ) {
    if ( false == $Object = Registry::Get($mas[0] . '_' . $mas[2]) ) {
      $Object = $mas[0]::Factory($mas[2]);
    }
  } else if ( false == $Object = Registry::Get($mas[0]) ) {
    $Object = $mas[0]::Factory();
  }
  //  инициализация метода
  $value = isset($mas[3]) ? $mas[3] : false ;
  if ( method_exists($Object, $method = 'Action_' . $mas[1]) ) {
    $subj = $Object->$method($value);
  } else if ( method_exists($Object, $mas[1]) ) {
    $subj = $Object->$mas[1]($value);
  } else {
    Logs::Save_File($op, 'error_method.log');
  }
}

/**
 * РАБОТА ФУНКИОНАЛЬНЫХ МОДУЛЕЙ
 * Загрузка конфигурации модулей и их выполнение
 */
foreach ($Tpl_Site->Get_Config() as $mod_data)
{
  if ( false == $mod_data['Modul_ID'] ) {
    $Tpl_Main->Assign($mod_data['Zone'], '');
    continue;
  }
  //  Инициализация
  $ModSystem = ModSystem::Factory($mod_data['Modul_ID']);
  $ModSystem->ModulUser = $mod_data['ModulUser'];
  $Zone = new Zone($mod_data['Zone_ID']);
  $Zone->Zone = $mod_data['Zone'];
  //  Поиск модуля и его выполнение
  if ( !file_exists($mod_path = 'modules/' . $ModSystem->ModulUser . '/' . $ModSystem->ModulUser . '.php') ) {
    $Tpl_Main->Assign($mod_data['Zone'], 'Не найден модуль ' . $mod_path);
  }
  else {
    $Tpl_Main->Assign($mod_data['Zone'], include $mod_path);
  }
}

/**
 * РАБОТА ЦЕНТРАЛЬНОГО МОДУЛЯ
 */
//  Инициализация
$ModSystem = ModSystem::Factory($Razdel->ModSystem_ID);
$ModSystem->Load_Prop_Cache('Name', 'ModulUser');

/**
 * Поиск модуля и его выполнение
 */
if ( !file_exists($mod_path = 'modules/' . $ModSystem->ModulUser . '/' . $ModSystem->ModulUser . '.php') ) {
  $Tpl_Main->Assign('zone_content', 'Не найден модуль ' . $mod_path);
}
else {
  if ( isset($_REQUEST['obj_id']) )
  {
    $text = include $mod_path;
    $text = $sape_context->replace_in_text_segment($text);
    $Tpl_Main->Assign('zone_content', $text);
  }
  else
  {
    $Tpl_Main->Assign('zone_content', include $mod_path);
  }
}

/**
 * ВЫВОД
 */
$Tpl_Main->Assign('Client', $Client);
$Tpl_Main->Assign('Razdel', $Razdel);


//  режим индексирования
$robots_list = array();
if ( !$Razdel->IsIndex ) {
  $robots_list[] = 'noindex';
}
if ( !$Razdel->IsFollow ) {
  $robots_list[] = 'nofollow';
}
$Tpl_Main->Assign('robots', implode(', ', $robots_list));
print $Tpl_Main->Fetch('index_' . $Razdel->Site_Template_ID, 'index_' . LANG_PREFIX);
if ( '' != $subj && 'ok' != $subj ) print '<script language="JavaScript" type="text/javascript">alert(\''.$subj.'\');</script>';
//  статистика выполнения
$sys_time = sprintf("%01.3f", microtime(1) - $sys_time);
if ( 1 <= $sys_time ) {
  Logs::Save_File(memory_get_usage() . ' - ' . $sys_time . ' - ' . HTTPL, 'error_time_limit.log');
}
exit;
