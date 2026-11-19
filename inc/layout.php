<?php
declare(strict_types=1);

/* Layout for the PHP-rendered pages (blog listing, single post). The static
   pages keep their own markup; this mirrors it so both stay visually identical. */

function ebt_head(string $title, string $description, string $base, string $canonical, string $type = 'website', string $keywords = ''): void
{
    $css = $base . 'css/wireframe.css?v=20261119-2';
    echo '<!DOCTYPE html>' . "\n";
    echo '<html lang="lv">' . "\n";
    echo '<head>' . "\n";
    echo '  <meta charset="UTF-8">' . "\n";
    echo '  <meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
    echo '  <link rel="icon" href="data:,">' . "\n";
    echo '  <title>' . e($title) . ' | ' . EBT_NAME . '</title>' . "\n";
    echo '  <meta name="description" content="' . e($description) . '">' . "\n";
    if ($keywords !== '') {
        echo '  <meta name="keywords" content="' . e($keywords) . '">' . "\n";
    }
    echo '  <meta name="robots" content="noindex, nofollow">' . "\n";
    echo '  <link rel="canonical" href="' . e(EBT_SITE . $canonical) . '">' . "\n";
    echo '  <meta property="og:title" content="' . e($title) . '">' . "\n";
    echo '  <meta property="og:description" content="' . e($description) . '">' . "\n";
    echo '  <meta property="og:type" content="' . e($type) . '">' . "\n";
    echo '  <meta property="og:url" content="' . e(EBT_SITE . $canonical) . '">' . "\n";
    echo '  <meta property="og:locale" content="lv_LV">' . "\n";
    echo '  <link rel="stylesheet" href="' . e($css) . '">' . "\n";
    echo '</head>' . "\n";
}

function ebt_header(string $base, string $current = ''): void
{
    $nav = [
        'pakalpojumi' => ['Pakalpojumi', $base . 'pakalpojumi/'],
        'par-mums' => ['Par mums', $base . 'par-mums/'],
        'jaunumi' => ['Jaunumi', $base . 'jaunumi/'],
        'kontakti' => ['Kontakti', $base . 'kontakti/'],
    ];
    echo '<body>' . "\n";
    echo '  <a class="skip-link" href="#content">Pāriet uz saturu</a>' . "\n\n";
    echo '  <header class="site-header">' . "\n";
    echo '    <div class="wrap header-inner">' . "\n";
    echo '      <a class="brand" href="' . e($base) . '">EB Technologies</a>' . "\n";
    echo '      <button class="menu-toggle" id="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav">Izvēlne</button>' . "\n";
    echo '      <nav class="site-nav" id="site-nav" aria-label="Galvenā navigācija">' . "\n";
    foreach ($nav as $key => $item) {
        $currentAttr = $key === $current ? ' aria-current="page"' : '';
        echo '        <a href="' . e($item[1]) . '"' . $currentAttr . '>' . e($item[0]) . '</a>' . "\n";
    }
    echo '        <a class="button button--solid" href="' . e($base) . 'kontakti/#pieteikums">Kontakti</a>' . "\n";
    echo '      </nav>' . "\n";
    echo '      <a class="header-phone" href="tel:+37129179536">' . EBT_PHONE . '</a>' . "\n";
    echo '    </div>' . "\n";
    echo '  </header>' . "\n\n";
    echo '  <main id="content" tabindex="-1">' . "\n";
}

function ebt_footer(string $base): void
{
    echo '  </main>' . "\n\n";
    echo '  <footer class="site-footer">' . "\n";
    echo '    <div class="wrap footer-grid">' . "\n";
    echo '      <div class="footer-brand">' . "\n";
    echo '        <p class="brand">EB Technologies</p>' . "\n";
    echo '        <p>Valsts atbalsta piesaiste, projektu vadība un inovāciju attīstība kopš 2020. gada.</p>' . "\n";
    echo '      </div>' . "\n";
    echo '      <div>' . "\n";
    echo '        <h2 class="footer-heading">Pakalpojumi</h2>' . "\n";
    echo '        <ul class="footer-list">' . "\n";
    echo '          <li><a href="' . e($base) . 'pakalpojumi/#atbalsts">Atbalsta piesaiste</a></li>' . "\n";
    echo '          <li><a href="' . e($base) . 'pakalpojumi/#investicijas">Investīciju stratēģija</a></li>' . "\n";
    echo '          <li><a href="' . e($base) . 'pakalpojumi/#inovacijas">Inovācijas</a></li>' . "\n";
    echo '        </ul>' . "\n";
    echo '      </div>' . "\n";
    echo '      <div>' . "\n";
    echo '        <h2 class="footer-heading">Uzņēmums</h2>' . "\n";
    echo '        <ul class="footer-list">' . "\n";
    echo '          <li><a href="' . e($base) . 'par-mums/">Par mums</a></li>' . "\n";
    echo '          <li><a href="' . e($base) . 'jaunumi/">Jaunumi</a></li>' . "\n";
    echo '          <li><a href="' . e($base) . 'kontakti/">Kontakti</a></li>' . "\n";
    echo '        </ul>' . "\n";
    echo '      </div>' . "\n";
    echo '      <div>' . "\n";
    echo '        <h2 class="footer-heading">Kontakti</h2>' . "\n";
    echo '        <ul class="footer-list">' . "\n";
    echo '          <li><a href="tel:+37129179536">' . EBT_PHONE . '</a></li>' . "\n";
    echo '          <li><a href="mailto:info@ebtechnologies.lv">info@ebtechnologies.lv</a></li>' . "\n";
    echo '          <li><a href="https://wa.me/37129179536">WhatsApp</a></li>' . "\n";
    echo '        </ul>' . "\n";
    echo '      </div>' . "\n";
    echo '    </div>' . "\n";
    echo '    <div class="wrap footer-legal">' . "\n";
    echo '      <p>© 2026 EB Technologies · Reģ. nr. LV40203255838</p>' . "\n";
    echo '      <p>' . "\n";
    echo '        <a href="' . e($base) . 'privatuma-politika/">Privātuma politika</a> ·' . "\n";
    echo '        <a href="' . e($base) . 'sikdatnu-politika/">Sīkdatņu politika</a> ·' . "\n";
    echo '        <button class="link-button" id="cookie-settings-footer" type="button">Sīkdatņu iestatījumi</button>' . "\n";
    echo '      </p>' . "\n";
    echo '    </div>' . "\n";
    echo '  </footer>' . "\n\n";
    echo '  <section class="cookie-banner" id="cookie-banner" aria-label="Sīkdatņu izvēle" hidden>' . "\n";
    echo '    <div class="cookie-inner">' . "\n";
    echo '      <h2>Sīkdatņu izvēle</h2>' . "\n";
    echo '      <p>Nepieciešamās sīkdatnes nodrošina vietnes darbību. Ar jūsu atļauju varam izmantot arī analītikas un mārketinga sīkdatnes. <a href="' . e($base) . 'sikdatnu-politika/">Sīkdatņu politika</a>.</p>' . "\n";
    echo '      <div class="cookie-actions">' . "\n";
    echo '        <button class="button button--solid" id="cookie-accept" type="button">Atļaut visas</button>' . "\n";
    echo '        <button class="button" id="cookie-necessary" type="button">Tikai nepieciešamās</button>' . "\n";
    echo '        <button class="link-button" id="cookie-open-settings" type="button">Iestatījumi</button>' . "\n";
    echo '      </div>' . "\n";
    echo '    </div>' . "\n";
    echo '  </section>' . "\n\n";
    echo '  <dialog class="dialog" id="cookie-dialog" aria-labelledby="cookie-dialog-title">' . "\n";
    echo '    <div class="dialog-inner">' . "\n";
    echo '      <button class="dialog-close" id="cookie-close" type="button" aria-label="Aizvērt"></button>' . "\n";
    echo '      <h2 id="cookie-dialog-title">Sīkdatņu iestatījumi</h2>' . "\n";
    echo '      <p>Izvēlieties, kuras sīkdatnes drīkstam izmantot. Nepieciešamās ir vajadzīgas vietnes darbībai.</p>' . "\n";
    echo '      <label class="cookie-option">' . "\n";
    echo '        <input type="checkbox" checked disabled>' . "\n";
    echo '        <span><strong>Nepieciešamās</strong><span>Vietnes darbībai un jūsu izvēles saglabāšanai.</span></span>' . "\n";
    echo '      </label>' . "\n";
    echo '      <label class="cookie-option">' . "\n";
    echo '        <input id="cookie-analytics" type="checkbox">' . "\n";
    echo '        <span><strong>Analītikas</strong><span>Palīdz saprast, kuras sadaļas tiek apmeklētas. Šobrīd nav aktīvu analītikas rīku.</span></span>' . "\n";
    echo '      </label>' . "\n";
    echo '      <label class="cookie-option">' . "\n";
    echo '        <input id="cookie-marketing" type="checkbox">' . "\n";
    echo '        <span><strong>Mārketinga</strong><span>Reklāmas un mērķēšanas rīkiem. Šobrīd nav aktīvu mārketinga rīku.</span></span>' . "\n";
    echo '      </label>' . "\n";
    echo '      <div class="dialog-actions">' . "\n";
    echo '        <button class="button button--solid" id="cookie-save" type="button">Saglabāt izvēli</button>' . "\n";
    echo '        <button class="button" id="cookie-accept-dialog" type="button">Atļaut visas</button>' . "\n";
    echo '      </div>' . "\n";
    echo '      <p class="form-note" style="margin-top: 14px;"><a href="' . e($base) . 'sikdatnu-politika/">Sīkdatņu politika</a></p>' . "\n";
    echo '    </div>' . "\n";
    echo '  </dialog>' . "\n\n";
    echo '  <script src="' . e($base) . 'js/main.js?v=20261111-5"></script>' . "\n";
}
