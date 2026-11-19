<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/auth.php';
require EBT_ROOT . '/inc/admin-layout.php';
require EBT_ROOT . '/inc/pages.php';

ebt_require_login();

$names = ebt_page_names();
$urls = ebt_page_urls();
$page = isset($_GET['p']) && is_string($_GET['p']) ? $_GET['p'] : '';
if (!isset($names[$page])) {
    http_response_code(404);
    ebt_admin_head('Lapa nav atrasta', 'pages');
    echo '    <p class="flash flash--error">Lapa nav atrasta.</p>' . "\n";
    echo '    <p><a class="button" href="/admin/pages.php">Atpakaļ uz lapām</a></p>' . "\n";
    ebt_admin_foot();
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    ebt_csrf_check();
    if (($_POST['action'] ?? '') === 'reset') {
        ebt_page_reset($page);
        ebt_redirect('/admin/page.php?p=' . rawurlencode($page) . '&reset=1');
    }
    $values = isset($_POST['text']) && is_array($_POST['text']) ? $_POST['text'] : [];
    $saved = ebt_page_save($page, $values);
    ebt_redirect('/admin/page.php?p=' . rawurlencode($page) . '&saved=' . $saved);
}

$fields = ebt_page_fields($page);
$groups = [];
foreach ($fields as $field) {
    $groups[$field['group']][] = $field;
}
$changedCount = 0;
foreach ($fields as $field) {
    if ($field['changed']) {
        $changedCount++;
    }
}

ebt_admin_head($names[$page], 'pages');
if (isset($_GET['saved'])) {
    echo '    <p class="flash">Izmaiņas saglabātas.</p>' . "\n";
}
if (isset($_GET['reset'])) {
    echo '    <p class="flash">Atjaunoti oriģinālie teksti.</p>' . "\n";
}
?>

    <div class="toolbar">
      <a class="hint" href="/admin/pages.php">← Visas lapas</a>
      <a class="hint" href="<?= e($urls[$page]) ?>" target="_blank" rel="noopener">Skatīt lapu</a>
      <span class="hint"><?= count($fields) ?> teksta lauki, <?= $changedCount ?> mainīti</span>
    </div>

    <form method="post" action="/admin/page.php?p=<?= e(rawurlencode($page)) ?>">
      <?= ebt_csrf_field() ?>
<?php $groupIndex = 0; ?>
<?php foreach ($groups as $groupName => $groupFields): ?>
<?php $groupIndex++; ?>
      <details class="group"<?= $groupIndex <= 1 ? ' open' : '' ?>>
        <summary><?= e($groupName) ?> <span class="group-count"><?= count($groupFields) ?></span></summary>
<?php foreach ($groupFields as $field): ?>
        <label class="field">
          <span class="field-label"><span class="tag"><?= e($field['tag']) ?></span> <?= e(mb_substr($field['original'], 0, 70)) ?><?= mb_strlen($field['original']) > 70 ? '…' : '' ?><?php if ($field['changed']): ?> <span class="tag">mainīts</span><?php endif; ?></span>
<?php if ($field['multiline']): ?>
          <textarea name="text[<?= e($field['key']) ?>]" rows="<?= mb_strlen($field['text']) > 260 ? 5 : 3 ?>"><?= e($field['text']) ?></textarea>
<?php else: ?>
          <input type="text" name="text[<?= e($field['key']) ?>]" value="<?= e($field['text']) ?>">
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

    <form method="post" action="/admin/page.php?p=<?= e(rawurlencode($page)) ?>" style="margin-top: 48px; padding-top: 24px; border-top: 1px solid var(--soft);">
      <?= ebt_csrf_field() ?>
      <input type="hidden" name="action" value="reset">
      <button class="button button--danger" type="submit">Atjaunot oriģinālos tekstus</button>
      <p class="hint" style="margin-top: 8px;">Visas šīs lapas izmaiņas tiks dzēstas un atgriezīsies sākotnējie teksti.</p>
    </form>

<?php
ebt_admin_foot();
