<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Системный абстрактный модуль.
 * 
 * Реализует основную работу с объектами типа Item
 * Или просто объектами.
 * Удаление. Создание связи. Удаление связи.
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
} else if ( is_null($ModSystem->Obj) ) {
  $ModSystem->Obj = $Obj = new $ModSystem->Tbl();
} else {
  $Obj = $ModSystem->Obj;
}
/* @var $Obj Obj_Item */

//  инициализация родительского объекта, если он есть
if ( 0 < count($ModSystem->Parent) ) {
  SC::Init($ModSystem->Parent['Tbl']);
  $ObjParent = new $ModSystem->Parent['Tbl']($ModSystem->Parent['Obj_ID']);
}
/* @var $ObjParent Obj_Item */

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
  if ( isset($ObjParent) ) {
    $Filter->Set_All(SC::$Link[$ObjParent->Tbl_Name][$ModSystem->Tbl]['LinkP']);
  } else {
    $Filter->Set_All();
  }
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
  if ( !$Obj->Remove() ) {
    $subj = $subj_list[52];
    $op = 'not';
    break;
  }
  //  сброс кеша объекта
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
  //  проверка
  if ( !$Access['L'] ) {
    $subj = $subj_list[50];
    $op = 'not';
    break;
  } else if ( 0 == $Obj->ID ) {
    $subj = $subj_list[51];
    $op = 'not';
    break;
  }
  //  создание связи
  $ObjParent->Create_Link($Obj);
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';создание связи;' . $ObjParent->ID . ';' . $ObjParent->Tbl_Name . ';' . $Obj->ID);
  $subj = $subj_list[4];
  break;
}

/**
 * Удаление связи
 */
while ( 'link_remove' == $op ) {
  //  проверка
  if ( !$Access['RL'] ) {
    $subj = $subj_list[50];
    $op = 'not';
    break;
  } else if ( 0 == $Obj->ID ) {
    $subj = $subj_list[51];
    $op = 'not';
    break;
  }
  //  удаление связи
  $ObjParent->Remove_Link($Obj);
  //  логирование операции
  $Logs->Save(';' . $Worker->Login . ';удаление связи;' . $ObjParent->ID . ';' . $ObjParent->Tbl_Name . ';' . $Obj->ID);
  $subj = $subj_list[5];
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
if ( !isset($ObjParent) ) {
  $Access['L'] = 0;
  $Access['RL'] = 0;
  $Obj_List = $Obj::Get_Object($Filter);
}
//  Получение ;томков от родителя
else
{
  //  если связь один ко многим отвязать нельзя
  if ( !isset(SC::$Link[$ObjParent->Tbl_Name][$ModSystem->Tbl]['LinkC']) ) {
    //  $Access['L'] = 0;
    $Access['RL'] = 0;
  }
  $Obj_List = $ObjParent->Get_Object_Link($Filter);
}

//  не показываем навигацию по модулям
$Tpl_Mod->Assign('Path', array());
//  получение не связанных объектов
if ( $Access['L'] ) {
  $Tpl_Mod->Assign('Obj_List_Link', $ObjParent->Get_Object_UnLink($Filter));
}
//
$Tpl_Mod->Assign_Link('ModSystem', $ModSystem);
$Tpl_Mod->Assign_Link('Filter', $Filter);
$Tpl_Mod->Assign('page_list', $Filter->Get_Page_List());
$Tpl_Mod->Assign('Access', $Access);
$Tpl_Mod->Assign_Link('Obj_List', $Obj_List);
$Tpl_Mod->Assign('mod_link', $mod_link);
$Tpl_Mod->Assign('mod_link_blank', $mod_link_blank);
//  отступы
$otstup = array();
$otstup[1] = '';
$otstup[2] = $otstup[1] . '';
$otstup[3] = $otstup[2] . '&nbsp;&nbsp;&nbsp;';
$otstup[4] = $otstup[3] . '&nbsp;&nbsp;&nbsp;';
$otstup[5] = $otstup[4] . '&nbsp;&nbsp;&nbsp;';
$otstup[6] = $otstup[5] . '&nbsp;&nbsp;&nbsp;';
$otstup[7] = $otstup[6] . '&nbsp;&nbsp;&nbsp;';
$otstup[8] = $otstup[7] . '&nbsp;&nbsp;&nbsp;';
$otstup[9] = $otstup[8] . '&nbsp;&nbsp;&nbsp;';
$otstup[10] = $otstup[9] . '&nbsp;&nbsp;&nbsp;';
$Tpl_Mod->Assign('otstup', $otstup);
//
return $Tpl_Mod->Fetch_System($ModSystem);
