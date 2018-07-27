SELECT
  SUM(Price - PriceBase)
FROM Orders as o
  INNER JOIN WareOrders as wo ON o.ID = wo.Orders_ID
WHERE
  o.Date BETWEEN '2009-05-25' AND '2009-06-25'