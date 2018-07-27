SELECT
      COUNT(DISTINCT g.ID)
    FROM Goods AS g
      INNER JOIN Razdel_Link_Goods AS rg ON rg.Goods_ID = g.ID
      INNER JOIN Razdel AS r ON r.ID = rg.Razdel_ID
    WHERE
      1
      AND r.Keyl >= 2
      AND r.Keyr <= 67
      AND g.IsVisible = 'да';


SELECT
  COUNT(DISTINCT g.ID)
FROM Goods AS g
  LEFT JOIN Razdel_Link_Goods AS rg ON rg.Goods_ID = g.ID
WHERE
  rg.Razdel_ID IS NOT NULL;