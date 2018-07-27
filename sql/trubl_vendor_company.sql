SELECT * FROM Vendor WHERE Company_ID IS NULL;
SELECT * FROM Ware WHERE Vendor_ID = 48;

SELECT
  w.Name, w.Company_ID, v.Name, v.Company_ID
FROM Ware as w
  INNER JOIN Vendor as v ON w.Vendor_ID = v.ID
WHERE
  v.Company_ID IS NULL;

SELECT
  DISTINCT w.Company_ID, v.Name, v.Company_ID
FROM Ware as w
  INNER JOIN Vendor as v ON w.Vendor_ID = v.ID
WHERE
  v.Company_ID IS NULL;
