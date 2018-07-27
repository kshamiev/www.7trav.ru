SELECT
  Name, Price, Price - PriceBase
FROM Ware
WHERE
  WareType_ID = 11
  AND Vendor_ID = 20
ORDER BY
  3 ASC
