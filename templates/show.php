<?
$body_id = "plugin-acc-copy";
PageLayout::setTitle(_("Veranstaltung kopieren"));
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

      <legend>
          <?= _("Welche Inhalte sollen in die neue Veranstaltung übernommen werden?") ?>
      </legend>

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
        <?= \Studip\Button::createAccept(_("Kopieren")) ?>
    </div>
</form>

<?

$text = _("Auf dieser Seite können Sie eine Veranstaltung in ein anderes Semester kopieren. Dabei werden &ndash; falls gewünscht &ndash; auch weitere Daten aus der Ursprungsveranstaltung kopiert.");

$infobox = array(
    'picture' => 'infobox/schedules.jpg',

    'content' => array(
        array(
            'kategorie' => _("Information:"),
            'eintrag'   => array(array("text" => $text, "icon" => "icons/16/black/info.png"))
        )
    )
);
