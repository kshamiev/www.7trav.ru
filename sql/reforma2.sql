INSERT INTO WareRegion (Ware_ID, Region_ID, PriceBase, Price, IsVisible, FlagNew)
SELECT
	ID, 1, PriceBase, Price, IsVisible, FlagNew
FROM Ware;
