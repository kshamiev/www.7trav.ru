SELECT
  DISTINCT g.ID, g.Name
FROM Goods AS g
  LEFT JOIN Razdel_Link_Goods AS rg ON g.ID = rg.Goods_ID
WHERE
  rg.Razdel_ID IS NULL;

SELECT
  COUNT(DISTINCT g.ID)
FROM Goods AS g
  LEFT JOIN Razdel_Link_Goods AS rg ON g.ID = rg.Goods_ID
WHERE
  rg.Razdel_ID IS NULL;
