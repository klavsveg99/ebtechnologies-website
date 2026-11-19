<?php
declare(strict_types=1);

/* Initial archive for the blog. Once the admin saves, the archive moves to
   private/data/posts.json and this file is only a fallback. */

return array_merge(
    require __DIR__ . '/posts-seed/legacy.php',
    require __DIR__ . '/posts-seed/2022.php',
    require __DIR__ . '/posts-seed/2023.php',
    require __DIR__ . '/posts-seed/2024.php',
    require __DIR__ . '/posts-seed/2025.php',
    require __DIR__ . '/posts-seed/2026.php'
);
