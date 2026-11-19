<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/auth.php';
require EBT_ROOT . '/inc/admin-layout.php';

ebt_require_login();

$errors = [];
$done = false;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    ebt_csrf_check();
    $current = isset($_POST['current']) && is_string($_POST['current']) ? $_POST['current'] : '';
    $new = isset($_POST['new']) && is_string($_POST['new']) ? $_POST['new'] : '';
    $confirm = isset($_POST['confirm']) && is_string($_POST['confirm']) ? $_POST['confirm'] : '';

    if (!password_verify($current, ebt_admin_hash())) {
        $errors[] = 'Pašreizējā parole nav pareiza.';
    }
    if (strlen($new) < 10) {
        $errors[] = 'Jaunajai parolei jābūt vismaz 10 zīmes garai.';
    }
    if ($new !== $confirm) {
        $errors[] = 'Jaunā parole un apstiprinājums nesakrīt.';
    }
    if (!$errors && ebt_admin_set_password($new)) {
        $done = true;
    } elseif (!$errors) {
        $errors[] = 'Paroli neizdevās saglabāt.';
    }
}

ebt_admin_head('Parole', 'password');
if ($done) {
    echo '    <p class="flash">Parole nomainīta.</p>' . "\n";
}
if ($errors) {
    echo '    <div class="flash flash--error"><strong>Lūdzu, izlabojiet:</strong><ul>';
    foreach ($errors as $error) {
        echo '<li>' . e($error) . '</li>';
    }
    echo '</ul></div>' . "\n";
}
?>

    <p class="lead">Parole attiecas uz visu pārvaldības sadaļu. Ieteicams izmantot garu un unikālu paroli.</p>

    <form method="post" action="/admin/password.php" style="max-width: 460px;">
      <?= ebt_csrf_field() ?>
      <label class="field">
        <span class="field-label">Pašreizējā parole</span>
        <input type="password" name="current" autocomplete="current-password" required>
      </label>
      <label class="field">
        <span class="field-label">Jaunā parole</span>
        <input type="password" name="new" autocomplete="new-password" minlength="10" required>
      </label>
      <label class="field">
        <span class="field-label">Atkārtot jauno paroli</span>
        <input type="password" name="confirm" autocomplete="new-password" minlength="10" required>
      </label>
      <button class="button button--solid" type="submit">Mainīt paroli</button>
    </form>

<?php
ebt_admin_foot();
