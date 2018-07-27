<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Фильтр реазлизует условия работы с объектами
 * (УП, УС, фильтры, поиск, сортировка, постраничность)
 * 
 * @package Core
 * @subpackage Filter
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 12.11.2008
 */
class Filter
{
  /**
   * Целевая таблица для фильтров
   * Таблица объектов
   *
   * @var string
   */
  protected $Tbl;
  /**
   * Выбранные фильтры
   *
   * @var array (Prop=>array(Type=>’varchar’, Form=>’text’, Comment=>’название’, (Value, Beg – End), [Uslovie]=>’3,4’), ...)
   */
  protected $Filter;
  /**
   * Фильтры. Варианты и конфигурации
   *
   * @var array (Prop=>array(ID=>Name), Prop=>array(Name), ...)
   */
  protected $Filter_Prop;
  /**
   * Выбранный поиск
   *
   * @var array (Prop=>’’,  Value=>’’, Type=>’’) + Type
   */
  protected $Search;
  /**
   * Фильтр для поиска
   *
   * @var array (Prop=>Name, ...)
   */
  protected $Search_Prop;
  /**
   * Выбранная сортировка 
   *
   * @var array ('Prop'=>'Name', 'Value'=>'ASC')
   */
  protected $Sort;
  /**
   * Фильтр для сортировки
   *
   * @var array (Prop=>Name, ...)
   */
  protected $Sort_Prop;
  /**
   * Полное количесвто элементов удовлетворяющих условию фильтра
   *
   * @var integer
   */
  public $Count;
  /**
   * Текущая выбранная страница
   *
   * @var integer
   */
  public $Page;
  /**
   * Количество элементов на странице
   *
   * @var integer
   */
  public $Page_Item;
  /**
   * Видимый диапазон страниц ( постраничная навигация )
   *
   * @var integer
   */
  public $Page_Step;
  /**
   * Статус видимости фильтра
   *
   * @var integer
   */
  public $IsVisible = 1;
  /**
   * Конструткор класса
   * Инициализация фильтра
   *
   * @param string $Tbl - таблица бд по которой строится фильтр
   * @param string $sorting - поле сортировки
   * @param string $direction - направление сортировки
   */
  /**
   * Конструткор класса.
   * 
   * Инициализация фильтра.
   * По умолчанию $page = 0.
   * Это означает вывод всех элементов.
   * 
   * @param string $Tbl - таблица бд по которой строится фильтр
   * @param integer $page - номер страницы
   * @param integer $page_item - количество элементов на странице
   * @param integer $page_step - диапазон видимых страниц
   */
  public function __construct($Tbl, $page = 0, $page_item = PAGE_ITEM, $page_step = PAGE_STEP)
  {
    //  загрузка конфигурации свойств объекта
    $this->Tbl = $Tbl;
    SC::IsInit($Tbl);
    //  фильтры
    $this->Filter = array();
    $this->Filter_Prop = array();
    //  поиск
    $this->Search = array('Prop'=>'Name', 'Value'=>'');
    $this->Search_Prop = array();
    //  сортировка
    $this->Sort = array('Prop'=>'Name', 'Value'=>'ASC');
    $this->Sort_Prop = array();
    //  постраничность
    $this->Page = $page;
    $this->Page_Item = $page_item;
    $this->Page_Step = $page_step;
  }
  /**
   * Формирование SQL условия по фильтру для запроса в БД
   *
   * @return array
   */
  public function Get_Sql_Filter()
  {
    $sql_where = array();
    foreach ($this->Filter as $prop => $row)
    {
      if ( 'enum' == $row['Type'] && $row['Value'] )
      {
        if ( 'NULL' == $row['Value'] ) {
          $sql_where[$prop] = 'c.' . $prop . ' IS NULL';
        } else if ( 'NOTNULL' == $row['Value'] ) {
          $sql_where[$prop] = 'c.' . $prop . ' IS NOT NULL';
        } else {
          $sql_where[$prop] = 'c.' . $prop . ' = ' . DB::S($row['Value']);
        }
      }
      else if ( '_ID' == substr($prop, -3) && $row['Value'] )
      {
        if ( 'NULL' == $row['Value'] ) {
          $sql_where[$prop] = 'c.' . $prop . ' IS NULL';
        } else if ( 'NOTNULL' == $row['Value'] ) {
          $sql_where[$prop] = 'c.' . $prop . ' IS NOT NULL';
        } else {
          $sql_where[$prop] = 'c.' . $prop . ' = ' . DB::I($row['Value']);
        }
      }
      else if ( 'datetime' == $row['Form'] )
      {
        if ( $row['ValueBeg'] )
        {
          $sql_where[$prop . '_Beg'] = 'c.' . $prop . ' >= ' . DB::D($row['ValueBeg']);
        }
        if ( $row['ValueEnd'] )
        {
          $sql_where[$prop . '_End'] = 'c.' . $prop . ' <= ' . DB::D($row['ValueEnd']);
        }
      } else if ( $row['Value'] ) {
        $sql_where[$prop] = 'c.' . $prop . ' = ' . DB::S($row['Value']);
      }
    }
    return $sql_where;
  }
  /**
   * Формирование SQL условия по поиску для запроса в БД
   *
   * @return array
   */
  public function Get_Sql_Search()
  {
    $sql_where = array();
    if ( $this->Search['Value'] )
    {
      if ( 'ID' == $this->Search['Prop'] ) {
        $method = 'I';
      }
      else if ( $this->Search['Prop'] ) {
        $method = SC::$PropAll[$this->Tbl][$this->Search['Prop']]['DB'];
      }
      else {
        $method = '';
      }
      //
      if ( 'I' == $method || 'F' == $method )
      {
        if ( preg_match("(^([0-9.]+)?-([0-9.]+)?$)si", $this->Search['Value']) )
        {
          $mas = explode('-', $this->Search['Value']);
          if ( $mas[0] ) $sql_where[$this->Search['Prop'] . 'Beg'] = 'c.' . $this->Search['Prop'] . ' >= ' . $mas[0];
          if ( $mas[1] ) $sql_where[$this->Search['Prop'] . 'End'] = 'c.' . $this->Search['Prop'] . ' <= ' . $mas[1];
        }
        else
        {
          $sql_where[$this->Search['Prop']] = 'c.' . $this->Search['Prop'] . ' = ' . ($this->Search['Value'] * 1);
        }
      }
      else if ( 'S' == $method )
      {
        $sql_where[$this->Search['Prop']] = 'c.' . $this->Search['Prop'] . " LIKE '%" . DB::$DB->real_escape_string(str_replace(' ', '%', $this->Search['Value'])) ."%'";
      }
      //  здесь поиск по всем полям
      else
      {

      }
    }
    return $sql_where;
  }
  /**
   * Получения массива для реализации постраничности
   *
   * @return array - (beg left list right end)
   */
  public function Get_Page_List()
  {
    $page_count = ceil($this->Count/$this->Page_Item);
    if ( $page_count < 2 || !$this->Page ) return array();
    //
    $page_mas = array($this->Page); $i = 0;
    while ( $page_count )
    {
      $i++;
      if ( 0 < $this->Page - $i )             //  навигация в начало
      {
        $page_mas[] = $this->Page - $i;
      }
      if ( 0 == $this->Page_Step - count($page_mas) || $page_count == count($page_mas) ) break;
      if ( $this->Page + $i <= $page_count )  //  навигация в конец
      {
        $page_mas[] = $this->Page + $i;
      }
      if ( 0 == $this->Page_Step - count($page_mas) || $page_count == count($page_mas) ) break;
    }
    sort($page_mas);
    $result = array();
    $result['list'] = $page_mas;
    $result['right'] = $this->Page + $this->Page_Step;
    if ( $page_count < $result['right'] ) $result['right'] = $page_count;
    $result['left'] = $this->Page - $this->Page_Step;
    if ( $result['left'] < 1 ) $result['left'] = 1;
    $result['beg'] = 1;
    $result['end'] = $page_count;
    //
    return $result;
  }
  /**
   * Интерфейс формирования фильтров
   * Инициализация свойств фильтров форм
   * Фильтры, Поиск, Видимые свойства
   * Фильтр сохраняется до новой инициализации
   * $ParentLink - родительская связь
   * 
   * @param string $ParentLink
   * @return void
   */
  public function Set_All($ParentLink = '')
  {
    foreach (SC::$Prop[$this->Tbl] as $prop => $row)
    {
      //  print $prop . '<br>';
      //  заблокированные свойства пропускаем
      if ( $row['IsLocked'] ) continue;
      //  видимые свойства
      if ( $row['IsVisible'] ) {
        $this->Sort_Prop[$prop] = $row['Comment'];
      }
      //  множества
      if ( 'checkbox' == $row['Form'] )
      {
        $this->Filter_Prop[$prop] = DB::Get_EnumSet_Value($this->Tbl, $prop);
      }
      //  ссылки
      else if ( '_ID' == substr($prop, -3) )
      {
        //if ( false !== strpos($prop, $this->Tbl . '_ID') ) continue;  //  РС - рекурсивные связи
        if ( $prop == $this->Tbl . '_ID' ) continue;                //  РС - рекурсивные связи
        if ( $ParentLink == $prop ) continue; //  УС - условие связи
        if ( isset(SC::$ConditionUser[$prop]) ) continue;           //  УП - условие пользователя
        //
        $this->Filter[$prop] = $row;
        if ( 'ModSystem_ID' == $prop ) {
          $this->Filter_Prop[$prop] = Razdel::Get_ModSystem_Content();
        } else {
          $this->Filter_Prop[$prop] = $this->_Load_Filter_List($prop);
        }
        $this->Filter[$prop]['Value'] = '';
      }
      //  перечмсления
      else if ( 'enum' == $row['Type'] )
      {
        $this->Filter[$prop] = $row;
        $this->Filter_Prop[$prop] = DB::Get_EnumSet_Value($this->Tbl, $prop);
        if ( isset(SC::$Rel[$this->Tbl]) && !$row['IsNull'] ) {
          $this->Filter[$prop]['Value'] = $this->Filter_Prop[$prop][0];
        } else {
          $this->Filter[$prop]['Value'] = '';
        }
      }
      //  временные фильтры
      else if ( 'datetime' == $row['Form'] )
      {
        $this->Filter[$prop] = $row;
        $this->Filter[$prop]['ValueBeg'] = '';
        $this->Filter[$prop]['ValueEnd'] = '';
      }
      //  поиск
      //  else if ( 'text'==$row['Form'] || 'checkbox'==$row['Form'] || 'textarea'==$row['Form'] || 'fckeditor'==$row['Form'] || 'file '==$row['Form'] || 'img'==$row['Form'])
      else if ( 'passw' != $row['Form'] )
      {
        $this->Search_Prop[$prop] = $row['Comment'];
      }
    }
  }
  /**
   * Установка основных фильтров
   *
   * @param string $prop
   * @param mixed $value
   */
  public function Set_Filter($prop, $value)
  {
    if ( !isset($this->Filter[$prop]) ) {
      $this->Filter[$prop] = SC::$PropAll[$this->Tbl][$prop];
    }
    $this->Filter[$prop]['Value'] = $value;
  }
  /**
   * Установка временного фильтра
   *
   * @param string $prop
   * @param string $value_beg
   * @param string $value_end
   */
  public function Set_Filter_DateTime($prop, $value_beg, $value_end)
  {
    if ( !isset($this->Filter[$prop]) ) {
      $this->Filter[$prop] = SC::$PropAll[$this->Tbl][$prop];
    }
    $this->Filter[$prop]['ValueBeg'] = $value_beg;
    $this->Filter[$prop]['ValueEnd'] = $value_end;
  }
  /**
   * Установка выбранного поискового фильтра
   *
   * @param string $prop
   * @param mixed $value
   */
  public function Set_Search($prop, $value)
  {
    $this->Search['Prop'] = $prop;
    $this->Search['Value'] = $value;
  }
  /**
   * Установка выбранного режима сортировки
   *
   * @param array $param
   */
  public function Set_Sort($prop, $direction = '')
  {
    if ( $direction ) {
      $this->Sort['Prop'] = $prop;
      $this->Sort['Value'] = $direction;
    } else {
      if ( $this->Sort['Prop'] == $prop ) {
        $this->Sort['Value'] = 'ASC' == $this->Sort['Value'] ? 'DESC' : 'ASC' ;
      } else {
        $this->Sort['Prop'] = $prop;
        $this->Sort['Value'] = 'ASC';
      }
    }
  }
  /**
   * Пользовательская инициализация фильтров
   * $prop - название свойства
   * $value - флаг операции (true - загрузка, либо массив допустимых значений фильтра)
   * (Массив для ссылок либо список для перечислений)
   *
   * @param string $prop
   * @param mixed $value
   */
  public function Add_Filter($prop, $value = true)
  {
    //  множества
    if ( 'checkbox' == SC::$PropAll[$this->Tbl][$prop]['Form'] )
    {
      if ( is_bool($value) ) {
        $this->Filter_Prop[$prop] = DB::Get_EnumSet_Value($this->Tbl, $prop);
      } else {
        $this->Filter_Prop[$prop] = $value;
      }
    }
    //  ссылки
    else if ( '_ID' == substr($prop, -3) )
    {
      if ( !isset($this->Filter[$prop]) ) {
        $this->Filter[$prop] = SC::$PropAll[$this->Tbl][$prop];
      }
      if ( is_bool($value) ) {
        $this->Filter_Prop[$prop] = $this->_Load_Filter_List($prop);
      } else {
        $this->Filter_Prop[$prop] = $value;
      }
      $this->Filter[$prop]['Value'] = '';
    }
    //  перечмсления
    else if ( 'enum' == SC::$PropAll[$this->Tbl][$prop]['Type'] )
    {
      if ( !isset($this->Filter[$prop]) ) {
        $this->Filter[$prop] = SC::$PropAll[$this->Tbl][$prop];
      }
      if ( is_bool($value) ) {
        $this->Filter_Prop[$prop] = DB::Get_EnumSet_Value($this->Tbl, $prop);
      } else {
        $this->Filter_Prop[$prop] = $value;
      }
      $this->Filter[$prop]['Value'] = '';
      /*
      if ( $row['IsNull'] ) {
      $this->Filter[$prop]['Value'] = '';
      } else {
      $this->Filter[$prop]['Value'] = $this->Filter_Prop[$prop][0];
      }
      */
    }
    //  временные фильтры
    else if ( 'datetime' == SC::$PropAll[$this->Tbl][$prop]['Form'] )
    {
      if ( !isset($this->Filter[$prop]) ) {
        $this->Filter[$prop] = SC::$PropAll[$this->Tbl][$prop];
      }
      $this->Filter[$prop]['ValueBeg'] = '';
      $this->Filter[$prop]['ValueEnd'] = '';
    }
    //  все остальные (простые поля)
    else
    {
      if ( !isset($this->Filter[$prop]) ) {
        $this->Filter[$prop] = SC::$PropAll[$this->Tbl][$prop];
      }
      $sql = "SELECT DISTINCT {$prop} FROM {$this->Tbl}";
      $this->Filter_Prop[$prop] = DB::Get_Query_One($sql);
      $this->Filter[$prop]['Value'] = '';
    }
  }
  /**
   * Удаление свойства фильтра
   *
   * @param string $prop
   */
  public function Rem_Filter($prop)
  {
    unset($this->Filter[$prop]); unset($this->Filter_Prop[$prop]);
  }
  /**
   * Пользовательская инициализация поискового фильтра
   *
   * @param string $prop - свойство (столбец) по которуму идет поиск
   * @param mixed $value - флаг операции (true - загрузка по умолчанию, либо комментарий свойства)
   */
  public function Add_Search($prop, $value = true)
  {
    if ( is_bool($value) ) {
      $this->Search_Prop[$prop] = SC::$PropAll[$this->Tbl][$prop]['Comment'];
    } else {
      $this->Search_Prop[$prop] = $value;
    }
  }
  /**
   * Удаление свойства из поиска
   *
   * @param string $prop
   */
  public function Rem_Search($prop)
  {
    unset($this->Search_Prop[$prop]);
  }
  /**
   * Пользовательское добавление свойства сортировки
   * $prop - название свойство
   * $value - флаг операции ( false - удаление, true - загрузка), либо название добавляемой позиции фильтра
   *
   * @param string $prop
   * @param mixed $value
   */
  public function Add_Sort($prop = '', $value = true)
  {
    if ( '' == $prop ) {
      foreach (SC::$Prop[$this->Tbl] as $prop => $row)
      {
        if ( $row['IsVisible'] ) {
          $this->Sort_Prop[$prop] = $row['Comment'];
        }
      }
    } else {
      if ( is_bool($value) ) {
        $this->Sort_Prop[$prop] = SC::$PropAll[$this->Tbl][$prop]['Comment'];
      } else {
        $this->Sort_Prop[$prop] = $value;
      }
    }
  }
  /**
   * Удаление свойства из сортировки
   *
   * @param string $prop
   */
  public function Rem_Sort($prop)
  {
    unset($this->Sort_Prop[$prop]);
  }
  /**
   * Получение самого фильтра по указанному свойству
   * Тоесть получение списка всех возможных значений для предаваемого свойства
   * С учетом УП - условия пользователя
   *
   * @param string $prop
   * @return list
   */
  private function _Load_Filter_List($prop)
  {
    global $mod_id;
    //  $sql = 'SELECT ID, Name FROM ' . array_shift(explode('_', $prop)) . ' ORDER BY Name ASC';
    if ( 'Catalog_ID' == $prop ) {
      $sql = 'SELECT ID, Name, Level FROM ' . preg_replace('~(_[A-Z]{1})?_ID$~si', '', $prop) . ' ORDER BY Keyl ASC';
      $result_mas = DB::Get_Query($sql);
    } else if ( 'Razdel_ID' == $prop ) {
      $sql = 'SELECT ID, Name, Level FROM ' . preg_replace('~(_[A-Z]{1})?_ID$~si', '', $prop) . ' WHERE ModSystem_ID = ' . $mod_id  . ' ORDER BY Keyl ASC';
      $result_mas = DB::Get_Query($sql);
    } else {
      $sql = 'SELECT ID, Name FROM ' . preg_replace('~(_[A-Z]{1})?_ID$~si', '', $prop) . ' ORDER BY Name ASC';
      $result_mas = DB::Get_Query_Two($sql);
    }
    return $result_mas;
  }
  /**
   * универсальный геттер позволяющий обернуть все прямые обращения
   * к абстрактным переменным в их персональный геттер
   *
   * @param string $field абстрактное свойство наследуемого класса
   * @return mixed
   */
  public function __get($field)
  {
    if ( method_exists($this, $method = 'Get_' . $field) ) {
      return $this->$method();
    }
    return $this->$field;
  }
}