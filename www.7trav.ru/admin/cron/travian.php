<?php
// 1 - ресурсная деревня
// 9 - столица
$village_mas = array();

$village_mas['113609'] = 1;
$village_mas['118859'] = 1;
$village_mas['121799'] = 1;
$village_mas['129179'] = 1;
$village_mas['126996'] = 1;
$village_mas['124407'] = 1;
$village_mas['121799'] = 1;
$village_mas['95801'] = 9;

//  основное
define('HTTP_TRAVIAN', 'http://ts8.travian.ru/');
define('LOGIN', 'sota');
define('PASSW', 'LeRo5Sir');
define('MAIL', 'konstanta75@mail.ru');

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

function scenario($level)
{
    if ( 9 == $level )
    {
        return scenario_stol();
    }
    else if ( 1 == $level )
    {
        return scenario_resurce();
    }
    else if ( 2 == $level )
    {
        return scenario_r2();
    }
    else
    {
        return scenario_resurce();
    }
}

function scenario_resurce()
{
    $village = [];
    // Step 1
    $village[26] = 20; // Главное здание
    $village[31] = 10; // Резиденция
    $village[19] = 18; // Склад
    $village[38] = 18; // Амбар
    $village[32] = 20; // Рынок
    // заводы
    $village[37] = 5;
    $village[34] = 5;
    $village[24] = 5;
    $village[27] = 5;
    $village[28] = 5;
    // структура
    $village[36] = 10; // Академия
    $village[20] = 10; // Ратуша
    $village[25] = 10; // Конюшня
    $village[21] = 10; // Торговая палата
    $village[30] = 10; // Посольство
    $village[35] = 12; // Склад
    $village[33] = 12; // Амбар
    $village[39] = 10; // Пункт сбора
    $village[40] = 20; // Стена
    return $village;
}

function scenario_stol()
{
    $village = [];
    $village[28] = 20; // 
    return $village;
}

function scenario_r2()
{
    $village = [];
    $village[31] = 20; // 
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

function get_travian_data($village_id)
{
    $path = __DIR__ . "/travian_data/" . $village_id . ".ini";
    if ( file_exists($path) )
    {
        return parse_ini_file($path);
    }
    $data = array();
    for ($i = 1; $i < 41; $i++)
    {
        $data[$i] = 0;
    }
    return $data;
}

function set_travian_data($village_id, $data)
{
    $path = __DIR__ . "/travian_data";
    if ( !is_dir($path) )
        mkdir($path);
    $path .= "/" . $village_id . ".ini";
    $cache = '';
    foreach ($data as $key => $val)
    {
        $cache .= $key . '="' . $val . '"' . "\n";
    }
    file_put_contents($path, $cache);
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

//  РАБОТА //  СТРОИТЕЛЬСТВО
foreach ($village_mas as $village_id => $scenario_id)
{
    $scenario = scenario($scenario_id);
//    pre($scenario);
    $village_data = get_travian_data($village_id);
//    pre($village_data);
    //  заходим в деревню  //  страница полей
    $page = get_page(HTTP_TRAVIAN . 'dorf1.php?newdid=' . $village_id);
    // echo HTTP_TRAVIAN . 'dorf1.php?newdid=' . $village_id . "<br>";
    //  ЗДАНИЯ
    // идем по сценарию
    foreach ($scenario as $id => $level)
    {
//        echo $village_id . " - " . $id . "<br>";
        // если не достроено
        if ( $village_data[$id] < $level )
        {
            echo $village_id . " - " . $id . " ZAPROS<br>";
            // сверяемся с реальностью
            $page = get_page(HTTP_TRAVIAN . 'build.php?id=' . $id);
            preg_match('~<h1 class="titleInHeader">.*?Уровень ([0-9]+).*?</h1>~si', $page, $level_current);
            // если здание существует
            if ( isset($level_current[1]) )
            {
                // уровень меньше пытаемся построить
                $village_data[$id] = $level_current[1];
                if ( $level_current[1] < $level && preg_match('~onclick="window.location.href = \'dorf2.php\?a=' . $id . '(?:.+?)c=([0-9a-z]+)\'~si', $page, $mas) )
                {
                    $village_data[$id]++;
                    get_page(HTTP_TRAVIAN . "dorf2.php?a={$id}&c={$mas[1]}");
                    Logs::Save_File($village_id . " улучшение: {$id} до уровня " . ($village_data[$id]), $file_log . '.log');
                    break;
                }
            }
        }
    }

    //  РЕСУРСНЫЕ ПОЛЯ
    // определяем нужный уровень постройки
    $level = 10;
    if ( $village_data[19] < 18 && $village_data[38] < 18 )
    {
        $level = 8;
    }
    if ( $village_data[19] < 14 && $village_data[38] < 14 )
    {
        $level = 4;
    }
    if ( $village_data[19] < 10 && $village_data[38] < 10 )
    {
        $level = 0;
    }
    if ( !$level )
        continue;
    // идем по фиктивному сценарию
    for ($id = 1; $id < 19; $id++)
    {
        // если не достроено
        if ( $village_data[$id] < $level )
        {
            echo $village_id . " - " . $id . " ZAPROS<br>";
            // сверяемся с реальностью
            $page = get_page(HTTP_TRAVIAN . 'build.php?id=' . $id);
            preg_match('~<h1 class="titleInHeader">.*?Уровень ([0-9]+).*?</h1>~si', $page, $level_current);
            // если здание существует
            if ( isset($level_current[1]) )
            {
                // уровень меньше пытаемся построить
                $village_data[$id] = $level_current[1];
                if ( $level_current[1] < $level && preg_match('~onclick="window.location.href = \'dorf1.php\?a=' . $id . '(?:.+?)c=([0-9a-z]+)\'~si', $page, $mas) )
                {
                    $village_data[$id]++;
                    get_page(HTTP_TRAVIAN . "dorf1.php?a={$id}&c={$mas[1]}");
                    Logs::Save_File($village_id . " улучшение: {$id} до уровня " . ($village_data[$id]), $file_log . '.log');
                    break;
                }
            }
        }
    }
    //  МОНИТОРИНГ РЕСУРСОВ
    /*
    $resurce_full = check_full($page);
    if ( '' != $resurce_full )
    {
        Logs::Save_File($village_name . $resurce_full, $file_log . '.log');
    }
    */
    set_travian_data($village_id, $village_data);
}

///