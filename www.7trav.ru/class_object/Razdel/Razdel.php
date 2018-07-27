<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 foldmethod=marker: */
/**
 * @package Cms
 */

/**
 * Класс работы с разделами сайта (страницами)
 *
 * @package Cms
 * @subpackage Razdel
 * @author Konstantin Shamiev aka marko-polo <konstanta75@mail.ru>
 * @version 27.01.2010
 */
class Razdel extends Obj_Catalog
{
    /**
     * Идентификатор
     *
     * @var integer
     */
    protected $ID = 0;

    /**
     * Левый ключ
     *
     * @var integer
     */
    protected $Keyl = 0;

    /**
     * Правый ключ
     *
     * @var integer
     */
    protected $Keyr = 0;

    /**
     * Уровень вложенности
     *
     * @var integer
     */
    protected $Level = 0;

    //  [BEG] Prop
    /**
     * Родительский раздел
     *
     * @var integer
     */
    protected $Razdel_ID;

    /**
     * Шаблон
     *
     * @var integer
     */
    protected $Site_Template_ID;

    /**
     * Центральный модуль
     *
     * @var integer
     */
    protected $ModSystem_ID;

    /**
     * Название
     *
     * @var string
     */
    protected $Name;

    /**
     * Титул (Title)
     *
     * @var string
     */
    protected $Title;

    /**
     * Ключи индексации (Keywords)
     *
     * @var string
     */
    protected $Keywords;

    /**
     * Крактое описание (Descrition)
     *
     * @var string
     */
    protected $Description;

    /**
     * Главаня статья
     *
     * @var string
     */
    protected $Content;

    /**
     * Абсолютная ссылка
     *
     * @var string
     */
    protected $UrlRoot;

    /**
     * Относительная ссылка
     *
     * @var string
     */
    protected $Url;

    /**
     * Редирект
     *
     * @var string
     */
    protected $UrlRedirect;

    /**
     * Период обновления раздела
     *
     * @var string
     */
    protected $SiteMapFlag;

    /**
     * Видимость в навигации
     *
     * @var integer
     */
    protected $IsVisible;

    /**
     * Индексация раздела
     *
     * @var integer
     */
    protected $IsIndex;

    /**
     * Переход по ссылкам
     *
     * @var integer
     */
    protected $IsFollow;

    /**
     * Доступ открыт
     *
     * @var integer
     */
    protected $IsAccess;

    //  [END] Prop
    /**
     * Имя таблицы хранящей объекты данного класса
     *
     * @var string
     */
    private $_Tbl_Name = 'Razdel';

    //  [BEG] Link
    /**
     * Новости
     *
     * @var News
     */
    protected $News;

    /**
     * Продукция
     *
     * @var Goods
     */
    protected $Goods;

    /**
     * Разделы
     *
     * @var Razdel
     */
    protected $Razdel;

    /**
     * Статьи
     *
     * @var Article
     */
    protected $Article;

    //  [END] Link

    /**
     * Seo teg complete
     *
     * @var string
     */
    protected $Seo = '';

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
     * Инициализация раздела на основе его абсолютного Url
     *
     * @param string $UrlRoot - полная ссылка до раздела (www.domainname.ru/article)
     * @return void
     */
    public function Init_Url($UrlRoot, $cache_time = CACHE_TIME)
    {
        $path = PATH_CACHE . '/Razdel_UrlRoot/' . LANG_PREFIX . '/' . str_replace('/', '_', $UrlRoot) . '.ini';
        if ( !file_exists($path) || time() - filemtime($path) > $cache_time )
        {
            if ( !is_dir($path = PATH_CACHE . '/Razdel_UrlRoot') )
                mkdir($path, 0777);
            if ( !is_dir($path = $path . '/' . LANG_PREFIX) )
                mkdir($path, 0777);
            $path = $path . '/' . str_replace('/', '_', $UrlRoot) . '.ini';
            $sql = "SELECT * FROM Razdel WHERE UrlRoot = " . s($UrlRoot);
            $row = DB::Get_Query_Row($sql);
            if ( 0 == count($row) )
            {
                $sql = "SELECT * FROM Razdel WHERE ID = 266";
                $row = DB::Get_Query_Row($sql);
            }
            $cache = serialize($row);
            file_put_contents($path, $cache);
        }
        else
        {
            $cache = trim(file_get_contents($path));
            $row = unserialize($cache);
        }
        if ( 0 == count($row) )
        {
            header('HTTP/1.1 404 Not Found');
            exit;
        }
        else if ( 266 == $row['ID'] )
        {
            header('HTTP/1.1 404 Not Found');
        }
        else if ( $row['UrlRedirect'] )
        {
            header('Location: ' . $row['UrlRedirect']);
            exit;
        }
        else if ( 0 == $row['IsAccess'] && 1 != $Client->Groups_ID )
        {
            $ip = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
            if ( file_exists('statistic.txt') )
            {
                $sys_ip_not_stat = file('statistic.txt');
                foreach ($sys_ip_not_stat as $ip_not)
                {
                    if ( $ip == trim($ip_not) )
                    {
                        header('HTTP/1.1 404 Not Found');
                        exit;
                    }
                }
            }
        }
        $this->Load($row);
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
     * Получение кеша seo раздела.
     * Созданного по аргоритму модуля его обрабатывающего.
     *
     * @return string
     */
    public final function Get_Seo()
    {
        if ( !$this->Seo )
        {
            if ( !$this->Seo = $this->Get_Cache('seo.htm') )
            {
                $this->Seo = '<title>' . htmlspecialchars($this->Title) . '. Купить в интернет магазине Ларец Лекаря</title>' . "\n";
                $this->Seo .= '<meta name="description" content="' . htmlspecialchars($this->Description) . '">' . "\n";
                $this->Seo .= '<meta name="keywords" content="' . htmlspecialchars($this->Keywords) . '">' . "\n";
                $this->Set_Cache('seo.htm', $this->Seo);
            }
        }
        return $this->Seo;
    }

    /**
     * Установка seo раздела.
     *
     * Установка seo раздела для запрошенного объекта (страницы раздела).
     * Установка seo конечной страницы
     *
     * @param string $seo - собранный SEO заголовок (Title, Keywords, Description)
     */
    public final function Set_Seo($seo)
    {
        $this->Seo = $seo;
    }

    /**
     * Получение списка статей раздела
     *
     * @param Filter $Filter - Фильтр
     * @return Article - список статей
     */
    public function Get_Article_List()
    {
        $sql = "
    SELECT
      ID, Name, Description
    FROM Article
    WHERE
      Razdel_ID = {$this->ID}
      AND IsVisible = 'да'
    ORDER BY
      Name ASC
    ";
        return DB::Get_Query($sql);
    }

    /**
     * Подучение продукции постранично.
     *
     * Продукция по отделам.
     *
     * @param Filter $Filter
     * @return array
     */
    public function Get_Goods_List(Filter $Filter)
    {
        $sql_where = array(1);
        //  фильтры
        $sql_where = array_merge($sql_where, $Filter->Get_Sql_Filter());
        //  количество
        $sql = "
    SELECT
      COUNT(DISTINCT c.ID)
    FROM Goods as c
      INNER JOIN Razdel_Link_Goods as rg ON rg.Goods_ID = c.ID
      INNER JOIN Razdel as r ON r.ID = rg.Razdel_ID
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND r.Keyl >= {$this->Keyl}
      AND r.Keyr <= {$this->Keyr}
      AND c.IsVisible = 'да'
    ";
        $Filter->Count = DB::Get_Query_Cnt($sql);
        //  список
        $sql = "
    SELECT
      DISTINCT c.ID, c.Name, c.Url, c.Description, c.Price, c.Imgs
    FROM Goods as c
      INNER JOIN Razdel_Link_Goods as rg ON rg.Goods_ID = c.ID
      INNER JOIN Razdel as r ON r.ID = rg.Razdel_ID
    WHERE
      " . implode(' AND ', $sql_where) . "
      AND r.Keyl >= {$this->Keyl}
      AND r.Keyr <= {$this->Keyr}
      AND c.IsVisible = 'да'
    ORDER BY
      c.{$Filter->Sort['Prop']} {$Filter->Sort['Value']}
    LIMIT
      " . (($Filter->Page - 1) * $Filter->Page_Item) . ", " . $Filter->Page_Item . "
    ";
        return DB::Get_Query($sql);
    }

    /**
     * Построение строки навигации до текущего раздела
     * Возвращает (0=>array(Name, UrlRoot), ...)
     * Использует систему кеширования
     *
     * @return array
     */
    public function Get_Navigation_Line()
    {
        if ( !$cache = $this->Get_Cache('line.ini') )
        {
            $sql = "
      SELECT
        ID,
        Name,
        SUBSTRING(UrlRoot, POSITION('/' IN UrlRoot)) as UrlRoot
      FROM Razdel
      WHERE
        Keyl <= " . $this->Keyl . "
        AND Keyr >= " . $this->Keyr . "
      ORDER BY
        Keyl ASC
      "; // AND Razdel_ID IS NOT NULL
            $this->Set_Cache('line.ini', System_File::Create_Ini(DB::Get_Query($sql), 3));
            $cache = $this->Get_Cache('line.ini');
        }
        return $cache;
    }

    /**
     * Получнеие видимых подразделов относительно текущего раздела
     * Возвращает (0=>array(Name, UrlRoot), ...)
     * Использует систему кеширования
     *
     * @return array
     */
    public function Get_Navigation_Child()
    {
        if ( !$cache = $this->Get_Cache('child.ini') )
        {
            $sql = "
      SELECT
        ID,
        Name,
        SUBSTRING(UrlRoot, POSITION('/' IN UrlRoot)) as UrlRoot
      FROM Razdel
      WHERE
        IsVisible = 1
        AND Razdel_ID = {$this->ID}
        AND IsAccess = 1
      ORDER BY
        Keyl ASC
      ";
            $razdel_list = DB::Get_Query($sql);
            $this->Set_Cache('child.ini', System_File::Create_Ini($razdel_list, 3));
            $cache = $this->Get_Cache('child.ini');
        }
        return $cache;
    }

    /**
     * Получнеие видимых подразделов относительно текущего раздела
     * Возвращает (0=>array(Name, UrlRoot), ...)
     * Использует систему кеширования
     *
     * @return array
     */
    public function Get_Navigation_ChildDescription()
    {
        $sql = "
    SELECT
      ID,
      Name,
      SUBSTRING(UrlRoot, POSITION('/' IN UrlRoot)) as UrlRoot,
      Description
    FROM Razdel
    WHERE
      IsVisible = 1
      AND Razdel_ID = {$this->ID}
      AND IsAccess = 1
    ORDER BY
      Keyl ASC
    ";
        return DB::Get_Query($sql);
    }

    /**
     * Получение списка модулей зоны центрального контента
     *
     * Используется для администрирования модулей разделов.
     *
     * @return array - список модулей контента текущего шаблона
     */
    public static function Get_ModSystem_Content()
    {
        $sql = "
    SELECT
      ID, Name
    FROM ModSystem
    WHERE
      Zone_Type_ID = 1
    ORDER BY
      Name ASC
    ";
        return DB::Get_Query_Two($sql);
    }

    /**
     * Корректировка абсолютной ссылки дочерних разделов.
     *
     * После изменения ссылки или премещения раздела.
     *
     * @param integer $parent_razdel_id - родительский каталог
     */
    public static function Act_UrlRoot_Update($parent_razdel_id)
    {
        //  получение абсолютной ссылки родительского раздела
        $sql = "SELECT UrlRoot FROM Razdel WHERE ID = {$parent_razdel_id}";
        $UrlRoot = DB::Get_Query_Cnt($sql);
        /*
         if ( false === strpos($UrlRoot, '.') ) {
         $UrlRoot = HOST;
         }
         */
        // обновление абсолютной ссылки у дочерних разделов
        $sql = "
    UPDATE
      Razdel
    SET
      UrlRoot = CONCAT('" . $UrlRoot . "', '/', Url)
    WHERE
      Razdel_ID = {$parent_razdel_id}
    ";
        DB::Set_Query($sql);
        //  рекурсивно обходим все подкаталоги
        $sql = "SELECT ID FROM Razdel WHERE Razdel_ID = " . $parent_razdel_id;
        $res = &DB::Query($sql);
        /* @var $res mysqli_result */
        while ( false != $row = $res->fetch_row() )
        {
            self::Act_UrlRoot_Update($row[0]);
        }
        $res->close();
    }

    /**
     * Создание и/или получение объекта
     * Работает через Регистр
     *
     * @param itneger $id
     * @return Razdel
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