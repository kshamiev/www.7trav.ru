<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Cms
 */

/**
 * Обратная связь
 * 
 * Реализует основной функционал работы с объектами:
 * <ol>
 * <li>Создание, Изменение, Сохранение, Удаление
 * <li>Получение связанных дочерних объектов по определенному типу связи
 * <li>Получение не связанных дочерних объектов по определенному типу связи
 * <li>Создание связи между объектами по определенному типу связи
 * <li>Удаление связи между объектами по определенному типу связи
 * <li>Работа с кешем объектов. Проверка на существование объекта
 * <li>Возможность работы через регистр
 * </ol>
 * 
 * @package Cms
 * @subpackage Feedback
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 20.03.2010
 * @see какие классы смотреить через запятую
 * @link какие скрипты смотреть через запятую
 */
class Feedback extends Obj_Item
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Тема сообщения
   *
   * @var string
   */
  protected $Name;
  /**
   * Сообщение
   *
   * @var string
   */
  protected $Message;
  /**
   * Фио
   *
   * @var string
   */
  protected $Fio;
  /**
   * Email
   *
   * @var string
   */
  protected $Email;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Feedback';
  //  [BEG] Link
  //  [END] Link
  /**
   * Конструткор класса
   * Инициализация объекта
   * $id == 0 - новый объект, не сохраненый в БД
   * Если $flag_load установлен в true и 0 < $id происходит загрузка свойств объекта из БД
   *
   * @param integer $id - идентификатор объекта
   * @param bolean $flag_load - флаг загрузки объекта
   */
  public function __construct($id = 0, $flag_load = false)
  {
    if ( 0 < $id ) {
      $this->ID = $id;
    }
    if ( 0 < $id && $flag_load ) {
      $this->Load();
    }
  }
  /**
   * Получение объектной таблицы класса
   * 
   * @return string - имя обрабатываемой таблицы класса
   */
  public function Get_Tbl_Name()
  {
    return $this->_Tbl_Name;
  }
  public function Save_Feedback()
  {
    $Client = Client::Factory();
    if ( $Client->Keystring != $_POST['Keystring'] ) {
      $subj = 'Контрольная строка введена неправильно';
    } else {
      $this->ID = 0;
      $this->__set('Name', $_POST['Subject']);
      $this->__set('Fio', $_POST['Fio']);
      $this->__set('Email', $_POST['Email']);
      $this->__set('Message', $_POST['Message']);
      $this->Save();
      //  письмо
      $subject = $_POST['Name'];
      $message = "";
      $message .= "Отправитель: " . $_POST['Fio'] . "\n\n";
      $message .= "Сообщение: \n" . $_POST['Message'] . "\n\n";
      //
      $Mailer = new Mail_Mailer($_POST['Email'], $_POST['Fio']);
      $Mailer->AddReplyTo($_POST['Email'], $_POST['Fio']);
      $Mailer->AddAddress(EMAIL_INFO, PROEKT_NAME);
      $Mailer->Subject = $subject;
      $Mailer->Body = $message;
      if ( !$Mailer->Send() ) {
        Logs::Save_File('ошибка отправки регистрационного письма', 'error_mail_send.log');
      }
      $Mailer->ClearAddresses();
      $Mailer->ClearAttachments();
      //
      $subj = "ok";
    }
    return $subj;
  }
  /**
   * Инициализация и/или получение клиента
   * Работает через Регистр
   * Индекс класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id - идентификатор объекта
   * @param bolean $flag_load - флаг загрузки объекта
   * @return Feedback
   */
  public static function Factory($id = 0, $flag_load = false)
  {
    $index = __CLASS__ . (0 < $id ? '_' . $id : '');
    if ( !$result = Registry::Get($index) ) {
      $result = new self($id, $flag_load);
      Registry::Set($index, $result);
    }
    return $result;
  }
}