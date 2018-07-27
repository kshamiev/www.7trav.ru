<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Системный абстрактный модуль.
 * 
 * Реализует основную работу с объектами типа Relation
 * Или объектами отношения. (Права доступа, Покупательская корзина)
 * Создание, Изменение, Сохранение, Удаление.
 * Работа с кешем.
 * @package Core
 * @subpackage Object
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
global $mod_link_blank;
global $Logs;
global $subj_list;

//  Конфигурация объекта
SC::Init($ModSystem->Tbl, $ModSystem->Parent['Tbl']);

//  Шаблон
$Tpl_Mod = new Templates();

//  права
$Access = $ModSystem->Access;

//  инициализация обрабатываемого объекта
if ( is_null($ModSystem->Obj) ) {
  $ModSystem->Obj = $Obj = new $ModSystem->Tbl($ModSystem->Parent['Obj_ID']);
} else {
  $Obj = $ModSystem->Obj;
}
/* @var $Obj Obj_Relation */

/**
 * Фильтры
 */
//  инициализация фильтра
if ( !$ModSystem->Filter instanceof Filter ) {
  $Filter = new Filter($ModSystem->Tbl);
  //  общая инициализация
  $Filter->Add_Sort();
  $Filter->Set_All();
  //  сортировка
  if ( isset(SC::$Prop[$ModSystem->Tbl]['Sort']) ) {
    $Filter->Set_Sort('Sort', 'ASC');
  } else if ( isset(SC::$Prop[$ModSystem->Tbl]['Date']) && SC::$Prop[$ModSystem->Tbl]['Date']['IsVisible'] ) {
    $Filter->Set_Sort('Date', 'DESC');
  }
  $ModSystem->Filter = $Filter;
} else {
  $Filter = $ModSystem->Filter;
}
//  Установка фильтров
if ( isset($_REQUEST['sort']) ) {
  $Filter->Set_Sort($_REQUEST['sort']);
}
if ( isset($_REQUEST['page']) ) {
  $Filter->Page = $_REQUEST['page'];
} else if ( !$Filter->Page ) {
  $Filter->Page = 1;
}
$Tpl_Mod->Assign('Filter', $Filter);

/**
 * Инициализация свойств для вывода на редактирование
 */
$Prop_List = SC::$Prop[$ModSystem->Tbl];
foreach ($Prop_List as $prop => $row) {
  //  убираем fckeditor
  if ( 'fckeditor' == $row['Form'] ) {
    unset($Prop_List[$prop]);
    continue;
  }
  //  убираем заблокированные свойства
  if ( $row['IsLocked'] ) {
    unset($Prop_List[$prop]);
  }
  //  убираем УП
  if ( isset(SC::$ConditionUser[$prop]) ) {
    unset($Prop_List[$prop]);
  }
}

//  редактируемый объект
$obj_edit_id = 0;

/**
 * Пользовательская инициализация
 */
if ( file_exists($mod_path = 'modules/' . $ModSystem->ModulUser . '/init_' . strtolower($ModSystem->Tbl) . '.php') ) {
  include $mod_path;
}

/**
 * РАБОТА
 */
/**
 * Удаление объекта
 */
while ( 'obj_remove' == $op ) {
  //  проверки
  if ( !$Access['R'] ) {
    $subj = $subj_list[50];
    $op = 'not';
    break;
  } else if ( !isset($_REQUEST['obj_id']) || !$_REQUEST['obj_id'] ) {
    $subj = $subj_list[51];
    $op = 'not';
    break;
  }
  //  удаление
  if ( !$Obj->Remove($_REQUEST['obj_id']) ) {
    $subj = $subj_list[52];
    $op = 'not';
    break;
  }
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';удаление позиции;' . $Obj->ID . ';' . $_REQUEST['obj_id']);
  $subj = $subj_list[3];
  break;
}

/**
 * Добавление позиции
 */
while ( 'obj_add' == $op ) {
  if ( !$Access['A'] ) {
    $subj = $subj_list[50];
    $op = 'not';
    break;
  } else if ( !isset($_REQUEST['obj_id']) || !$_REQUEST['obj_id'] ) {
    $subj = $subj_list[51];
    $op = 'not';
    break;
  }
  $Obj->Edit();
  //  добавление
  $Obj->Save($_REQUEST['obj_id'], -1);
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . 'добавление позиции;' . $Obj->ID . ';' . $_REQUEST['obj_id']);
  $subj = $subj_list[1];
  break;
}

/**
 * Редактирование объекта
 */
while ( 'obj_edit' == $op ) {
  if ( !$Access['E'] ) {
    $subj = $subj_list[50];
    $op = 'not';
    break;
  } else if ( !isset($_REQUEST['obj_id']) || !$_REQUEST['obj_id'] ) {
    $subj = $subj_list[51];
    $op = 'not';
    break;
  }
  $obj_edit_id = $_POST['obj_id'];
  break;
}

/**
 * Сохранение объекта
 */
while ( 'obj_save' == $op ) {
  if ( !$Access['E'] ) {
    $subj = $subj_list[50];
    $op = 'not';
    break;
  } else if ( !isset($_REQUEST['obj_id']) || !$_REQUEST['obj_id'] ) {
    $subj = $subj_list[51];
    $op = 'not';
    break;
  }
  $Obj->Edit();
  //  сохранение
  $Obj->Save($_REQUEST['obj_id'], 1);
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';сохранение позиции;' . $Obj->ID . ';' . $_REQUEST['obj_id']);
  $subj = $subj_list[2];
  break;
}

/**
 * Пользовательская обработка
 */
if ( file_exists($mod_path = 'modules/' . $ModSystem->ModulUser . '/action_' . strtolower($ModSystem->Tbl) . '.php') ) {
  include $mod_path;
}

/**
 * ВЫВОД
 */
//  Получение ;томков от родителя
$Access['L'] = 0;
$Access['RL'] = 0;
$Obj_List = $Obj->Get_Relation_Link($Filter);
//  получение не связанных объектов
if ( $Access['A'] ) {
  $Tpl_Mod->Assign('Obj_List_Link', $Obj->Get_Relation_UnLink());
}
$Tpl_Mod->Assign('Prop_List', $Prop_List);
$Tpl_Mod->Assign_Link('ModSystem', $ModSystem);
$Tpl_Mod->Assign('obj_edit_id', $obj_edit_id);
$Tpl_Mod->Assign('page_list', $Filter->Get_Page_List());
$Tpl_Mod->Assign('Access', $Access);
$Tpl_Mod->Assign_Link('Obj_List', $Obj_List);
$Tpl_Mod->Assign('mod_link', $mod_link);
$Tpl_Mod->Assign('mod_link_blank', $mod_link_blank);
return $Tpl_Mod->Fetch_System($ModSystem);
