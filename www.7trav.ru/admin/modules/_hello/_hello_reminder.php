<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль по умолчанию или стартовый модуль.
 * 
 * Модуль работающий при входе в систему.
 * Напоминание реквизитов доступа.
 * 
 * @package Core
 * @subpackage System
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $ModSystem;
/* @var $ModSystem ModSystem */
global $op;
global $mod_link;
global $Worker;
global $Logs;

//  проверка блокировки

//  очистка устаревших блокировок
DB::Set_Query("DELETE FROM Cache_AccessIp WHERE Date < NOW() - INTERVAL 600 SECOND");

//  флаг блокировки
$ip = $_SERVER["REMOTE_ADDR"];
$flag_access = DB::Get_Query_Cnt("SELECT COUNT(*) FROM Cache_AccessIp WHERE Ip = '{$ip}' AND 3 <= Cnt");

//  Шаблон
$Tpl_Mod = new Templates;

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * забыли пароль
 */
while ( 'reminder' == $op && !$flag_access )
{
  /**
   * безопасность, если много и часто запрашивают
   */
  if ( !$Worker->Ip ) {
    $Worker->Ip = $ip;
    DB::Set_Query("INSERT INTO Cache_AccessIp (Ip) VALUES ('{$ip}')");
  }
  else {
    DB::Set_Query("UPDATE Cache_AccessIp SET Cnt=Cnt+1 WHERE Ip = '{$ip}'");
  }
  //  Стандартные проверки
  if ( !$_POST['Email'] ) {
    $subj = "! E-mail не указан !";
    break;
  }
  else if ( $Worker->Keystring != $_POST['Keystring'] ) {
    $sys_subj = 'Контрольная строка введена неправильно';
    $Worker->Keystring = '';
    break;
  }
  else if ( !$Login = DB::get_query_cnt("SELECT Login FROM Worker WHERE Email = " . DB::S($_POST['Email'])) ) {
    $subj="! Учетная запись с указанным электронным адресом не найдена !";
    break;
  }
  $Passw = System_String::Random();
  $sql = "UPDATE Worker SET Passw = '" . md5($Passw) . "' WHERE Email = " . DB::S($_POST['Email']);
  DB::Set_Query($sql);
  //
  $Name = DB::Get_Query_Cnt("SELECT Name FROM Worker WHERE Email = " . DB::S($_POST['Email']));
  //  Email обслуживающий группы (Администраторы)
  $Groups = new Groups(1);
  $Groups->Load_Prop('Email');
  /**
   * создание письма
   */
  $subject = "Напоминание реквизитов доступа от административной части сайта " . $_SERVER['HTTP_HOST'];
  $message = "";
  $message.= "Уважаемый(ая) " . $Name . "\n\n";
  $message.= "Ваши реквизиты:\n";
  $message.= "Логин:    " . $Login . "\n";
  $message.= "Пароль:   " . $Passw . "\n\n";
  /**
   * отправка письма
   */
  $mailer = new Mail_Mailer($Groups->Email, PROEKT_NAME);
  $mailer->AddAddress($_POST['Email'], $Name);
  $mailer->AddBCC($Groups->Email, PROEKT_NAME);
  $mailer->Subject = $subject;
  $mailer->Body = $message;
  if ( !$mailer->Send() ) {
    Logs::Save_File('Ошибка напоминания реквизитов доступа к админке: ' . $_POST['Email'], 'error_mail_reminder.log');
  } else {
    $subj = 'Письмо с реквизитами отправлены на указанный Вами E-mail';
  }
  $mailer->ClearAddresses();
  $mailer->ClearAttachments();
  $Worker->Keystring = '';
  break;
}

/**
 * ВЫВОД
 */
$Tpl_Mod->Assign('flag_access', $flag_access);
$Tpl_Mod->Assign('Worker', $Worker);
$Tpl_Mod->Assign('ModSystem', $ModSystem);
$Tpl_Mod->Assign('mod_link', $mod_link);
return $Tpl_Mod->Fetch_System($ModSystem);
