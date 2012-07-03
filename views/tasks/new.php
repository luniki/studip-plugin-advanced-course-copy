<form action="<?= $controller->url_for('lists', $list->id, 'tasks', 'create') ?>" method="post">

<? var_dump($list->to_array()) ?>

     <label>
     Kurzbeschreibung:
     <input type="text" name="task[short_description]" value="">
     </label>

     <label>
     Langbeschreibung:
     <textarea name="task[long_description]"></textarea>
     </label>

     <?= \Studip\Button::createSubmit() ?>
</form>
