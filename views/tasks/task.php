<div class="todo <?= $task->isDone() ? 'done' : '' ?>">
  <div class="display">
    <input class="check" type="checkbox" <?= $task->isDone() ? 'checked="checked"' : '' ?>>
    <label class="todo-content"><?= htmlReady($task->short_description) ?></label>
    <a href="<?= $controller->url_for('tasks/show', $task->id . '.html') ?>">show</a>
    <a href="<?= $controller->url_for('tasks/edit', $task->id . '.html') ?>">edit</a>
    <a href="<?= $controller->url_for('tasks/destroy', $task->id . '.html') ?>">destroy</a>
    <span class="todo-destroy"></span>
  </div>
  <div class="edit">
    <input class="todo-input" type="text" value="<?= htmlReady($task->short_description) ?>">
  </div>
</div>
