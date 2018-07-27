<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Файловый менеджер. Отдача запрошенного файлов
 *
 * @package Core
 * @subpackage System
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 * @see System_File
 */

/**
 * ВЫВОД
 */
//  $type = mime_content_type($file_path); print $type;
$mas = explode('/', $_GET['file_path']);
$file_name = array_pop($mas);
header("Content-Disposition: attachment; filename = " . $file_name);
$fp = fopen($_GET['file_path'], "rb");
$file_name = fread($fp, filesize($_GET['file_path']));
fclose($fp);
die($file_name);