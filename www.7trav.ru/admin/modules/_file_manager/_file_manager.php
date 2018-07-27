<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Файловый менеджер.
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
$Tpl_Mod = new Templates();

//  права
$Access = $ModSystem->Access;

//  путь
if ( !count($ModSystem->Path) )
{
  $ModSystem->Path[0] = PATH_ROOT;
}

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * удаление папки
 */
while ( 'folder_rem' == $op )
{
  if ( !$Access['R'] )
  {
    $subj = $subj_list[50];
    break;
  } else if ( !isset($_POST['folder_name']) || !$_POST['folder_name'] )
  {
    $subj = $subj_list[51];
    break;
  }
  System_File::Folder_Remove($ModSystem->Path[0] . '/' . $_POST['folder_name']);
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';удаление папки;' . $ModSystem->Path[0] . '/' . $_POST['folder_name']);
  $subj = $subj_list[6];
  break;
}

/**
 * удаление файла
 */
while ( 'file_rem' == $op )
{
  if ( !$Access['R'] )
  {
    $subj = $subj_list[50];
    break;
  } else if ( !isset($_POST['file_name']) || !$_POST['file_name'] )
  {
    $subj = $subj_list[51];
    break;
  }
  //
  unlink($ModSystem->Path[0] . '/' . $_POST['file_name']);
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';удаление файла;' . $ModSystem->Path[0] . '/' . $_POST['file_name']);
  $subj = $subj_list[7];
  break;
}

/**
 * загрузка файла
 */
while ( 'file_add' == $op )
{
  if ( !$Access['A'] )
  {
    $subj = $subj_list[50];
    break;
  } else if ( !isset($_FILES['file_name']) || !$_FILES['file_name'] )
  {
    $subj = $subj_list[51];
    break;
  }
  //
  $file_name = &$_FILES['file_name'];
  if ( 4 == $file_name['error'] )
  {
    $subj = $subj_list[56];
    break;
  } else if ( !is_uploaded_file($file_name['tmp_name']) )
  {
    $subj = $subj_list[57];
    break;
  } else if ( 0 != $file_name['error'] )
  {
    $subj = $subj_list[58];
    break;
  }
  copy($file_name['tmp_name'], $ModSystem->Path[0] . '/' . $file_name['name']);
  chmod($ModSystem->Path[0] . '/' . $file_name['name'], 0666);
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';загрузка файла;' . $ModSystem->Path[0] . '/' . $file_name['name']);
  $subj = $subj_list[8];
  break;
}

/**
 * создание папки
 */
while ( 'folder_add' == $op )
{
  if ( !$Access['A'] )
  {
    $subj = $subj_list[50];
    break;
  } else if ( !isset($_POST['folder_name']) || !$_POST['folder_name'] )
  {
    $subj = $subj_list[51];
    break;
  }
  //
  mkdir($ModSystem->Path[0] . '/' . $_POST['folder_name']);
  chmod($ModSystem->Path[0] . '/' . $_POST['folder_name'], 0777);
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';создание папки;' . $ModSystem->Path[0] . '/' . $_POST['folder_name']);
  $subj = $subj_list[9];
  break;
}

/**
 * переход по папкам
 */
while ( isset($_GET['path_new']) )
{
  if ( $_GET['path_new'] == '..' && $ModSystem->Path[0] != PATH_ROOT )
  {
    $mas = explode('/', $ModSystem->Path[0]);
    array_pop($mas); // array_pop($mas);
    $ModSystem->Path[0] = implode('/', $mas);
  } else if ( $_GET['path_new'] != '..' )
  {
    $ModSystem->Path[0] .= '/' . $_GET['path_new'];
  }
  break;
}

/**
 * ВЫВОД
 */
$folder_mas = array();
$files_mas = array();
$dr = opendir($ModSystem->Path[0]);
while ( false != $file_name = readdir($dr) )
{
  if ( '..' == $file_name || '.' == $file_name ) continue;
  $file_path = $ModSystem->Path[0] . '/' . $file_name;
  if ( is_dir($file_path) )
  {
    $folder_mas[$file_name]['edit'] = date("d.m.Y H:i:s", filemtime($file_path));
  } else
  {
    $mas = explode('.', $file_name);
    $files_mas[$file_name]['edit'] = date("d.m.Y H:i:s", filemtime($file_path));
    $files_mas[$file_name]['size'] = (filesize($file_path) / 1000) . ' kb';
    $files_mas[$file_name]['ext'] = array_pop($mas);
  }
}
closedir($dr);
//  массивы па;к и файлов
ksort($folder_mas);
$Tpl_Mod->Assign('folder_mas', $folder_mas);
ksort($files_mas);
$Tpl_Mod->Assign('files_mas', $files_mas);
//  ;лное количество
$count_item = count($folder_mas) + count($files_mas) - 1;
$Tpl_Mod->Assign('count_item', $count_item);
//  допустимые расширения файлов для редактирования
$file_edit_flag = array('txt' , 'ini' , 'log' , 'php' , 'htm' , 'html' , 'tpl' , 'css' , 'js');
$Tpl_Mod->Assign('file_edit_flag', $file_edit_flag);
//  стандартные данные
$Tpl_Mod->Assign('Access', $Access);
$Tpl_Mod->Assign('ModSystem', $ModSystem);
$Tpl_Mod->Assign('mod_link', $mod_link);
return $Tpl_Mod->Fetch_System($ModSystem);