# EB Technologies website remake - wireframe prototype

Black and white content and functionality prototype for ebtechnologies.lv. It replaces the old WordPress presentation with the section structure, updated copy and interactions the new site needs, without visual design.

## Run locally

Serve the project root (clean URLs are directories):

```sh
python3 -m http.server 8080
```

Open http://localhost:8080/. Use a trailing slash on section URLs, for example `/pakalpojumi/`.

## Pages

| Route | Content |
| --- | --- |
| `/` | Hero, trust strip, client situations, eligible costs, three services, process, why us, sectors, about teaser, latest news, FAQ, application form |
| `/pakalpojumi/` | Approach, support directions (not individual programme detail), three detailed services, outcomes, process, collaboration models, common concerns, FAQ, CTA |
| `/par-mums/` | Company story, what we do, approach, experience metrics, own construction technology, contact person |
| `/jaunumi/` | News index with the three existing posts |
| `/jaunumi/atbalsta-programmu-jaunumi-atbalsts-zvejas-un-akvakulturas-produktu-apstradei/` | Large investment projects programme (2021) |
| `/jaunumi/atbalsta-programmu-jaunumi-norvegu-finansu-instrumenta-atbalsts/` | LAD support for farms and processing (2022) |
| `/jaunumi/atbalsta-programmu-jaunumi/` | Startup support changes (2021) |
| `/kontakti/` | Contact methods, requisites with copyable IBAN, full application form, first conversation steps, what to prepare |
| `/privatuma-politika/` | Privacy policy draft |
| `/sikdatnu-politika/` | Cookie policy and consent settings |

The three news slugs match the live WordPress URLs, so links and bookmarks survive the move off WordPress. Old navigation URLs (`/pakalpojumi/`, `/par-mums/`, `/kontakti/`, `/jaunumi/`) are also preserved.

## Interactions

- Accessible mobile menu: `aria-expanded`, Escape to close, closes on outside click and on resize to desktop.
- Cookie banner with "accept all", "necessary only" and a settings dialog with necessary, analytics and marketing categories. The choice is stored in `localStorage` under `eb-cookie-consent`; it can be reopened from the footer and from the cookie policy page.
- Contact forms on the homepage and contact page: required-field and e-mail validation, inline errors, honeypot, consent checkbox, loading state, success and error messages.
- FAQ uses native `<details>` so it works without JavaScript.
- Skip link, landmarks, visible focus styles, keyboard-operable dialogs and status messages with `aria-live`.

There are no analytics or marketing scripts yet. If they are added, they must wait for the matching consent category.

## Contact form backend

`contact.php` sends the form over SMTP with PHPMailer and returns JSON. It validates required fields, the e-mail address and field lengths, ignores submissions with a filled honeypot, and logs failures without exposing details to the visitor.

1. Run `composer install` to create `vendor/`.
2. Create `../private/config.php` (outside the web root on the live server) returning:

```php
<?php
return [
    'smtpHost' => 'smtp.example.com',
    'smtpUser' => 'info@ebtechnologies.lv',
    'smtpPass' => 'secret',
    'smtpPort' => 465,
    'contactTo' => 'info@ebtechnologies.lv',
];
```

Without PHP or the config the form shows a direct e-mail and phone fallback instead of pretending to send.

## Content notes

- Copy is taken from the live site as of 11 November 2026 and rewritten for clarity. Facts, contacts and requisites are kept exactly; typos in the old news posts are fixed.
- The client wants the main service copy to stay general about the kinds of institutions, funds and programmes EB Technologies works with. Individual programmes appear only as references inside news posts, not as a detailed programme catalogue.
- No testimonials, client names, project counts or response-time promises were invented. The old counters that displayed zero were replaced with verifiable facts (founded 2020, programmes, full-cycle delivery).
- The wireframe uses black and white, system fonts and dashed image placeholders on purpose. Real photography and branding come later.

## Before launch

- Review the privacy and cookie policy drafts with the client; both currently say that no analytics or marketing tools are active.
- Connect the form config and test delivery on Hostinger.
- Add real images, favicon, Open Graph image and the client's brand styling.
- Remove `<meta name="robots" content="noindex, nofollow">` and check canonical URLs.
- Add redirects only if any old WordPress URL is dropped; the content routes above are already unchanged.
- Add a sitemap and analytics after consent handling is confirmed.
