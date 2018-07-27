<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * На всякий случай указываем настройки для дополнительного (внешнего) SMTP сервера.
 */
/**
 * 
 * @var string
 */
define('SMTP_MODE', 'disabled');  // enabled or disabled (включен или выключен)
/**
 * 
 * @var bolean or string
 */
define('SMTP_HOST', false);
/**
 * 
 * @var bolean or integer
 */
define('SMTP_PORT', false);
/**
 * 
 * @var bolean or string
 */
define('SMTP_USERNAME', false);
/**
 * 
 * @var bolean or string
 */
define('SMTP_PASSWORD', false);

/**
 * PHPMailer - PHP email transport class
 * NOTE: Designed for use with PHP version 5 and up
 * 
 * @author Andy Prevost
 * @package Core
 * @subpackage Mail
 * @copyright 2004 - 2008 Andy Prevost
 * @link http://php.russofile.ru/ru/translate/mail/phpmailer
 */
class Mail_Mailer extends Mail_PHPMailer
{
  public $priority = 3;
  public $to_name;
  public $to_email;
  public $From = null;
  public $FromName = null;
  public $Sender = null;
  /**
   * Конструктор
   *
   */
  function __construct($from_email, $from_name)
  {
    // Берем из файла config.php массив $site
    if ( 'enabled' == SMTP_MODE )
    {
      $this->Host = SMTP_HOST;
      $this->Port = SMTP_PORT;
      if ( SMTP_USERNAME )
      {
        $this->SMTPAuth  = true;
        $this->Username  = SMTP_USERNAME;
        $this->Password  =  SMTP_PASSWORD;
      }
      $this->Mailer = "smtp";
    }

    $this->From = $from_email;
    $this-> FromName = $from_name;
    $this->Sender = $from_email;
    /*
    if ( !$this->From )
    {
    $this->From = FROM_EMAIL;
    }
    if ( !$this->FromName )
    {
    $this->FromName = FROM_NAME;
    }
    if ( !$this->Sender )
    {
    $this->Sender = FROM_EMAIL;
    }
    */
    $this->Priority = $this->priority;
  }
}