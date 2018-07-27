SELECT 
  o.*
FROM Orders AS o
  LEFT JOIN CLIENT AS c ON o.Client_ID = c.ID
WHERE
  c.ID IS NULL;


SELECT 
  o.*
FROM Orders AS o
  LEFT JOIN Orders_Goods AS og ON og.Orders_ID = o.ID
WHERE
  og.ID IS NULL;



SELECT 
  og.*
FROM Orders_Goods AS og
  LEFT JOIN Goods AS g ON og.Goods_ID = g.ID
WHERE
  g.ID IS NULL
ORDER BY Goods_ID DESC;

SELECT * FROM Orders_Goods WHERE ID IN (76, 32, 203);