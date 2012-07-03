<form action="<?= $controller->url_for('lists/update', $list->id) ?>" method="post">

     <label>
     Beschreibung:
     <input type="text" name="list[description]" value="<?= htmlReady($list->description) ?>">
     </label>

     <?= \Studip\Button::createSubmit() ?>
</form>
