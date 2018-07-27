<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * Модуль каталога продукции.
 *
 * Постраничный вывод продукции с условием (или без) выбранного каталога.
 *
 * @package WareHouse
 * @subpackage Goods
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 11.05.2010
 */

/**
 * безопасность
 */
if ( !class_exists('DB') )
    return;

/**
 * ИНИЦИАЛИЗАЦИЯ
 */
global $ModSystem;
/* @var $ModSystem ModSystem */
global $Razdel;
/* @var $Razdel Razdel */
global $Basket;
/* @var $Basket Basket */
global $page;

/**
 * РАБОТА МОДУЛЯ
 */

/**
 * ВЫВОД
 */
SC::Init('Goods');
/**
 * Фильтры
 */
if ( !$ModSystem->Filter instanceof Filter )
{
    $Filter = new Filter('Goods', 1, 20, 11);
    $Filter->Add_Filter('Supplier_ID');
    $Filter->Add_Filter('Vendor_ID');
    $ModSystem->Filter = $Filter;
}
else
{
    $Filter = $ModSystem->Filter;
}
//
if ( isset($_REQUEST['Supplier_ID']) )
{
    $Filter->Set_Filter('Supplier_ID', $_REQUEST['Supplier_ID']);
    if ( 0 < $_REQUEST['Supplier_ID'] )
    {
        $Filter->Add_Filter('Vendor_ID', DB::Get_Query_Two("SELECT ID, Name FROM Vendor WHERE Supplier_ID = " . i($_REQUEST['Supplier_ID'])));
    }
    else
    {
        $Filter->Add_Filter('Vendor_ID');
    }
}
if ( isset($_REQUEST['Vendor_ID']) )
{
    $Filter->Set_Filter('Vendor_ID', $_REQUEST['Vendor_ID']);
}
if ( isset($page) && $page )
{
    $Filter->Page = $page;
}
else
{
    $Filter->Page = 1;
}

if ( isset($_REQUEST['obj_id']) && 0 < $_REQUEST['obj_id'] )
{ //  статья
    $Goods = new Goods($_REQUEST['obj_id']);
    // защита невидимых товаров и заодно от перегрузок сервера
    if ( $Goods->Get_Cache('stop.log') )
    {
        header('HTTP/1.1 404 Not Found');
        exit();
    }
    $Goods->Load();
    if ( 'да' != $Goods->IsVisible )
    {
        $Goods->Set_Cache('stop.log', date('d.m.Y H;i'));
        header('HTTP/1.1 404 Not Found');
        exit();
    }
    $Tpl_Mod = new Templates();
    $Tpl_Mod->Assign_Link('Goods', $Goods);
    $Supplier = new Supplier($Goods->Supplier_ID);
    $Supplier->Load_Prop('Name');
    $Tpl_Mod->Assign_Link('Supplier', $Supplier);
    $Vendor = new Vendor($Goods->Vendor_ID);
    $Vendor->Load_Prop('Name');
    $Tpl_Mod->Assign_Link('Vendor', $Vendor);
    $Tpl_Mod->Assign_Link('Basket', $Basket);
    $Razdel->Set_Seo($Goods->Seo);
    return $Tpl_Mod->Fetch($ModSystem->ModulUser, 'con_goods');
}
else
{  //  постраничный вывод товаров
    $Tpl_Mod = new Templates();
    $Tpl_Mod->Assign_Link('Razdel', $Razdel);
    $Tpl_Mod->Assign_Link('Filter', $Filter);
    $Tpl_Mod->Assign_Link('Basket', $Basket);
    if ( 81 == $Razdel->ID )
    {
        //  $goods_list = $Razdel->Get_Goods_List($Filter);
        $goods_list = Goods::Get_Goods_List_All($Filter);
    }
    else if ( 265 == $Razdel->ID )
    {
        $goods_list = Goods::Get_Goods_List_Type_Goods($Filter, '806, 809, 810, 811, 813, 815, 2298, 2299, 2300, 2304, 814');
    }
    else if ( 264 == $Razdel->ID )
    {
        $goods_list = Goods::Get_Goods_List_Type_Goods($Filter, '2982, 3437, 2962');
    }
    else if ( 263 == $Razdel->ID )
    {
        $goods_list = Goods::Get_Goods_List_Type_Goods($Filter, '90, 2554, 2314, 1111');
    }
    else if ( 262 == $Razdel->ID )
    {
        $goods_list = Goods::Get_Goods_List_Type_Goods($Filter, '1297, 4, 3');
    }
    else if ( 161 == $Razdel->ID )
    {
        $goods_list = Goods::Get_Goods_List_Type_Goods($Filter, '263, 10, 11, 12, 1, 2, 1297, 4, 3, 13, 14, 3730, 3731, 5, 6, 7, 8, 9, 1422');
    }
    else if ( 2 < $Razdel->Keyl && $Razdel->Keyr < 67 )
    {
        $Tpl_Mod->Assign('navigation_list', $Razdel->Get_Navigation_Child());
        $goods_list = $Razdel->Get_Goods_List($Filter);
    }
    else if ( 126 == $Razdel->Razdel_ID )
    {   //  тип продукции
        $goods_list = Goods::Get_Goods_List_Type($Filter, $Razdel);
    }
    else if ( 155 == $Razdel->Razdel_ID )
    {   //  образ жизни
        $goods_list = Goods::Get_Goods_List_Life($Filter, $Razdel);
    }
    $Tpl_Mod->Assign_Link('goods_list', $goods_list);
    $Tpl_Mod->Assign('page_list', $Filter->Get_Page_List());
    return $Tpl_Mod->Fetch($ModSystem->ModulUser, 'con_catalog');
}
