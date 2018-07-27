<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Класс реализующий обрабчики нод XML объекта.
 * 
 * Для обработки нужной ноды создется метод-обработчик с соответсвующим ей именем.
 * К примеру: &lt;item ...&gt; ... &lt;/item&gt; = function Item()
 * ! Регистр и там и там не имеет значения !
 * Сулжит в первую очередь для поточной обработки нод.
 * Во время парсинга файла очень большего размера.
 * После обработки он не добавляется в общее дерево объекта XML
 * 
 * @package Core
 * @subpackage XML
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 16.03.2010
 */
class Xml_Handler
{
  /**
   * Выходной файл
   *
   * @var resource
   */
  private $_FileName;
  /**
   * Дескриптор выходного файла
   *
   * @var resource
   */
  private $_Fp;
  /**
   * Конструткор
   * 
   * @param string $file - xml файл источник (который обрабатывается)
   */
  public function __construct($file)
  {
    $mas = explode('.', $file);
    array_pop($mas);
    $this->_FileName = implode('.', $mas) . '.txt';
    $this->_Fp = fopen($this->_FileName, 'a');
  }
  public function Example(Xml_Object $Xml)
  {
    //  print '$Xml<pre>'; print_r($Xml); print '</pre>';
  }
  private function _Save($str)
  {
    fputs($this->_Fp, $str . "\n");
  }
  public function __destruct()
  {
    fclose($this->_Fp);
    if ( 0 == filesize($this->_FileName) ) {
      unlink($this->_FileName);
    }
  }
}
