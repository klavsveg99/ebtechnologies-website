<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/auth.php';

ebt_session_start();
if (ebt_is_logged_in()) {
    ebt_redirect('/admin/');
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    ebt_csrf_check();
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    if (ebt_login_attempt($password)) {
        ebt_redirect('/admin/');
    }
    $error = 'Nepareiza parole.';
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="data:,">
  <meta name="robots" content="noindex, nofollow">
  <title>Pieteikšanās | EB Technologies admin</title>
  <link rel="stylesheet" href="/admin/admin.css?v=20261119-1">
</head>
<body>
  <main class="login">
    <p class="admin-brand" style="margin-bottom: 28px;">EB Technologies</p>
    <h1>Pārvaldība</h1>
<?php if (!ebt_admin_configured()): ?>
    <p class="flash flash--error">Administratora parole vēl nav iestatīta. Serverī jāizpilda <code>php tools/set-admin-password.php</code>.</p>
<?php else: ?>
    <p class="lead">Ievadiet administratora paroli.</p>
<?php if ($error !== ''): ?>
    <p class="flash flash--error"><?= e($error) ?></p>
<?php endif; ?>
    <form method="post" action="/admin/login.php">
      <?= ebt_csrf_field() ?>
      <label class="field">
        <span class="field-label">Parole</span>
        <input type="password" name="password" autocomplete="current-password" required autofocus>
      </label>
      <button class="button button--solid" type="submit">Pieteikties</button>
    </form>
<?php endif; ?>
  </main>
</body>
</html>
