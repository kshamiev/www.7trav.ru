<p align="center">
<a href="<?=$mod_link?>">На главную</a>&nbsp;&nbsp;
<a href="<?=$mod_link?>&block=registartion">Регистрация</a>&nbsp;&nbsp;
<a href="<?=$mod_link?>&block=reminder">Забыл пароль</a>
</p>
<div class="txtb">
Гость = <span class="mar"><?=$online_count['guest']?></span>&nbsp;&nbsp;&nbsp;
Клиент = <span class="mar"><?=$online_count['client']?></span>&nbsp;&nbsp;&nbsp;
Сотрудник = <span class="mar"><?=$online_count['worker']?></span>&nbsp;&nbsp;&nbsp;
</div>
<table align="center" cellspacing="0" cellpadding="0" border="0">
<tr>
	<td>
	<h3><?=$Site->Name?></h3>
	<?=$Site->Description?>
	</td>
</tr>
</table>