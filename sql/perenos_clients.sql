insert into 7trav.Client 
	(ID, Worker_ID, Groups_ID, Metro_ID, Name, Login, Passw, Status, Email, Tel, Icq, Address, 
	Avatar, 
	StatOnline, 
	DateOnline, 
	Date
	)
select 	ID, Worker_ID, GroupsS_ID, Metro_ID, Name, Login, Passw, Status, Email, Tel, Icq, Address, 
	ImgAvatar, 
	StatOnline, 
	DateOnline, 
	Date
	from 
	sturdy.Client;

insert into 7trav.Orders 
	(ID, Client_ID, Metro_ID, Name, Address, Comment, Status, Date)
select 	ID, Client_ID, Metro_ID, Name, Address, Comment, Status, Date 
	from 
	sturdy.Orders;

insert into 7trav.WareOrders 
	(ID, Orders_ID, Ware_ID, Name, Price, PriceBase, Cnt)
select 	ID, Orders_ID, Ware_ID, Name, Price, PriceBase, Cnt 
	from 
	sturdy.WareOrders;

insert into 7trav.Basket 
	(Client_ID, Ware_ID, Name, Price, PriceBase, Cnt)
select 	Client_ID, Ware_ID, Name, Price, PriceBase, Cnt 
	from 
	sturdy.WareBasket;
