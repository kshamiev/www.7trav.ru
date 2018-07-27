<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Демон травиана.
 * 
 * @package Core
 * @subpackage Cron
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 14.05.2010
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
define(HTTP_TRAVIAN, 'http://s1.travian.ru/');
define(LOGIN, 'aser1705');
define(PASSW, 'LeRo_3riS');
define(MAIL, 'konstanta75@mail.ru');
//  здесь указываются деревни

/*
1 - дефовые
2 - атакующие
3 - осадные
4 - ресурсные
*/

/*
soldier
t1 - фаланга
t2 - меч
t3 - следопыт
t4 - гром
t5 - друид
t6 - эдуйец
t7 - таран
t8 - требучет
 */
$village_mas = array();

$village_mas['96x11']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=194371';
$village_mas['96x11']['rpl'] = 10;
$village_mas['96x11']['soldier'] = 0;
$village_mas['96x11']['deffer'] = 0;

$village_mas['96x10']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=147787';
$village_mas['96x10']['rpl'] = 10;
$village_mas['96x10']['soldier'] = 0;
$village_mas['96x10']['deffer'] = 0;

$village_mas['97x9']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=157937';
$village_mas['97x9']['rpl'] = 10;
$village_mas['97x9']['soldier'] = 0;
$village_mas['97x9']['deffer'] = 0;

$village_mas['96x9']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=176510';
$village_mas['96x9']['rpl'] = 10;
$village_mas['96x9']['soldier'] = 0;
$village_mas['96x9']['deffer'] = 0;

$village_mas['97x10']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=13910';
$village_mas['97x10']['rpl'] = 10;
$village_mas['97x10']['soldier'] = 0;
$village_mas['97x10']['deffer'] = 0;

//  РАБОТА

//  ЛОГИРОВАНИЕ
$page = get_page(HTTP_TRAVIAN . 'login.php');
if ( !preg_match('(<form method="post" name="snd" action="dorf1.php">(.+?)</form>)si', $page, $mas) )
{
  print "ошибка получения страницы логирования\n<br>";
  Logs::Save_File('ошибка получения страницы логирования', $file_log); return;
}
//  file_put_contents(PATH_ROOT . '/log/login.htm', $page);
//
$mas = explode("\n", $mas[1]);
$postdata = '';
foreach ($mas as $str)
{
  if ( preg_match('(type="text" name="(.+?)" value="(.*?)")si', $str, $mas) ) {
    //  print $mas[1] . '=' . $mas[2] . "\n";
    $postdata.= '&' . $mas[1] . '=' . LOGIN;
  }
  else if ( preg_match('(type="password" name="(.+?)" value="(.*?)")si', $str, $mas) ) {
    $postdata.= '&' . $mas[1] . '=' . PASSW;
  }
  else if ( preg_match('(type="hidden" name="(.+?)" value="(.*?)")si', $str, $mas) ) {
    if ( 'w' == $mas[1] ) {
      $postdata.= '&' . $mas[1] . '=1680:1050';
    } else {
      $postdata.= '&' . $mas[1] . '=' . $mas[2];
    }
  }
}
$postdata = 'w=1680:1050' . $postdata;
$page = get_page(HTTP_TRAVIAN . 'dorf1.php', $postdata);
//  file_put_contents(PATH_ROOT . '/log/login_yes.htm', $page);

//  массив атак
$village_attack_mas = array();

//  СТРОИТЕЛЬСТВО
$message_attak = ''; $flag_message = false;
foreach ($village_mas as $village_name => $village)
{
  $flag_build = false;
  //  заходим в деревню  //  страница полей
  $page = get_page($village['url']);
  //  АТАКИ
  //  if ( preg_match('~<div class="mov"><span class="a1">([0-9]+)&nbsp;Нападение</span></div>~si', $page, $mas) ) {
  if ( preg_match('~<div class="mov"><span class="a1">([0-9]+)&nbsp;Нападение</span></div><div class="dur_r">&nbsp;<span id="timer1">([0-9|:]+)</span>~si', $page, $mas) ) {
    //  <div class="mov"><span class="a1">1&nbsp;Нападение</span></div>
    //  <div class="mov"><span class="a2">1&nbsp;Нападение</span></div>
    //  <div class="dur_r">&nbsp;<span id="timer1">1:13:07</span>&nbsp;ч.</div>
    $message_attak.= "Нападение на деревню: {$village_name} в количестве: {$mas[1]} через: {{$mas[2]}} часов" . "\n";
    //
    $village_attack_mas[$village_name] = true;
  }
  //  МОНИТОРИНГ РЕСУРСОВ
  if ( preg_match('~<div id="resWrap">(.+?)</div>~si', $page, $mas) ) {
    if ( preg_match_all("~<td[^>]+?>([0-9]+)/([0-9]+)</td>~si", $mas[1], $mas, 1) ) {
      //  log_file($village_name . " ресурсы: {$mas[1][0]}, {$mas[1][1]}, {$mas[1][2]}, {$mas[1][3]}", $file_log);
      $resurce_full = ''; 
      if ( $mas[1][0] >= $mas[2][0] ) {
        $resurce_full.= " < ДРЕВЕСИНА";
      }
      if ( $mas[1][1] >= $mas[2][0] ) {
        $resurce_full.= " < ГЛИНА";
      }
      if ( $mas[1][2] >= $mas[2][0] ) {
        $resurce_full.= " < ЖЕЛЕЗО";
      }
      if ( $mas[1][3] >= $mas[2][3] ) {
        $resurce_full.= " < ЗЕРНО";
      }
      if ( '' != $resurce_full ) {
        Logs::Save_File($village_name . $resurce_full, $file_log);
      }
    }
  }
  //  проверяем идет ли стройка?
  if ( false !== stripos($page, 'class="del"') ) continue;
  //  file_put_contents(PATH_ROOT . '/' . translit_file($village_name) . '.htm', $page);
  //  ЗДАНИЯ
  for ($i=19; $i <= 40; $i++)
  {
    if ( !isset($village[$i]) ) continue;
    //  print $village_name . '-' . $i . '<br>';
    $page = get_page(HTTP_TRAVIAN . 'build.php?id=' . $i);
    //  file_put_contents(PATH_ROOT . '/log/' . $i . '.htm', $page);
    preg_match('~<h1>([^<]+?)<span class="level">Уровень ([0-9]+)</span></h1>~si', $page, $mas);
    $name = trim($mas[1]);
    $level = $mas[2];
    if ( $level < $village[$i] )
    {
      if ( preg_match('~<a class="build" href="dorf2.php\?a=' . $i . '(?:.+?)c=([0-9a-z]+)">~si', $page, $mas) )
      {
        get_page(HTTP_TRAVIAN . "dorf2.php?a={$i}&c={$mas[1]}");
        Logs::Save_File($village_name . " улучшение: {$name} до уровня " . ($level + 1), $file_log);
        $flag_build = true;
        break;
      } else {
        //log_file("Не возможно улучшить: {$name}", $file_log);
      }
    }
  }
  //
  if ( $flag_build ) continue;
  //  РЕСУРСНЫЕ ПОЛЯ
  for ($i=1; $i < 19; $i++)
  {
    if ( !$village['rpl'] ) break;
    if ( isset($village[$i]) ) continue;
    $page = get_page(HTTP_TRAVIAN . 'build.php?id=' . $i);
    //  file_put_contents(PATH_ROOT . '/log/' . $i . '.htm', $page);
    preg_match('~<h1>([^<]+?)<span class="level">Уровень ([0-9]+)</span></h1>~si', $page, $mas);
    $name = $mas[1];
    $level = $mas[2];
    if ( isset($village[$i]) && $level >= $village[$i] ) continue;
    if ( $level < $village['rpl'] )
    {
      if ( preg_match('~<a class="build" href="dorf1.php\?a=' .$i . '(?:.+?)c=([0-9a-z]+)">~si', $page, $mas) )
      {
        get_page(HTTP_TRAVIAN . "dorf1.php?a={$i}&c={$mas[1]}");
        Logs::Save_File($village_name . " улучшение: {$name} до уровня " . ($level + 1), $file_log);
        $flag_build = true;
        break;
      } else {
        //log_file("Не возможно улучшить: {$name}", $file_log);
      }
    }
  }
}

//  ОБУЧЕНИЕ ВОЙСК ПРИ ОБНАРУЖЕНИИ НАПАДЕНИЯ
foreach ($village_mas as $village_name => $village)
{
  if ( !isset($village_attack_mas[$village_name]) ) continue;
  //  заходим в деревню  //  страница полей
  get_page($village['url']);
  //  инициализация
  if ( $village['deffer'] < 3 ) {
    $page = get_page(HTTP_TRAVIAN . 'build.php?id=23');
  } else if ( $village['deffer'] < 7 ) {
    $page = get_page(HTTP_TRAVIAN . 'build.php?id=27');
  } else {
    continue;
  }
  //
  if ( preg_match('(<form method="post" name="snd" action="build.php">(.+?)</form>)si', $page, $mas) )
  {
    //  сколько обучать (все)
    if ( preg_match('(<a href="#" onClick="document.snd.t' . $village['deffer'] . '[^>]+>(.+?)</a>)si', $mas[1], $mas_soldier_count) ) {
      $soldier_count = substr($mas_soldier_count[1], 1, -1);
      $postdata = "t{$village['deffer']}={$soldier_count}";
      Logs::Save_File($village_name . " обучение: {$village['deffer']} = {$soldier_count}", $file_log);
    } else {
      $postdata = "t{$village['deffer']}=1";
    }
    //  служебные формы
    $mas = explode("\n", $mas[1]);
    foreach ($mas as $str)
    {
      if ( preg_match('(<input type="hidden" name="(.+?)" value="(.*?)")si', $str, $mas) ) {
        $postdata.= '&' . $mas[1] . '=' . $mas[2];
      }
    }
    //  log_file($village_name . '=' . $postdata, $file_log);
    get_page(HTTP_TRAVIAN . 'build.php', $postdata);
  }
}

//  ОБУЧЕНИЕ ВОЙСК
foreach ($village_mas as $village_name => $village)
{
  if ( 0 == $village['soldier'] ) continue;
  //  заходим в деревню  //  страница полей
  get_page($village['url']);
  //  инициализация
  if ( $village['soldier'] < 3 ) {
    $page = get_page(HTTP_TRAVIAN . 'build.php?id=23');
  } else if ( $village['soldier'] < 7 ) {
    $page = get_page(HTTP_TRAVIAN . 'build.php?id=27');
  } else if ( $village['soldier'] < 9 ) {
    $page = get_page(HTTP_TRAVIAN . 'build.php?id=31');
  } else {
    continue;
  }
  //
  if ( preg_match('(<form method="post" name="snd" action="build.php">(.+?)</form>)si', $page, $mas) )
  {
    //  сколько обучать (все)
    if ( preg_match('(<a href="#" onClick="document.snd.t' . $village['soldier'] . '[^>]+>(.+?)</a>)si', $mas[1], $mas_soldier_count) ) {
      $soldier_count = substr($mas_soldier_count[1], 1, -1);
      $postdata = "t{$village['soldier']}={$soldier_count}";
      Logs::Save_File($village_name . " обучение: {$village['deffer']} = {$soldier_count}", $file_log);
    } else {
      $postdata = "t{$village['soldier']}=1";
    }
    //  служебные формы
    $mas = explode("\n", $mas[1]);
    foreach ($mas as $str)
    {
      if ( preg_match('(<input type="hidden" name="(.+?)" value="(.*?)")si', $str, $mas) ) {
        $postdata.= '&' . $mas[1] . '=' . $mas[2];
      }
    }
    //  log_file($village_name . '=' . $postdata, $file_log);
    get_page(HTTP_TRAVIAN . 'build.php', $postdata);
  }
}

/**
 * отправка письма
 */
if ( '' != $message_attak ) {
  //  $subject = 
  $subject = "! Травиан нападения на игрока " . LOGIN . " !";
  
  $mailer = new Mail_Mailer(MAIL, LOGIN);
  $mailer->AddAddress(MAIL, LOGIN);
  $mailer->AddAddress('dtokarenko@gdcentre.ru', 'Vasal');
  $mailer->AddAddress('aser1705@mail.ru', 'acer1705');
  $mailer->Subject = $subject;
  $mailer->Body = $message_attak;
  if ( !$mailer->Send() ) {
    Logs::Save_File($village_name . " ошибка отправки письма об атаке", 'error_mail_reminder.log');
  }
  $mailer->ClearAddresses();
  $mailer->ClearAttachments();
}

//  ЗАВЕРШЕНИЕ
//  mail($mails, $subj, $message);