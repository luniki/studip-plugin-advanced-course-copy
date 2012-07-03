<ol>
  <? foreach ($lists as $list) { ?>
  <li>

    <a href="<?= $controller->url_for('lists/show', $list->id . '.html') ?>">
      <?= htmlReady($list->description) ?>
    </a>

  </li>
  <? } ?>
</ol>

<? var_dump($lists); ?>
