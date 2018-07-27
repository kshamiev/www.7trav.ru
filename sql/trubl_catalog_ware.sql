-- 375
SELECT
  w.*
FROM Catalog_Ware as cw
  INNER JOIN Ware as w ON cw.Ware_ID = w.ID AND cw.Catalog_ID = 63
WHERE
  CatalogLife_ID IS NULL;

DELETE FROM Catalog_Ware WHERE Catalog_ID = 63;



-- 2