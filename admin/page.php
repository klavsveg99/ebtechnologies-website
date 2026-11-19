<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/auth.php';
require EBT_ROOT . '/inc/admin-layout.php';
require EBT_ROOT . '/inc/pages.php';

ebt_require_login();

$pages = ebt_pages();
$path = isset($_GET['p']) && is_string($_GET['p']) ? $_GET['p'] : '';
if (!isset($pages[$path])) {
    http_response_code(404);
    ebt_admin_head('Lapa nav atrasta', 'pages');
    echo '    <p class="flash flash--error">Lapa nav atrasta.</p>' . "\n";
    echo '    <p><a class="button" href="/admin/pages.php">Atpakaļ uz lapām</a></p>' . "\n";
    ebt_admin_foot();
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    ebt_csrf_check();
    $values = isset($_POST['text']) && is_array($_POST['text']) ? $_POST['text'] : [];
    $saved = ebt_page_save($path, $values);
    ebt_redirect('/admin/page.php?p=' . rawurlencode($path) . '&saved=' . $saved);
}

$dom = ebt_page_load($path);
if ($dom === null) {
    http_response_code(500);
    ebt_admin_head('Lapu nevar atvērt', 'pages');
    echo '    <p class="flash flash--error">Failu nevarēja nolasīt.</p>' . "\n";
    ebt_admin_foot();
    exit;
}

$fields = ebt_page_fields($dom);
$groups = [];
foreach ($fields as $field) {
    $groups[$field['group']][] = $field;
}

ebt_admin_head($pages[$path], 'pages');
ebt_flash();
?>

    <div class="toolbar">
      <a class="hint" href="/admin/pages.php">← Visas lapas</a>
      <a class="hint" href="/<?= e($path) ?>" target="_blank" rel="noopener">Skatīt lapu</a>
      <span class="hint"><?= count($fields) ?> teksta lauki</span>
    </div>

    <form method="post" action="/admin/page.php?p=<?= e(rawurlencode($path)) ?>">
      <?= ebt_csrf_field() ?>
<?php $groupIndex = 0; ?>
<?php foreach ($groups as $groupName => $groupFields): ?>
<?php $groupIndex++; ?>
      <details class="group"<?= $groupIndex <= 1 ? ' open' : '' ?>>
        <summary><?= e($groupName) ?> <span class="group-count"><?= count($groupFields) ?></span></summary>
<?php foreach ($groupFields as $field): ?>
        <label class="field">
          <span class="field-label"><span class="tag"><?= e($field['tag']) ?></span> <?= e(mb_substr($field['text'], 0, 70)) ?><?= mb_strlen($field['text']) > 70 ? '…' : '' ?></span>
<?php if ($field['multiline']): ?>
          <textarea name="text[<?= (int) $field['key'] ?>]" rows="<?= mb_strlen($field['text']) > 260 ? 5 : 3 ?>"><?= e($field['text']) ?></textarea>
<?php else: ?>
          <input type="text" name="text[<?= (int) $field['key'] ?>]" value="<?= e($field['text']) ?>">
<?php endif; ?>
        </label>
<?php endforeach; ?>
      </details>
<?php endforeach; ?>

      <div class="toolbar" style="margin-top: 32px;">
        <button class="button button--solid" type="submit">Saglabāt izmaiņas</button>
        <span class="hint">Tukšs lauks nozīmē, ka attiecīgā vieta lapā paliks tukša.</span>
      </div>
    </form>

<?php
ebt_admin_foot();
