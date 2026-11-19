<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/posts.php';
require EBT_ROOT . '/inc/settings.php';
require EBT_ROOT . '/inc/layout.php';

$perPage = 6;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$result = ebt_posts_paginate($page, $perPage);
$base = '../';

$cards = '';
foreach ($result['items'] as $post) {
    $cards .= ebt_post_card($post, $base) . "\n";
}
$pagination = ebt_pagination_html($result, $base);

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'page' => $result['page'],
        'pages' => $result['pages'],
        'total' => $result['total'],
        'html' => $cards,
        'pagination' => $pagination,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

ebt_head(
    'Jaunumi',
    'Atbalsta programmu jaunumi un skaidrojumi uzņēmējiem no EB Technologies.',
    $base,
    '/jaunumi/' . ($result['page'] > 1 ? '?page=' . $result['page'] : '')
);
ebt_header($base, 'jaunumi');
?>

    <div class="wrap breadcrumbs">
      <ol>
        <li><a href="<?= e($base) ?>">Sākums</a></li>
        <li>Jaunumi</li>
      </ol>
    </div>

    <section class="page-head">
      <div class="wrap">
        <p class="eyebrow">Jaunumi</p>
        <h1><?= e(ebt_setting('news_title')) ?></h1>
        <p class="lead"><?= e(ebt_setting('news_lead')) ?></p>
      </div>
    </section>

    <section class="section" aria-labelledby="articles-title">
      <div class="wrap">
        <p class="section-label">Arhīvs</p>
        <h2 class="visually-hidden" id="articles-title">Raksti</h2>
        <div class="news-grid" id="news-list" tabindex="-1" data-page="<?= (int) $result['page'] ?>">
<?= $cards ?>
        </div>
        <p class="visually-hidden" id="news-status" role="status" aria-live="polite"></p>
        <div id="news-pagination">
<?= $pagination ?>
        </div>
        <p class="note" style="margin-top: 32px;"><?= e(ebt_setting('news_note')) ?></p>
      </div>
    </section>

    <section class="section section--inverse">
      <div class="wrap cta">
        <div>
          <p class="section-label">Aktuāli</p>
          <h2>Neesat pārliecināts, kura programma ir aktuāla?</h2>
          <p>Pastāstiet par savu projektu, un mēs pārbaudīsim pieejamās iespējas.</p>
        </div>
        <div class="actions">
          <a class="button button--solid" href="<?= e($base) ?>kontakti/#pieteikums">Pieteikt izvērtēšanu</a>
          <a class="button" href="tel:+37129179536">Zvanīt <?= EBT_PHONE ?></a>
        </div>
      </div>
    </section>

<?php
ebt_footer($base);
echo '  <script src="' . e($base) . 'js/blog.js?v=20261119-2"></script>' . "\n";
echo '</body>' . "\n";
echo '</html>' . "\n";
