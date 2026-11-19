<?php
declare(strict_types=1);

/* Static page markup lives in inc/templates and is served through PHP, so the
   text overrides the client edits can stay outside the git checkout (in
   private/data/pages.json) and survive every deploy.
 *
 * An override is keyed by a hash of the original text plus its occurrence
 * number, so reordering or editing a template does not move an override onto a
 * different line. If the original text in a template changes, its key changes
 * and the old override is simply no longer applied. */

function ebt_page_names(): array
{
    return [
        'home' => 'Sākums',
        'pakalpojumi' => 'Pakalpojumi',
        'par-mums' => 'Par mums',
        'kontakti' => 'Kontakti',
        'privatuma-politika' => 'Privātuma politika',
        'sikdatnu-politika' => 'Sīkdatņu politika',
    ];
}

function ebt_page_urls(): array
{
    return [
        'home' => '/',
        'pakalpojumi' => '/pakalpojumi/',
        'par-mums' => '/par-mums/',
        'kontakti' => '/kontakti/',
        'privatuma-politika' => '/privatuma-politika/',
        'sikdatnu-politika' => '/sikdatnu-politika/',
    ];
}

function ebt_template_file(string $name): string
{
    return EBT_ROOT . '/inc/templates/' . $name . '.html';
}

function ebt_page_load(string $name): ?DOMDocument
{
    $file = ebt_template_file($name);
    if (!is_file($file)) {
        return null;
    }
    $html = file_get_contents($file);
    if ($html === false || $html === '') {
        return null;
    }
    $dom = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $ok = $dom->loadHTML($html, LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    return $ok ? $dom : null;
}

function ebt_page_text_nodes(DOMDocument $dom): array
{
    $skipTags = ['script', 'style', 'noscript', 'template', 'head', 'title', 'meta', 'link'];
    $skipClasses = ['skip-link', 'breadcrumbs', 'site-header', 'site-footer', 'cookie-banner', 'dialog'];
    $nodes = [];
    $collect = static function (DOMNode $node) use (&$collect, &$nodes, $skipTags, $skipClasses): void {
        if ($node instanceof DOMElement) {
            if (in_array(strtolower($node->nodeName), $skipTags, true)) {
                return;
            }
            $class = ' ' . $node->getAttribute('class') . ' ';
            foreach ($skipClasses as $skipClass) {
                if (strpos($class, ' ' . $skipClass . ' ') !== false) {
                    return;
                }
            }
        }
        if ($node instanceof DOMText) {
            $text = $node->nodeValue;
            if ($text !== null && trim($text) !== '') {
                $nodes[] = $node;
            }
            return;
        }
        foreach ($node->childNodes as $child) {
            $collect($child);
        }
    };
    if ($dom->documentElement !== null) {
        $collect($dom->documentElement);
    }
    return $nodes;
}

function ebt_page_key(string $text, array &$seen): string
{
    $hash = substr(sha1($text), 0, 10);
    $seen[$hash] = ($seen[$hash] ?? 0) + 1;
    return $hash . '-' . $seen[$hash];
}

/* Node key => trimmed original text, in document order. */
function ebt_page_node_keys(DOMDocument $dom): array
{
    $seen = [];
    $keys = [];
    foreach (ebt_page_text_nodes($dom) as $node) {
        $keys[] = ebt_page_key(trim((string) $node->nodeValue), $seen);
    }
    return $keys;
}

function ebt_page_group(DOMElement $element): string
{
    $node = $element;
    while ($node instanceof DOMElement) {
        $name = strtolower($node->nodeName);
        if ($name === 'section' || $name === 'dialog' || $name === 'header' || $name === 'footer') {
            $labelledBy = $node->getAttribute('aria-labelledby');
            if ($labelledBy !== '') {
                $label = $node->ownerDocument->getElementById($labelledBy);
                if ($label !== null && trim($label->textContent) !== '') {
                    return trim($label->textContent);
                }
            }
            foreach (['h1', 'h2', 'h3'] as $heading) {
                $found = $node->getElementsByTagName($heading);
                if ($found->length > 0 && trim($found->item(0)->textContent) !== '') {
                    return trim($found->item(0)->textContent);
                }
            }
            return 'Sadaļa';
        }
        $node = $node->parentNode;
    }
    return 'Cita informācija';
}

function ebt_page_overrides(): array
{
    $stored = ebt_json_read(EBT_DATA . '/pages.json');
    return is_array($stored) ? $stored : [];
}

function ebt_page_fields(string $name): array
{
    $dom = ebt_page_load($name);
    if ($dom === null) {
        return [];
    }
    $overrides = ebt_page_overrides()[$name] ?? [];
    $multilineTags = ['p', 'li', 'td', 'th', 'dd', 'blockquote', 'figcaption', 'textarea'];
    $seen = [];
    $fields = [];
    foreach (ebt_page_text_nodes($dom) as $node) {
        $parent = $node->parentNode;
        $tag = $parent instanceof DOMElement ? strtolower($parent->nodeName) : 'text';
        $original = trim((string) $node->nodeValue);
        $key = ebt_page_key($original, $seen);
        $text = isset($overrides[$key]) && is_string($overrides[$key]) ? $overrides[$key] : $original;
        $fields[] = [
            'key' => $key,
            'tag' => $tag,
            'group' => $parent instanceof DOMElement ? ebt_page_group($parent) : 'Cita informācija',
            'text' => $text,
            'original' => $original,
            'changed' => isset($overrides[$key]),
            'multiline' => in_array($tag, $multilineTags, true) || mb_strlen($text) > 90,
        ];
    }
    return $fields;
}

/* Stores only texts that differ from the template, drops the rest. */
function ebt_page_save(string $name, array $values): int
{
    $dom = ebt_page_load($name);
    if ($dom === null) {
        return 0;
    }
    $originals = [];
    $seen = [];
    foreach (ebt_page_text_nodes($dom) as $node) {
        $original = trim((string) $node->nodeValue);
        $originals[ebt_page_key($original, $seen)] = $original;
    }

    $all = ebt_page_overrides();
    $current = isset($all[$name]) && is_array($all[$name]) ? $all[$name] : [];
    $saved = 0;
    foreach ($values as $key => $value) {
        $key = (string) $key;
        if (!is_string($value) || !isset($originals[$key])) {
            continue;
        }
        $clean = trim(str_replace(["\r\n", "\r"], "\n", $value));
        if ($clean === $originals[$key]) {
            unset($current[$key]);
        } else {
            $current[$key] = $clean;
            $saved++;
        }
    }
    if ($current) {
        $all[$name] = $current;
    } else {
        unset($all[$name]);
    }
    ebt_json_write(EBT_DATA . '/pages.json', $all);
    return $saved;
}

function ebt_page_reset(string $name): void
{
    $all = ebt_page_overrides();
    if (isset($all[$name])) {
        unset($all[$name]);
        ebt_json_write(EBT_DATA . '/pages.json', $all);
    }
}

function ebt_page_render(string $name): void
{
    $file = ebt_template_file($name);
    if (!is_file($file)) {
        http_response_code(404);
        echo 'Lapa nav atrasta.';
        return;
    }
    $html = (string) file_get_contents($file);
    $overrides = ebt_page_overrides()[$name] ?? [];

    if ($overrides) {
        $dom = ebt_page_load($name);
        if ($dom !== null) {
            $seen = [];
            foreach (ebt_page_text_nodes($dom) as $node) {
                $key = ebt_page_key(trim((string) $node->nodeValue), $seen);
                if (!isset($overrides[$key]) || !is_string($overrides[$key])) {
                    continue;
                }
                $original = (string) $node->nodeValue;
                preg_match('/^(\s*)(.*?)(\s*)$/s', $original, $parts);
                $node->nodeValue = ($parts[1] ?? '') . $overrides[$key] . ($parts[3] ?? '');
            }
            $rendered = $dom->saveHTML();
            if ($rendered !== false) {
                $html = $rendered;
            }
        }
    }

    header('Content-Type: text/html; charset=utf-8');
    echo $html;
}
