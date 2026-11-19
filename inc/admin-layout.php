<?php
declare(strict_types=1);

/* Shared chrome for the admin screens. */

function ebt_admin_head(string $title, string $active = ''): void
{
    header('Cache-Control: no-store');
    $nav = [
        'pages' => ['Lapas', '/admin/pages.php'],
        'posts' => ['Jaunumi', '/admin/posts.php'],
        'settings' => ['Jaunumu lapa', '/admin/settings.php'],
        'password' => ['Parole', '/admin/password.php'],
    ];
    echo '<!DOCTYPE html>' . "\n";
    echo '<html lang="lv">' . "\n";
    echo '<head>' . "\n";
    echo '  <meta charset="UTF-8">' . "\n";
    echo '  <meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
    echo '  <link rel="icon" href="data:,">' . "\n";
    echo '  <meta name="robots" content="noindex, nofollow">' . "\n";
    echo '  <title>' . e($title) . ' | EB Technologies admin</title>' . "\n";
    echo '  <link rel="stylesheet" href="/admin/admin.css?v=20261119-1">' . "\n";
    echo '</head>' . "\n";
    echo '<body>' . "\n";
    echo '  <header class="admin-header">' . "\n";
    echo '    <div class="admin-header-inner">' . "\n";
    echo '      <a class="admin-brand" href="/admin/">EB Technologies</a>' . "\n";
    echo '      <nav class="admin-nav">' . "\n";
    foreach ($nav as $key => $item) {
        $current = $key === $active ? ' aria-current="page"' : '';
        echo '        <a href="' . e($item[1]) . '"' . $current . '>' . e($item[0]) . '</a>' . "\n";
    }
    echo '        <a href="/" target="_blank" rel="noopener">Skatīt vietni</a>' . "\n";
    echo '        <a href="/admin/logout.php">Iziet</a>' . "\n";
    echo '      </nav>' . "\n";
    echo '    </div>' . "\n";
    echo '  </header>' . "\n";
    echo '  <main class="admin-main">' . "\n";
    echo '    <h1>' . e($title) . '</h1>' . "\n";
}

function ebt_admin_foot(): void
{
    echo '  </main>' . "\n";
    echo '</body>' . "\n";
    echo '</html>' . "\n";
}

function ebt_flash(): void
{
    if (isset($_GET['saved'])) {
        echo '    <p class="flash">Izmaiņas saglabātas.</p>' . "\n";
    }
    if (isset($_GET['deleted'])) {
        echo '    <p class="flash">Ieraksts dzēsts.</p>' . "\n";
    }
    if (isset($_GET['created'])) {
        echo '    <p class="flash">Ieraksts izveidots.</p>' . "\n";
    }
}
