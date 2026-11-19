<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/auth.php';
require EBT_ROOT . '/inc/admin-layout.php';
require EBT_ROOT . '/inc/settings.php';

ebt_require_login();

$labels = [
    'news_title' => 'Arhīva virsraksts',
    'news_lead' => 'Arhīva ievads',
    'news_note' => 'Piezīme zem rakstu saraksta',
    'post_note' => 'Piezīme raksta beigās',
    'post_cta_title' => 'Raksta nobeiguma virsraksts',
    'post_cta_text' => 'Raksta nobeiguma teksts',
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    ebt_csrf_check();
    $values = [];
    foreach ($labels as $key => $label) {
        $values[$key] = ebt_post_string($key);
    }
    ebt_settings_save($values);
    ebt_redirect('/admin/settings.php?saved=1');
}

$settings = ebt_settings();

ebt_admin_head('Jaunumu lapa', 'settings');
ebt_flash();
?>

    <p class="lead">Teksti, kas parādās jaunumu sarakstā un katra raksta beigās. Rakstu saturs tiek labots sadaļā Jaunumi.</p>

    <form method="post" action="/admin/settings.php">
      <?= ebt_csrf_field() ?>
<?php foreach ($labels as $key => $label): ?>
      <label class="field">
        <span class="field-label"><?= e($label) ?></span>
<?php if (in_array($key, ['news_lead', 'news_note', 'post_note'], true)): ?>
        <textarea name="<?= e($key) ?>" rows="3"><?= e($settings[$key]) ?></textarea>
<?php else: ?>
        <input type="text" name="<?= e($key) ?>" value="<?= e($settings[$key]) ?>">
<?php endif; ?>
      </label>
<?php endforeach; ?>
      <button class="button button--solid" type="submit">Saglabāt</button>
    </form>

<?php
ebt_admin_foot();
