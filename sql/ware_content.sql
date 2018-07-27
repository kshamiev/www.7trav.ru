SELECT COUNT(*) FROM Ware WHERE ContentNew IS NOT NULL;
SELECT COUNT(*) FROM Ware WHERE ContentNew IS NULL;

SELECT ID, Content FROM Ware WHERE Content LIKE '%Описание%Описание%';
