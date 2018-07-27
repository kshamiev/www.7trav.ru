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
 * @subpackage Client
 * @version 12.03.2009
 */
class Client extends Obj_Item
{
  /**
   * Идентификатор
   *
   * @var integer
   */
  protected $ID = 0;
  //  [BEG] Prop
  /**
   * Группа
   *
   * @var integer
   */
  protected $Groups_ID;
  /**
   * Станция Метро
   *
   * @var integer
   */
  protected $Metro_ID;
  /**
   * Имя
   *
   * @var string
   */
  protected $Name;
  /**
   * Логин
   *
   * @var string
   */
  protected $Login;
  /**
   * Пароль
   *
   * @var string
   */
  protected $Passw;
  /**
   * Статус доступа
   *
   * @var string
   */
  protected $Status;
  /**
   * E-mail
   *
   * @var string
   */
  protected $Email;
  /**
   * Телефон
   *
   * @var string
   */
  protected $Tel;
  /**
   * Icq
   *
   * @var integer
   */
  protected $Icq;
  /**
   * Условие клиента
   *
   * @var integer
   */
  protected $IsClient;
  /**
   * Адресс
   *
   * @var string
   */
  protected $Address;
  /**
   * Аватар
   *
   * @var string
   */
  protected $Avatar;
  /**
   * Статус присутствия
   *
   * @var string
   */
  protected $StatOnline;
  /**
   * Дата посещения
   *
   * @var string
   */
  protected $DateOnline;
  /**
   * Дата регистрации
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
  private $_Tbl_Name = 'Client';
  //  [BEG] Link
  /**
   * Заказы
   *
   * @var Orders
   */
  protected $Orders;
  /**
   * Корзина
   *
   * @var Basket
   */
  protected $Basket;
  /**
   * Статистика посещений
   *
   * @var Statistic_Host
   */
  protected $Statistic_Host;
  /**
   * Статистика скаченных клиентами файлов
   *
   * @var Statistic_DuwnLoadFileClient
   */
  protected $Statistic_DuwnLoadFileClient;
  //  [END] Link
  /**
   * Условия пользователя.
   * Права по горизотали ( на строки )
   *
   * @var array('Worker_ID'=>23, ...)
   */
  private $_Condition = array();
  /**
   * Время прошедшее в секундах от начала эпохи Unix (TIMESTAMP)
   *
   * @var integer
   */
  private $_Timeout = 0;
  /**
   * Количество кликов разрешенных в единицу времени (секунда)
   *
   * @var integer
   */
  private $_CntClick = 0;
  /**
   * Идентификатор статистки посещения
   *
   * @var integer
   */
  public $Statistic_Host_ID;
  /**
   * KCAPTCHA
   *
   * @var string
   */
  public $Keystring;
  /**
   * Ip адрес клиента в текущей сессиии
   *
   * @var string
   */
  public $Ip;
  /**
   * Поисковый запрос пользователя
   *
   * @var string
   */
  public $Search;
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
   * Авторизация пользователя по логину и паролю
   * Инициализация данных пользователоя
   * Инициализация его прав в свойство $Condition
   * Возвращает пустаю строку в случае успеха
   * Либо сообщение об ошибке в случае провала
   *
   * @return string
   */
  public function Init_Login()
  {
    $row = DB::Get_Query_Row("SELECT * FROM Client WHERE Login = " . s($_POST['Prop']['Login']));
    if ( !isset($row['ID']) ) {
      $subj = "! Не зарегистрирован !";
    } else if ( $row['Passw'] != md5($_POST['Prop']['Passw']) ) {
      $subj = "! Неправильный пароль !";
    } else if ( 'открыт' != $row['Status'] ) {
      $subj = "! Аккаунт заблокирован !";
      //    } else if ( 'да' == $row['StatOnline'] ) {
    //    $subj = "! Этот аккаунт уже используется в системе !";
    } else if ( !$row['Groups_ID'] ) {
      $subj = "! Аккаунт не входит ни в одну группу !";
    } else if ( 'открыта' != DB::Get_Query_Cnt("SELECT Status FROM Groups WHERE ID = " . $row['Groups_ID']) ) {
      $subj = "! Группа в которую входит Ваш аккаунт заблокирована !";
    } else {
      /**
       * Загрузка клиента
       */
      $this->Load($row);
      /**
       * Инициализация условий клиента
       */
      //  условие клиента
      if ( $row['IsClient'] ) {
        $this->_Condition['Client_ID'] = $this->ID;
      }
      unset($row['Client_ID']);
      //  условие группы
      if ( 1 < $row['Groups_ID'] ) {
        $this->_Condition['Groups_ID'] = $row['Groups_ID'];
      }
      unset($row['Groups_ID']);
      //  дополнительные условия
      foreach ($row as $field => $value) {
        if ( '_ID' == substr($field, -3) && $value ) {
          $this->_Condition[$field] = $value;
        }
      }
      /**
       * Вкусности клиента
       */
      //  инициализация корзины
      $this->Get_Basket();
      //  $this->Basket->Init_Client($row['ID']);
      //  инициализируем сессию и куку
      $this->_Timeout = 0;
      $this->Set_Timeout();
      setcookie('client_id', $thid->ID, time() + COOKIE_TIME, '/');
      //  выгружаем условия клиента
      SC::$ConditionUser = $this->_Condition;
      //  логирование входа и выхода
      Logs::Save_File($this->_Tbl_Name . ' ; ' . $_POST['Prop']['Login'] . ' ; ' . $_SERVER["REMOTE_ADDR"], 'access_login.log');
      return 'ok';
    }
    Logs::Save_File($this->_Tbl_Name . ' ; ' . $_POST['Prop']['Login'] . ' ; ' . $_SERVER["REMOTE_ADDR"] . ' ; ' . $subj, 'access_denied.log');
    return $subj;
  }
  public function Init_Logout()
  {
    setcookie("client_id", 0, time() - COOKIE_TIME, '/');
    session_unset();
    session_destroy();
    header('Location: ' . URL);
    exit();
  }
  /**
   * Проверка на существование объекта
   *
   * @param $id - идентификатор объекта
   */
  public static function Is_Exists($id)
  {
    return DB::Get_Query_Cnt('SELECT COUNT(*) FROM ' . __CLASS__ . ' WHERE ID = ' . intval($id));
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
  /**
   * Получение покупательской корзины
   *
   * @return Basket
   */
  public function Get_Basket()
  {
    if ( !$this->Basket instanceof Basket ) {
      $this->Basket = Basket::Factory();
    }
    if ( 0 < $this->ID && $this->ID != $this->Basket->ID ) {
      $this->Basket->Init_Client();
    }
    return $this->Basket;
  }
  /**
   * Получение условий клиента
   * 
   * @return array - массив условий пользоваетя (ключ свойство, значение условие)
   */
  public function Get_Condition()
  {
    return $this->_Condition;
  }
  /**
   * Получение времени бездействия сотрудника в секундах
   * Время прошедшее с момента последнего обновления (запроса) страницы
   *
   * @return integer (timestamp)
   */
  public function Get_Timeout()
  {
    return time() - $this->_Timeout;
    //  return DB::Get_Query_Cnt("SELECT UNIX_TIMESTAMP() - UNIX_TIMESTAMP(DateOnline) FROM {$this->_Tbl_Name} WHERE ID = " . $this->ID);
  }
  /**
   * инициализация онлайн статуса
   * Обновляем дату присутсвия на текущую
   *
   */
  public function Set_Timeout()
  {
    $t = time();
    $p = $t - $this->_Timeout;
    $this->_Timeout = $t;
    if ( 0 == $p ) {
      $this->_CntClick++;
      if ( 4 < $this->_CntClick ) {
        die('слишком частые запросы, не спешите');
      }
    } else if ( 60 < $p ) {
      $this->_CntClick = 0;
      file_put_contents(PATH_SITE . '/session/client/' . $this->ID, $this->_Timeout);
    }
  }
  /**
   * Напоминание реквизитов доступа на сайт
   * 
   * @return string - сообщение результата операции
   */
  public function Action_Reminder()
  {
    $subj = 'ok';
    if ( !$_POST['Email'] ) {
      $subj = 'E-mail не указан';
    } else if ( $this->Keystring != $_POST['Keystring'] ) {
      $subj = 'Контрольная строка ввдена неправильно';
    } else if ( !$id = DB::Get_Query_Cnt("SELECT ID FROM Client WHERE Email = " . DB::S($_POST['Email'])) ) {
      $subj = 'Указанный E-mail отсутсвует';
    } else {
      $Passw = System_String::Random();
      //  напоминание реквизитов доступа
      $ClientReminder = new Client($id, true);
      $ClientReminder->__set('Passw', md5($Passw));
      $ClientReminder->Save();
      //  письмо
      $subject = "Напоминание реквизитов доступа на сайте " . HOST;
      $message = "";
      $message .= "Уважаемый(ая) " . $ClientReminder->Name . "\n\n";
      $message .= "Ваши реквизиты:\n";
      $message .= "Логин:    " . $ClientReminder->Login . "\n";
      $message .= "Пароль:   " . $Passw . "\n\n";
      //
      $Mailer = new Mail_Mailer(EMAIL_INFO, PROEKT_NAME);
      $Mailer->AddReplyTo(EMAIL_INFO, PROEKT_NAME);
      $Mailer->AddAddress($_POST['Email'], $ClientReminder->Name);
      //  $Mailer->AddBCC(EMAIL_INFO, PROEKT_NAME);
      $Mailer->Subject = $subject;
      $Mailer->Body = $message;
      if ( !$Mailer->Send() ) {
        Logs::Save_File('ошибка отправки напоминания реквизитов доступа к сайту', 'error_mail_send.log');
      }
      $Mailer->ClearAddresses();
      $Mailer->ClearAttachments();
    }
    return $subj;
  }
  /**
   * Регистрация нового клиента
   * 
   * @return string - сообщение результата операции
   */
  public function Save_Registration()
  {
    $subj = 'ok';
    if ( !$_POST['Name'] || !$_POST['Email'] || !$_POST['Tel'] || !$_POST['Address'] || !$_POST['Keystring'] ) {
      $subj = 'Одно из обязательных полей не заполнено';
    } else if ( $this->Keystring != $_POST['Keystring'] ) {
      $subj = 'Контрольная строка ввдена неправильно';
    } else if ( !preg_match("([a-z]{1}[a-z|.|\\-|_|0-9]{1,20}@[a-z|\\-|0-9|.]{1,30}[.]{1}[a-z]{2,7})si", $_POST['Email']) ) {
      $subj = 'Поле "E-mail" заполнено не правильно';
    } else if ( 0 < DB::Get_Query_Cnt("SELECT COUNT(*) FROM Client WHERE Email = " . DB::S($_POST['Email'])) ) {
      $subj = 'Этот E-mail уже используется и занят';
    } else {
      $Passw = System_String::Random();
      //  регистрация
      $this->__set('Groups_ID', 3);
      $this->__set('Metro_ID', $_POST['Metro_ID']);
      $this->__set('Name', $_POST['Name']);
      $this->__set('Login', $_POST['Email']);
      $this->__set('Passw', md5($Passw));
      $this->__set('Email', $_POST['Email']);
      $this->__set('Tel', $_POST['Tel']);
      $this->__set('Icq', $_POST['Icq']);
      $this->__set('Address', $_POST['Address']);
      $this->Save();
      //  письмо
      $subject = "Регистрация на сайте " . HOST;
      $message = "";
      $message .= "Уважаемый(ая) " . $_POST['Name'] . "\n\n";
      $message .= "Ваш email был использован для регистрации на сайте " . HOST . "\n";
      $message .= "Если это ошибка просто не отвечайте на него.\n\n";
      $message .= "Реквизиты:\n";
      $message .= "Логин:    " . $_POST['Email'] . "\n";
      $message .= "Пароль:   " . $Passw . "\n\n";
      //
      $Mailer = new Mail_Mailer(EMAIL_INFO, PROEKT_NAME);
      $Mailer->AddReplyTo(EMAIL_INFO, PROEKT_NAME);
      $Mailer->AddAddress($this->Email, $this->Name);
      $Mailer->AddBCC(EMAIL_INFO, PROEKT_NAME);
      $Mailer->Subject = $subject;
      $Mailer->Body = $message;
      if ( !$Mailer->Send() ) {
        Logs::Save_File('ошибка отправки регистрационного письма', 'error_mail_send.log');
      }
      $Mailer->ClearAddresses();
      $Mailer->ClearAttachments();
    }
    $this->Keystring = '';
    return $subj;
  }
  /**
   * Изменение профиля клиента
   * 
   * @return string - сообщение результата операции
   */
  public function Save_Profile()
  {
    $subj = 'ok';
    if ( !$_POST['Name'] || !$_POST['Email'] || !$_POST['Tel'] || !$_POST['Address'] ) {
      $subj = 'Одно из обязательных полей не заполнено';
    } else if ( !preg_match("([a-z]{1}[a-z|.|\\-|_|0-9]{1,20}@[a-z|\\-|0-9]{1,20}[.]{1}[a-z]{2,7})si", $_POST['Email']) ) {
      $subj = 'Поле "E-mail" заполнено не правильно';
    } else if ( $_POST['Passw'] != $_POST['PasswR'] ) {
      $subj = 'Пароли не совпадают';
    } else if ( 0 < strlen($_POST['Passw']) && strlen($_POST['Passw']) < 6 ) {
      $subj = 'Длина пароля менее 6 символов';
    } else if ( 0 < DB::Get_Query_Cnt("SELECT COUNT(*) FROM Client WHERE Email = " . DB::S($_POST['Email']) . " AND ID != {$this->ID}") ) {
      $subj = 'Этот E-mail уже используется и занят';
    } else {
      //  изменение профиля
      if ( 0 < $_POST['Passw'] ) $this->__set('Passw', md5($_POST['Passw']));
      
      $this->__set('Metro_ID', $_POST['Metro_ID']);
      $this->__set('Name', $_POST['Name']);
      $this->__set('Email', $_POST['Email']);
      $this->__set('Tel', $_POST['Tel']);
      $this->__set('Icq', $_POST['Icq']);
      $this->__set('Address', $_POST['Address']);
      $this->Save();
    }
    return $subj;
  }
  /**
   * Инициализация и/или получение клиента
   * Работает через Регистр
   * Индекс класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id
   * @return Client
   */
  public static function Factory($id = 0, $flag_load = false)
  {
    $index = __CLASS__ . (0 < $id ? '_' . $id : '');
    if ( !$result = Registry::Get($index) ) {
      $result = new self($id, $flag_load);
      Registry::Set($index, $result);
    }
    if ( !$result->Groups_ID ) {
      $result->Groups_ID = 2;
    }
    return $result;
  }
}