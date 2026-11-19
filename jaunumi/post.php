<?php
declare(strict_types=1);

/* Rendered for /jaunumi/<slug>/ through the rewrite rule in .htaccess. */

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/posts.php';
require EBT_ROOT . '/inc/settings.php';
require EBT_ROOT . '/inc/layout.php';

$postSlug = isset($_GET['slug']) && is_string($_GET['slug']) ? $_GET['slug'] : '';

/* Keep one canonical URL per post: /jaunumi/<slug>/ from the rewrite rule. */
$requestPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if ($postSlug !== '' && strpos($requestPath, '/post.php') !== false) {
    ebt_redirect('/jaunumi/' . rawurlencode($postSlug) . '/');
}

$post = $postSlug !== '' ? ebt_post_find($postSlug) : null;
$base = '../../';

if ($post === null) {
    http_response_code(404);
    ebt_head('Lapa nav atrasta', 'Raksts nav atrasts.', $base, '/jaunumi/');
    ebt_header($base, 'jaunumi');
    echo '    <section class="page-head">' . "\n";
    echo '      <div class="wrap">' . "\n";
    echo '        <p class="eyebrow">404</p>' . "\n";
    echo '        <h1>Raksts nav atrasts</h1>' . "\n";
    echo '        <p class="lead">Iespējams, raksts ir pārvietots vai dzēsts.</p>' . "\n";
    echo '        <p><a class="arrow-link" href="' . e($base) . 'jaunumi/">Atpakaļ uz jaunumiem</a></p>' . "\n";
    echo '      </div>' . "\n";
    echo '    </section>' . "\n";
    ebt_footer($base);
    echo '</body>' . "\n</html>\n";
    exit;
}

$keywords = ebt_post_keywords($post);
$neighbours = ebt_post_neighbours($post['slug']);
$previous = $neighbours[0];
$next = $neighbours[1];
$canonical = '/jaunumi/' . $post['slug'] . '/';

ebt_head($post['title'], $post['excerpt'], $base, $canonical, 'article', implode(', ', $keywords));
ebt_header($base, 'jaunumi');
?>

    <div class="wrap breadcrumbs">
      <ol>
        <li><a href="<?= e($base) ?>">Sākums</a></li>
        <li><a href="<?= e($base) ?>jaunumi/">Jaunumi</a></li>
        <li><?= e($post['title']) ?></li>
      </ol>
    </div>

    <header class="page-head">
      <div class="wrap article-body">
        <div class="article-meta">
          <span class="tag"><?= e(ebt_post_year($post)) ?></span>
          <time datetime="<?= e($post['date']) ?>"><?= e(ebt_post_date_label($post)) ?></time>
<?php if (!empty($post['category'])): ?>
          <span><?= e($post['category']) ?></span>
<?php endif; ?>
        </div>
        <h1><?= e($post['title']) ?></h1>
        <p class="lead"><?= e($post['excerpt']) ?></p>
      </div>
    </header>

    <section class="section">
      <div class="wrap article-body">
<?= ebt_render_body((string) $post['body']) ?>
<?php if ($keywords): ?>
        <ul class="keyword-list" aria-label="Atslēgvārdi">
<?php foreach ($keywords as $keyword): ?>
          <li><?= e($keyword) ?></li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
        <p class="note"><?= e(ebt_setting('post_note')) ?></p>
      </div>
    </section>

<?php if ($previous !== null || $next !== null): ?>
    <nav class="section section--tight post-nav" aria-label="Citi raksti">
      <div class="wrap">
        <div class="post-nav-grid">
<?php if ($previous !== null): ?>
          <a class="post-nav-item" href="<?= e($base) ?>jaunumi/<?= e(rawurlencode($previous['slug'])) ?>/" rel="prev">
            <span class="post-nav-label">Iepriekšējais raksts</span>
            <span class="post-nav-title"><?= e($previous['title']) ?></span>
          </a>
<?php else: ?>
          <span class="post-nav-item is-empty"></span>
<?php endif; ?>
<?php if ($next !== null): ?>
          <a class="post-nav-item post-nav-item--next" href="<?= e($base) ?>jaunumi/<?= e(rawurlencode($next['slug'])) ?>/" rel="next">
            <span class="post-nav-label">Nākamais raksts</span>
            <span class="post-nav-title"><?= e($next['title']) ?></span>
          </a>
<?php endif; ?>
        </div>
        <a class="arrow-link" href="<?= e($base) ?>jaunumi/">Visi jaunumi</a>
      </div>
    </nav>
<?php endif; ?>

    <section class="section section--inverse">
      <div class="wrap cta">
        <div>
          <p class="section-label">Jautājumi</p>
          <h2><?= e(ebt_setting('post_cta_title')) ?></h2>
          <p><?= e(ebt_setting('post_cta_text')) ?></p>
        </div>
        <div class="actions">
          <a class="button button--solid" href="<?= e($base) ?>kontakti/#pieteikums">Pieteikt izvērtēšanu</a>
        </div>
      </div>
    </section>

<?php
ebt_footer($base);
echo '</body>' . "\n</html>\n";
