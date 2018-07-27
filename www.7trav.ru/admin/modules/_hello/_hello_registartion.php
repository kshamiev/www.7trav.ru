<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль по умолчанию или стартовый модуль.
 * 
 * Модуль работающий при входе в систему.
 * Регистрация новых сотрудников.
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
 * Регистрация
 */
while ( 'registration' == $op && !$flag_access )
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
  if ( !$_POST['Name'] || !$_POST['Email'] || !$_POST['Keystring'] ) {
    $subj = 'обязательные поля не заполнены';
    break;
  }
  //	мыло
  else if ( !preg_match("([a-z]{1}[a-z|.|\\-|_|0-9]{1,20}@[a-z|\\-|0-9]{1,20}[.]{1}[a-z]{2,4})si", $_POST['Email']) ) {
    $subj = 'Поле "E-mail" не заполнено, либо заполнено не правильно';
    break;
  }
  else if ( 0 < DB::Get_Query_Cnt("SELECT COUNT(*) FROM Worker WHERE Email = " . DB::s($_POST['Email'])) ) {
    $subj = 'Этот E-mail уже используется и занят';
    break;
  }
  //	контрольная строка
  else if ( $Worker->Keystring != $_POST['Keystring'] ) {
    $subj = 'Контрольная строка ввдена неправильно';
    break;
  }
  $Passw = System_String::Random();
  $Worker->Name = $_POST['Email'];
  $Worker->Login = $_POST['Email'];
  $Worker->Email = $_POST['Email'];
  $Worker->Passw = md5($Passw);
  $Worker->Groups_ID = 3;
  $Worker->Save();
  //  Email обслуживающий группы (Администраторы)
  $Groups = new Groups(1);
  $Groups->Load_Prop('Email');
  /**
   * создание письма
   */
  $subject = "Регистрация на сайте " . $_SERVER['HTTP_HOST'];
  $message = "";
  $message.= "Уважаемый(ая) " . $Worker->Name . "\n\n";
  $message.= "Ваш email был использован для регистрации на сайте " . $_SERVER['HTTP_HOST'] . "\n";
  $message.= "Если это ошибка просто не отвечайте на него.\n\n";
  $message.= "Реквизиты:\n";
  $message.= "Логин:    " . $Worker->Login . "\n";
  $message.= "Пароль:   " . $Passw . "\n\n";
  /**
   * отправка письма
   */
  $mailer = new Mail_Mailer($Groups->Email, PROEKT_NAME);
  $mailer->AddAddress($_POST['Email'], $_POST['Email']);
  $mailer->AddBCC($Groups->Email, PROEKT_NAME);
  $mailer->Subject = $subject;
  $mailer->Body = $message;
  if ( !$mailer->Send() ) {
    Logs::Save_File('Ошибка регистрации: ' . $_POST['Email'], 'error_mail_registration.log');
  } else {
    $subj = 'Письмо с регистрацией отправлено на указанный Вами E-mail';
  }
  $mailer->ClearAddresses();
  $mailer->ClearAttachments();
  $Worker->Keystring = '';
  $Worker->Logout();
  Registry::Unset_Index('Worker');
  break;
}

/**
 * ВЫВОД
 */
$Tpl_Mod->Assign('flag_access', $flag_access);
$Tpl_Mod->Assign('ModSystem', $ModSystem);
$Tpl_Mod->Assign('mod_link', $mod_link);
return $Tpl_Mod->Fetch_System($ModSystem);
