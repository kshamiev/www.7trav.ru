<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Файловый менеджер. Редатирование текстовых файлов. 
 *
 * @package Core
 * @subpackage System
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 * @see System_File
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $ModSystem;
/* @var $ModSystem ModSystem */
global $op;
global $mod_link;
global $mod_link_blank;
global $Logs;
global $subj_list;

//  Шаблон
$Tpl_Mod = new Templates;

//  права
$Access = $ModSystem->Access;

//  Файл
$file_path = $ModSystem->Path[0] . '/' . $_POST['file_name'];

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * редактирование файла
 */
while ( 'file_edit' == $op )
{
  if ( !$Access['E'] ) {
    $subj = $subj_list[50];
    break;
  } else if ( !isset($_POST['file_data']) || !$_POST['file_data'] ) {
    $subj = $subj_list[51];
    break;
  }
  //
  $_POST['file_data'] = str_replace("\r\n", "\n", $_POST['file_data']);
  file_put_contents($file_path, $_POST['file_data']);
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';измнение файла;' . $file_path);
  $subj = $subj_list[11];
  break;
}

/**
 * ВЫВОД
 */
$file_data = file_get_contents($file_path);
$Tpl_Mod->Assign('file_data', $file_data);
//
$Tpl_Mod->Assign('file_name', $_POST['file_name']);
//  стандартные данные
$Tpl_Mod->Assign('Access', $Access);
$Tpl_Mod->Assign('ModSystem', $ModSystem);
$Tpl_Mod->Assign('mod_link', $mod_link);
return $Tpl_Mod->Fetch_System($ModSystem);