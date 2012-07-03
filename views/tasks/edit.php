<form action="<?= $controller->url_for('lists', $list->id, 'update', $task->id) ?>" method="post">

     <label>
     Kurzbeschreibung:
     <input type="text" name="task[short_description]" value="<?= htmlReady($task->short_description) ?>">
     </label>

     <label>
     Langbeschreibung:
     <textarea name="task[long_description]"><?= htmlReady($task->short_description) ?></textarea>
     </label>

     <?= \Studip\Button::createSubmit() ?>
</form>
