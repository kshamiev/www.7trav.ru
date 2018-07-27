<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Обслуживание системы.
 * 
 * Реализует обслуживание системы в целом.
 * Проверка работоспособности.
 * Разработки нового функционала.
 * Вспомогательные функции.
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

//  Шаблон
$Tpl_Mod = new Templates;

//  права
$Access = $ModSystem->Access;

//  отчет операции
$op_report = '';

/**
 * РАБОТА МОДУЛЯ
 */
//  Факториг системы в целом.
if ( 'structure_all' == $op )
{
  System_Factory::Factory();
  if ( !file_exists(PATH_LOG . '/error_system_factory.log') ) {
    $subj = 'Завершено успешно';
  } else {
    $subj = '! Завершено с ошибками !';
  }
}
//  Импорт структуры объектов
else if ( 'structure_import' == $op )
{
  System_Factory::Import_Structure();
  if ( !file_exists(PATH_LOG . '/error_system_factory.log') ) {
    $subj = 'Импорт структуры объектов произведен успешно';
  } else {
    $subj = '! Импорт структуры объектов произведен с ошибками !';
  }
}
//  Экспорт структуры объектов
else if ( 'structure_export' == $op )
{
  System_Factory::Export_Structure();
  if ( !file_exists(PATH_LOG . '/error_system_factory.log') ) {
    $subj = 'Экспорт структуры объектов произведен успешно';
  } else {
    $subj = '! Экспорт структуры объектов произведен с ошибками !';
  }
}
//  Сброс кеша сайта
else if ( 'cash_clear' == $op )
{
  System_File::Cache_Clear_All();
  $subj = 'Кеш сайта очищен';
}
//  Удаление пустых папок модулей, шаблонов и бинарных данных
else if ( 'folder_clear' == $op )
{
  System_File::Folder_Empty_Remove();
  $subj = 'Пустые папки удалены';
}
//  Сверка двух БД на предмет струтуры
else if ( 'bd_validation' == $op && ( $_POST['bd_name1'] != $_POST['bd_name2'] ) )
{
  $_POST['bd_name1'] = addslashes($_POST['bd_name1']);
  $_POST['bd_name2'] = addslashes($_POST['bd_name2']);
  //
  $tbl_list1 = array_flip(DB::Get_Query_One('SHOW TABLES FROM ' . $_POST['bd_name1']));
  $tbl_list2 = array_flip(DB::Get_Query_One('SHOW TABLES FROM ' . $_POST['bd_name2']));
  foreach ($tbl_list2 as $tbl_name => $row)
  {
    $tbl_list2[$tbl_name] = array();
  }
  foreach ($tbl_list1 as $tbl_name => $row)
  {
    $tbl_list1[$tbl_name] = array();
    if ( isset($tbl_list2[$tbl_name]) )
    {
      $prop_list1 = array_flip(DB::Get_Query_One('SHOW COLUMNS FROM ' . $tbl_name . ' FROM ' . $_POST['bd_name1']));
      $prop_list2 = array_flip(DB::Get_Query_One('SHOW COLUMNS FROM ' . $tbl_name . ' FROM ' . $_POST['bd_name2']));
      foreach ($prop_list1 as $filed_name => $row)
      {
        if ( isset($prop_list2[$filed_name]) )
        {
          unset($prop_list1[$filed_name]);
          unset($prop_list2[$filed_name]);
        }
      }
      //  анализ
      if ( 0 < count($prop_list1) ) {
        $tbl_list1[$tbl_name] = array_flip($prop_list1);
      } else {
        unset($tbl_list1[$tbl_name]);
      }
      if ( 0 < count($prop_list2) ) {
        $tbl_list2[$tbl_name] = array_flip($prop_list2);
      } else {
        unset($tbl_list2[$tbl_name]);
      }
    }
  }
  $Tpl_Mod->Assign_Link('tbl_list1', $tbl_list1);
  $Tpl_Mod->Assign_Link('tbl_list2', $tbl_list2);
}
//  Анализ классов
else if ( 'class_analiz' == $op && $_POST['class_name'] )
{
  $_POST['class_name'] = addslashes($_POST['class_name']);
  //  $Object = new $_POST['class_name'](1, true);
  //  все свойства класса включая наследуемые от родителей с их значениями по умолчанию array('Name'=>'Vasya Pupkin')
  $prop_list = get_class_vars($_POST['class_name']);
  //  все методы класса включая наследуемые от родителей в виде списка
  $metod_list = get_class_methods($_POST['class_name']);
  //
  $Tpl_Mod->Assign_Link('prop_list', $prop_list);
  $Tpl_Mod->Assign_Link('metod_list', $metod_list);
}
//  Проверка целостности каталога
else if ( 'nested' == $op )
{
  //  print $_POST['param'] . '<br>'; exit;
  if ( $_POST['param'] ) {
    $Obj = new $_POST['param']();
    $subj = $Obj->Act_Check();
    if ( '' != $subj ) {
      $subj = 'обнаружено нарушение целостности дерева';
      $Obj->Act_Repair();
      $subj.= ' (система попыталась восстановить целостность, проверте еще раз)';
    } else {
      $subj = 'целостность дерева не нарушена';
    }
  } else {
    $subj = 'таблица не указана';
  }
}
//  Создание документации на БД
else if ( 'bd_create_document' == $op )
{
  $link = System_Factory::Act_Generate_Documentation_DB();
  header("Content-Disposition: attachment; filename = " . basename($link));
  header("Content-Length: " . filesize($link));
  $fp = fopen($link, "rb");
  $file_name = fread($fp, filesize($link));
  fclose($fp);
  print $file_name;
  $subj = 'Документация на БД создана';
}
//  Удаление старых архивов
else if ( 'arhiv_clear' == $op )
{
  System_File::File_Arhiv_Remove();
  $subj = 'Старые архивы удалены';
}
//  Анализ не системных объектных классов
else if ( 'class_empty' == $op )
{
  System_Factory::Get_Class_Empty_System();
  if ( file_exists(PATH_LOG . '/class_empty_system.log') ) {
    $subj = 'Информация лежит в файл-логе - class_empty_system.log';
  } else {
    $subj = 'Необнаружено';
  }
}
//  пользовательский запрос
else if ( 'user' == $op )
{
  $subj = 'выполнено';
}

/**
 * ВЫВОД
 */
$Tpl_Mod->Assign('db_list', DB::Get_Query_One('SHOW DATABASES'));
$Tpl_Mod->Assign_Link('op', $op);
$Tpl_Mod->Assign_Link('op_report', $op_report);
$Tpl_Mod->Assign('Access', $Access);
$Tpl_Mod->Assign('ModSystem', $ModSystem);
$Tpl_Mod->Assign('mod_link', $mod_link);
return $Tpl_Mod->Fetch_System($ModSystem);

/*
$sql = "SELECT Tbl FROM ModSystem WHERE Tbl REGEXP 'Goods[0-9]+'";
$tbl_list = DB::Get_Query_One($sql);
foreach ($tbl_list as $tbl_name)
{
$sql = "ALTER TABLE `{$tbl_name}` CHANGE `Supplier_ID` `Vendor_ID` INT(11) COMMENT 'Производитель'";
DB::Set_Query($sql);
$sql = "ALTER TABLE `{$tbl_name}` DROP INDEX `Supplier_ID`, ADD INDEX `Vendor_ID` (`Vendor_ID`)";
DB::Set_Query($sql);
}
*/
