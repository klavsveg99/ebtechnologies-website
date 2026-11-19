<?php
declare(strict_types=1);

/* Shared paths and helpers. The site root is the webroot; private data lives
   one level above it, outside the document root. */
define('EBT_ROOT', dirname(__DIR__));
define('EBT_PRIVATE', dirname(EBT_ROOT) . '/private');
define('EBT_DATA', EBT_PRIVATE . '/data');

define('EBT_SITE', 'https://ebtechnologies.lv');
define('EBT_NAME', 'EB Technologies');
define('EBT_PHONE', '+371 29 179 536');

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function ebt_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }
    $file = EBT_PRIVATE . '/config.php';
    $config = is_file($file) ? (array) require $file : [];
    return $config;
}

function ebt_json_read(string $file): ?array
{
    if (!is_file($file)) {
        return null;
    }
    $raw = file_get_contents($file);
    if ($raw === false || trim($raw) === '') {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function ebt_json_write(string $file, array $data): bool
{
    $dir = dirname($file);
    if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
        return false;
    }
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    $tmp = $file . '.tmp';
    if (file_put_contents($tmp, $json . "\n") === false) {
        return false;
    }
    return rename($tmp, $file);
}

function ebt_redirect(string $url): void
{
    header('Location: ' . $url, true, 303);
    exit;
}

function ebt_post_string(string $name): string
{
    $value = $_POST[$name] ?? '';
    return is_string($value) ? trim($value) : '';
}
