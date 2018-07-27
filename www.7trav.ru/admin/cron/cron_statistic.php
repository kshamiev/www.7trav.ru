<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Формирование отчета статистики посещений сайта.
 * 
 * Информация формируется в csv файлы.
 * Далее все файлы упаковываются в архив.
 * Затем архив с пояснительной информацией отсылается на почту администратору.
 * Период статистики берется от текущего времени
 * Ее глубину во времени можно задать в константе PERIOD.
 * <ol>
 * <li>Хиты, хосты.
 * <li>Статистика разделов.
 * <li>Откуда приходят посетители.
 * <li>Разрешение монитора и глубина цвета.
 * </ol>
 *
 * @package Core
 * @subpackage Cron
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.06.2008
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

/**
 * НАСТРОЙКИ
 */
/**
 * Глубина статистики в месяцах.
 *
 * @var integer
 */
define('PERIOD', 1);
/**
 * Путь к файлам статистики
 *
 * @var string
 */
define('PATH_STAT', PATH_EXPORT . '/statistic');
if ( !is_dir(PATH_STAT) ) mkdir(PATH_STAT);
$date_beg = date('d.m.Y', mktime(0, 0, 0, date("m") - PERIOD, date("d"), date("Y")));
$date_end = date('d.m.Y');
$file_name = $date_beg . '_' . $date_end . '.csv';

/**
 * РАБОТА
 */
//  рейтинг ;сещаемости разделов
$sql = "
  SELECT
    sr.Name, COUNT(*)
  FROM Statistic_Host as sh
    INNER JOIN Statistic_Razdel as sr ON sr.Statistic_Host_ID = sh.ID
  WHERE
    sh.Date BETWEEN NOW() - INTERVAL 1 MONTH AND NOW()
    AND sh.Name = '" . HOST . "'
  GROUP BY
    1
  HAVING
    10 < COUNT(*)
  ORDER BY
    2 DESC
  ";
$fp = fopen(PATH_STAT . '/razdel_' . $file_name, 'w');
fputs($fp, 'запрошенные страницы;количество' . "\n");
$res = &DB::Query($sql);
while ( false != $row = $res->fetch_row() )
{
  fputs($fp, $row[0] . ';' . $row[1] . "\n");
}
$res->close();
fclose($fp);
//  сайты с которых пришли и их рейтинг
$sql = "
  SELECT
    Ref, COUNT(*)
  FROM Statistic_Host
  WHERE
    Date BETWEEN NOW() - INTERVAL 1 MONTH AND NOW()
    AND Name = '" . HOST . "'
  GROUP BY
    1
  HAVING
    10 < COUNT(*)
  ORDER BY
    2 DESC
  ";
$fp = fopen(PATH_STAT . '/referer_' . $file_name, 'w');
fputs($fp, 'откуда пришел;количество' . "\n");
$res = &DB::Query($sql);
while ( false != $row = $res->fetch_row() )
{
  fputs($fp, $row[0] . ';' . $row[1] . "\n");
}
$res->close();
fclose($fp);
//  разрешение экрана
$sql = "
  SELECT
    Width, Height, COUNT(*)
  FROM Statistic_Host
  WHERE
    Date BETWEEN NOW() - INTERVAL 1 MONTH AND NOW()
    AND Name = '" . HOST . "'
  GROUP BY
    Width, Height
  ORDER BY
    3 DESC
  ";
$fp = fopen(PATH_STAT . '/width_height_' . $file_name, 'w');
fputs($fp, 'разрешение экрана;количество' . "\n");
$res = &DB::Query($sql);
while ( false != $row = $res->fetch_row() )
{
  fputs($fp, $row[0] . 'x' . $row[1] . ';' . $row[2] . "\n");
}
$res->close();
fclose($fp);
//  глубина цвета
$sql = "
  SELECT
    Color, COUNT(*)
  FROM Statistic_Host
  WHERE
    Date BETWEEN NOW() - INTERVAL 1 MONTH AND NOW()
    AND Name = '" . HOST . "'
  GROUP BY
    Color
  ORDER BY
    2 DESC
  ";
$fp = fopen(PATH_STAT . '/color_' . $file_name, 'w');
fputs($fp, 'глубина цвета;количество' . "\n");
$res = &DB::Query($sql);
while ( false != $row = $res->fetch_row() )
{
  fputs($fp, $row[0] . ';' . $row[1] . "\n");
}
$res->close();
fclose($fp);
//  уникальные посещения (хосты)
$sql = "SELECT COUNT(DISTINCT Ip, OS) FROM Statistic_Host WHERE Date BETWEEN NOW() - INTERVAL 1 MONTH AND NOW() AND Name = '" . HOST . "'";
$host_count1 = DB::Get_Query_Cnt($sql);
//  не уникальные посещения (хосты)
$sql = "SELECT COUNT(*) FROM Statistic_Host WHERE Date BETWEEN NOW() - INTERVAL 1 MONTH AND NOW() AND Name = '" . HOST . "'";
$host_count2 = DB::Get_Query_Cnt($sql);
//  клики - просмотры страниц (хиты)
$sql = "SELECT COUNT(*) FROM Statistic_Razdel WHERE Date BETWEEN NOW() - INTERVAL 1 MONTH AND NOW() AND Name = '" . HOST . "'";
$hit_count = DB::Get_Query_Cnt($sql);
$fp = fopen(PATH_STAT . '/hosthit_' . $file_name . '.txt', 'w');
fputs($fp, 'уникальные хосты: ' . $host_count1 . "\n");
fputs($fp, 'не уникальные хосты: ' . $host_count2 . "\n");
fputs($fp, 'хиты:  ' . $hit_count . "\n");
fclose($fp);
//  писмьо
$subject = "Статистика сайта [" . HOST . "] за период с " . $date_beg . " по " . $date_end;
$message = $subject . "<br>";
$message.= "Уникальные хвосты: " . $host_count1 . "<br>";
$message.= "Не уникальные хвосты: " . $host_count2 . "<br>";
$message.= "Хиты: " . $hit_count . "<br>";
$message.= "В архиве: " . "<br>";
$message.= "Статистика посещений разделов: " . 'razdel_' . $file_name . "<br>";
$message.= "Статистика переходов: " . 'referer_' . $file_name . "<br>";
$message.= "Разрешение монитора: " . 'width_height_' . $file_name . "<br>";
$message.= "Глубина цвета: " . 'color_' . $file_name . "<br>";
//
$file_name = PATH_STAT . '/statistic_' . substr($file_name, 0, -3) . '.zip';
exec('zip -rjm ' . $file_name . ' ' . PATH_STAT);
//  отправка отчета статистики посещений
$Mailer = new Mail_Mailer(EMAIL_SEO, PROEKT_NAME);
$Mailer->AddReplyTo(EMAIL_SEO, PROEKT_NAME);
$Mailer->AddAddress(EMAIL_SEO, PROEKT_NAME);
$Mailer->Subject = $subject;
$Mailer->Body = $message;
$Mailer->isHTML(true);
$Mailer->AltBody = strip_tags(nl2br($message));
$Mailer->AddAttachment($file_name, 'statistic.zip');
if ( !$Mailer->Send() ) {
  Logs::Save_File('ошибка отправки статистики сайта', 'error_mail_send.log');
}
$Mailer->ClearAddresses();
$Mailer->ClearAttachments();

//  чистка устаревшей статистики
DB::Set_Query("DELETE FROM Statistic_Host WHERE Date < NOW() - INTERVAL 2 MONTH AND Name = '" . HOST . "'");
$sql = "
DELETE
  sr
FROM Statistic_Host as sh 
  RIGHT JOIN Statistic_Razdel as sr ON sh.ID = sr.Statistic_Host_ID
WHERE
  sh.ID IS NULL
";
DB::Set_Query($sql);

return 0;
//  exec('pkzip25 -extract '.$file_name.' download/ -over=all -nofix',$rez,$kod);
//  'pkzip25 -extract /var/www/vhosts/eleks.ru/httpdocs/site_admin/import/adrotator.zip /var/www/vhosts/eleks.ru/httpdocs/site_admin/import/ -over=all -nofix'
//   unzip -j /var/www/vhosts/eleks.ru/httpdocs/site_admin/import/adrotator.zip -d /var/www/vhosts/eleks.ru/httpdocs/site_admin/import/