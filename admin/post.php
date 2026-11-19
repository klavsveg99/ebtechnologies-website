<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/auth.php';
require EBT_ROOT . '/inc/admin-layout.php';
require EBT_ROOT . '/inc/posts.php';

ebt_require_login();

$isNew = isset($_GET['new']);
$slugParam = isset($_GET['slug']) && is_string($_GET['slug']) ? $_GET['slug'] : '';
$existing = $isNew ? null : ebt_post_find($slugParam);

if (!$isNew && $existing === null) {
    http_response_code(404);
    ebt_admin_head('Raksts nav atrasts', 'posts');
    echo '    <p class="flash flash--error">Raksts nav atrasts.</p>' . "\n";
    echo '    <p><a class="button" href="/admin/posts.php">Atpakaļ uz jaunumiem</a></p>' . "\n";
    ebt_admin_foot();
    exit;
}

$post = $existing ?? [
    'slug' => '',
    'title' => '',
    'date' => date('Y-m-d'),
    'category' => '',
    'excerpt' => '',
    'keywords' => [],
    'body' => '',
];

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    ebt_csrf_check();

    $post['title'] = ebt_post_string('title');
    $post['date'] = ebt_post_string('date');
    $post['category'] = ebt_post_string('category');
    $post['excerpt'] = ebt_post_string('excerpt');
    $post['body'] = trim((string) ($_POST['body'] ?? ''));

    $slugInput = ebt_post_string('slug');
    $post['slug'] = $slugInput !== '' ? $slugInput : ebt_slugify($post['title']);

    $keywords = [];
    foreach (explode(',', ebt_post_string('keywords')) as $keyword) {
        $keyword = trim($keyword);
        if ($keyword !== '') {
            $keywords[] = $keyword;
        }
    }
    $post['keywords'] = array_slice($keywords, 0, 12);

    if ($post['title'] === '') {
        $errors[] = 'Virsraksts ir obligāts.';
    }
    if (!preg_match('/^[a-z0-9-]+$/', $post['slug'])) {
        $errors[] = 'Adresē drīkst būt tikai mazie burti, cipari un defises.';
    }
    $date = DateTime::createFromFormat('Y-m-d', $post['date']);
    if ($date === false || $date->format('Y-m-d') !== $post['date']) {
        $errors[] = 'Datums jānorāda formātā GGGG-MM-DD.';
    }
    if ($post['excerpt'] === '') {
        $errors[] = 'Īsais apraksts ir obligāts.';
    }
    if ($post['body'] === '') {
        $errors[] = 'Raksta teksts ir obligāts.';
    }
    if (mb_strlen($post['excerpt']) > 400) {
        $errors[] = 'Īsais apraksts ir pārāk garš (līdz 400 zīmēm).';
    }
    if (mb_strlen($post['body']) > 20000) {
        $errors[] = 'Raksta teksts ir pārāk garš.';
    }
    foreach (ebt_posts_all() as $other) {
        if ($other['slug'] === $post['slug'] && ($existing === null || $other['slug'] !== $existing['slug'])) {
            $errors[] = 'Raksts ar šādu adresi jau eksistē.';
            break;
        }
    }

    if (!$errors) {
        $posts = ebt_posts_all();
        $updated = [];
        $replaced = false;
        foreach ($posts as $item) {
            if ($existing !== null && $item['slug'] === $existing['slug']) {
                $updated[] = $post;
                $replaced = true;
            } else {
                $updated[] = $item;
            }
        }
        if (!$replaced) {
            $updated[] = $post;
        }
        if (ebt_posts_save($updated)) {
            ebt_redirect('/admin/post.php?slug=' . rawurlencode($post['slug']) . ($existing === null ? '&created=1' : '&saved=1'));
        }
        $errors[] = 'Neizdevās saglabāt failu. Pārbaudiet, vai serverim ir rakstīšanas tiesības.';
    }
}

$keywordString = implode(', ', ebt_post_keywords($post));

ebt_admin_head($existing === null ? 'Jauns raksts' : 'Labot rakstu', 'posts');
if ($errors) {
    echo '    <div class="flash flash--error"><strong>Lūdzu, izlabojiet:</strong><ul>';
    foreach ($errors as $error) {
        echo '<li>' . e($error) . '</li>';
    }
    echo '</ul></div>' . "\n";
}
if ($existing !== null && isset($_GET['saved'])) {
    echo '    <p class="flash">Izmaiņas saglabātas.</p>' . "\n";
}
if ($existing === null && isset($_GET['created'])) {
    echo '    <p class="flash">Raksts izveidots un publicēts.</p>' . "\n";
}
?>

    <div class="toolbar">
      <a class="hint" href="/admin/posts.php">← Visi jaunumi</a>
<?php if ($existing !== null): ?>
      <a class="hint" href="/jaunumi/<?= e($existing['slug']) ?>/" target="_blank" rel="noopener">Skatīt rakstu</a>
<?php endif; ?>
    </div>

    <form method="post" action="/admin/post.php<?= $existing === null ? '?new=1' : '?slug=' . e(rawurlencode($existing['slug'])) ?>">
      <?= ebt_csrf_field() ?>

      <div class="grid-2">
        <label class="field">
          <span class="field-label">Virsraksts</span>
          <input type="text" name="title" value="<?= e($post['title']) ?>" required>
        </label>
        <label class="field">
          <span class="field-label">Datums</span>
          <input type="date" name="date" value="<?= e($post['date']) ?>" required>
        </label>
      </div>

      <div class="grid-2">
        <label class="field">
          <span class="field-label">Adrese (slug)</span>
          <input type="text" name="slug" value="<?= e($post['slug']) ?>" placeholder="aizpildās no virsraksta">
          <span class="field-hint">Mazie burti, cipari un defises. Mainot adresi, vecā saite pārstāj darboties.</span>
        </label>
        <label class="field">
          <span class="field-label">Kategorija</span>
          <input type="text" name="category" value="<?= e($post['category']) ?>" placeholder="Piemēram, ES fondi">
        </label>
      </div>

      <label class="field">
        <span class="field-label">Īsais apraksts</span>
        <textarea name="excerpt" rows="3" required><?= e($post['excerpt']) ?></textarea>
        <span class="field-hint">Redzams rakstu sarakstā un raksta ievadā. Izmanto arī meta aprakstam.</span>
      </label>

      <label class="field">
        <span class="field-label">Atslēgvārdi</span>
        <input type="text" name="keywords" value="<?= e($keywordString) ?>" placeholder="ES fondi, energoefektivitāte, projektu vadība">
        <span class="field-hint">Atdaliet ar komatu. Parādās raksta beigās un meta atslēgvārdos.</span>
      </label>

      <label class="field">
        <span class="field-label">Raksta teksts</span>
        <textarea name="body" rows="20" required><?= e($post['body']) ?></textarea>
        <span class="field-hint">Tukša rinda sāk jaunu rindkopu. "## " rindas sākumā veido apakšvirsrakstu, "- " veido sarakstu.</span>
      </label>

      <div class="toolbar">
        <button class="button button--solid" type="submit"><?= $existing === null ? 'Publicēt rakstu' : 'Saglabāt izmaiņas' ?></button>
      </div>
    </form>

<?php
ebt_admin_foot();
