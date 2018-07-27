<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Cms
 */

/**
 * Шаблонный класс для работы объектами системы типа простой объект
 * 
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @package Cms
 * @subpackage Orders
 * @version 10.06.2009
 */
class Orders extends Obj_Item
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Клиент
   *
   * @var integer
   */
  protected $Client_ID;
  /**
   * Метро
   *
   * @var integer
   */
  protected $Metro_ID;
  /**
   * Названание
   *
   * @var string
   */
  protected $Name;
  /**
   * Адрес доставки
   *
   * @var string
   */
  protected $Address;
  /**
   * Комментарий
   *
   * @var string
   */
  protected $Comment;
  /**
   * Статус заказа
   *
   * @var string
   */
  protected $Status;
  /**
   * Дата
   *
   * @var string
   */
  protected $Date;
  //  [END] Prop
  /**
   * Имя таблицы хранящей объекты данного класса
   *
   * @var string
   */
  private $_Tbl_Name = 'Orders';

  //  [BEG] Link
  /**
   * Товары заказа
   *
   * @var Orders_Goods
   */
  protected $Orders_Goods;
  //  [END] Link

  /**
   * Конструткор класса
   * Инициализация идентификатора объекта
   * 0 - новый объект, не сохраненый в БД
   * Если $flag_load установлен в true происходит загрузка свойств объекта из БД
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
  public function Save_Order()
  {
    $subj = 'ok';
    $Client = Client::Factory();
    //  проверки и регистрация нового клиента
    if ( 2 == $Client->Groups_ID ) {
      $subj = $Client->Save_Registration();
    } else if ( !$_POST['Address'] ) {
      $subj = 'Одно из обязательных полей не заполнено';
    }
    if ( 'ok' != $subj ) {
      $Client->Keystring = '';
      return $subj;
    }
    $Basket = $Client->Basket;
    $metro_list = Metro::Get_Metro_All();
    //  создание заказа
    $Orders = new Orders();
    $Orders->__set('Client_ID', $Client->ID);
    $Orders->__set('Metro_ID', $_POST['Metro_ID']);
    $Orders->__set('Address', $_POST['Address']);
    $Orders->__set('Comment', $_POST['Comment']);
    $Orders->Save();
    $mas = explode('.', HOST); 
    $Orders->__set('Name', $mas[1] . '-' . $Orders->ID);
    $Orders->Save();
    //  Переброс корзины в заказ
    $Orders->Save_Basket($Basket);
    //  ПИСЬМО КЛИЕНТУ
    $subject = "Оформление заказа на сайте " . HOST;
    $message_html = "
    <html>
    <head>
    <title>" . PROEKT_NAME . "</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
    </head>
    <body>
    ";
    $message_html.= "Уважаемый(ая) " . $Client->Name . "<br>";
    $message_html.= '
    На сайте ' . HOST . ' Вами был оформлен следующий заказ:</b>
    <table cellspacing="0" cellpadding="0" border="1">
    <tr>
      <td colspan="4" align="center">Заказ № ' . $Orders->ID . '</td>
    </tr>
    <tr bgcolor="#c0c0c0">
      <td>&nbsp;&nbsp;Наименование&nbsp;&nbsp;</td>
      <td>&nbsp;&nbsp;Цена&nbsp;&nbsp;</td>
      <td>&nbsp;&nbsp;Количество&nbsp;&nbsp;</td>
      <td>&nbsp;&nbsp;Сумма&nbsp;&nbsp;</td>
    </tr>
    ';
    foreach ($Basket->Basket as $goods_id => $Goods)
    {
      $message_html.= '
      <tr>
        <td nowrap>&nbsp;&nbsp;' . $Goods->Name . '&nbsp;&nbsp;</td>
        <td>&nbsp;&nbsp;' . $Goods->Price . '&nbsp;&nbsp;</td>
        <td>&nbsp;&nbsp;' . $Goods->Cnt . '&nbsp;&nbsp;</td>
        <td>&nbsp;&nbsp;' . ( $Goods->Price * $Goods->Cnt ) . '&nbsp;&nbsp;</td>
      </tr>
      ';
    }
    $message_html.= '
    <tr>
      <td colspan="4" align="right">Итого: '. $Basket->Summa . ' руб.&nbsp;&nbsp;</td>
    </tr>
    </table>
    ';
    $message_html.= 'При необходимости с Вами свяжутся для уточнения выполнения заказа' . "<br>";
    $message_html_client = $message_html . "
    </body>
    </html>    
    ";
    //
    $Mailer = new Mail_Mailer(EMAIL_ORDER, PROEKT_NAME);
    $Mailer->AddReplyTo(EMAIL_ORDER, PROEKT_NAME);
    $Mailer->AddAddress($Client->Email, $Client->Name);
    $Mailer->Subject = $subject;
    $Mailer->Body = $message_html;
    $Mailer->isHTML(true);
    $Mailer->AltBody = strip_tags(nl2br($message_html_client));
    if ( !$Mailer->Send() ) {
      Logs::Save_File('ошибка отправки письма оформления заказа клиенту', 'error_mail_send.log');
    }
    $Mailer->ClearAddresses();
    $Mailer->ClearAttachments();

    //  ПИСЬМО МАГАЗИНУ
    $metro = isset($metro_list[$_POST['Metro_ID']]) ? $metro_list[$_POST['Metro_ID']] : 'нет' ;
    $message_html.="
    <br><b>Телефон клиента:</b><br>
    {$Client->Tel}
    <br><b>Станция метро:</b><br>
    {$metro}
    <br><b>Адрес доставки:</b><br>
    " . nl2br($_POST['Address']) . "
    <br><b>Комментарий доставки:</b><br>
    " . nl2br($_POST['Comment']) . "
    </body>
    </html>    
    ";
    //
    $Mailer = new Mail_Mailer($Client->Email, $Client->Name);
    $Mailer->AddReplyTo($Client->Email, $Client->Name);
    $Mailer->AddAddress(EMAIL_ORDER, PROEKT_NAME);
    $Mailer->Subject = $subject;
    $Mailer->Body = $message_html;
    $Mailer->isHTML(true);
    $Mailer->AltBody = strip_tags(nl2br($message_html));
    if ( !$Mailer->Send() ) {
      Logs::Save_File('ошибка отправки письма оформления заказа магазину', 'error_mail_send.log');
    }
    $Mailer->ClearAddresses();
    $Mailer->ClearAttachments();

    //  очистка корзины
    $Basket->Action_Clear();
    //  завершение
    $this->ID = 0;
    $Client->Keystring = '';
    return $subj;
  }
  /**
   * Перенос корзины в заказ
   *
   * @param Basket $Basket
   */
  public function Save_Basket(Basket $Basket)
  {
    foreach ($Basket->Basket as $id => $Goods)
    {
      $sql = "INSERT INTO Orders_Goods (
        Orders_ID,
        Goods_ID,
        Name,
        Price,
        PriceBase,
        Cnt
      )
      VALUES (
        " . $this->ID . ",
        " . $Goods->ID . ",
        " . s($Goods->Name) . ",
        " . f($Goods->Price) . ",
        " . f($Goods->PriceBase) . ",
        " . $Goods->Cnt . "
      )";
      DB::Set_Query($sql);
    }
  }
  /**
   * Создание и/или получение объекта
   * Работает через Регистр
   *
   * @param itneger $id
   * @return Orders
   */
  public static function Factory($id = 0, $flag_load = false)
  {
    $index = __CLASS__ . (0 < $id ? '_' . $id : '');
    if ( Registry::Is_Exists($index) )
    {
      $result = Registry::Get($index);
    }
    else
    {
      $result = new self($id, $flag_load);
      Registry::Set($index, $result);
    }
    return $result;
  }
}