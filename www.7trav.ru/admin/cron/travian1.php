<?php
/**
 * Демон травиана.
 *
 * @package Developer
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 2012.05.01
 */

/**
 * безопасность
 */
if ( !class_exists('DB') ) return;

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $sys_time;
global $file_name;
global $file_log;
global $file_exit;

//  основное
define(HTTP_TRAVIAN, 'http://ts4.travian.ru/');
define(LOGIN, 'ilosa');
define(PASSW, 'omega_75');
define(MAIL, 'konstanta75@mail.ru');

//  ЛОГИРОВАНИЕ
$page = get_page(HTTP_TRAVIAN . 'login.php');
if ( !preg_match('(<form name="login" method="POST" action="dorf1.php">(.+?)</form>)si', $page, $mas) )
{
  Logs::Save_File('ошибка получения страницы логирования', $file_log);
  return;
}
$mas = explode("\n", $mas[1]);
$postdata = '';
foreach ( $mas as $str )
{
  if ( preg_match('(type="text" name="(.+?)" value="(.*?)")si', $str, $mas) )
  {
    //  print $mas[1] . '=' . $mas[2] . "\n";
    $postdata.= '&' . $mas[1] . '=' . LOGIN;
  }
  else if ( preg_match('(type="password" maxlength="20" name="(.+?)" value="(.*?)")si', $str, $mas) )
  {
    $postdata.= '&' . $mas[1] . '=' . PASSW;
  }
  else if ( preg_match('(type="hidden" name="(.+?)" value="(.*?)")si', $str, $mas) )
  {
    $postdata.= '&' . $mas[1] . '=' . $mas[2];
  }
}
$postdata = substr($postdata, 1) . '&w=1680:1050';
$page = get_page(HTTP_TRAVIAN . 'dorf1.php', $postdata);

$village_mas = array();

$village_mas['0']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=135242';
$village_mas['0']['rpl'] = 10;
$village_mas['0'][40] = 20;

/*

$village_mas['1']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=20288';
$village_mas['1']['rpl'] = 8;
$village_mas['1'][20] = 20;
$village_mas['1'][37] = 20;

$village_mas['2']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=101575';
$village_mas['2']['rpl'] = 0;
$village_mas['2'][22] = 20;
$village_mas['2'][33] = 20;

$village_mas['3']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=110226';
$village_mas['3']['rpl'] = 0;
$village_mas['3'][23] = 20;

$village_mas['4']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=116757';
$village_mas['4']['rpl'] = 0;
$village_mas['4'][23] = 20;

$village_mas['5']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=121284';
$village_mas['5']['rpl'] = 0;
$village_mas['5'][23] = 20;

$village_mas['7']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=129405';
$village_mas['7']['rpl'] = 0;
$village_mas['7'][30] = 10;
$village_mas['7'][33] = 10;
$village_mas['7'][20] = 20;

$village_mas['6']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=125915';
$village_mas['6']['rpl'] = 0;
$village_mas['6'][29] = 20;

$village_mas['8']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=132339';
$village_mas['8']['rpl'] = 0;
$village_mas['8'][21] = 12;
$village_mas['8'][22] = 12;
$village_mas['8'][23] = 20;

*/

$village_mas['9']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=134869';
$village_mas['9']['rpl'] = 0;
$village_mas['9'][21] = 15;
$village_mas['9'][22] = 15;

$village_mas['10']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=136554';
$village_mas['10']['rpl'] = 0;
$village_mas['10'][35] = 15;
$village_mas['10'][38] = 15;

$village_mas['11']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=114388';
$village_mas['11']['rpl'] = 0;
$village_mas['11'][27] = 15;
$village_mas['11'][32] = 15;


$village_mas['12']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=139809';
$village_mas['12']['rpl'] = 0;
$village_mas['12'][19] = 15;
$village_mas['12'][24] = 15;
$village_mas['12'][32] = 20;

$village_mas['13']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=140897';
$village_mas['13']['rpl'] = 9;
$village_mas['13'][26] = 20;
$village_mas['13'][39] = 10;
$village_mas['13'][28] = 20;

$village_mas['14']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=141638';
$village_mas['14']['rpl'] = 3;
$village_mas['14'][26] = 5;
$village_mas['14'][27] = 12;
$village_mas['14'][28] = 12;

//  РАБОТА
//  СТРОИТЕЛЬСТВО
foreach ( $village_mas as $village_name => $village )
{
  //  заходим в деревню  //  страница полей
  $page = get_page($village['url']);
  //  МОНИТОРИНГ РЕСУРСОВ
  if ( preg_match('~<ul id="res">(.+?)</ul>~si', $page, $mas) )
  {
    if ( preg_match_all("~<span[^>]+?>([0-9]+)/([0-9]+)</span>~si", $mas[1], $mas, 1) )
    {
//      Logs::Save_File($village_name . " ресурсы: {$mas[1][0]}, {$mas[1][1]}, {$mas[1][2]}, {$mas[1][3]}", $file_log);
      $resurce_full = '';
      if ( $mas[1][0] >= $mas[2][0] )
      {
        $resurce_full.= " < ДРЕВЕСИНА";
      }
      if ( $mas[1][1] >= $mas[2][0] )
      {
        $resurce_full.= " < ГЛИНА";
      }
      if ( $mas[1][2] >= $mas[2][0] )
      {
        $resurce_full.= " < ЖЕЛЕЗО";
      }
      if ( $mas[1][3] >= $mas[2][3] )
      {
        $resurce_full.= " < ЗЕРНО";
      }
      if ( '' != $resurce_full )
      {
        Logs::Save_File($village_name . $resurce_full, $file_log);
      }
    }
  }
  //  РЕСУРСНЫЕ ПОЛЯ
  for ( $i = 1; $i < 19; $i++ )
  {
    if ( !$village['rpl'] )
      break;
    if ( isset($village[$i]) ) // это чтобы отшить постройки зданий случайно (вообще быть не должно)
      continue;
    if ( '0' == $village_name && ( 3 == $i || 4 == $i || 16 == $i ) )
      continue;
    $page = get_page(HTTP_TRAVIAN . 'build.php?id=' . $i);
    //  file_put_contents(PATH_ROOT . '/log/' . $i . '.htm', $page);
//    preg_match('~<h1 class="titleInHeader">([^<]*?)<span class="level">([^<]*?)Уровень ([0-9]+)([^<]*?)</span>([^<]+?)</h1>~si', $page, $mas);
    preg_match('~Уровень ([0-9]+)~si', $page, $mas);
//    $name = trim($mas[1]);
    $level = $mas[1];
    if ( isset($village[$i]) && $level >= $village[$i] )
      continue;
    if ( $level < $village['rpl'] )
    {
      if ( preg_match('~onclick="window.location.href = \'dorf1.php\?a=' . $i . '(?:.+?)c=([0-9a-z]+)\'~si', $page, $mas) )
      {
        get_page(HTTP_TRAVIAN . "dorf1.php?a={$i}&c={$mas[1]}");
        Logs::Save_File($village_name . " улучшение: {$i} до уровня " . ($level + 1), $file_log);
        break;
      }
      else
      {
        //log_file("Не возможно улучшить: {$name}", $file_log);
      }
    }
  }
  //  ЗДАНИЯ
  for ( $i = 19; $i <= 40; $i++ )
  {
    if ( !isset($village[$i]) )
      continue;
    $page = get_page(HTTP_TRAVIAN . 'build.php?id=' . $i);
//    preg_match('~<h1 class="titleInHeader">([^<]+?)<span class="level">Уровень ([0-9]+)</span></h1>~si', $page, $mas);
    preg_match('~Уровень ([0-9]+)~si', $page, $mas);
//    $name = trim($mas[1]);
    $level = $mas[1];
    if ( $level < $village[$i] )
    {
      if ( preg_match('~onclick="window.location.href = \'dorf2.php\?a=' . $i . '(?:.+?)c=([0-9a-z]+)\'~si', $page, $mas) )
      {
        get_page(HTTP_TRAVIAN . "dorf2.php?a={$i}&c={$mas[1]}");
        Logs::Save_File($village_name . " улучшение: {$i} до уровня " . ($level + 1), $file_log);
        break;
      }
      else
      {
        //log_file("Не возможно улучшить: {$name}", $file_log);
      }
    }
  }
}
