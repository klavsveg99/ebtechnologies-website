<?php
declare(strict_types=1);

/* Copy for the blog pages that is not a post: the archive intro, the note
   under the article body and the closing call to action. */

function ebt_settings_defaults(): array
{
    return [
        'news_title' => 'Atbalsta programmu jaunumi',
        'news_lead' => 'Būtiskākās izmaiņas un skaidrojumi par atbalsta iespējām uzņēmējiem. Konkrētu programmu aktuālos nosacījumus pārbaudām katram projektam atsevišķi.',
        'news_note' => 'Raksti atspoguļo situāciju publicēšanas brīdī. Pirms pieteikšanās pārbaudām aktuālos termiņus un nosacījumus.',
        'post_note' => 'Raksts atspoguļo situāciju publicēšanas brīdī. Programmu nosacījumi un termiņi laika gaitā mainās, tāpēc aktuālo informāciju pārbaudām katram projektam atsevišķi.',
        'post_cta_title' => 'Vai jūsu projektam ir pieejams atbalsts?',
        'post_cta_text' => 'Izvērtēsim situāciju un palīdzēsim saprast iespējas.',
    ];
}

function ebt_settings(): array
{
    $stored = ebt_json_read(EBT_DATA . '/settings.json');
    if (!is_array($stored)) {
        $stored = [];
    }
    return array_merge(ebt_settings_defaults(), $stored);
}

function ebt_setting(string $key): string
{
    $settings = ebt_settings();
    return (string) ($settings[$key] ?? '');
}

function ebt_settings_save(array $values): bool
{
    $current = ebt_json_read(EBT_DATA . '/settings.json');
    $current = is_array($current) ? $current : [];
    foreach (ebt_settings_defaults() as $key => $default) {
        if (array_key_exists($key, $values)) {
            $current[$key] = (string) $values[$key];
        }
    }
    return ebt_json_write(EBT_DATA . '/settings.json', $current);
}
