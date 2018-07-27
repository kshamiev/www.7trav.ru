UPDATE Goods SET PriceBase = NULL, Price = NULL;
UPDATE Goods
	INNER JOIN _GoodsRegion ON Goods.ID = _GoodsRegion.Goods_ID AND _GoodsRegion.Region_ID = 2
SET
	Goods.PriceBase = _GoodsRegion.PriceBase,
	Goods.Price = _GoodsRegion.Price,
	Goods.FlagNew = _GoodsRegion.FlagNew,
	Goods.IsVisible = _GoodsRegion.IsVisible;
UPDATE Goods SET IsVisible = 'нет' WHERE Price IS NULL;
