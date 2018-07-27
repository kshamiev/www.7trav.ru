<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Системный абстрактный модуль.
 * 
 * Реализует основную работу с объектами типа Catalog
 * Или объектами типа Каталог.
 * Сортировка. Удаление. Создание связи. Удаление связи.
 * Получение дочерних объектов по связи.
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
SC::Init($ModSystem->Tbl);

//  Права
$Access = $ModSystem->Access;

//  Шаблон
$Tpl_Mod = new Templates();

//  инициализация обрабатываемого объекта
if ( isset($_REQUEST['obj_id']) ) {
  $ModSystem->Obj = $Obj = new $ModSystem->Tbl($_REQUEST['obj_id']);
  $Obj->Load_Node();
} else if ( is_null($ModSystem->Obj) ) {
  $ModSystem->Obj = $Obj = new $ModSystem->Tbl(1);
  $Obj->Load_Node();
} else {
  $Obj = $ModSystem->Obj;
}
/* @var $Obj Obj_Catalog */

//  инициализация родительского объекта, если он есть
if ( count($ModSystem->Parent) ) {
  $ObjParent = new $ModSystem->Parent['Tbl']($ModSystem->Parent['Obj_ID']);
  $ObjParent->Load_Node();
} //  В режиме каталога всегда есть родитель, Родитель верхнего уровня 0
else {
  $ObjParent = new $ModSystem->Tbl();
}
/* @var $ObjParent Obj_Catalog */

/**
 * Фильтры
 */
//  Сброс фильтров
while ( 'filter_reset' == $op ) {
  if ( $ModSystem->Filter instanceof Filter ) {
    $ModSystem->Filter = null;
  }
  break;
}
//  инициализация фильтра
if ( !$ModSystem->Filter instanceof Filter ) {
  $Filter = new Filter($ModSystem->Tbl);
  //  общая инициализация
  $Filter->Set_All();
  //  сортировка
  $Filter->Set_Sort('Keyl', 'ASC');
  $ModSystem->Filter = $Filter;
} else {
  $Filter = $ModSystem->Filter;
}
//  Установка фильтров
if ( 'filter_reset' != $op ) {
  if ( isset($_REQUEST['filter']) ) {
    foreach ($_REQUEST['filter'] as $prop => $value) {
      if ( 2 == count($value) ) {
        $Filter->Set_Filter_DateTime($prop, $value['ValueBeg'], $value['ValueEnd']);
      } else {
        $Filter->Set_Filter($prop, $value);
      }
    }
  }
  if ( isset($_REQUEST['search']) ) {
    $Filter->Set_Search($_REQUEST['search']['Prop'], $_REQUEST['search']['Value']);
  }
}
if ( isset($_REQUEST['sort']) ) {
  $Filter->Set_Sort($_REQUEST['sort']);
}
if ( isset($_REQUEST['page']) ) {
  $Filter->Page = $_REQUEST['page'];
} else if ( !$Filter->Page ) {
  $Filter->Page = 1;
}
if ( isset($_REQUEST['filter_flag']) ) {
  if ( 1 == $Filter->IsVisible ) {
    $Filter->IsVisible = 0;
  } else {
    $Filter->IsVisible = 1;
  }
}

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
  } else if ( 0 == $Obj->ID ) {
    $subj = $subj_list[51];
    $op = 'not';
    break;
  }
  //  удаление
  $obj_id = $Obj->ID;
  if ( !$ObjParent->Remove_Child($Obj) ) {
    $subj = $subj_list[52];
    $op = 'not';
    break;
  }
  //  сброс кеша объекта
  $ObjParent->Act_Cache_Clear();
  $Obj->Act_Cache_Clear();
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';удаление позиции;' . $obj_id);
  $subj = $subj_list[3];
  break;
}

/**
 * Создание связи
 */
while ( 'link_add' == $op ) {
  if ( !$Access['L'] ) {
    $subj = $subj_list[50];
    $op = 'not';
    break;
  } else if ( 0 == $Obj->ID ) {
    $subj = $subj_list[51];
    $op = 'not';
    break;
  }
  $ObjParent->Act_Move($Obj);
  $ModSystem->Obj = $Obj = new $ModSystem->Tbl($Obj->ID);
  $Obj->Load_Node();
  //  сброс кеша объекта
  $ObjParent->Act_Cache_Clear();
  $Obj->Act_Cache_Clear();
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';создание связи;' . $ObjParent->ID . ';' . $Obj->ID);
  $subj = 'Связь создана';
  break;
}

/**
 * Сортировка вверх
 */
while ( 'sort_up' == $op ) {
  if ( !$Access['E'] ) {
    $subj = $subj_list[50];
    $op = 'not';
    break;
  } else if ( 0 == $Obj->ID ) {
    $subj = $subj_list[51];
    $op = 'not';
    break;
  }
  $Obj->Act_Sortig(false);
  $ModSystem->Obj = $Obj = new $ModSystem->Tbl($Obj->ID);
  $Obj->Load_Node();
  //  сброс кеша объекта
  $ObjParent->Act_Cache_Clear();
  $Obj->Act_Cache_Clear();
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';сортировка в начало;' . $Obj->ID);
  $subj = 'Сортировка в начало';
  break;
}

/**
 * Сортировка вниз
 */
while ( 'sort_down' == $op ) {
  if ( !$Access['E'] ) {
    $subj = $subj_list[50];
    $op = 'not';
    break;
  } else if ( 0 == $Obj->ID ) {
    $subj = $subj_list[51];
    $op = 'not';
    break;
  }
  $Obj->Act_Sortig(true);
  $ModSystem->Obj = $Obj = new $ModSystem->Tbl($Obj->ID);
  $Obj->Load_Node();
  //  сброс кеша объекта
  $ObjParent->Act_Cache_Clear();
  $Obj->Act_Cache_Clear();
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';сортировка в конец;' . $Obj->ID);
  $subj = 'Сортировка в конец';
  break;
}

/**
 * Подкаталоги
 */
while ( 'folder_down' == $op || 0 == $ObjParent->ID ) {
  if ( 0 == $Obj->ID ) {
    $subj = $subj_list[51];
    $op = 'not';
    break;
  }
  header('location: ' . $mod_link . '&mod_child_id=' . $ModSystem->ID);
  exit();
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
//  Получение ;томков без родителя
if ( 0 == $ObjParent->ID ) {
  $Access['RL'] = 0;
  $Obj_List = $Obj::Get_Object($Filter);
} //  Получение ;томков от родителя
else {
  $Access['RL'] = 0;
  $Obj_List = $ObjParent->Get_Object_Link($Filter);
}
//  ;казываем навигацию ; модулям
$Tpl_Mod->Assign('Path', $ModSystem->Path);
//  ;лучение не связанных объектов
if ( $Access['L'] ) {
  $Tpl_Mod->Assign('Obj_List_Link', $ObjParent->Get_Catalog_UnLink($Filter));
}
//
$Tpl_Mod->Assign_Link('ModSystem', $ModSystem);
$Tpl_Mod->Assign_Link('Filter', $Filter);
$Tpl_Mod->Assign('page_list', $Filter->Get_Page_List());
$Tpl_Mod->Assign('Access', $Access);
$Tpl_Mod->Assign_Link('Obj_List', $Obj_List);
$Tpl_Mod->Assign('mod_link', $mod_link);
$Tpl_Mod->Assign('mod_link_blank', $mod_link_blank);
return $Tpl_Mod->Fetch_System($ModSystem);
