SELECT
	a1.Name as Article_Name,
	SUM(IF(a2.TypeID = 4, 1, 0)) as Count_Official_Text,
	SUM(IF(a2.TypeID = 5, 1, 0)) as Count_English_Text
FROM Articles as a1
	LEFT JOIN Article_Link as al ON al.Article1_ID = a1.ID
  LEFT JOIN Articles as a2 ON al.Article2_ID = a2.ID AND a2.TypeID IN (4, 5)
WHERE
	a1.TypeID = 1
GROUP BY
  1
ORDER BY
  1;

SELECT
	o.Name as OrderName,
	GROUP_CONCAT( CONCAT(' ', t.LastName, ' ',  t.FirstName, ' '), org.Name ) as TestersName
FROM Orders as o
  INNER JOIN Order_Tester as ot ON ot.OrderID = o.OrderID
	INNER JOIN Testers as t ON t.ID = ot.TesterID
	INNER JOIN Organizations as org ON org.ID = t.OrganizationID
GROUP BY
  1
ORDER BY
	1;






















SELECT IF(1<2,'yes','no');

SELECT
	o.Name as OrderName,
	GROUP_CONCAT( CONCAT(' ', t.LastName, ' ',  t.FirstName, ' '), IF( Testing LIKE '%auto%', org.Name, 0) ) as Testing
FROM Orders as o
  INNER JOIN Order_Tester as ot ON ot.OrderID = o.OrderID
	INNER JOIN Testers as t ON t.ID = ot.TesterID
	INNER JOIN Organizations as org ON org.ID = t.OrganizationID
GROUP BY
  1
ORDER BY
	1;





SELECT
	o.Name as OrderName,
	GROUP_CONCAT( CONCAT( ' ', t.LastName, ' ',  t.FirstName, ' ', IF(COUNT(org.Name), 1, 0) ) )
FROM Orders as o
  INNER JOIN Order_Tester as ot ON ot.OrderID = o.OrderID
	INNER JOIN Testers as t ON t.ID = ot.TesterID
	INNER JOIN Organizations as org ON org.ID = t.OrganizationID
GROUP BY
  1
ORDER BY
	1;





SELECT
	o.Name as OrderName,
	GROUP_CONCAT( CONCAT(' ', t.LastName, ' ',  t.FirstName, ' '),  LOCATE (org.Name, org.Name) ORDER BY 1, 2 ASC )
FROM Orders as o
  INNER JOIN Order_Tester as ot ON ot.OrderID = o.OrderID
	INNER JOIN Testers as t ON t.ID = ot.TesterID
	INNER JOIN Organizations as org ON org.ID = t.OrganizationID
GROUP BY
  1
ORDER BY
	1;






SELECT
	o.Name as OrderName,
	REPLACE( GROUP_CONCAT( CONCAT(' ', t.LastName, ' ',  t.FirstName, ' '), org.Name ORDER BY 1, 2 ASC ), org.Name, '')
FROM Orders as o
  INNER JOIN Order_Tester as ot ON ot.OrderID = o.OrderID
	INNER JOIN Testers as t ON t.ID = ot.TesterID
	INNER JOIN Organizations as org ON org.ID = t.OrganizationID
GROUP BY
  1
ORDER BY
	1;
