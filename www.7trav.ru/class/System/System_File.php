<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Системный Класс.
 *
 * Работа с файловой системой.
 * Специфичные файловые функции системы.
 *
 * @package Core
 * @subpackage System
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 22.01.2009
 */
final class System_File
{
  /**
   * Очистка кеша сайта
   *
   * Фильтр работает в режиме регулярного выражения
   *
   * @param $folder - Папка кеша (если не указан удаляются все папки кеша)
   * @param $filter - Фильтр имен файлов кеша (если не указан удаляются все файлы кеша)
   */
  public static function Cache_Clear_All($folder = '', $filter = '')
  {
    if ( '' == $folder ) {
      $folder_mas = glob(PATH_CACHE . '/*', GLOB_ONLYDIR);
    } else {
      $folder_mas = glob(PATH_CACHE . '/ ' . $folder . '/*', GLOB_ONLYDIR);
    }
    foreach ($folder_mas as $folder) {
      self::File_Remove($folder, $filter);
    }
  }
  /**
   * Формирование ini файла
   *
   * Формирует данные в ini формате
   * И записывает в файл по указанному пути.
   * Либо является возвращаемым значением, если путь не указан.
   * @param array $data - массив данных для записи
   * @param integer $flag - флаг режима формирования
   * (1 - одномерный, 2 - двухмерный, 3 - двухмерный где первый элемент становится индексом)
   * @param string $path - абсолютный путь файла
   * @return bolean or string - флаг операции либо собранный ini файл в строке
   */
  public static function Create_Ini($data, $flag, $path = '')
  {
    if ( false == is_array($data) ) return false;
    $cache = '';
    if ( 1 == $flag ) {
      foreach ($data as $key => $val) {
        $cache.= $key . '="' . $val . '"' . "\n";
      }
    } else if ( 2 == $flag ) {
      foreach ($data as $key => $val) {
        $cache.= '[' . $key . ']' . "\n";
        foreach ($val as $key2 => $val2) {
          $cache.= $key2 . '="' . $val2 . '"' . "\n";
        }
        $cache.= "\n";
      }
    } else if ( 3 == $flag ) {
      foreach ($data as $val) {
        $cache.= '[' . array_shift($val) . ']' . "\n";
        foreach ($val as $key2 => $val2) {
          $cache.= $key2 . '="' . $val2 . '"' . "\n";
        }
        $cache.= "\n";
      }
    }
    //  если путь к файлу не указан
    if ( '' == $path ) return trim($cache);
    //  если путь к файлу указан
    file_put_contents($path, $cache); chmod($path, 0666);
    return true;
  }
  /**
   * Создание служебных папок системы
   */
  public static function Folder_System_Create()
  {
    if ( !is_dir($path = PATH_LOG) ) {
      mkdir($path);
    }
    if ( !is_dir($path = PATH_CLASS_CONFIG) ) {
      mkdir($path);
    }
    if ( !is_dir($path = PATH_IMPORT . '/import') ) {
      mkdir($path);
    }
    if ( !is_dir($path = PATH_ADMIN . '/session') ) {
      mkdir($path);
    }
    if ( !is_dir($path = PATH_ADMIN . '/session/worker') ) {
      mkdir($path);
    }
    if ( !is_dir($path = PATH_CACHE) ) {
      mkdir($path);
    }
    if ( !is_dir($path = PATH_EXPORT) ) {
      mkdir($path);
    }
    if ( !is_dir($path = PATH_SITE . '/session') ) {
      mkdir($path);
    }
    if ( !is_dir($path = PATH_SITE . '/session/client') ) {
      mkdir($path);
    }
    @symlink(PATH_ADMIN . '/img', PATH_SITE . '/img');
  }
  /**
   * Удаление пустых папок административных модулей, шаблонов и бинарных данных объектов.
   *
   */
  public static function Folder_Empty_Remove()
  {
    $folder_mas = glob(PATH_ADMIN . '/img/*', GLOB_ONLYDIR);
    foreach ($folder_mas as $folder) {
      @rmdir($folder);
    }
    $folder_mas = glob(PATH_ADMIN . '/modules/*', GLOB_ONLYDIR);
    foreach ($folder_mas as $folder) {
      @rmdir($folder);
    }
    $folder_mas = glob(PATH_ADMIN . '/templates/*', GLOB_ONLYDIR);
    foreach ($folder_mas as $folder) {
      @rmdir($folder);
    }
  }
  /**
   * Копирование содержимого каталога
   *
   * Если фильтр не задан копируются все каталоги.
   *
   * @param string $path_input - каталог источник
   * @param string $path_output - целевой каталог (создает если его нет)
   * @param string $filter - фильтр имен каталогов
   */
  public static function Folder_Copy($path_input, $path_output, $filter = '')
  {
    if ( !is_dir($path_input) ) return true;
    if ( !is_dir($path_output) ) mkdir($path_output);
    chmod($path_output, 0755);
    $fp_folder = opendir($path_input);
    while ( false != $name_file = readdir($fp_folder) ) {
      if ( '.' == $name_file || '..' == $name_file ) continue;
      if ( is_dir($path_input . '/' . $name_file) ) {
        if ( '' == $filter || preg_match('~' . $filter . '~si', $name_file) ) {
          mkdir($path_output . '/' . $name_file);
          chmod($path_output . '/' . $name_file, 0755);
          self::Folder_Copy($path_input . '/' . $name_file, $path_output . '/' . $name_file, $filter);
        }
      } else {
        copy($path_input . '/' . $name_file, $path_output . '/' . $name_file);
        chmod($path_output . '/' . $name_file, 0644);
      }
    }
    closedir($fp_folder);
    return true;
  }
  /**
   * Перемещение содержимого каталога
   *
   * Если фильтр не задан перемещаются все каталоги.
   *
   * @param string $path_input - каталог источник
   * @param string $path_output - целевой каталог (создает если его нет)
   * @param string $filter - фильтр имен каталогов
   */
  public static function Folder_Move($path_input, $path_output, $filter = '')
  {
    if ( !is_dir($path_input) ) return true;
    if ( !is_dir($path_output) ) mkdir($path_output);
    chmod($path_output, 0755);
    $fp_folder = opendir($path_input);
    while ( false != $name_file = readdir($fp_folder) ) {
      if ( '.' == $name_file || '..' == $name_file ) continue;
      if ( is_dir($path_input . '/' . $name_file) ) {
        if ( '' == $filter || preg_match('~' . $filter . '~si', $name_file) ) {
          mkdir($path_output . '/' . $name_file);
          chmod($path_output . '/' . $name_file, 0755);
          self::Folder_Move($path_input . '/' . $name_file, $path_output . '/' . $name_file, $filter);
          //rmdir($path_input . '/' . $name_file);
        }
      } else {
        rename($path_input . '/' . $name_file, $path_output . '/' . $name_file);
        chmod($path_output . '/' . $name_file, 0644);
      }
    }
    closedir($fp_folder);
    rmdir($path_input);
    return true;
  }
  /**
   * Удаление содержимого каталога
   *
   * Если фильтр не задан удаляются все каталоги.
   *
   * @param string $path_output - целевой каталог
   * @param string $filter - фильтр имен каталогов
   */
  public static function Folder_Remove($path, $filter = '')
  {
    if ( !is_dir($path) ) return true;
    $fp_folder = opendir($path);
    while ( false != $name_file = readdir($fp_folder) ) {
      if ( '.' == $name_file || '..' == $name_file ) continue;
      if ( is_dir($path . '/' . $name_file) ) {
        if ( '' == $filter || preg_match('~' . $filter . '~si', $name_file) ) {
          self::Folder_Remove($path . '/' . $name_file, $filter);
        }
      } else {
        unlink($path . '/' . $name_file);
      }
    }
    closedir($fp_folder);
    rmdir($path);
    return true;
  }
  /**
   * Удаление содержимого каталога. Функция рекурсивна.
   *
   * Если фильтр не задан удаляются все файлы каталога.
   * Каталоги не удаляются.
   *
   * @param string $path_output - целевой каталог
   * @param string $filter - фильтр имен удаляемых файлов
   */
  public static function File_Remove($path, $filter = '')
  {
    if ( !is_dir($path) ) return true;
    $fp_folder = opendir($path);
    while ( false != $name_file = readdir($fp_folder) ) {
      if ( '.' == $name_file || '..' == $name_file ) continue;
      if ( is_dir($path . '/' . $name_file) ) {
        self::File_Remove($path . '/' . $name_file, $filter);
      } else {
        if ( '' == $filter || preg_match('~' . $filter . '~si', $name_file) ) {
          unlink($path . '/' . $name_file);
        }
      }
    }
    closedir($fp_folder);
    return true;
  }
  /**
   * Удаление пустых папок административных модулей, шаблонов и бинарных данных объектов.
   *
   */
  public static function File_Arhiv_Remove()
  {
    $folder_list = glob(PATH_SITE . '/templates_main/*', GLOB_ONLYDIR);
    foreach ($folder_list as $folder) {
      $file_list = glob($folder . '/*_arhiv_*.htm');
      array_pop($file_list);
      foreach ($file_list as $file) {
        unlink($file);
      }
    }
    $folder_list = glob(PATH_SITE . '/templates/*', GLOB_ONLYDIR);
    foreach ($folder_list as $folder) {
      $file_list = glob($folder . '/*_arhiv_*.htm');
      array_pop($file_list);
      foreach ($file_list as $file) {
        unlink($file);
      }
    }
    $folder_list = glob(PATH_ADMIN . '/templates/*', GLOB_ONLYDIR);
    foreach ($folder_list as $folder) {
      $file_list = glob($folder . '/*_arhiv_*.htm');
      array_pop($file_list);
      foreach ($file_list as $file) {
        unlink($file);
      }
    }
  }
  /**
   * Очистка имени файла
   *
   * Удаление из имени файла вредоносных символов
   * @param $filename - имя файла
   */
  public static function File_Name_Filter($filename)
  {
    $filename = str_replace('/', '', $filename);
    $filename = str_replace('.', '', $filename);
    $filename = str_replace('\\', '', $filename);
    return $filename;
  }
  /**
   * Конвертация файлов из одной кодировки в другую
   *
   * Рекурсивно обходит все подкаталоги
   * @param string $path - целевой каталог
   * @param string $k_in - текущая кодировка файла
   * @param string $k_ot - целевая кодировка файла
   */
  public static function File_Convert($path, $k_in, $k_ot)
  {
    foreach (glob($path . '/*', GLOB_ONLYDIR) as $path_file) {
      self::File_Convert($path_file, $k_in, $k_ot);
    }
    foreach (glob($path . '/*.*') as $path_file) {
      file_put_contents($path_file, iconv("WINDOWS-1251", "UTF-8", file_get_contents($path_file)));
      //  exec("iconv --from-code={$k_in} --to-code={$k_ot} {$path_file} > {$path_file}");
    }
  }
  /**
   * Определения типа документа по расщирению для заголовка.
   *
   * @param string $file_name - имя файла или путь до него
   * @return string - тип документа для заголовка
   */
  public static function Type_Head($file_name)
  {
    $mas = explode(".",basename($file_name));
    $str = array_pop($mas);
    if ( $str=='gif' )
    { return 'image/gif'; }
    else if ( $str=='jpg' || $str=='jpeg' )
    { return 'image/jpeg'; }
    else if ( $str=='png' )
    { return 'image/png'; }
    else if ( $str=='txt' )
    { return 'text/plain'; }
    else if ( $str=='html' || $str=='htm' || $str=='xml' )
    { return 'text/html'; }
    else if ( $str=='doc' )
    { return 'application/msword'; }
    else if ( $str=='xls' )
    { return 'application/vnd.ms-excel'; }
    else if ( $str=='csv' )
    { return 'application/vnd.ms-excel'; }
    else if ( $str=='swf' )
    { return 'application/x-shockwave-flash'; }
    else if ( $str=='pdf' )
    { return 'application/pdf'; }
    else if ( $str=='ppt' )
    { return 'application/vnd.ms-powerpoint'; }
    else if ( $str=='pps' )
    { return 'application/vnd.ms-powerpoint'; }
    else if ( $str=='mdb' )
    { return 'application/msaccess'; }
    else if ( $str=='vsd' )
    { return 'application/vnd.visio'; }
    else if ( $str=='rar' )
    { return 'application/x-tar'; }
    else if ( $str=='zip' )
    { return 'application/x-zip-compressed'; }
    else if ( $str=='mp3' )
    { return 'audio/wav'; }
    else if ( $str=='wav' )
    { return 'audio/mpeg'; }
    else if ( $str=='wmv' )
    { return 'video/x-ms-wmv'; }
    else if ( $str=='mpg' )
    { return 'video/mpeg'; }
    else if ( $str=='avi' )
    { return 'video/x-msvideo'; }
    else
    { return 'application/octet-stream'; }
  }
}
