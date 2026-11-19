<?php
declare(strict_types=1);

/* Single-user admin login. The password hash lives in private/admin.php,
   outside the webroot; sessions are server-side and CSRF-protected. */

function ebt_admin_file(): string
{
    return EBT_PRIVATE . '/admin.json';
}

function ebt_admin_configured(): bool
{
    $config = ebt_json_read(ebt_admin_file());
    return is_array($config) && !empty($config['hash']);
}

function ebt_admin_hash(): string
{
    $config = ebt_json_read(ebt_admin_file());
    return is_array($config) && !empty($config['hash']) ? (string) $config['hash'] : '';
}

function ebt_admin_set_password(string $password): bool
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    return ebt_json_write(ebt_admin_file(), [
        'hash' => $hash,
        'updated' => date('c'),
    ]);
}

function ebt_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('ebt_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function ebt_is_logged_in(): bool
{
    ebt_session_start();
    return !empty($_SESSION['admin']) && $_SESSION['admin'] === true;
}

function ebt_require_login(): void
{
    if (!ebt_is_logged_in()) {
        ebt_redirect('/admin/login.php');
    }
}

function ebt_csrf_token(): string
{
    ebt_session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function ebt_csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(ebt_csrf_token()) . '">';
}

function ebt_csrf_check(): void
{
    ebt_session_start();
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(400);
        echo 'Nederīga sesija. Lūdzu, pārlādējiet lapu.';
        exit;
    }
}

function ebt_login_attempt(string $password): bool
{
    ebt_session_start();
    $hash = ebt_admin_hash();
    if ($hash === '' || !password_verify($password, $hash)) {
        usleep(400000);
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['admin'] = true;
    return true;
}

function ebt_logout(): void
{
    ebt_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}
