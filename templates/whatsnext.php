<? ob_start() ?>
  <?= _("Diese Veranstaltung ist eine Kopie von einer") ?>
  <a href="<?= URLHelper::getLink('seminar_main.php', array('cid' => $course->getSourceCourse())) ?>">
    <?= _("anderen Veranstaltung") ?>.
  </a>

  <p>
    <?= _("Bestimmte Daten – wie zum Beispiel Räume/Zeiten – konnten nicht sinnvoll kopiert werden.") ?>
  </p>

  <p>
    <?= _("Überprüfen Sie abschließend, ob Ihre Veranstaltung noch um die nebenstehenden Punkte ergänzen werden müssen.") ?>
  </p>
<? $text = ob_get_clean() ?>

<? if ($list) {
      $factory = new Flexi_TemplateFactory(dirname(__FILE__) . "/../views/");
      $todo_list_view = $factory->open('lists/show');
      echo $this->render_partial($todo_list_view, compact('list'));
  } else { ?>

  <?= MessageBox::error(_('Keine Liste!')) ?>

<? } ?>

<?
$infobox = array(
    'picture' => 'infobox/schedules.jpg',

    'content' => array(
        array(
            'kategorie' => _("Information:"),
            'eintrag'   => array(array("text" => $text, "icon" => "icons/16/black/info.png"))
        )
    )
);
