<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Системный Класс.
 *
 * Работа со строковыми величинами.
 * 
 * @package Core
 * @subpackage System
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 22.01.2009
 */
final class System_String
{
  /**
   * Алгоритмичные свойства для трансформации числа в словесное представление
   *
   * @var array
   */
  private static $_1_2 = array();
  private static $_1_19 = array();
  private static $_des = array();
  private static $_hang = array();
  private static $_namerub = array();
  private static $_nametho = array();
  private static $_namemil = array();
  private static $_namemrd = array();
  private static $_kopeek = array();
  /**
   * Литеральный перевод слов.
   * 
   * @param string $string - входящая строка
   * @return string - обработанная строка
   */
  public static function Translit($string)
  {
    $string = strtr($string, "абвгдезийклмнопрстуфхъыэ","abvgdeziyklmnoprstufh'ie");
    $string = strtr($string, "АБВГДЕЗИЙКЛМНОПРСТУФХЪЫЭ","ABVGDEZIYKLMNOPRSTUFH'IE");
    $string = strtr($string, array('ж'=>'zh','Ж'=>'Zh','ц'=>'ts','Ц'=>'Ts','ч'=>'ch','Ч'=>'Ch','ш'=>'sh','Ш'=>'Sh','щ'=>'shch','Щ'=>'Shch','ь'=>'','Ь'=>'','ю'=>'yu','Ю'=>'Yu','я'=>'ya','Я'=>'Ya'));
    return $string;
  }
  /**
   * Литеральный перевод имен файлов.
   * 
   * @param string $string - входящая строка
   * @return string - обработанная строка
   */
  public static function Translit_File($string)
  {
    $string = strtr($string, "абвгдезийклмнопрстуфхыэ","abvgdeziyklmnoprstufhie");
    $string = strtr($string, "АБВГДЕЗИЙКЛМНОПРСТУФХЫЭ","ABVGDEZIYKLMNOPRSTUFHIE");
    $string = strtr($string, array('ъ'=>'','Ъ'=>'',' '=>'_','ж'=>'zh','Ж'=>'Zh','ц'=>'ts','Ц'=>'Ts','ч'=>'ch','Ч'=>'Ch','ш'=>'sh','Ш'=>'Sh','щ'=>'shch','Щ'=>'Shch','ь'=>'','Ь'=>'','ю'=>'yu','Ю'=>'Yu','я'=>'ya','Я'=>'Ya'));
    return preg_replace('([^.a-z0-9_-])si', '', $string);
  }
  /**
   * Литеральный перевод в url вид.
   * 
   * @param string $string - входящая строка
   * @return string - обработанная строка
   */
  public static function Translit_Url($string)
  {
    $string = strtr($string, "абвгдезийклмнопрстуфхыэ","abvgdeziyklmnoprstufhie");
    $string = strtr($string, "АБВГДЕЗИЙКЛМНОПРСТУФХЫЭ","abvgdeziyklmnoprstufhie");
    $string = strtr($string, array('ъ'=>'','Ъ'=>'',' '=>'-','_'=>'-','ж'=>'zh','Ж'=>'Zh','ц'=>'ts','Ц'=>'Ts','ч'=>'ch','Ч'=>'Ch','ш'=>'sh','Ш'=>'Sh','щ'=>'shch','Щ'=>'Shch','ь'=>'','Ь'=>'','ю'=>'yu','Ю'=>'Yu','я'=>'ya','Я'=>'Ya'));
    return preg_replace('([^-|a-z|.|0-9])si', '', strtolower($string));
  }
  /**
   * Разбиение числа на разрядность.
   * 
   * @param integer $int
   * @return string - обработанная строка
   * @deprecated
   */
  public static function wordwrapint($int)
  {
    $int = trim(implode("",array_reverse(preg_split("//", $int))));
    $int = wordwrap($int, 3, ' ', 1);
    return trim(implode("",array_reverse(preg_split("//", $int))));
  }
  /**
   * Полипендрон.
   * 
   * Переворачивает строку символов (задом на перед).
   * 
   * @param string $string - входящая строка
   * @return string - перевернутая строка
   */
  public static function Reverse($string)
  {
    return trim(implode("",array_reverse(preg_split("//", $string))));
  }
  /**
   * Генератор случайной строки символов.
   * 
   * @return string - случайная строка символов
   */
  public static function Random() // { mt_srand((double)microtime()*1000000); return md5(uniqid(mt_rand())); }
  {
    $passw_mas=array(0,1,2,3,4,5,6,7,8,9,'q','w','e','r','t','y','u','i','o','p','a','s','d','f','g','h','j','k','l','z','x','c','v','b','n','m','Q','W','E','R','T','Y','U','I','O','P','A','S','D','F','G','H','J','K','L','Z','X','C','V','B','N','M');
    while ( true )
    {
      $passw_str='';
      for ($i=0;$i<9;$i++)
      {
        shuffle($passw_mas);
        $passw_str.=$passw_mas[mt_rand(0,61)];
      }
      if ( preg_match('([0-9](.*?)[0-9])s',$passw_str) && preg_match('([a-z](.*?)[a-z])s',$passw_str) && preg_match('([A-Z](.*?)[A-Z])s',$passw_str) ) break;
    }
    return $passw_str;
  }
  /**
   * Трансформация числа в словесное представление
   *
   * @param integer $int
   * @return string - словесное представление числа
   */
  public static function Num_Str($int)
  {
    self::_Init_Semantic();
    $s = " ";
    $s1 = " ";
    $s2 = " ";
    $kop = intval(($int * 100 - intval($int) * 100));
    $int = intval($int);
    if ( $int >= 1000000000 ) {
      $many = 0;
      self::_Semantic(intval($int / 1000000000), $s1, $many, 3);
      $s .= $s1 . self::$_namemrd[$many];
      $int %= 1000000000;
    }
    if ( $int >= 1000000 ) {
      $many = 0;
      self::_Semantic(intval($int / 1000000), $s1, $many, 2);
      $s .= $s1 . self::$_namemil[$many];
      $int %= 1000000;
      if ( $int == 0 ) {
        $s .= "рублей ";
      }
    }
    if ( $int >= 1000 ) {
      $many = 0;
      self::_Semantic(intval($int / 1000), $s1, $many, 1);
      $s .= $s1 . self::$_nametho[$many];
      $int %= 1000;
      if ( $int == 0 ) {
        $s .= "рублей ";
      }
    }
    if ( $int != 0 ) {
      $many = 0;
      self::_Semantic($int, $s1, $many, 0);
      $s .= $s1 . self::$_namerub[$many];
    }
    if ( $kop > 0 ) {
      $many = 0;
      self::_Semantic($kop, $s1, $many, 1);
      $s .= $s1 . self::$_kopeek[$many];
    } else {
      $s .= " 00 копеек";
    }
    return $s;
  }
  /**
   * Инициализация алгоритмичных свойств для трансформации числа в словесное представление
   * 
   * @return void
   */
  private static function _Init_Semantic()
  {
    self::$_1_2[1] = "одна ";
    self::$_1_2[2] = "две ";
    
    self::$_1_19[1] = "один ";
    self::$_1_19[2] = "два ";
    self::$_1_19[3] = "три ";
    self::$_1_19[4] = "четыре ";
    self::$_1_19[5] = "пять ";
    self::$_1_19[6] = "шесть ";
    self::$_1_19[7] = "семь ";
    self::$_1_19[8] = "восемь ";
    self::$_1_19[9] = "девять ";
    self::$_1_19[10] = "десять ";
    
    self::$_1_19[11] = "одиннацать ";
    self::$_1_19[12] = "двенадцать ";
    self::$_1_19[13] = "тринадцать ";
    self::$_1_19[14] = "четырнадцать ";
    self::$_1_19[15] = "пятнадцать ";
    self::$_1_19[16] = "шестнадцать ";
    self::$_1_19[17] = "семнадцать ";
    self::$_1_19[18] = "восемнадцать ";
    self::$_1_19[19] = "девятнадцать ";
    
    self::$_des[2] = "двадцать ";
    self::$_des[3] = "тридцать ";
    self::$_des[4] = "сорок ";
    self::$_des[5] = "пятьдесят ";
    self::$_des[6] = "шестьдесят ";
    self::$_des[7] = "семьдесят ";
    self::$_des[8] = "восемдесят ";
    self::$_des[9] = "девяносто ";
    
    self::$_hang[1] = "сто ";
    self::$_hang[2] = "двести ";
    self::$_hang[3] = "триста ";
    self::$_hang[4] = "четыреста ";
    self::$_hang[5] = "пятьсот ";
    self::$_hang[6] = "шестьсот ";
    self::$_hang[7] = "семьсот ";
    self::$_hang[8] = "восемьсот ";
    self::$_hang[9] = "девятьсот ";
    
    self::$_namerub[1] = "рубль ";
    self::$_namerub[2] = "рубля ";
    self::$_namerub[3] = "рублей ";
    
    self::$_nametho[1] = "тысяча ";
    self::$_nametho[2] = "тысячи ";
    self::$_nametho[3] = "тысяч ";
    
    self::$_namemil[1] = "миллион ";
    self::$_namemil[2] = "миллиона ";
    self::$_namemil[3] = "миллионов ";
    
    self::$_namemrd[1] = "миллиард ";
    self::$_namemrd[2] = "миллиарда ";
    self::$_namemrd[3] = "миллиардов ";
    
    self::$_kopeek[1] = "копейка ";
    self::$_kopeek[2] = "копейки ";
    self::$_kopeek[3] = "копеек ";
  }
  /**
   * Служебный метод для семантического построения словоформы
   *
   * @param itneger $i
   * @param string $words
   * @param integer $fem
   * @param integer $f
   */
  private static function _Semantic($i, &$words, &$fem, $f)
  {
    $words = "";
    $fl = 0;
    if ( $i >= 100 ) {
      $jkl = intval($i / 100);
      $words .= self::$_hang[$jkl];
      $i %= 100;
    }
    if ( $i >= 20 ) {
      $jkl = intval($i / 10);
      $words .= self::$_des[$jkl];
      $i %= 10;
      $fl = 1;
    }
    switch ( $i ) {
      case 1:
        $fem = 1;
        break;
      case 2:
      case 3:
      case 4:
        $fem = 2;
        break;
      default:
        $fem = 3;
        break;
    }
    if ( $i ) {
      if ( $i < 3 && $f > 0 ) {
        if ( $f >= 2 ) {
          $words .= self::$_1_19[$i];
        } else {
          $words .= self::$_1_2[$i];
        }
      } else {
        $words .= self::$_1_19[$i];
      }
    }
  }
}