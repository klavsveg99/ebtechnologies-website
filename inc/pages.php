<?php
declare(strict_types=1);

/* Page copy is edited in place: the admin reads a page, exposes every visible
   text node as a field, and writes the changed text back. Structure, markup
   and links are never touched, so no section can be added or removed. */

function ebt_pages(): array
{
    return [
        'index.html' => 'Sākums',
        'pakalpojumi/index.html' => 'Pakalpojumi',
        'par-mums/index.html' => 'Par mums',
        'kontakti/index.html' => 'Kontakti',
        'privatuma-politika/index.html' => 'Privātuma politika',
        'sikdatnu-politika/index.html' => 'Sīkdatņu politika',
    ];
}

function ebt_page_file(string $rel): string
{
    return EBT_ROOT . '/' . $rel;
}

function ebt_page_load(string $rel): ?DOMDocument
{
    $file = ebt_page_file($rel);
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

function ebt_page_group(DOMElement $element): string
{
    $node = $element;
    while ($node instanceof DOMElement) {
        $name = strtolower($node->nodeName);
        if ($name === 'section' || $name === 'dialog' || $name === 'header' || $name === 'footer') {
            if ($name === 'header' && $node->getAttribute('class') === 'site-header') {
                return 'Galvene';
            }
            if ($name === 'footer') {
                return 'Kājene';
            }
            if ($name === 'dialog') {
                return 'Sīkdatņu dialogs';
            }
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

function ebt_page_fields(DOMDocument $dom): array
{
    $multilineTags = ['p', 'li', 'td', 'th', 'dd', 'blockquote', 'figcaption', 'textarea'];
    $fields = [];
    foreach (ebt_page_text_nodes($dom) as $index => $node) {
        $parent = $node->parentNode;
        $tag = $parent instanceof DOMElement ? strtolower($parent->nodeName) : 'text';
        $text = (string) $node->nodeValue;
        $trimmed = trim($text);
        $fields[] = [
            'key' => $index,
            'tag' => $tag,
            'group' => $parent instanceof DOMElement ? ebt_page_group($parent) : 'Cita informācija',
            'text' => $trimmed,
            'multiline' => in_array($tag, $multilineTags, true) || mb_strlen($trimmed) > 90,
        ];
    }
    return $fields;
}

function ebt_page_save(string $rel, array $values): int
{
    $dom = ebt_page_load($rel);
    if ($dom === null) {
        return 0;
    }
    $nodes = ebt_page_text_nodes($dom);
    $saved = 0;
    foreach ($values as $key => $value) {
        $key = (int) $key;
        if (!is_string($value) || !isset($nodes[$key])) {
            continue;
        }
        $node = $nodes[$key];
        $original = (string) $node->nodeValue;
        preg_match('/^(\s*)(.*?)(\s*)$/s', $original, $parts);
        $lead = $parts[1] ?? '';
        $trail = $parts[3] ?? '';
        $clean = trim(str_replace(["\r\n", "\r"], "\n", $value));
        $node->nodeValue = $lead . $clean . $trail;
        $saved++;
    }
    if ($saved === 0) {
        return 0;
    }
    $html = $dom->saveHTML();
    if ($html === false) {
        return 0;
    }
    $file = ebt_page_file($rel);
    $tmp = $file . '.tmp';
    if (file_put_contents($tmp, $html) === false) {
        return 0;
    }
    if (!rename($tmp, $file)) {
        @unlink($tmp);
        return 0;
    }
    return $saved;
}
