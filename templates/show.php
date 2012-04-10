<form action="<?= PluginEngine::getLink($plugin, array(), 'copy') ?>" method="post">

    <fieldset>

    <select name="semester">
    <? foreach ($semesters as $semester) { ?>
        <option value="<?= htmlReady($semester->getId()) ?>"<?= $semester == $next_semester ? ' selected' : '' ?>>
            <?= htmlReady($semester->getValue('name'))?>
        </option>
    <? } ?>
    </select>

    </fieldset>
    <fieldset>

      <? foreach ($modules as $module) { ?>

      <label>
        <input type="checkbox"
               checked
               name="modules[<?= $module['name'] ?>]"
               value="on">
        <?= htmlReady($module['label']) ?>
      </label>

      <? } ?>

    </fieldset>

     <div class="button-group">
        <?= \Studip\Button::createSubmit() ?>
        <?= \Studip\LinkButton::createCancel() ?>
    </div>
</form>

<style>
  label {
    display: block;
  }
</style>

<? var_dump($course) ?>
