<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package WareHouse
 */

/**
 * Шаблонный класс для работы объектами системы типа простой объект
 *
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @package WareHouse
 * @subpackage Goods
 * @version 08.06.2009
 */
class Goods extends Obj_Item
{
    /**
     * Идентификатор
     *
     * @var integer
     */
    protected $ID = 0;

    //  [BEG] Prop
    /**
     * Поставщик
     *
     * @var integer
     */
    protected $Supplier_ID;

    /**
     * Производитель
     *
     * @var integer
     */
    protected $Vendor_ID;

    /**
     * Вид продукции
     *
     * @var integer
     */
    protected $Goods_Type_ID;

    /**
     * Образ жизни
     *
     * @var integer
     */
    protected $Goods_Life_ID;

    /**
     * Название
     *
     * @var string
     */
    protected $Name;

    /**
     * Титул товара
     *
     * @var string
     */
    protected $Title;

    /**
     * Keywords
     *
     * @var string
     */
    protected $Keywords;

    /**
     * Краткое описание
     *
     * @var string
     */
    protected $Description;

    /**
     * Ссылка
     *
     * @var string
     */
    protected $Url;

    /**
     * Код поставщика
     *
     * @var string
     */
    protected $Kod;

    /**
     * Форма выпуска
     *
     * @var string
     */
    protected $Form;

    /**
     * Маленькая картинка
     *
     * @var string
     */
    protected $Imgs;

    /**
     * Большая картинка
     *
     * @var string
     */
    protected $Imgb;

    /**
     * Полное описание товара
     *
     * @var string
     */
    protected $Content;

    /**
     * Дата появления
     *
     * @var string
     */
    protected $Date;

    /**
     * Оптимизирован
     *
     * @var string
     */
    protected $FlagJob;

    /**
     * Индексация в поисковиках
     *
     * @var string
     */
    protected $IsIndex;

    /**
     * Учетная цена
     *
     * @var float
     */
    protected $PriceBase;

    /**
     * Цена продажи
     *
     * @var float
     */
    protected $Price;

    /**
     * Видимость на сайте
     *
     * @var string
     */
    protected $IsVisible;

    /**
     * Новинка
     *
     * @var string
     */
    protected $FlagNew;

    //  [END] Prop
    /**
     * Имя таблицы хранящей объекты данного класса
     *
     * @var string
     */
    private $_Tbl_Name = 'Goods';

    //  [BEG] Link
    /**
     * Корзина
     *
     * @var Basket
     */
    protected $Basket;

    /**
     * Товары заказа
     *
     * @var Orders_Goods
     */
    protected $Orders_Goods;

    //  [END] Link

    /**
     * Конструткор класса
     * Инициализация объекта
     * $id == 0 - новый объект, не сохраненый в БД
     * Если $flag_load установлен в true и 0 < $id происходит загрузка свойств объекта из БД
     *
     * @param integer $id - идентификатор объекта
     * @param bolean $flag_load - флаг загрузки объекта
     */
    public function __construct($id = 0, $flag_load = false)
    {
        if ( 0 < $id )
        {
            $this->ID = $id;
        }
        if ( 0 < $id && $flag_load )
        {
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

    /**
     * Получение seo объекта.
     *
     * Title, Keywords, Description
     * Использует систему кеширования.
     *
     * @return string
     */
    public function Get_Seo()
    {
        if ( !$cache = $this->Get_Cache('seo.htm') )
        {
            if ( false == $this->Is_Load )
            {
                $this->Load_Prop('Title', 'Description', 'Keywords');
            }
            $cache = '<title>' . htmlspecialchars($this->Title) . '. Купить в интернет магазине Ларец Лекаря</title>' . "\n";
            $cache .= '<meta name="description" content="' . htmlspecialchars($this->Description) . '">' . "\n";
            $cache .= '<meta name="keywords" content="' . htmlspecialchars($this->Keywords) . '">' . "\n";
            $this->Set_Cache('seo.htm', $cache);
        }
        return $cache;
    }

    /**
     * Получение витрины
     * Семь новинок магазина
     *
     * @return array
     */
    public static function Get_Vitrina()
    {
        $sql = "
    SELECT
      ID,
      Name,
      Imgs
    FROM Goods
    WHERE
      FlagNew = 'да'
      AND IsVisible = 'да'
    LIMIT
      0, 7
    ";
        return DB::Get_Query($sql);
    }

    /**
     * Получение топ продаж
     * Семь самых продаваемых товаров
     *
     * @return array
     */
    public static function Get_TopSale()
    {
        $sql = "
    SELECT
      w.ID,
      w.Name,
      w.Imgs,
      SUM(wo.Cnt)
    FROM Goods as w
      INNER JOIN Orders_Goods as wo ON w.ID = wo.Goods_ID
    WHERE
      w.IsVisible = 'да'
    GROUP BY
      1, 2, 3
    HAVING
      1 < SUM(Cnt)
    ORDER BY
      4 DESC
    LIMIT
      0, 7
    ";
        return DB::Get_Query($sql);
    }

    /**
     * Подучение продукции постранично.
     *
     * Продукция по отделам.
     *
     * @param Filter $Filter
     * @param Razdel $Rzadel
     * @return array
     */
    public static function Get_Goods_List_All(Filter $Filter)
    {
        $sql_where = array(1);
        //  фильтры
        $sql_where = array_merge($sql_where, $Filter->Get_Sql_Filter());
        //  количество
        $sql = "
    SELECT
      COUNT(c.ID)
    FROM Goods as c
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND c.IsVisible = 'да'
    ";
        $Filter->Count = DB::Get_Query_Cnt($sql);
        //  список
        $sql = "
    SELECT
      c.ID, c.Name, c.Url, c.Description, c.Price, c.Imgs
    FROM Goods as c
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND c.IsVisible = 'да'
    ORDER BY
      c.{$Filter->Sort['Prop']} {$Filter->Sort['Value']}
    LIMIT
      " . (($Filter->Page - 1) * $Filter->Page_Item) . ", " . $Filter->Page_Item . "
    ";
        return DB::Get_Query($sql);
    }

    /**
     * Подучение продукции постранично.
     *
     * Продукция по отделам.
     *
     * @param Filter $Filter
     * @param Razdel $Rzadel
     * @return array
     */
    public static function Get_Goods_List_Type(Filter $Filter, Razdel $Razdel)
    {
        $sql_where = array(1);
        //  фильтры
        $sql_where = array_merge($sql_where, $Filter->Get_Sql_Filter());
        $sql_where[] = 'c.Goods_Type_ID = ' . DB::Get_Query_Cnt("SELECT ID FROM Goods_Type WHERE Url = '{$Razdel->Url}'");
        //  количество
        $sql = "
    SELECT
      COUNT(c.ID)
    FROM Goods as c
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND c.IsVisible = 'да'
    ";
        $Filter->Count = DB::Get_Query_Cnt($sql);
        //  список
        $sql = "
    SELECT
      c.ID, c.Name, c.Url, c.Description, c.Price, c.Imgs
    FROM Goods as c
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND c.IsVisible = 'да'
    ORDER BY
      c.{$Filter->Sort['Prop']} {$Filter->Sort['Value']}
    LIMIT
      " . (($Filter->Page - 1) * $Filter->Page_Item) . ", " . $Filter->Page_Item . "
    ";
        return DB::Get_Query($sql);
    }

    /**
     * Подучение продукции постранично.
     *
     * Продукция по отделам.
     *
     * @param Filter $Filter
     * @param Razdel $Rzadel
     * @return array
     */
    public static function Get_Goods_List_Type_Goods(Filter $Filter, $goods)
    {
        $sql_where = array(1);
        //  фильтры
        $sql_where = array_merge($sql_where, $Filter->Get_Sql_Filter());
        $sql_where[] = 'c.ID IN (' . $goods . ')';
        //  количество
        $sql = "
        SELECT
          COUNT(c.ID)
        FROM Goods as c
        WHERE
          " . implode(' AND ', $sql_where) . "
          AND c.IsVisible = 'да'
        ";
        $Filter->Count = DB::Get_Query_Cnt($sql);
        //  список
        $sql = "
        SELECT
          c.ID, c.Name, c.Url, c.Description, c.Price, c.Imgs
        FROM Goods as c
        WHERE
          " . implode(' AND ', $sql_where) . "
          AND c.IsVisible = 'да'
        ORDER BY
          c.{$Filter->Sort['Prop']} {$Filter->Sort['Value']}
        LIMIT
          " . (($Filter->Page - 1) * $Filter->Page_Item) . ", " . $Filter->Page_Item . "
        ";
        return DB::Get_Query($sql);
    }

    /**
     * Подучение продукции постранично.
     *
     * Продукция по отделам.
     *
     * @param Filter $Filter
     * @param Razdel $Rzadel
     * @return array
     */
    public static function Get_Goods_List_Life(Filter $Filter, Razdel $Razdel)
    {
        $sql_where = array(1);
        //  фильтры
        $sql_where = array_merge($sql_where, $Filter->Get_Sql_Filter());
        $sql_where[] = 'c.Goods_Life_ID = ' . DB::Get_Query_Cnt("SELECT ID FROM Goods_Life WHERE Url = '{$Razdel->Url}'");
        //  количество
        $sql = "
    SELECT
      COUNT(c.ID)
    FROM Goods as c
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND c.IsVisible = 'да'
    ";
        $Filter->Count = DB::Get_Query_Cnt($sql);
        //  список
        $sql = "
    SELECT
      c.ID, c.Name, c.Url, c.Description, c.Price, c.Imgs
    FROM Goods as c
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND c.IsVisible = 'да'
    ORDER BY
      c.{$Filter->Sort['Prop']} {$Filter->Sort['Value']}
    LIMIT
      " . (($Filter->Page - 1) * $Filter->Page_Item) . ", " . $Filter->Page_Item . "
    ";
        return DB::Get_Query($sql);
    }

    /**
     * Подучение продукции постранично.
     *
     * Продукция по типу.
     *
     * @param Filter $Filter
     * @return array
     */
    public static function Get_Goods_Search_List(Filter $Filter)
    {
        $sql_where = array(1);
        $Client = Client::Factory();
        $search = addslashes($Client->Search);
        //  поиск
        $str = "( g.Name LIKE '%" . $search . "%'";
        $str .= " OR g.Keywords LIKE '%" . $search . "%'";
        $str .= " OR g.Description LIKE '%" . $search . "%'";
        $str .= " OR g.Content LIKE '%" . $search . "%'";
        $str .= " OR g.ID = " . i($search);
        $str .= " )";
        $sql_where[] = $str;
        //  количество
        $sql = "
    SELECT
      COUNT(*)
    FROM Goods as g
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND g.IsVisible = 'да'
    ";
        //    pre($sql);
        $Filter->Count = DB::Get_Query_Cnt($sql);
        //  список
        $sql = "
    SELECT
      g.ID, g.Name, g.Url, g.Description, g.Price, g.Imgs
    FROM Goods as g
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND g.IsVisible = 'да'
    ORDER BY
      g.{$Filter->Sort['Prop']} {$Filter->Sort['Value']}
    LIMIT
      " . (($Filter->Page - 1) * $Filter->Page_Item) . ", " . $Filter->Page_Item . "
    ";
        //      pre($sql);
        return DB::Get_Query($sql);
    }

    /**
     * Создание и/или получение объекта
     * Работает через Регистр
     *
     * @param itneger $id
     * @return Goods
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