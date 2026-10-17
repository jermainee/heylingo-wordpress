# Heylingo for WordPress

Make your WordPress site multilingual. Heylingo translates your pages into more than 30 languages
with AI, keeps every translation in your site's translation memory and lets you correct any
sentence right on your live site.

[Website](https://heylingo.io) | [Pricing](https://heylingo.io/#pricing) | [Dashboard](https://dashboard.heylingo.io) | [Support](mailto:info@heylingo.io)

## Why Heylingo

- **Live in minutes.** Activate the plugin and subscribe with your domain. No API key, no
  translation project, no changes to your theme or content.
- **Translations that read naturally.** Whole sentences are translated with your site's context,
  tone and glossary in mind, not word by word.
- **Translated once, served from memory.** Each new sentence is translated once and stored for
  your site. Every later visitor gets it straight from your translation memory.
- **Correct it where you see it.** Open your site from the dashboard, click a sentence and type
  your version. It is live for every visitor within about a minute, and Heylingo never translates
  over it again.
- **Every language included.** All languages, including regional variants such as Brazilian and
  European Portuguese, are available from the start. There is no fee per language.
- **Your price follows your content, not your traffic.** Page views and visitors cost nothing.
- **Privacy-friendly.** No cookies, no visitor tracking, and a data processing agreement is part
  of the terms.

## How it works

The plugin adds the Heylingo script to every page of your site. When someone visits, the script
picks their preferred language, adds a language switcher and replaces the page text with
translations from your translation memory. Content that appears later, such as sliders, filters
or content loaded on demand, is translated as well.

Your WordPress content stays exactly as it is. There are no duplicate pages, no translated posts
to maintain and no changes to your database. Deactivate the plugin and your site is back to its
original state.

## Requirements

- WordPress 5.7 or later
- PHP 7.4 or later
- A Heylingo subscription for your site's domain

## Installation

The plugin is not yet listed in the WordPress plugin directory. Until it is, install it manually:

1. Download this repository (**Code > Download ZIP**) and unpack it.
2. Install the `heylingo` folder it contains, in one of two ways:
   - In WordPress: compress the `heylingo` folder into `heylingo.zip`, then go to
     **Plugins > Add New Plugin > Upload Plugin** and upload it.
   - Over FTP or SFTP: copy the `heylingo` folder into `wp-content/plugins/`.
3. Activate **Heylingo** under **Plugins**.

## Getting started

1. **Subscribe** on [heylingo.io](https://heylingo.io/#pricing) and enter your site's domain at
   checkout, for example `example.com`. Whether you include "www." makes no difference.
2. **Check the connection** under **Settings > Heylingo** in WordPress. Once your subscription is
   active, the page confirms it and lists your languages.
3. **Fine-tune in the dashboard.** Sign in at [dashboard.heylingo.io](https://dashboard.heylingo.io)
   with the email address you used at checkout; you receive a 6-digit code, no password needed.
   There you choose your languages, tone and switcher position, maintain your glossary and invite
   your team.

## Editing translations

In the dashboard, choose **Edit on your website**. Your site opens with an editor: click a
sentence, type your version and save. Only you see the editor; your visitors see the finished
page. The dashboard also lists every translation, so you can search and correct them in one
place.

## Keeping content untranslated

Brand names, product codes or legal text sometimes need to stay in the original language.

- **In the block editor:** select the block, open **Advanced > Additional CSS class(es)** and add
  `notranslate`.
- **In HTML:** add the attribute `translate="no"` to any element.

Everything inside such an element stays as it is. Code blocks and anything visitors type into
forms are never translated, and neither is the WordPress admin bar. To translate a term the same way everywhere,
add it to the glossary in the dashboard.

## Compatibility

- **Themes:** Heylingo translates the page as your visitors see it, so it works with any theme.
- **Caching and optimization plugins:** the Heylingo script has to load in the page head without
  being deferred or delayed. The plugin marks it so that WP Rocket, LiteSpeed Cache and
  Cloudflare Rocket Loader leave it alone. If you use another plugin that defers, delays or
  combines JavaScript, exclude `cdn.heylingo.io` in its settings.

## Privacy

- The script sets no cookies and does not track your visitors. It remembers the chosen language
  in the visitor's browser.
- To translate a page, the visitor's browser sends the page's text, its address and the chosen
  language to Heylingo. Like every web request, this includes the visitor's IP address, which
  Heylingo keeps only as a one-way hash for the current day to limit excessive use.
- Text that is new to your site is translated by a large language model from Anthropic. The script
  is delivered through Cloudflare's network.
- A data processing agreement under Art. 28 GDPR is part of the terms and is concluded at
  checkout. You do not need to request or sign one separately.
- Text that is shown only to logged-in users, such as account pages, is sent for translation too.
  You decide where the script runs; see [For developers](#for-developers).

Read more in the [privacy policy](https://heylingo.io/#privacy), the
[terms](https://heylingo.io/#terms) and the [data processing agreement](https://heylingo.io/#dpa).

## Pricing

There is one plan: pay per use.

- EUR 20 per 1,000,000 translated characters per month, billed monthly through Stripe.
- EUR 5 monthly minimum, which includes your first 250,000 characters. Nothing is charged until
  Heylingo has translated text for your site.
- The count is the translated text Heylingo keeps for your site, across all its languages. Page
  views and visitors never change the price.

The [calculator on heylingo.io](https://heylingo.io/#pricing) estimates your monthly price.
Invoices, payment method and cancellation are managed in the Stripe billing portal, which you
open from the dashboard. You can cancel at any time; your site then shows its original language
again.

## Frequently asked questions

**Do I need an API key?**
No. Heylingo recognizes your site by its domain. Subscribe with the domain your site runs on.

**Will Heylingo slow down my site?**
Your page loads as usual. The script is small, translations come from your translation memory,
and translations a visitor has seen before are kept in their browser and shown immediately.

**What happens if my subscription ends?**
The script leaves your pages untouched, and your site shows its original language.

**Can my team help with translations?**
Yes. Invite editors in the dashboard. They can correct translations on your site the same way you
do.

**Can I use Heylingo on a staging site?**
Heylingo translates the domain you subscribed with. On a staging site with a different domain,
the script stays inactive and your pages show their original language. **Settings > Heylingo**
shows which domain WordPress reports and whether it has an active subscription.

## For developers

The `heylingo_load_embed` filter decides whether the Heylingo script loads on the current page.
For example, to leave a members area untranslated:

```php
add_filter( 'heylingo_load_embed', function ( $load ) {
	return $load && ! is_page( 'members' );
} );
```

Notes on local development and releases are in [DEVELOPMENT.md](DEVELOPMENT.md).

## Support

Questions or problems: [info@heylingo.io](mailto:info@heylingo.io).

## License

GPL-2.0-or-later. Using the plugin requires a Heylingo subscription, subject to the
[Heylingo terms](https://heylingo.io/#terms).
