<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Системный Класс.
 *
 * Работа с графическими файлами, и графикой в целом.
 * 
 * @package Core
 * @subpackage System
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 22.01.2009
 */
final class System_Image
{
  /**
   * Ресайз картинок всех объектов определенного типа
   *
   * @param string $tbl
   * @param string $colum
   * @param int $x
   * @param int $y
   */
  public static function Resize_System($tbl, $colum, $x, $y)
  {
    $sql = "SELECT {$colum} FROM $tbl";
    $res = &DB::Query($sql);
    /* @var $res mysqli_result */
    while ( false != $row = $res->fetch_row() )
    {
      if ( !file_exists($path = PATH_SITE . '/img/' . $row[0]) ) continue;
      $size = getimagesize($path);
      if ( $x < $size[0] || $y < $size[1] )
      {
        $file_tmp = tempnam(PATH_SITE . '/img', 'imgresize');
        System_Image::Resize($path, $file_tmp, $x, $y);
        rename($file_tmp, $path);
      }
    }
    $res->close();
  }
  /**
   * Изменение разрешения картинки с возможностью поворота с кратностью 90 градусов.
   * 
   * Поворот картинки происходит прежде ресайза.
   * 
   * @param $src - путь до исходного файла
   * @param $dest - путь до генерируемого файла
   * @param $width - ширина генерируемого изображения, в пикселях
   * @param $height - высота генерируемого изображения, в пикселях
   * @param $rotate - градус ротации (-1 = -90, 1 = 90, 2 = 180)
   * @param $rgb - цвет фона, по умолчанию - белый
   * @param $quality - качество генерируемого JPEG, по умолчанию - максимальное (100)
   * @return bolean флаг успешности операции
   */
  public static function Resize($src, $dest, $width = 0, $height = 0, $rotate = 0, $rgb = 0xFFFFFF, $quality = 100)
  {
    if (!file_exists($src)) return false;
  
    $size = getimagesize($src);
    if ($size === false) return false;
  
    // Определяем исходный формат по MIME-информации, предоставленной
    // функцией getimagesize, и выбираем соответствующую формату
    // imagecreatefrom-функцию.
    $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
    $icfunc = "imagecreatefrom" . $format;
    if (!function_exists($icfunc)) return false;
  
    if ( !$width && !$height ) {
      $width = $size[0];
      $height = $size[1];
    }
    else if ( $width && $height ) {
      //
    }
    else if ( $width < $size[0] && $width ) {
      $coefficient = $size[0] / $width;
      $height = ceil($size[1] / $coefficient);
    }
    else if ( $height < $size[1] && $height ) {
      $coefficient = $size[1] / $height;
      $width = ceil($size[0] / $coefficient);
    } else {
      $width = $size[0];
      $height = $size[1];
    }
  
    //  rotate
    if ( -1 == $rotate || 1 == $rotate ) {
      $n = $size[0];
      $size[0] = $size[1];
      $size[1] = $n;
    }
    //
  
    $isrc = $icfunc($src);
    $idest = imagecreatetruecolor($width, $height);
  
    //  rotate
    if ( 2 == $rotate ) {
      $isrc = imagerotate($isrc, 180, 0);
    }
    else if ( 0 < $rotate ) {
      $isrc = imagerotate($isrc, -90, 0);
    }
    else if ( $rotate < 0 ) {
      $isrc = imagerotate($isrc, 90, 0);
    }
    //
    $x_ratio = $width / $size[0];
    $y_ratio = $height / $size[1];
  
    $ratio       = min($x_ratio, $y_ratio);
    $use_x_ratio = ($x_ratio == $ratio);
  
    $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
    $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
    $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
    $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
  
    imagefill($idest, 0, 0, $rgb);
    imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);
  
    imagejpeg($idest, $dest, $quality);
  
    imagedestroy($isrc);
    imagedestroy($idest);
  
    return true;
  }
}
