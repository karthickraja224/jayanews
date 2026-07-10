# Jaya Plus — Public Frontend

A complete, production-ready public website for your `jayaplus_admin` panel — built directly
against your real database tables (`tbl_news`, `tbl_categories`, `tbl_slider`, `tbl_breaking_news`,
`tbl_ads`, `tbl_comments`, `tbl_settings`, `tbl_pages`). This is **not** a static template —
every page is live PHP that reads straight from MySQL, exactly like the admin panel.

It has been tested end-to-end against a seeded copy of your schema (homepage, category pages,
article pages, search, static pages, comment form, contact form, pagination, the EN/TA language
toggle, view counting, robots.txt and sitemap.xml) with zero PHP errors.

## Design

**"Newsroom Wire"** — an editorial broadsheet system built for a live digital newsroom:
- Oxblood/maroon masthead + warm newsprint paper background + saffron accents, Source Serif 4
  headlines paired with Noto Sans Tamil so English and Tamil headlines share one visual voice.
- A teleprinter-style **wire ticker** for breaking news (pulled from `tbl_breaking_news`) —
  the one signature device the rest of the design stays disciplined around.
- Category colours (from `tbl_categories.color`) drive badges, nav underlines, and section accents
  automatically — no design work needed when you add a new category in the admin.
- Fully responsive: mobile hamburger nav, swipeable Editor's Picks carousel, stacked layout.
- Bilingual EN/TA throughout (UI labels + your bilingual category names), using the same
  `$_SESSION['lang']` convention as the admin panel.

## File map

```
index.php            Homepage — hero, breaking ticker, Editor's Picks slider, category sections, sidebar
category.php         Category + subcategory listing, paginated
news.php             Single article — share buttons, tags, related stories, comments
search.php           Search results
page.php             About / Privacy / Terms (and Contact, with a working contact form)
comment-submit.php   Handles comment form POSTs (stored as 'pending' for your admin to approve)
contact-submit.php   Handles contact form POSTs (stored in tbl_contact_messages)
lang-switch.php      EN/TA toggle
robots.php           Serves /robots.txt (uses your SEO settings, or a sensible default)
sitemap.php          Serves /sitemap.xml (homepage, categories, pages, latest 1000 articles)
404.php              Not-found page

config/database.php    DB connection — same `jayanews` database as the admin panel
config/constants.php   Site URL, upload path, pagination sizes, pretty-URL toggle
includes/              header.php, footer.php, functions.php (all data queries + helpers), lang-strings.php

assets/css/style.css  The full design system
assets/js/main.js     Mobile nav, ticker pause-on-hover, carousel, back-to-top, copy-link

.htaccess             Pretty URLs (optional — see below)
extra_tables.sql      Adds tbl_contact_messages + safe default About/Contact/Privacy/Terms pages
```

## Setup (3 steps)

**1. Place the folder.** Put this `jayaplus_frontend` folder's *contents* at your site root, as a
sibling of `jayaplus_admin`:

```
yourdomain.com/              <- contents of this package go here
yourdomain.com/jayaplus_admin/   <- your existing admin panel
```

**2. Database.** Open `config/database.php` and confirm the four values match what's already in
your admin panel's `config/database.php` (host/user/pass/database name) — they should be identical
since this connects to the *same* database.

**3. Run the extra SQL once.** In phpMyAdmin, run `extra_tables.sql` against your `jayanews`
database. It only adds a `tbl_contact_messages` table and a couple of safe placeholder rows in
`tbl_pages` (using `INSERT IGNORE`, so it will never overwrite content you've already written there).

> ⚠️ If you import it from the command line instead of phpMyAdmin, make sure to use
> `mysql --default-character-set=utf8mb4 -u youruser -p jayanews < extra_tables.sql` —
> otherwise Tamil text can get mangled on import. phpMyAdmin handles this correctly by default.

That's it — visit your domain and the homepage should pull in your real categories, news, slider
and settings automatically.

## Two things worth checking in the admin panel

- **`tbl_settings`** — `site_url`, `site_name`, `whatsapp_number`, social URLs, `default_meta_description`,
  `og_image`, `google_analytics_id` etc. are all read live from here (Settings → General/SEO/Social).
  Fill these in and the frontend picks them up with no code changes.
- **Image paths** — `config/constants.php` has one line, `ADMIN_UPLOAD_URL`, pointing at
  `/jayaplus_admin/`. If your admin folder is named or located differently, update only that line.

## Pretty URLs

By default the site uses clean URLs like `/cinema-block-buster-review` and
`/category/cinema` (this matches the link your admin's "View site" button already generates —
`site_url + slug`). These need the included `.htaccess` + `mod_rewrite`, which almost all shared
PHP hosts support.

If your host doesn't support it, open `config/constants.php` and set:
```php
define('PRETTY_URLS', false);
```
Every link on the site will automatically switch to `?slug=...` style URLs instead — nothing else
needs to change.

## Notes on content

- Article body HTML (`tbl_news.content`) is rendered as-is, since it's your own admin-authored
  content (from the TinyMCE editor) — same trust model as any CMS.
- Comments submitted on the site are stored as `pending` and show up in your existing
  Comments moderation screen in the admin panel; only `approved` comments are shown publicly.
- The comment and contact forms both have an invisible honeypot field for basic spam filtering.
- Article view counts increment once per visitor session (not on every refresh).
- Requires the `mysqli` and `mbstring` PHP extensions — both are standard on virtually every
  PHP host, and `mysqli` is already required by your admin panel.
