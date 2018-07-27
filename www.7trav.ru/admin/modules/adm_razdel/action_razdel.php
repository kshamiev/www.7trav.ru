<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Расширенная работа с разделами сайта.
 * 
 * Корректировка абсолютной ссылки при перемещении и сохранении разделов.
 * Формирование абсолютной ссылки для раздела.
 * @package Cms
 * @subpackage Razdel
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $sys_lang_list;
global $op;
global $ObjParent;
/* @var $ObjParent Razdel */
global $Obj;
/* @var $Obj Razdel */

/**
 * Корректировка абсолютной ссылки при перемещении разделов.
 * Сброс кеша адресной строки навигации для всех разделов.
 */
while ( 'link_add' == $op ) {
  //  Корректировка абсолютной ссылки
  Razdel::Act_UrlRoot_Update($ObjParent->ID);
  break;
}

/**
 * Корректировка абсолютной ссылки при сохранении раздела.
 * Сброс кеша адресной строки навигации для всех разделов.
 * Сброс кеша конфигурации разделов по абсолютному URL.
 * Формирование абсолютной ссылки для раздела.
 */
while ( 'obj_save' == $op || 'obj_save_ok' == $op ) {
  //  Проверки
  if ( 2 == $Obj->Level && isset($_POST['Prop']['Url']) && strlen($_POST['Prop']['Url']) < 3 ) {
    $subj = '! Ссылка на главные разделы не может быть менее 3 символов !';
    break;
  } else if ( 1 == $Obj->Level && isset($_POST['Prop']['UrlRedirect']) && $_POST['Prop']['UrlRedirect'] = trim($_POST['Prop']['UrlRedirect']) ) {
    $subj = '! Редирект с главного раздела недопустим !';
    break;
  } else if ( isset($_POST['Prop']['Url']) ) {
    $_POST['Prop']['Url'] = System_String::Translit_Url($_POST['Prop']['Url']);
    if ( 0 < $Obj->Razdel_ID ) {
      $_POST['Prop']['UrlRoot'] = DB::Get_Query_Cnt("SELECT UrlRoot FROM Razdel WHERE ID = " . $Obj->Razdel_ID);
      $_POST['Prop']['UrlRoot'] .= '/' . $_POST['Prop']['Url'];
    } else {
      if ( 1 == $Worker->Groups_ID ) {
        $_POST['Prop']['UrlRoot'] = $_POST['Prop']['Url'];
      } else {
        $_POST['Prop']['UrlRoot'] = HOST;
        $_POST['Prop']['Url'] = $_POST['Prop']['UrlRoot'];
      }
    }
    $sql = "SELECT COUNT(*) FROM Razdel WHERE ID != {$Obj->ID} AND UrlRoot = '{$_POST['Prop']['UrlRoot']}'";
    if ( 0 < DB::Get_Query_Cnt($sql) ) {
      $subj = '! Раздел с таким Url уже существует в текущем родительском разделе !';
      break;
    }
    if ( $Obj->UrlRoot != $_POST['Prop']['UrlRoot'] ) {
      //  Сохранение объекта
      $Obj->Url = $_POST['Prop']['Url'];
      $Obj->UrlRoot = $_POST['Prop']['UrlRoot'];
      $Obj->Save();
      // Корректировка абсолютной ссылки
      Razdel::Act_UrlRoot_Update($Obj->ID);
    }
    break;
  }
  break;
}
