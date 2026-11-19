<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/auth.php';
require EBT_ROOT . '/inc/admin-layout.php';
require EBT_ROOT . '/inc/pages.php';

ebt_require_login();
ebt_admin_head('Lapas', 'pages');
?>

    <p class="lead">Katra lapa tiek rediģēta atsevišķi. Sadaļas un izkārtojums ir fiksēti, mainīt var tekstu.</p>

    <table class="table">
      <thead>
        <tr><th>Lapa</th><th>Adrese</th><th></th></tr>
      </thead>
      <tbody>
<?php foreach (ebt_pages() as $path => $label): ?>
        <tr>
          <td><?= e($label) ?></td>
          <td><a href="/<?= e($path) ?>" target="_blank" rel="noopener">/<?= e(str_replace('index.html', '', $path)) ?></a></td>
          <td class="actions"><a class="button button--small" href="/admin/page.php?p=<?= e(rawurlencode($path)) ?>">Labot tekstu</a></td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>

<?php
ebt_admin_foot();
