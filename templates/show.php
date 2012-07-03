<?
$body_id = "plugin-acc";

PageLayout::addStylesheet($plugin->getPluginUrl() . '/css/style.css');
?>

<h2>Erweitertes Kopieren dieser Veranstaltung</h2>

<form class="plugin-acc-copy" action="<?= PluginEngine::getLink($plugin, array(), 'copy') ?>" method="post">

    <fieldset>

    <legend>In welches Semester möchten Sie die Veranstaltung kopieren?</legend>

    <select name="semester">
    <? foreach ($semesters as $semester) { ?>
        <option value="<?= htmlReady($semester->getId()) ?>"<?= $semester == $next_semester ? ' selected' : '' ?>>
            <?= htmlReady($semester->getValue('name'))?>
        </option>
    <? } ?>
    </select>

    </fieldset>
    <fieldset>

<legend>Welche Inhalte sollen in die neue Veranstaltung übernommen werden?</legend>
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
        <?= \Studip\Button::createAccept() ?>
        <?= \Studip\LinkButton::createCancel() ?>
    </div>
</form>

<style>
</style>
<?

$text = "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enimad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";

$infobox = array(
    'picture' => 'infobox/schedules.jpg',

    'content' => array(
        array(
            'kategorie' => _("Information:"),
            'eintrag'   => array(array("text" => $text, "icon" => "icons/16/black/info.png"))
        )
    )
);
