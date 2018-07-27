<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Core
 */

/**
 * Базовый абстрактный класс для работы с объектами типа каталог.
 * 
 * Реализует основной функционал работы с объектами данного типа:
 * <ol>
 * <li>Создание, Изменение, Сохранение, Удаление.
 * <li>Получение связанных дочерних объектов по определенному типу связи.
 * <li>Получение не связанных дочерних объектов по определенному типу связи.
 * <li>Создание связи между объектами по определенному типу связи.
 * <li>Удаление связи между объектами по определенному типу связи.
 * <li>Работа с кешем объектов. Проверка на существование объекта.
 * <li>Работа со свойстваи объекта через сеттеры и геттеры.
 * <li>Сортировка объектов между собой.
 * </ol>
 * @package Core
 * @subpackage Object
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */
abstract class Obj_Catalog extends Obj_Item
{
  /**
   * Получение нод несвязанных относительно родительской ноды
   * Которые можно привязать
   *
   * @return list
   */
  public function Get_Catalog_UnLink()
  {
    if ( 0 < $this->ID )
    {
      $sql = '
      SELECT
        ID, Name
      FROM ' . $this->Tbl_Name . '
      WHERE
        ( Keyl < ' . $this->Keyl . ' AND Keyr < ' . $this->Keyr . ' )
        OR
        ( Keyl > ' . $this->Keyl . ' AND Keyr > ' . $this->Keyr . ' )
        OR
        ( Keyl > ' . $this->Keyl . ' AND Keyr < ' . $this->Keyr . ' AND ' . $this->Tbl_Name . "_ID != " . $this->ID . ' )
      ORDER BY
        Name ASC
      ';
      return DB::Get_Query_Two($sql);
    }
    else
    {
      return DB::Get_Query_Two("SELECT ID, Name FROM " . $this->Tbl_Name . " WHERE Level > 1 ORDER BY Name ASC");
    }
  }
  /**
   * Сортировка ноды
   * Перемещение ноды внутри одного уровня
   *
   * @param bolean $direction
   * @return bolean
   */
  public function Act_Sortig($direction)
  {
    //  if ( 0 == $this->ID ) return false;
    if ( $direction ) { //	проверка конца
      $sql = "SELECT Keyl, Keyr FROM " . $this->Tbl_Name . " WHERE Keyl = " . ( $this->Keyr + 1 );
    }
    else {  //	проверка начало
      $sql = "SELECT Keyl, Keyr FROM " . $this->Tbl_Name . " WHERE Keyr = " . ( $this->Keyl - 1 );
    }
    $point = DB::Get_Query_Row($sql); if ( count($point) != 2 ) return true;
    //  здесь должна быть атомарная блокировка таблицы
    DB::Table_Lock_Write($this->Tbl_Name);
    //	псевдо удаление ноды из каталога ( прячем в минус )
    $sql = "UPDATE " . $this->Tbl_Name . "
    SET
      Keyl = Keyl * -1, Keyr = Keyr * -1
    WHERE
      Keyl >= " . $this->Keyl . " AND Keyr <= " . $this->Keyr;
    DB::Set_Query($sql);
    //	вычисдения смещения ключей для дерева ( соседней ноды )
    $key_step = $this->Keyr - $this->Keyl + 1;
    if ( $direction )
    {
      //	сортировка в конец
      $sql = "UPDATE " . $this->Tbl_Name . "
      SET
        Keyl = Keyl - " . $key_step . ",
        Keyr = Keyr - " . $key_step. "
      WHERE
        Keyl >= " . $point['Keyl'] . " AND Keyr <= " . $point['Keyr'];
      DB::Set_Query($sql);
      //	вычисдения смещения ключей для дерева ( перемещаемой ноды )
      $key_step = $point['Keyr'] - $point['Keyl'] + 1;
      //	вывод из тени и перемещение ноды
      $sql = "UPDATE " . $this->Tbl_Name . "
      SET
        Keyl = Keyl * -1 + " . $key_step . ",
        Keyr = Keyr * -1 + " . $key_step . "
      WHERE
        Keyl < 0 and Keyr < 0";
      DB::Set_Query($sql);
      //
      $this->Keyl+= $key_step;
      $this->Keyr+= $key_step;
    }
    else
    {
      //  print $this->ID . 'OK';
      //	сортировка в начало
      $sql = "UPDATE " . $this->Tbl_Name . "
      SET
        Keyl = Keyl + " . $key_step . ",
        Keyr = Keyr + " . $key_step . "
      WHERE
        Keyl >= " . $point['Keyl'] . " AND Keyr <= " . $point['Keyr'];
      DB::Set_Query($sql);
      //	вычисдения смещения ключей для дерева ( перемещаемой ноды )
      $key_step = $point['Keyr'] - $point['Keyl'] + 1;
      //	вывод из тени и перемещение ноды
      $sql = "UPDATE " . $this->Tbl_Name . "
      SET
        Keyl = Keyl * -1 - " . $key_step . ",
        Keyr = Keyr * -1 - " . $key_step . "
      WHERE
        Keyl < 0 and Keyr < 0";
      DB::Set_Query($sql);
      //
      $this->Keyl-= $key_step;
      $this->Keyr-= $key_step;
    }
    //  здесь должна быть снятие блокировки таблицы
    DB::Table_Unlock();
    return true;
  }
  /**
   * Произвольное перемещение ноды
   *
   * @param object $Obj
   * @return bolean
   */
  public function Act_Move($Obj)
  {
    //  if ( 0 == $Obj->ID ) return false;
    //  здесь должна быть атомарная блокировка таблицы
    DB::Table_Lock_Write($this->Tbl_Name);
    //	вычисдения смещения ключей для дерева
    $key_step1 = $Obj->Keyr - $Obj->Keyl + 1;
    //	псевдо удаление ноды из каталога ( прячем в минус )
    $sql = "UPDATE " . $this->Tbl_Name . "
    SET
      Keyl = Keyl * -1, Keyr = Keyr * -1
    WHERE
      Keyl >= " . $Obj->Keyl . " AND Keyr <= " . $Obj->Keyr;
    DB::Set_Query($sql);
    //	обновление дерева ( псевдо удаление - схлопывание )
    $sql = "UPDATE " . $this->Tbl_Name . "
    SET
      Keyr = Keyr - " . $key_step1 . ",
      Keyl = if ( Keyl > " . $Obj->Keyl . ", Keyl - " . $key_step1 . ", Keyl)
    WHERE
      Keyr > " . $Obj->Keyr;
    DB::Set_Query($sql);
    //
    if ( $this->ID > 0 )
    {
      // инициализация родителя после схлопывания	//
      if ( $Obj->Keyr < $this->Keyr )
      {
        $this->Keyr-= $key_step1;
        if ( $Obj->Keyl < $this->Keyl )
        {
          $this->Keyl-= $key_step1;
        }
      }
      //	вычисдения смещения ключей для перемещаемой ветки
      $key_step2 = $this->Keyr - $Obj->Keyl;
      $level_step = $this->Level - $Obj->Level + 1;
      //	обновление дерева ( псевдо добавление - раздвиг )
      $sql = "UPDATE " . $this->Tbl_Name . "
      SET
        Keyr = Keyr + " . $key_step1. ",
        Keyl = if ( Keyl > " . $this->Keyr . ", Keyl + " . $key_step1. ", Keyl)
      WHERE
        Keyr >= " . $this->Keyr;
      DB::Set_Query($sql);
      //	вывод из тени и перемещение ноды
      $sql = "UPDATE " . $this->Tbl_Name . "
      SET
        Keyl = Keyl * -1 + " . $key_step2 . ",
        Keyr = Keyr * -1 + " . $key_step2. ",
        Level = Level + " . $level_step . "
      WHERE
        Keyl < 0 and Keyr < 0";
      DB::Set_Query($sql);
      $sql = "UPDATE " . $this->Tbl_Name . " SET " . $this->Tbl_Name . "_ID = " . $this->ID . " WHERE ID = " . $Obj->ID;
      DB::Set_Query($sql);
      // инициализация родителя после раздвига	//
      $this->Keyr+= $key_step1;
    }
    else
    {
      $sql = "SELECT MAX(Keyr) FROM " . $this->Tbl_Name;
      $keyr = DB::Get_Query_Cnt($sql);
      //	вычисдения смещения ключей для перемещаемой ветки
      $key_step2 = $keyr - $Obj->Keyl + 1;
      $level_step = $Obj->Level - 1;
      //	вывод из тени и перемещение ноды
      $sql = "UPDATE " . $this->Tbl_Name . "
      SET
        Keyl = Keyl * -1 + " . $key_step2 . ",
        Keyr = Keyr * -1 + " . $key_step2 . ",
        Level = Level - " . $level_step . "
      WHERE
        Keyl < 0 and Keyr < 0";
      DB::Set_Query($sql);
      $sql = "UPDATE " . $this->Tbl_Name . " SET " . $this->Tbl_Name . "_ID = NULL WHERE ID = " . $Obj->ID;
      DB::Set_Query($sql);
    }
    //  здесь должна быть снятие блокировки таблицы
    DB::Table_Unlock();
    return true;
  }
  /**
   * Проверка целостности дерева каталога
   *
   * @return string
   */
  public function Act_Check()
  {
    $subj_error = '';
    //	Левый ключ ВСЕГДА меньше правого
    $sql="select ID FROM ".$this->Tbl_Name." where Keyl >= Keyr";
    $rezult = DB::Get_Query_One($sql);
    if ( count($rezult) )
    {
      //			$this->error('',$sql,'каталог - левый ключ не меньше правого: '.implode(', ',$rezult));
      $subj_error.='- левый ключ не меньше правого: '.implode(', ',$rezult)."<br>\n";
    }
    //	Наименьший левый ключ ВСЕГДА равен 1
    //	Наибольший правый ключ ВСЕГДА равен двойному числу узлов
    $sql="select count(ID), min(Keyl), max(Keyr) FROM ".$this->Tbl_Name."";
    $rezult=DB::Get_Query_Row($sql);
    if ( $rezult['min(Keyl)']!=1 )
    {
      //			$this->error('',$sql,'каталог - наименьший левый ключ не равен 1');
      $subj_error.='- наименьший левый ключ не равен 1'."<br>\n";
    }
    if ( $rezult['max(Keyr)']!=($rezult['count(ID)']*2) )
    {
      //			$this->error('',$sql,'каталог - наибольший правый ключ не равен двойному числу узлов');
      $subj_error.='- наибольший правый ключ не равен двойному числу узлов'."<br>\n";
    }
    //	Разница между правым и левым ключом ВСЕГДА нечетное число
    $sql="SELECT ID FROM ".$this->Tbl_Name." WHERE mod((Keyr-Keyl),2)=0";
    $rezult=DB::Get_Query_One($sql);
    if ( count($rezult) )
    {
      //			$this->error('',$sql,'каталог - разница между правым и левым ключом четное число: '.implode(', ',$rezult));
      $subj_error.='- разница между правым и левым ключом четное число: '.implode(', ',$rezult)."<br>\n";
    }
    //	Если уровень узла нечетное число то тогда левый ключ ВСЕГДА нечетное число, то же самое и для четных чисел
    $sql="select ID FROM ".$this->Tbl_Name." where mod((Keyl-Level+2),2)=1";
    $rezult=DB::Get_Query_One($sql);
    if ( count($rezult) )
    {
      //			$this->error('',$sql,'каталог - четность левого ключа и его уровня не совпадают: '.implode(', ',$rezult));
      $subj_error.='- четность левого ключа и его уровня не совпадают: '.implode(', ',$rezult)."<br>\n";
    }
    //	Ключи ВСЕГДА уникальны, вне зависимости от того правый он или левый
    $sql="select t1.ID FROM ".$this->Tbl_Name." as t1, ".$this->Tbl_Name." as t2 where t1.Keyl=t2.Keyr or t1.Keyr=t2.Keyl
			union 
				select t1.ID FROM ".$this->Tbl_Name." as t1, ".$this->Tbl_Name." as t2 where t1.Keyl=t2.Keyl or t1.Keyr=t2.Keyr
			group by t1.ID having count(t1.ID)>1";
    $rezult=DB::Get_Query_One($sql);
    if ( count($rezult) )
    {
      //			$this->error('',$sql,'каталог - ключи не уникальны: '.implode(', ',$rezult));
      $subj_error.='- ключи не уникальны: '.implode(', ',$rezult)."<br>\n";
    }
    return $subj_error;
  }
  /**
   * Востановление древовидного каталога по рефлексивным связям
   *
   * @param integer $par_id
   * @param integer $keyl
   * @param integer $level
   * @return integer
   */
  public function Act_Repair()
  {
    DB::Table_Lock_Write($this->Tbl_Name);
    $this->_Act_Repair();
    DB::Table_Unlock();
  }
  /**
   * Создание ноды верхнего уровня (родительской ноды).
   * 
   * Реализует механизим абстрактного достпа к созданию объекта.
   * C учетом условий ( фильтры, УП ).
   *
   * @param Filter $Filter
   * @return object
   */
  public static function Create(Filter $Filter)
  {
    //  загрузка конфигурации объекта
    SC::IsInit($Filter->Tbl);
    //
    $Tbl = $Filter->Tbl;
    //  Nested Sets
    //  здесь должна быть атомарная блокировка таблицы
    DB::Table_Lock_Write($Tbl);
    //
    $sql = "SELECT MAX(Keyr) FROM " . $Tbl;
    $Keyr = DB::Get_Query_Cnt($sql);
    //  Каталог верхнего уровня
    if ( $Keyr )
    {
      $sql = "INSERT INTO " . $Tbl . "
        SET
          Keyl = " . ($Keyr + 1) . ",
          Keyr = " . ($Keyr + 2) . ",
          Level = 1";
    }
    //  Первый каталог
    else
    {
      $sql = "INSERT INTO " . $Tbl . "
        SET
          Keyl = 1,
          Keyr = 2,
          Level = 1";
    }
    $id = DB::Ins_Query($sql);
    //  здесь должна быть снятие блокировки таблицы
    DB::Table_Unlock();
    //  Инициализация созданного аталога ( фильтры, УП )
    $Obj = new $Tbl($id);
    /* @var $Obj Obj_Item */
    /**
     * фильтры
     */
    foreach ($Filter->Filter as $prop => $row )
    {
      if ( isset($row['Value']) && $row['Value'] ) {
        $Obj->$prop = $row['Value'];
      }
    }
    /**
     * УП - условие пользователя
     */
    foreach (SC::$PropAll[$Tbl] as $prop => $row)
    {
      if ( isset(SC::$ConditionUser[$prop]) ) {
        $Obj->$prop = SC::$ConditionUser[$prop];
      }
    }
    //  сохранение объекта и возврат на него указателя
    $Obj->Save();
    return $Obj;
  }
  /**
   * Создание дочерней ноды относительно родительской
   * C учетом условий ( фильтры, УП )
   *
   * @param Filter $Filter
   * @return object
   */
  public function Create_Child(Filter $Filter)
  {
    //  загрузка конфигурации объекта
    SC::IsInit($this->Tbl_Name);
    //
    $Tbl = $Filter->Tbl;
    //  Nested Sets
    //  здесь должна быть атомарная блокировка таблицы
    DB::Table_Lock_Write($Tbl);
    //  сдвиг правой и родительской стороны
    $sql = "UPDATE " . $this->Tbl_Name . "
    SET
      Keyr = Keyr + 2,
      Keyl = if ( Keyl > " . $this->Keyr . ", Keyl + 2, Keyl)
    WHERE
      Keyr >= " . $this->Keyr;
    DB::Set_Query($sql);
    //  добавление узла - Подкаталог
    $sql = "INSERT INTO " . $this->Tbl_Name . "
      (" . $this->Tbl_Name . "_ID, Keyl, Keyr, Level)
    VALUES
      (" . $this->ID . ", " . $this->Keyr . ", " . $this->Keyr . " + 1, " . $this->Level . " + 1)
    ";
    $id = DB::Ins_Query($sql);
    //  корректировка ключей родителя после добавления
    $this->Keyr+= 2;
    //  здесь должна быть снятие блокировки таблицы
    DB::Table_Unlock();
    //  Инициализация созданного аталога ( фильтры, УП )
    $Obj = new $this->Tbl_Name($id);
    /* @var $Obj Obj_Item */
    /**
     * фильтры
     */
    foreach ($Filter->Filter as $prop => $row )
    {
      if ( isset($row['Value']) && $row['Value'] ) {
        $Obj->$prop = $row['Value'];
      }
    }
    /**
     * УП - условие пользователя
     */
    foreach (SC::$PropAll[$this->Tbl_Name] as $prop => $row)
    {
      if ( isset(SC::$ConditionUser[$prop]) ) {
        $Obj->$prop = SC::$ConditionUser[$prop];
      }
    }
    //  сохранение объекта и возврат на него указателя
    $Obj->Save();
    return $Obj;
  }
  /**
   * Загрузка ключей ноды (каталога)
   */
  public final function Load_Node()
  {
    $sql = "SELECT {$this->Tbl_Name}_ID, Keyl, Keyr, Level, Name FROM {$this->Tbl_Name} WHERE ID = {$this->ID}";
    $this->Load(DB::Get_Query_Row($sql));
  }
  /**
   * Удаление текущей ноды относительно родительской
   *
   * @param object $Obj
   * @return bolean
   */
  public final function Remove_Child($Obj)
  {
    if ( 0 == $Obj->ID ) return true;
    //  загрузка конфигурации объекта
    SC::IsInit($this->Tbl_Name);
    //  здесь должна быть атомарная блокировка таблицы
    DB::Table_Lock_Write($this->Tbl_Name);
    //  удаление узла
    $sql = "DELETE FROM " . $this->Tbl_Name . " WHERE Keyl >= " . $Obj->Keyl . " and Keyr <= " . $Obj->Keyr;
    if ( !DB::Query_Ignore($sql) ) {  //  здесь должна быть снятие блокировки таблицы
      DB::Table_Unlock();
      return false;
    }
    //  обновление
    $key_step = $Obj->Keyr - $Obj->Keyl + 1;
    $sql = "UPDATE " . $this->Tbl_Name . "
    SET
      Keyr = Keyr - " . $key_step . ",
      Keyl = if ( Keyl > " . $Obj->Keyl . ", Keyl - " . $key_step . ", Keyl)
    WHERE
      Keyr > " . $Obj->Keyr;
    DB::Set_Query($sql);
    //  здесь должна быть снятие блокировки таблицы
    DB::Table_Unlock();
    //  корректировка ключей родителя после удаления
    if ( $this->ID > 0 ) $this->Keyr-= $key_step;
    //  удаление файлов объекта
    if ( is_dir($path = PATH_ADMIN . '/img/' . strtolower($this->Tbl_Name) . '/' . $Obj->ID) ) {
      System_File::Folder_Remove($path);
    }
    /**
     * для справки
     * чтобы не перегружать ключи родительского кталога относительно удаляемого каталога 
     * его правый ключ нужно уменьшить на величину ($key_step)
     */
    //  при удалении
    unset($Obj);
    return true;
  }
  /**
   * Востановление каталога
   *
   * @param integer $par_id
   * @param integer $keyl
   * @param integer $level
   * @return integer
   */
  private function _Act_Repair($par_id = 0, $keyl = 1, $level = 1)
  {
    if ( $par_id > 0 ) {
      $sql = "SELECT ID FROM ".$this->Tbl_Name." WHERE ".$this->Tbl_Name."_ID = ".$par_id." ORDER BY Keyl";
    } else {
      $sql = "SELECT ID FROM ".$this->Tbl_Name." WHERE ".$this->Tbl_Name."_ID IS NULL ORDER BY Keyl";
    }
    $catalog_mas = DB::Get_Query_One($sql);
    foreach ($catalog_mas as $cat_id)
    {
      $sql = "UPDATE ".$this->Tbl_Name." SET Keyl = " . $keyl . ", Level = ".$level." WHERE ID = " . $cat_id;
      DB::Set_Query($sql);
      $keyl = $this->_Act_Repair($cat_id,($keyl+1),($level+1));
      $sql = "UPDATE ".$this->Tbl_Name." SET Keyr = ".$keyl." WHERE ID = " . $cat_id;
      DB::Set_Query($sql);
      $keyl++;
    }
    return $keyl;
  }
}