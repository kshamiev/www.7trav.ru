<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль реализует календарь для удобства ввода дат. 
 *
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @package Core
 * @subpackage Core
 * @version 15.11.2009
 */

/**
 * Подключение конфигурации
 */
chdir(dirname(__FILE__));
require_once '../config.php';

global $g;
global $m;
global $d;
global $obj;

extract($_REQUEST);

header("Content-Type: text/html; charset=utf-8");
?>
<html>
<head>
<title>Календарь</title>
<meta name="author" content="Шамиев Константин Валерьевич 2007 год">
<meta http-equiv="Content-Type" content="text/html; charset=utr-8">
<style>
body			{
				font-family: Verdana Cyr, Verdana, Sans-Serif, Arial Cyr, Arial; font-size: 10px; color: #000000; background-color: #FFFFFF;
				margin: 0px 0px 0px 0px; overflow-x: auto; overflow-y: auto;
				}
tr				{ background-color: #FFFFFF; }
td				{ font-family: Verdana Cyr, Verdana, Sans-Serif, Arial Cyr, Arial; font-size: 10px; color: #000000; }

a				{ color:#093F6E; font-size:10px; font-family: Verdana Cyr, Verdana, Sans-Serif, Arial Cyr, Arial; text-decoration: none; font-weight: bold; }
a:link		{ color:#093F6E; font-size:10px; font-family: Verdana Cyr, Verdana, Sans-Serif, Arial Cyr, Arial; text-decoration: none; font-weight: bold; }
a:visited	{ color:#093F6E; font-size:10px; font-family: Verdana Cyr, Verdana, Sans-Serif, Arial Cyr, Arial; text-decoration: none; font-weight: bold; }
a:active		{ color:#B22917; font-size:10px; font-family: Verdana Cyr, Verdana, Sans-Serif, Arial Cyr, Arial; text-decoration: underline;  font-weight: bold; }
a:hover		{ color:#B22917; font-size:10px; font-family: Verdana Cyr, Verdana, Sans-Serif, Arial Cyr, Arial; text-decoration: underline; font-weight: bold; }
</style>
</head>
<body>
<? if ( isset($g) && isset($m) && isset($d) ) { ?>
<script type="text/javascript" language="JavaScript">
//<!--
//	завершение календаря
<?
if ( 1 == strlen($g) ) $g = '0' . $g;
if ( 1 == strlen($m) ) $m = '0' . $m;
if ( 1 == strlen($d) ) $d = '0' . $d;
?>
//  str = '<?=$obj?>';
//  mas = str.split('.');
window.opener.document.getElementById('<?=$obj?>').focus();
window.opener.document.getElementById('<?=$obj?>').value='<?=$g.'.'.$m.'.'.$d?> ';
window.close();
//-->
</script>
<? } ?>
<?
$month_mas=array(1=>'Ян',2=>'Фв',3=>'Мт',4=>'Ап',5=>'Ма',6=>'Ин',7=>'Ил',8=>'Ав',9=>'Сн',10=>'Ок',11=>'Но',12=>'Де');
if ( !isset($g) )
	{ $g=date('Y'); $m=date('m'); $d=date('d'); }
else
	{
	if ( !isset($m) ) $m=1;
	if ( !isset($d) ) $d=1;
	}
$weeks = System_Functional::Calendar($g,$m);

print '<table cellspacing="1" cellpadding="3" border="1">';
//	годы
print '<tr><td colspan="7" align="center">
		<a href="calendar.php?obj='.$obj.'&g='.($g-2).'">'.($g-2).'</a> 
		<a href="calendar.php?obj='.$obj.'&g='.($g-1).'">'.($g-1).'</a> 
		<a href="calendar.php?obj='.$obj.'&g='.$g.'"><font color="#ff0000">'.$g.'</font></a> 
		<a href="calendar.php?obj='.$obj.'&g='.($g+1).'">'.($g+1).'</a> 
		<a href="calendar.php?obj='.$obj.'&g='.($g+2).'">'.($g+2).'</a> 
		</td></tr>';
//	месяцы
print '<tr><td colspan="7">';
foreach ($month_mas as $key=>$month)
	{
	if ( $m==$key )
		print '<a href="calendar.php?obj='.$obj.'&g='.$g.'&m='.$key.'"><font color="#ff0000">'.$month.'</font></a>&nbsp;';
	else
		print '<a href="calendar.php?obj='.$obj.'&g='.$g.'&m='.$key.'">'.$month.'</a>&nbsp;';
	}
print '</td></tr>';
//	дни
print '<tr><td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td>Сб</td><td>Вс</td></tr>';
foreach ($weeks as $week)
	{
	print '<tr>';
	foreach ($week as $w)
		{
		if ( $w )
			{
			if ( $d==$w )
				print '<td><a href="calendar.php?obj='.$obj.'&g='.$g.'&m='.$m.'&d='.$w.'"><font color="#ff0000">'.$w.'</font></a></td>';
			else
				print '<td><a href="calendar.php?obj='.$obj.'&g='.$g.'&m='.$m.'&d='.$w.'">'.$w.'</a></td>';
			}
		else
			{
			print '<td>&nbsp;</td>';
			}
		}
	print '</tr>';
	}
print '</table>';
?>
</body>
</html>