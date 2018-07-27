<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Базовый класс для работы с сотрудниками
 * 
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @package Core
 * @subpackage Worker
 * @version 16.02.2009
 */
class Worker extends Obj_Item
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
   * Условие сотрудника
   *
   * @var integer
   */
  protected $IsWorker;
  /**
   * Фото - Аватар
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
  private $_Tbl_Name = 'Worker';
  //  [BEG] Link
  /**
   * Статистика скаченных сотрудниками файлов
   *
   * @var Statistic_DuwnLoadFileWorker
   */
  protected $Statistic_DuwnLoadFileWorker;
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
   * KCAPTCHA
   *
   * @var string
   */
  public $Keystring = '';
  /**
   * Ip адрес сотрудника в текущей сессиии
   *
   * @var string
   */
  public $Ip = '';
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
   * Инициализация его горизонтальных прав в свойство $Condition
   *
   * @return string
   */
  public function Init_Login()
  {
    $row = DB::Get_Query_Row("SELECT * FROM {$this->_Tbl_Name} WHERE Login = " . DB::S($_POST['Login']));
    if ( $row['Passw'] != md5($_POST['Passw']) ) {
      return "! Неправильный пароль !";
    }
    return $this->_Init($row);
  }
  /**
   * Инициализация пользователя (перезагрузка его данных не выходя из сессии)
   * Инициализация данных пользователоя
   * Инициализация его горизонтальных прав в свойство $Condition
   *
   * @return string
   */
  public function Init_Reload()
  {
    $row = DB::Get_Query_Row("SELECT * FROM Worker WHERE ID = " . $this->ID);
    return $this->_Init($row);
  }
  /**
   * Инициализация данных пользователоя
   * Инициализация его горизонтальных прав в свойство $Condition
   *
   * @param array $row
   * @return string
   */
  public function _Init($row)
  {
    if ( !isset($row['ID']) )
    {
      $subj = "! Не зарегистрирован !";
    }
    else if ( 'открыт' != $row['Status'] )
    {
      $subj = "! Аккаунт заблокирован !";
    }
    /*
    else if ( 'да' == $row['StatOnline'] )
    {
    $subj = "! Этот аккаунт уже используется в системе !";
    }
    */
    else if ( !$row['Groups_ID'] )
    {
      $subj = "! Аккаунт не входит ни в одну группу !";
    }
    else if ( 'открыта' != DB::Get_Query_Cnt("SELECT Status FROM Groups WHERE ID = " . $row['Groups_ID']) )
    {
      $subj = "! Группа в которую входит Ваш аккаунт заблокирована !";
    }
    else if ( 0 < $row['Site_ID'] && HOST != DB::Get_Query_Cnt("SELECT Host FROM Site WHERE ID = {$row['Site_ID']}") )
    {
      $subj = "! Access denied this Site !";
    }
    else
    {
      $this->Load($row);
      //  условие пользователя
      if ( $row['IsWorker'] ) {
        $this->_Condition['Worker_ID'] = $this->ID;
      }
      unset($row['Worker_ID']);
      //  условие группы
      if ( 1 < $row['Groups_ID'] ) {
        $this->_Condition['Groups_ID'] = $row['Groups_ID'];
      }
      unset($row['Groups_ID']);
      //  дополнительные условия
      foreach ($row as $field => $value)
      {
        if ( '_ID' == substr($field, -3) && $value )
        {
          $this->_Condition[$field] = $value;
        }
      }
      //  инициализируем сессию
      $this->_Timeout = 0;
      $this->Set_Timeout();
      //  логирование входа и выход
      Logs::Save_File($this->_Tbl_Name . ' ; ' . $this->Login . ' ; ' . $_SERVER["REMOTE_ADDR"], 'access_login.log');
      return '';
    }
    Logs::Save_File($this->_Tbl_Name . ' ; ' . $this->Login . ' ; ' . $_SERVER["REMOTE_ADDR"] . ' ; ' . $subj, 'access_denied.log');
    return $subj;
  }
  /**
   * Проверка на существование объекта
   *
   * @param $id - идентификатор объекта
   */
  public static function Is_Exists($id)
  {
    return DB::Get_Query_Cnt('SELECT COUNT(*) FROM ' . __CLASS__ . ' WHERE ID = ' . $id);
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
    if ( 60 < $p ) {
      file_put_contents(PATH_ADMIN . '/session/worker/' . $this->ID, $this->_Timeout);
    }
  }
  /**
   * Инициализация и/или получение клиента
   * Работает через Регистр
   * Индекс класс объекта + [_{$id} - если 0 < $id] 
   *
   * @param itneger $id - идентификатор объекта
   * @param bolean $flag_load - флаг загрузки объекта
   * @return Worker
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