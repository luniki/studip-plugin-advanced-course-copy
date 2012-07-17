<?
PageLayout::addStylesheet($plugin->getPluginUrl() . '/css/style.css');
PageLayout::addScript($plugin->getPluginUrl() . '/js/date.js');
PageLayout::addScript($plugin->getPluginUrl() . '/js/yepnope.1.5.4-min.js');
?>

<div id="todoapp">
  <h3><?= htmlReady($list->description) ?></h3>
  <ol id="todo-list" class="todo-list"></ol>
  <form action="#" method="post">
    <?= \Studip\Button::create(_("Liste löschen"), array('class' => 'delete-todo-list')) ?>
  </form>
</div>

<script type="text/template" id="item-template">
  <div class="todo <%= completed_at ? 'done' : '' %>">
    <div class="display">
      <input class="check" type="checkbox" <%= completed_at ? 'checked="checked"' : '' %> />
      <label class="todo-content"><%= short_description %></label>
      <span class="todo-switch-content"></span>
      <label class="todo-long-content"><%= long_description %></label>
    </div>
  </div>
</script>

<script>
STUDIP.ACC = {
    initial_todo_list: <?= json_encode($list->to_array()) ?>
  , initial_tasks: <?= json_encode(array_map(
                                       function ($task) {
                                           return $task->to_array();
                                       }, $list->tasks)) ?>
  , base_url: "<?= htmlReady("{$GLOBALS['ABSOLUTE_URI_STUDIP']}plugins.php/todo") ?>"
  , user_role: "<?= $GLOBALS['auth']->auth['perm'] ?>"
};

yepnope({
    test: typeof JSON === "undefined",
    yep: "<?= $plugin->getPluginUrl() . '/js/json2.min.js' ?>"
});

yepnope({
  test: typeof Backbone === "undefined",
  yep: "<?= $plugin->getPluginUrl() . '/js/backbone.js' ?>",
  complete:
        function () {
            yepnope([
                        "<?= $plugin->getPluginUrl() . '/js/backbone-trails.js' ?>",
                        "<?= $plugin->getPluginUrl() . '/js/backbone-relational.js' ?>",
                        "<?= $plugin->getPluginUrl() . '/js/app.js' ?>"
                    ]);
        }
});
</script>
