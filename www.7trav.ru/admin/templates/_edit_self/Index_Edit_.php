<h1><?php if ( 0 < $ID ) { ?>Редактирование<?php } else { ?>Добавление<?php } ?> - <?php echo $Object['Comment'] ?></h1>
<table width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
  <tr>
    <td>
      <input class="btn" type="button" value="список" onclick="button_form(this.form, '<?php echo HTTPL ?>/..', '', 0);">
      <?php if ( isset($Action['Save']) ) { ?><input class="btn" type="button" value="<?php if ( 0 < $ID ) { ?>сохранить<?php } else { ?>добавить<?php } ?>" onclick="button_form(this.form, '<?php echo HTTPL ?>', 'Save', 0);"><?php } ?>
    </td>
    <td width="100px" align="right"><?php echo $ID ?></td>
    <td width="20px"><img src="/img/!admin/button_id.gif" border="0"></td>
  </tr>
</table>
<table class="tbledit" width="100%" cellspacing="1" cellpadding="0" border="0" align="center">
  <tr>
    <th>Свойства</th>
    <th>Данные</th>
  </tr>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action">
    <input type="hidden" name="obj_id" value="<?php echo $ID ?>">
    <?php foreach ($Props as $Prop => $row) { ?>
    <tr>
      <td width="220px"<?php if ( isset($Error_Validator[$Prop]) ) { ?> class="error_prop"<?php } ?>><?php echo $row['Comment'] ?><br><?php echo $Error_Validator[$Prop] ?></td>
      <td>
        <?php if ( 'ReadOnly' == $row['Validator'] ) { ?>
        <?php echo $row['Value'] ?>

        <?php if ( 'Link' == $row['Validator'] ) { ?>
        <select name="Prop[<?php echo $Prop ?>]" style="width: 100%;"><option value="">не выбрано</option>
        <?php foreach ($Filter[$Prop]['List'] as $id => $name) { ?><option value="<?php echo $id ?>"<?php if ( $id == $row['Value'] ) { ?> selected<?php } ?>><?php echo $name ?></option><?php } ?>
        </select>

        <?php } else if ( 'Select' == $row['Validator'] ) { ?>
        <select name="Prop[<?php echo $Prop ?>]" style="width: 100%;"><option value="">не выбрано</option>
        <?php foreach ($Filter[$Prop]['List'] as $name) { ?><option value="<?php echo $name ?>"<?php if ( $name == $row['Value'] ) { ?> selected<?php } ?>><?php echo $name ?></option><?php } ?>
        </select>

        <?php } else if ( 'Radio' == $row['Validator'] ) { ?>
        <div id="blockroot">
          <?php foreach ($Filter[$Prop]['List'] as $name) { ?>
          <div id="blocklevel25"><div id="blocklevel2"><input type="radio" name="Prop[<?php echo $Prop ?>]" value="<?php echo $name ?>"<?php if ( $name == $row['Value'] ) { ?> checked<?php } ?>>&nbsp;<?php echo $name ?></div></div>
          <?php } ?>
        </div>

        <?php } else if ( 'Checkbox' == $row['Validator'] ) { ?>
        <div id="blockroot">
          <?php foreach ($Filter[$Prop]['List'] as $name) { ?>
          <div id="blocklevel25"><div id="blocklevel2"><input type="checkbox" name="Prop[<?php echo $Prop ?>][]" value="<?php echo $name ?>"<?php if ( $name == $row['Value'] ) { ?> checked<?php } ?>>&nbsp;<?php echo $name ?></div></div>
          <?php } ?>
        </div>

        <?php } else if ( 'Textarea' == $row['Validator'] ) { ?>
        <textarea rows="6" name="Prop[<?php echo $Prop ?>]" style="width: 100%;"><?php echo htmlspecialchars($row['Value']) ?></textarea>

        <?php } else if ( 'Date' == $row['Validator'] ) { ?>
        <input type="text" id="<?php echo $Prop ?>" name="Prop[<?php echo $Prop ?>]" value="<?php echo htmlspecialchars($row['Value']) ?>" maxlength="250" style="width: 50%;">
        <img src="/img/!admin/button_calendar.gif" onclick="calendar('<?php echo $Prop ?>');" style="cursor: pointer;">

        <?php } else if ( 'Img' == $row['Validator'] && 0 < $ID ) { ?>
        <?php if ( $row['Value'] ) { ?>
        <table class="notpad" width="100%" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td class="notpad" width="335"><input type="file" name="<?php echo $Prop ?>" size="50"></td>
            <td width="25" align="center"><a href="/img/<?php echo $row['Value'] ?>" title="просмотреть файл" target="_blank"><img src="/img/!admin/button_view_file.gif" border="0"></a></td>
            <td width="70" align="right">Повернуть:</td>
            <td width="50"><select name="Prop[<?php echo $Prop ?>][Edit][R]" style="width: 45px;"><option value="0">нет</option><option value="1">+90</option><option value="-1">-90</option><option value="2">180</option></select></td>
            <td width="50" class="tdtxt">Размер:</td>
            <td width="85" align="center"><input type="text" name="Prop[<?php echo $Prop ?>][Edit][X]" size="2">&nbsp;<b>x</b>&nbsp;<input type="text" name="Prop[<?php echo $Prop ?>][Edit][Y]" size="2"></td>
            <td width="55" align="center">Удалить:</td>
            <td><input type="checkbox" name="Prop[<?php echo $Prop ?>][Rem]" value="1" title="удалить"></td>
          </tr>
        </table>
        <?php } else { ?>
        <table class="notpad" width="100%" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td width="335"><input type="file" name="<?php echo $Prop ?>" size="50"></td>
            <td width="70" align="right">Повернуть:</td>
            <td width="50"><select name="Prop[<?php echo $Prop ?>][Edit][R]" style="width: 45px;"><option value="0">нет</option><option value="1">+90</option><option value="-1">-90</option><option value="2">180</option></select></td>
            <td width="50" class="tdtxt">Размер:</td>
            <td width="85" align="center"><input type="text" name="Prop[<?php echo $Prop ?>][Edit][X]" size="2">&nbsp;<b>x</b>&nbsp;<input type="text" name="Prop[<?php echo $Prop ?>][Edit][Y]" size="2"></td>
            <td>&nbsp;</td>
          </tr>
        </table>
        <?php } ?>

        <?php } else if ( 'File' == $row['Validator'] && 0 < $ID ) { ?>
        <?php if ( $row['Value'] ) { ?>
        <table class="notpad" width="100%" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td width="335"><input type="file" name="<?php echo $Prop ?>" size="50"></td>
            <td width="25" align="center"><a href="/img/<?php echo $row['Value'] ?>" title="просмотреть файл" target="_blank"><img src="/img/!admin/button_view_file.gif" border="0"></a></td>
            <td width="55" align="center">Удалить:</td>
            <td><input type="checkbox" name="Prop[<?php echo $Prop ?>][Rem]" value="1" title="удалить"></td>
          </tr>
        </table>
        <?php } else { ?>
        <table class="notpad" width="100%" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td width="335"><input type="file" name="<?php echo $Prop ?>" size="50"></td>
            <td>&nbsp;</td>
          </tr>
        </table>
        <?php } ?>

        <?php } else if ( 'Pass' == $row['Validator'] ) { ?>
        <input type="password" name="Prop[<?php echo $Prop ?>]" value="<?php echo htmlspecialchars($row['Value']) ?>" maxlength="250" style="width: 100%;">

        <?php } else { ?>
        <input type="text" name="Prop[<?php echo $Prop ?>]" value="<?php echo htmlspecialchars($row['Value']) ?>" maxlength="250" style="width: 100%;">

        <?php } ?>
      </td>
    </tr>
    <?php } ?>
  </form>
</table>
<table width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
  <tr>
    <td>
      <input class="btn" type="button" value="список" onclick="button_form(this.form, '<?php echo HTTPL ?>/..', '', 0);">
      <?php if ( isset($Action['Save']) ) { ?><input class="btn" type="button" value="<?php if ( 0 < $ID ) { ?>сохранить<?php } else { ?>добавить<?php } ?>" onclick="button_form(this.form, '<?php echo HTTPL ?>', 'Save', 0);"><?php } ?>
    </td>
    <td width="100px" align="right"><?php echo $ID ?></td>
    <td width="20px"><img src="/img/!admin/button_id.gif" border="0"></td>
  </tr>
</table>
