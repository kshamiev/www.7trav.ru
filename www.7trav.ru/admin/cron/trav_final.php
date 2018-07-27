<?php
require __DIR__ . "/trav.php";

$village_mas = array();

$village_mas['sota6']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=126996';
$village_mas['sota6']['rpl'] = 10;
$village_mas['sota6']['zod'] = scenario_start();

$village_mas['sota5']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=124407';
$village_mas['sota5']['rpl'] = 10;
$village_mas['sota5']['zod'] = scenario_start();

$village_mas['sota4']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=121799';
$village_mas['sota4']['rpl'] = 10;
$village_mas['sota4']['zod'] = scenario_final();

//$village_mas['sota3']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=118859';
//$village_mas['sota3']['rpl'] = 0;
//$village_mas['sota3']['zod'] = [];

//$village_mas['sota2']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=113609';
//$village_mas['sota2']['rpl'] = 0;
//$village_mas['sota2']['zod'] = scenario_final();

//$village_mas['sota1']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=105231';
//$village_mas['sota1']['rpl'] = 0;
//$village_mas['sota1']['zod'] = scenario_final();

//$village_mas['15']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=95801';
//$village_mas['15']['rpl'] = 0;
//$village_mas['15']['zod'] = scenario_final();

//$village_mas['root']['url'] = HTTP_TRAVIAN . 'dorf1.php?newdid=15564';
//$village_mas['root']['rpl'] = 0;
//$village_mas['root']['zod'] = [];

//  РАБОТА
//  СТРОИТЕЛЬСТВО
foreach ($village_mas as $village_name => $village)
{
    //  заходим в деревню  //  страница полей
    $page = get_page($village['url']);
    //  ЗДАНИЯ
    foreach ($village['zod'] as $i => $level)
    {
        $page = get_page(HTTP_TRAVIAN . 'build.php?id=' . $i);
        preg_match('~Уровень ([0-9]+)~si', $page, $mas);
        $level_current = $mas[1];
        if ( $level_current < $level )
        {
            if ( preg_match('~onclick="window.location.href = \'dorf2.php\?a=' . $i . '(?:.+?)c=([0-9a-z]+)\'~si', $page, $mas) )
            {
                get_page(HTTP_TRAVIAN . "dorf2.php?a={$i}&c={$mas[1]}");
                Logs::Save_File($village_name . " улучшение: {$i} до уровня " . ($level_current + 1), $file_log . '.log');
                break;
            }
        }
    }
    //  РЕСУРСНЫЕ ПОЛЯ
	if ( !$village['rpl'] )
		break;
    for ($i = 1; $i < 19; $i++)
    {
        $page = get_page(HTTP_TRAVIAN . 'build.php?id=' . $i);
        preg_match('~Уровень ([0-9]+)~si', $page, $mas);
        $level = $mas[1];
        if ( $level < $village['rpl'] )
        {
            if ( preg_match('~onclick="window.location.href = \'dorf1.php\?a=' . $i . '(?:.+?)c=([0-9a-z]+)\'~si', $page, $mas) )
            {
                get_page(HTTP_TRAVIAN . "dorf1.php?a={$i}&c={$mas[1]}");
                Logs::Save_File($village_name . " улучшение: {$i} до уровня " . ($level + 1), $file_log . '.log');
                break;
            }
            else
            {
                //log_file("Не возможно улучшить: {$name}", $file_log);
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
}

///