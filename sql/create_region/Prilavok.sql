-- DELETE FROM WareRegion WHERE Region_ID = 2;
INSERT INTO WareRegion
SELECT
  w.ID, 2, wr.PriceBase, wr.Price, wr.IsVisible, wr.FlagNew
FROM Ware as w
  INNER JOIN WareRegion AS wr ON w.ID = wr.Ware_ID AND Region_ID = 1
WHERE
  w.Company_ID IN (1, 2, 5, 8, 12, 7);
