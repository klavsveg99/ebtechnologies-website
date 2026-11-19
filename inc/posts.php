<?php
declare(strict_types=1);

/* Blog posts live in private/data/posts.json. Until the admin saves for the
   first time the seed in inc/posts-seed.php is used, so a fresh deploy already
   renders the full archive. */

function ebt_seed_posts(): array
{
    static $seed = null;
    if ($seed === null) {
        $seed = require __DIR__ . '/posts-seed.php';
    }
    return $seed;
}

function ebt_posts_file(): string
{
    return EBT_DATA . '/posts.json';
}

function ebt_posts_all(): array
{
    $stored = ebt_json_read(ebt_posts_file());
    if ($stored === null || !isset($stored['posts']) || !is_array($stored['posts'])) {
        return ebt_seed_posts();
    }
    return array_values($stored['posts']);
}

function ebt_posts_save(array $posts): bool
{
    usort($posts, static function (array $a, array $b): int {
        return strcmp($b['date'], $a['date']);
    });
    return ebt_json_write(ebt_posts_file(), ['posts' => array_values($posts)]);
}

function ebt_posts_sorted(): array
{
    $posts = ebt_posts_all();
    usort($posts, static function (array $a, array $b): int {
        return strcmp($b['date'], $a['date']);
    });
    return $posts;
}

function ebt_post_find(string $slug): ?array
{
    foreach (ebt_posts_sorted() as $post) {
        if ($post['slug'] === $slug) {
            return $post;
        }
    }
    return null;
}

/* Neighbours in date order: previous is older, next is newer. */
function ebt_post_neighbours(string $slug): array
{
    $posts = ebt_posts_sorted();
    $index = null;
    foreach ($posts as $i => $post) {
        if ($post['slug'] === $slug) {
            $index = $i;
            break;
        }
    }
    if ($index === null) {
        return [null, null];
    }
    return [$posts[$index + 1] ?? null, $posts[$index - 1] ?? null];
}

function ebt_posts_paginate(int $page, int $perPage = 6): array
{
    $posts = ebt_posts_sorted();
    $total = count($posts);
    $pages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $pages));
    $items = array_slice($posts, ($page - 1) * $perPage, $perPage);
    return ['items' => $items, 'page' => $page, 'pages' => $pages, 'total' => $total];
}

function ebt_slugify(string $value): string
{
    $map = [
        'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k',
        'ļ' => 'l', 'ņ' => 'n', 'š' => 's', 'ū' => 'u', 'ž' => 'z',
        'Ā' => 'a', 'Č' => 'c', 'Ē' => 'e', 'Ģ' => 'g', 'Ī' => 'i', 'Ķ' => 'k',
        'Ļ' => 'l', 'Ņ' => 'n', 'Š' => 's', 'Ū' => 'u', 'Ž' => 'z',
    ];
    $value = strtr($value, $map);
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'raksts';
}

function ebt_post_year(array $post): string
{
    return substr((string) $post['date'], 0, 4);
}

function ebt_post_date_label(array $post): string
{
    $months = [1 => 'janvāris', 'februāris', 'marts', 'aprīlis', 'maijs', 'jūnijs',
        'jūlijs', 'augusts', 'septembris', 'oktobris', 'novembris', 'decembris'];
    $time = strtotime((string) $post['date']);
    if ($time === false) {
        return (string) $post['date'];
    }
    return (int) date('j', $time) . '. ' . $months[(int) date('n', $time)] . ' ' . date('Y', $time);
}

/* Small, predictable formatter for admin-written bodies: blank line starts a
   paragraph, "## " a subheading, "- " a list item. Everything is escaped. */
function ebt_render_body(string $body): string
{
    $blocks = preg_split('/\n{2,}/', trim(str_replace("\r\n", "\n", $body))) ?: [];
    $html = '';
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        $lines = preg_split('/\n/', $block) ?: [];
        $isList = true;
        foreach ($lines as $line) {
            if (strpos(trim($line), '- ') !== 0) {
                $isList = false;
                break;
            }
        }
        if ($isList) {
            $html .= "<ul>\n";
            foreach ($lines as $line) {
                $html .= '  <li>' . e(trim(substr(trim($line), 2))) . "</li>\n";
            }
            $html .= "</ul>\n";
            continue;
        }
        if (strpos($block, '## ') === 0) {
            $html .= '<h2>' . e(trim(substr($block, 3))) . "</h2>\n";
            continue;
        }
        $html .= '<p>' . e(preg_replace('/\s*\n\s*/', ' ', $block) ?? $block) . "</p>\n";
    }
    return $html;
}

function ebt_post_keywords(array $post): array
{
    $keywords = $post['keywords'] ?? [];
    if (is_string($keywords)) {
        $keywords = explode(',', $keywords);
    }
    $out = [];
    foreach ($keywords as $keyword) {
        $keyword = trim((string) $keyword);
        if ($keyword !== '') {
            $out[] = $keyword;
        }
    }
    return $out;
}

/* Card markup shared by the listing page and the AJAX fragment. */
function ebt_post_card(array $post, string $base): string
{
    $url = $base . 'jaunumi/' . rawurlencode($post['slug']) . '/';
    $html = '<article class="news-card">' . "\n";
    $html .= '  <span class="tag">' . e(ebt_post_year($post)) . '</span>' . "\n";
    $html .= '  <h3><a href="' . e($url) . '">' . e($post['title']) . '</a></h3>' . "\n";
    $html .= '  <p>' . e($post['excerpt']) . '</p>' . "\n";
    $html .= '  <a class="arrow-link" href="' . e($url) . '">Lasīt vairāk</a>' . "\n";
    $html .= '</article>';
    return $html;
}

function ebt_pagination_html(array $result, string $base): string
{
    if ($result['pages'] < 2) {
        return '';
    }
    $page = $result['page'];
    $pages = $result['pages'];
    $html = '<nav class="pagination" aria-label="Lapas">' . "\n";
    $html .= '  <ul>' . "\n";

    if ($page > 1) {
        $html .= '    <li><a class="pagination-link" href="' . e($base) . 'jaunumi/?page=' . ($page - 1) . '" rel="prev">Iepriekšējā</a></li>' . "\n";
    } else {
        $html .= '    <li><span class="pagination-link is-disabled">Iepriekšējā</span></li>' . "\n";
    }

    for ($i = 1; $i <= $pages; $i++) {
        if ($i === $page) {
            $html .= '    <li><span class="pagination-link is-current" aria-current="page">' . $i . '</span></li>' . "\n";
        } else {
            $html .= '    <li><a class="pagination-link" href="' . e($base) . 'jaunumi/?page=' . $i . '">' . $i . '</a></li>' . "\n";
        }
    }

    if ($page < $pages) {
        $html .= '    <li><a class="pagination-link" href="' . e($base) . 'jaunumi/?page=' . ($page + 1) . '" rel="next">Nākamā</a></li>' . "\n";
    } else {
        $html .= '    <li><span class="pagination-link is-disabled">Nākamā</span></li>' . "\n";
    }

    $html .= '  </ul>' . "\n";
    $html .= '</nav>';
    return $html;
}
