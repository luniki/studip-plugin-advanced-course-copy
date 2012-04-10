<form action="<?= $controller->url_for('lists') ?>" method="post">

     <label>
     Beschreibung:
     <input type="text" name="list[description]" value="">
     </label>

     <?= \Studip\Button::createSubmit() ?>
</form>
