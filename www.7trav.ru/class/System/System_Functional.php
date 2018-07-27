<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Системный Класс.
 *
 * Различный функционал общего назначения и применения.
 * 
 * @package Core
 * @subpackage System
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 22.01.2009
 */
final class System_Functional
{
  /**
   * Формирование календарного месяца.
   * 
   * @param integer $g - год
   * @param integer $m - месяц
   * @return array - сформированный месяц
   */
  public static function Calendar($g, $m)
  {
    $week=array();
    // 1. Первая неделя
    $num=0; $day_count=1;
    $dayofmonth=date('t',mktime(0,0,0,$m,$day_count,$g));
    for ($i=0;$i<7;$i++)
    {
      // Вычисляем номер дня недели для числа
      $dayofweek=date('w',mktime(0,0,0,$m,$day_count,$g));
      // Приводим к числа к формату 1 - понедельник, ..., 6 - суббота
      $dayofweek=$dayofweek-1;
      if ( $dayofweek==-1 ) $dayofweek=6;
      if ( $dayofweek==$i )
      { $week[$num][$i]=$day_count; $day_count++; }
      else
      { $week[$num][$i]=''; }
    }
    // 2. Последующие недели месяца
    while ( true )
    {
      $num++;
      for ($i=0;$i<7;$i++)
      {
        if ( $day_count>$dayofmonth )
        { $week[$num][$i]=''; }
        else
        { $week[$num][$i]=$day_count; $day_count++; }
        // Если достигли конца месяца - выходим из цикла
      }
      // Если достигли конца месяца - выходим из цикла
      if ( $day_count>$dayofmonth ) break;
    }
    return $week;
  }
}
