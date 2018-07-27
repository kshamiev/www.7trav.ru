<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Файл с библиотекой для работы с шаблонами/
 *
 * Реализует взаимодействие с шаблонами.
 * Собирает и инкапсулирует данные в пределах шаблона.
 * Компиляция шаблонов (механизм похожий на Smarty).
 * Собирает готовый шаблон с переданными данными.
 *
 * @package Core
 * @subpackage Templates
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */
final class Templates
{
  /**
   * Путь до папки шаблонов
   *
   * @var string
   */
  private $_Path;
  /**
   * Путь до скомпилированного tpl шаблона
   *
   * @var unknown_type
   */
  private $_PathTpl = '';
  /**
   * Данные вставляемые в шаблон
   *
   * @var array
   */
  private $_Data = array();
  /**
   * Конструтор
   * Инициализация пути до модуля и шаблонов
   *
   * @param string $modul
   */
  public function __construct($path = 'templates')
  {
    $this->_Path = $path;
  }
  /**
   * Добавление данных в шаблон
   * Для последующего вывода в шаблон
   *
   * @param string $variable  переменная шаблона
   * @param mixed $value      ее значение
   */
  public function Assign($variable, $value)
  {
    $this->_Data[$variable] = $value;
  }
  /**
   * Добавление данных в шаблон по ссылке
   * Для последующего вывода в шаблон
   *
   * @param string $variable  переменная шаблона
   * @param mixed $value      ее значение по ссылке
   */
  public function Assign_Link($variable, &$value)
  {
    $this->_Data[$variable] = &$value;
  }
  /**
   * Удаление данных из шаблон
   * И из последующего вывода в шаблон
   *
   * @param string $variable  переменная шаблона
   */
  public function Remove($variable)
  {
    unset($this->_Data[$variable]);
  }
  /**
   * Работа с шаблоны системы администрирования
   *
   * Компиляция htm шаблона, если он есть в tpl шаблон.
   * Выполнение tpl шаблона и получение конечного результата работы модуля.
   * В боевом режиме после компиляции htm шаблон архивируется (для увеличения производительности)
   * Алгоритм поиска шаблона:
   * 1 templates/obj_$tbl/$tbl_$block.htm
   * 2 templates/$modul/$modul_$block.htm
   * 3 templates/$block/$block.htm
   *
   * @param ModSystem $ModSystem
   * @return string - собранный шаблон со вставленными данными
   */
  public function Fetch_System(ModSystem $ModSystem)
  {
    $mod = $ModSystem->Modul;
    $tbl = $ModSystem->ModulUser;
    $blk = $ModSystem->Block;
    //  print $mod . '-' . $tbl . '-' . $blk . '<br>';  
    //  поиск по таблице
    if ( file_exists($htm = $this->_Path . '/' . $tbl . '/' . $tbl . $blk . '.htm') ) {
      $this->_PathTpl = $this->_Path . '/' . $tbl . '/' . $tbl . $blk . '.tpl';
      file_put_contents($this->_PathTpl, $this->_Pasing(file_get_contents($htm)));
      if ( FLAG_RUN ) rename($htm, str_replace('.htm', '_arhiv_' . date('Ymd') . '.htm', $htm));
      return $this->_Run(); //  chmod($this->_PathTpl, 0666);
    } else if ( file_exists($this->_PathTpl = $this->_Path . '/' . $tbl . '/' . $tbl . $blk . '.tpl') ) {
      return $this->_Run();
    }
    //  поиск по модулю
    if ( file_exists($htm = $this->_Path . '/' . $mod . '/' . $mod . $blk . '.htm') ) {
      $this->_PathTpl = $this->_Path . '/' . $mod . '/' . $mod . $blk . '.tpl';
      file_put_contents($this->_PathTpl, $this->_Pasing(file_get_contents($htm)));
      if ( FLAG_RUN ) rename($htm, str_replace('.htm', '_arhiv_' . date('Ymd') . '.htm', $htm));
      return $this->_Run(); //  chmod($this->_PathTpl, 0666);
    } else if ( file_exists($this->_PathTpl = $this->_Path . '/' . $mod . '/' . $mod . $blk . '.tpl') ) {
      return $this->_Run();
    }
    //  поиск по блоку
    if ( file_exists($htm = $this->_Path . '/' . $blk . '/' . $blk . '.htm') ) {
      $this->_PathTpl = $this->_Path . '/' . $blk . '/' . $blk . '.tpl';
      file_put_contents($this->_PathTpl, $this->_Pasing(file_get_contents($htm)));
      if ( FLAG_RUN ) rename($htm, str_replace('.htm', '_arhiv_' . date('Ymd') . '.htm', $htm));
      return $this->_Run(); //  chmod($this->_PathTpl, 0666);
    } else if ( file_exists($this->_PathTpl = $this->_Path . '/' . $blk . '/' . $blk . '.tpl') ) {
      return $this->_Run();
    } else {
      Logs::Save_File("Не найден шаблон [{$this->_PathTpl}]", 'error_tpl_exists.log');
      return '';
    }
    unset($mod); unset($tbl); unset($blk);
  }
  /**
   * Работа с шаблоны сайта (внешнего ресурса)
   *
   * Компиляция htm шаблона, если он есть в tpl шаблон.
   * Выполнение tpl шаблона и получение конечного результата работы модуля.
   * В боевом режиме после компиляции htm шаблон архивируется (для увеличения производительности)
   * Алгоритм поиска шаблона:
   * 1 templates/$modul/$block.htm
   *
   * @param string $modul - папка шаблона
   * @param string $block - имя шаблона
   * @return string - собранный шаблон со вставленными данными
   */
  public function Fetch($modul, $block = '')
  {
    if ( '' == $block ) {
      $block = $modul;
    }
    if ( file_exists($htm = $this->_Path . '/' . $modul . '/' . $block . '.htm') ) {
      $this->_PathTpl = $this->_Path . '/' . $modul . '/' . $block . '.tpl';
      file_put_contents($this->_PathTpl, $this->_Pasing(file_get_contents($htm)));
      if ( FLAG_RUN ) rename($htm, str_replace('.htm', '_arhiv_' . date('Ymd') . '.htm', $htm));
      return $this->_Run(); //  chmod($this->_PathTpl, 0666);
    } else if ( file_exists($this->_PathTpl = $this->_Path . '/' . $modul . '/' . $block . '.tpl') ) {
      return $this->_Run();
    } else {
      Logs::Save_File("Не найден шаблон [{$this->_PathTpl}]", 'error_tpl_exists.log');
      return '';
    }
    unset($modul); unset($block);
  }
  /**
   * Парсинг (компиляция) htm шаблона в tpl шаблон
   *
   * @param string $tpl
   * @return string
   */
  private function _Pasing($tpl)
  {
    //	цикл foreach
    $tpl = preg_replace('({(foreach .+?)})s', '<?php \\1 { ?>', $tpl);
    $tpl = preg_replace('({(/foreach)})s', '<?php } ?>', $tpl);
    //	цикл for
    $tpl = preg_replace('({(for .+?)})s', '<?php \\1 { ?>', $tpl);
    $tpl = preg_replace('({(/for)})s', '<?php } ?>', $tpl);
    //	цикл while
    $tpl = preg_replace('({(while .+?)})s', '<?php \\1 { ?>', $tpl);
    $tpl = preg_replace('({(/while)})s', '<?php } ?>', $tpl);
    //	логика if
    $tpl = preg_replace('({(if .+?)})s', '<?php \\1 { ?>', $tpl);
    $tpl = preg_replace('({(else if .+?)})s', '<?php } \\1 { ?>', $tpl);
    $tpl = preg_replace('({(else)})s', '<?php } else { ?>', $tpl);
    $tpl = preg_replace('({(/if)})s', '<?php } ?>', $tpl);
    //	переменные установка
    $tpl = preg_replace('({(\$[a-z]{1}[^}]{0,50}[+|-]{2})})si', '<?php \\1; ?>', $tpl);
    $tpl = preg_replace('({(\$[a-z]{1}[^}]{0,50}=[^}]{1,50})})si', '<?php \\1; ?>', $tpl);
    $tpl = preg_replace('({(\$_[a-z]{1}[^}]{0,50}=[^}]{1,50})})si', '<?php \\1; ?>', $tpl);
    //	переменные вывод
    $tpl = preg_replace('({([a-z]{1}[^}]{0,70})})si', '<?=\\1?>', $tpl);
    $tpl = preg_replace('({(\$[a-z]{1}[^}]{0,70})})si', '<?=\\1?>', $tpl);
    $tpl = preg_replace('({(\$_[a-z]{1}[^}]{0,70})})si', '<?=\\1?>', $tpl);
    //
    return $tpl;
  }
  /**
   * Выполнение скомпилированного шаблона
   * И возвращение результата его работы.
   * Собранный блок страницы с конечными данными
   *
   * @return string
   */
  private function _Run()
  {
    extract($this->_Data);
    ob_start();
    include $this->_PathTpl;
    $tpl = ob_get_contents();
    ob_end_clean();
    $this->_Data = array();
    return $tpl;
  }
}