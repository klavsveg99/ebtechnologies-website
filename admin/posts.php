<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/auth.php';
require EBT_ROOT . '/inc/admin-layout.php';
require EBT_ROOT . '/inc/posts.php';

ebt_require_login();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    ebt_csrf_check();
    $slug = ebt_post_string('slug');
    $posts = ebt_posts_all();
    $kept = [];
    foreach ($posts as $post) {
        if ($post['slug'] !== $slug) {
            $kept[] = $post;
        }
    }
    if (count($kept) !== count($posts)) {
        ebt_posts_save($kept);
        ebt_redirect('/admin/posts.php?deleted=1');
    }
    ebt_redirect('/admin/posts.php');
}

$posts = ebt_posts_sorted();

ebt_admin_head('Jaunumi', 'posts');
ebt_flash();
?>

    <div class="toolbar">
      <a class="button button--solid" href="/admin/post.php?new=1">Jauns raksts</a>
      <span class="hint"><?= count($posts) ?> raksti</span>
    </div>

    <table class="table">
      <thead>
        <tr><th>Datums</th><th>Virsraksts</th><th>Adrese</th><th></th></tr>
      </thead>
      <tbody>
<?php foreach ($posts as $post): ?>
        <tr>
          <td><?= e($post['date']) ?></td>
          <td>
            <a href="/admin/post.php?slug=<?= e(rawurlencode($post['slug'])) ?>"><?= e($post['title']) ?></a>
<?php if (!empty($post['category'])): ?>
            <div class="hint"><?= e($post['category']) ?></div>
<?php endif; ?>
          </td>
          <td class="hint">/jaunumi/<?= e($post['slug']) ?>/</td>
          <td class="actions">
            <form method="post" action="/admin/posts.php" onsubmit="return confirm('Dzēst šo rakstu? To nevarēs atsaukt.');">
              <?= ebt_csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="slug" value="<?= e($post['slug']) ?>">
              <button class="button button--small button--danger" type="submit">Dzēst</button>
            </form>
          </td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>

<?php
ebt_admin_foot();
