<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/auth.php';
require EBT_ROOT . '/inc/admin-layout.php';
require EBT_ROOT . '/inc/posts.php';

ebt_require_login();
ebt_admin_head('Pārvaldība', '');
ebt_flash();

$posts = ebt_posts_sorted();
$recent = array_slice($posts, 0, 5);
?>

    <p class="lead">Šeit rediģējat vietnes tekstus un jaunumu rakstus. Lapu struktūra un sadaļas ir fiksētas - mainīt var tikai tekstu.</p>

    <div class="grid-2">
      <div class="panel">
        <h2 style="margin-top: 0;">Lapas</h2>
        <p class="hint">Sākums, pakalpojumi, par mums, kontakti un politikas.</p>
        <p><a class="button" href="/admin/pages.php">Atvērt lapas</a></p>
      </div>
      <div class="panel">
        <h2 style="margin-top: 0;">Jaunumi</h2>
        <p class="hint"><?= count($posts) ?> raksti arhīvā. Jaunus rakstus var pievienot un dzēst.</p>
        <p><a class="button" href="/admin/posts.php">Atvērt jaunumus</a></p>
      </div>
    </div>

    <h2>Pēdējie raksti</h2>
    <table class="table">
      <thead>
        <tr><th>Datums</th><th>Virsraksts</th><th></th></tr>
      </thead>
      <tbody>
<?php foreach ($recent as $post): ?>
        <tr>
          <td><?= e($post['date']) ?></td>
          <td><?= e($post['title']) ?></td>
          <td class="actions"><a href="/admin/post.php?slug=<?= e(rawurlencode($post['slug'])) ?>">Labot</a></td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>

<?php
ebt_admin_foot();
