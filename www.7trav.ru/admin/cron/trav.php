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
if ( !class_exists('DB') )
    return;

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $sys_time;
global $file_name;
global $file_log;
global $file_exit;

//  основное
define(HTTP_TRAVIAN, 'http://ts8.travian.ru/');
define(LOGIN, 'sota');
define(PASSW, 'LeRo5Sir');
define(MAIL, 'konstanta75@mail.ru');

function scenario_start()
{
    $village = [];
    // структура
    $village[26] = 20;
    $village[31] = 10;
    $village[32] = 10;
    // заводы
    $village[37] = 5;
    $village[34] = 5;
    $village[24] = 5;
    $village[27] = 5;
    $village[28] = 5;
    // склад и амбар
    $village[19] = 20;
    $village[38] = 20;
    return $village;
}

function scenario_final()
{
    $village = [];
    // структура
    $village[19] = 20;
    $village[38] = 20;
    $village[32] = 20;
    $village[30] = 20;
    $village[36] = 20;
    $village[20] = 20;
    $village[39] = 10;
    $village[40] = 20;
    // склад и амбар
    $village[33] = 20;
    $village[35] = 20;
    return $village;
}

function check_full($page)
{
    $resurce_full = '';
    // склад
    $stockBarWarehouse = 0;
    if ( preg_match('~<span class="value" id="stockBarWarehouse">([0-9]+)</span>~si', $page, $match) )
    {
        $stockBarWarehouse = $match[1];
    }
    // амбар
    $stockBarGranary = 0;
    if ( preg_match('~<span class="value" id="stockBarGranary">([0-9]+)</span>~si', $page, $match) )
    {
        $stockBarGranary = $match[1];
    }
    // дерево
    if ( preg_match('~<span id="l1" class="value">([0-9]+)</span>~si', $page, $match) )
    {
        if ( $stockBarWarehouse <= $match[1] )
            $resurce_full .= " < ДРЕВЕСИНА";
    }
    // глина
    if ( preg_match('~<span id="l2" class="value">([0-9]+)</span>~si', $page, $match) )
    {
        if ( $stockBarWarehouse <= $match[1] )
            $resurce_full .= " < ГЛИНА";
    }
    // железо
    if ( preg_match('~<span id="l3" class="value">([0-9]+)</span>~si', $page, $match) )
    {
        if ( $stockBarWarehouse <= $match[1] )
            $resurce_full .= " < ЖЕЛЕЗО";
    }
    // зерно
    if ( preg_match('~<span id="l4" class="value">([0-9]+)</span>~si', $page, $match) )
    {
        if ( $stockBarGranary <= $match[1] )
            $resurce_full .= " < ЗЕРНО";
    }
    return $resurce_full;
}

//  ЛОГИРОВАНИЕ
$page = get_page(HTTP_TRAVIAN . 'login.php');
if ( !preg_match('(<form name="login" method="POST" action="dorf1.php">(.+?)</form>)si', $page, $mas) )
{
    Logs::Save_File('ошибка получения страницы логирования', $file_log);
    return;
}
$mas = explode("\n", $mas[1]);
$postdata = '';
foreach ($mas as $str)
{
    if ( preg_match('(type="text" name="(.+?)" value="(.*?)")si', $str, $mas) )
    {
        //  print $mas[1] . '=' . $mas[2] . "\n";
        $postdata .= '&' . $mas[1] . '=' . LOGIN;
    }
    else if ( preg_match('(type="password" maxlength="20" name="(.+?)" value="(.*?)")si', $str, $mas) )
    {
        $postdata .= '&' . $mas[1] . '=' . PASSW;
    }
    else if ( preg_match('(type="hidden" name="(.+?)" value="(.*?)")si', $str, $mas) )
    {
        $postdata .= '&' . $mas[1] . '=' . $mas[2];
    }
}
$postdata = substr($postdata, 1) . '&w=1680:1050';
$page = get_page(HTTP_TRAVIAN . 'dorf1.php', $postdata);

///
