<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Разбор Url запрошенной страницы (раздела)
 * 
 * Аля modrewrite
 * Получение на выходе следующего результата:
 * URL - ссылка на текущий раздел для работы (/article/)
 * LANG_ID - язык
 * $sys_http_zapros - строковый идентификатор раздела (www.domain.ru/article/)
 * $obj_id - числовой идентификатор объекта
 * $page - номер запрошенной страницы
 * $sort - поле по которому происходит сортировка
 * 
 * @package Cms
 * @subpackage Core
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $sys_lang_list;

/*
if ( isset($_REQUEST['sys_http_zapros']) ) {
  $_SERVER["REQUEST_URI"] = $_REQUEST['sys_http_zapros'];
}
*/

if ( '/' == $_SERVER["REQUEST_URI"] ) {
  $sys_lang_id = 1;
  $sys_lang_pref = 'ru';
  $sys_url = '/';
  $sys_http_zapros = HOST;
} else {
  /**
   * 301
   * Коррекция запросов ссылок переехавших на новый адрес
   */
  //  Logs::Save_File($_SERVER["REQUEST_URI"], '!.log');
  /*
  if ( 'URL' == $_SERVER["REQUEST_URI"] ) {
    $_SERVER["REQUEST_URI"] = 'URL_NEW';
  } else if ( false !== strpos($_SERVER["REQUEST_URI"], '/ware/') ) {
    $_SERVER["REQUEST_URI"] = str_replace('/ware/', '/production/', $_SERVER["REQUEST_URI"]);
  } else if ( preg_match('~^/tentorium[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/951.htm';
  } else if ( preg_match('~^/akuliyhryasch[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/90.htm';
  } else if ( preg_match('~^/applikatorlyapko[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/668.htm';
  } else if ( preg_match('~^/asoniya-podushka[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/1224.htm';
  } else if ( preg_match('~^/dienay[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/1107.htm';
  } else if ( preg_match('~^/nartok[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/1462.htm';
  } else if ( preg_match('~^/litovit[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/7.htm';
  } else if ( preg_match('~^/mamawit[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/874.htm';
  } else if ( preg_match('~^/oligopeptid[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/1051.htm';
  } else if ( preg_match('~^/polymedel[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/759.htm';
  } else if ( preg_match('~^/rino-faktor[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/961.htm';
  } else if ( preg_match('~^/trezvit[/]?$~', $_SERVER["REQUEST_URI"]) ) {
    $_SERVER["REQUEST_URI"] = '/production/1374.htm';
  }
  */
  //  Logs::Save_File($_SERVER["REQUEST_URI"], 'REQUEST_URI.log');
  /**
   * РАБОТА
   */
  $mas = explode('/', $_SERVER["REQUEST_URI"]);
  array_shift($mas);
  /**
   * языки
   * сюда надо заносить языки которые добавляются в систему 
   */
  if ( 2 == strlen($mas[0]) && isset($sys_lang_list[$mas[0]]) ) {
    $sys_lang_id = $sys_lang_list[$mas[0]];
    $sys_lang_pref = $mas[0];
    unset($mas[0]);
  } else {
    $sys_lang_id = 1;
    $sys_lang_pref = 'ru';
  }
  /**
   * объект, каталог, страница
   */
  if ( !$end = trim(end($mas)) ) {
    array_pop($mas);
  } else if ( preg_match("~([0-9]+)?(page([0-9]+))?(sort([a-z|0-9]+))?[.]htm[l]?~si", $end, $row) ) {
    if ( $row[1] ) {
      $obj_id = $row[1];
      $_REQUEST['obj_id'] = $obj_id;
    } 
    //  print $obj_id . ' - объект<br>';
    if ( $row[3] ) {
      $page = $row[3];
      $_REQUEST['page'] = $page;
    }
    //  print $page . ' - страница<br>';
    if ( $row[5] ) {
      $sort = $row[5];
      $_REQUEST['sort'] = $sort;
    }
    //  print $sort . ' - сортировка<br>';
    array_pop($mas);
  }
  /**
   * страница (раздел)
   */
  $sys_url = '';
  if ( '' != $str = implode('/', $mas) ) {
    $sys_url.= '/' . $str . '/';
    $sys_http_zapros = HOST . substr($sys_url, 0, -1);
  } else {
    $sys_url.= '/';
    $sys_http_zapros = HOST;
  }
  /**
   * Левые запросы
   * Или несуществующие файлы которые реально должны быть (картинки...)
   * Хотя возможны и атаки
   */
  if ( strpos($sys_url, '.') ) {
    header('HTTP/1.1 404 Not Found');
    exit();
  }
}
define('LANG_ID', $sys_lang_id);
define('LANG_PREFIX', $sys_lang_pref);
define('URL', $sys_url);
unset($sys_lang_id);
unset($sys_lang_pref);
unset($sys_url);
