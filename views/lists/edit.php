<form action="<?= $controller->url_for('lists/update', $list->id) ?>" method="put">
     <input type="hidden" name="_method" value="put">

     <label>
     Beschreibung:
     <input type="text" name="list[description]" value="<?= htmlReady($list->description) ?>">
     </label>

     <?= \Studip\Button::createSubmit() ?>
</form>
