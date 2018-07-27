UPDATE Ware
SET
	Ware.Company_ID = Company.ID
FROM Ware
  INNER JOIN Company ON Ware.Brend = Company.Name;

UPDATE Ware INNER JOIN Company ON Ware.Brend = Company.Name SET Ware.Company_ID = Company.ID;




/*
SET Company_ID =   [LOW_PRIORITY] [IGNORE] tbl_name
    SET col_name1=expr1 [, col_name2=expr2, ...]
    [WHERE where_definition]
    [LIMIT #]
*/