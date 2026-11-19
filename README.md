# EB Technologies website

Static marketing pages plus a small PHP layer for the blog and a content admin.
Hosted on Hostinger; pushing to `master` deploys to the site root.

## Structure

- `inc/templates/*.html` - page markup, served by the matching `index.php` through
  `inc/pages.php` so client text edits can live outside the repo.
- `jaunumi/index.php` - blog archive with numbered pagination (AJAX-enhanced).
- `jaunumi/post.php` - single post renderer, reached through `/jaunumi/<slug>/` (see `.htaccess`).
- `inc/` - shared includes: layout, post store, page editor, settings, auth.
- `admin/` - content admin at `/admin/`.
- `private/` (outside the webroot, not in git) - `config.php`, `data/posts.json`,
  `data/settings.json`, `data/pages.json` (page text overrides), `admin.json`
  (password hash). Deploys re-clone the repo, so nothing the client edits may live
  in the checkout.

## Blog posts

Posts live in `private/data/posts.json`. Until the admin saves for the first time the
archive is read from `inc/posts-seed/`, so a fresh deploy already renders everything.
Post URLs are handled by the rewrite rule in `.htaccess`; no files are created per post.

Body text supports blank-line paragraphs, `## ` subheadings and `- ` list items.

## Admin

1. Set the password on the server:

   ```
   php tools/set-admin-password.php
   ```

   A random password is written to `private/admin-password.txt` (0600). Pass a value
   instead to choose one: `php tools/set-admin-password.php 'my-long-password'`.
   The password can also be changed later in the admin under "Parole".

2. Sign in at `/admin/`. Sections: pages (text only, structure is fixed), posts,
   blog page copy, password.

The page editor exposes each visible text node of a page's `<main>` as a field. Edits
are stored in `private/data/pages.json` keyed by a hash of the original text, so they
survive deploys and are re-applied at request time. A changed template line simply
drops its old override. Header, footer and cookie notice are shared chrome and are not
editable per page; elements with the `no-edit` class (charts and other structural
graphics) are skipped as well.

## Local preview

```
php -S 127.0.0.1:8099 -t .
```

`php -S` ignores `.htaccess`, so `/jaunumi/<slug>/` needs a router that maps the path
to `jaunumi/post.php?slug=...` when previewing locally.
