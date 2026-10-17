=== Heylingo ===
Contributors: heylingo
Tags: translation, multilingual, language switcher, translate, ai translation
Requires at least: 5.7
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Make your WordPress site multilingual: AI translations with translation memory, a language switcher and corrections right on your own pages.

== Description ==

Heylingo translates your site in the visitor's browser. Activate the plugin, subscribe with your site's domain, and your pages appear in your visitors' languages with a language switcher.

* **AI translation with context.** Whole sentences are translated with your site's name, description and glossary in mind, not word by word.
* **Translation memory.** Every sentence is translated once and stored for your site. Repeat views are served from memory, so the same text is never translated twice.
* **Corrections on the page.** Open edit mode from the dashboard and change a translation right where it appears. Your correction is never overwritten.
* **No configuration in WordPress.** Languages, switcher position, glossary and team live in the Heylingo dashboard.
* **Light.** One small script, no cookies, no tracking.

Heylingo is a paid service, billed per translated character. See [pricing](https://heylingo.io/#pricing).

= Keep content untranslated =

Add the CSS class `notranslate` to a block (Block settings → Advanced → Additional CSS class) or the attribute `translate="no"` to any element. The WordPress admin bar is never translated.

= For developers =

The filter `heylingo_load_embed` decides whether the script loads on the current page:

`add_filter( 'heylingo_load_embed', function ( $load ) { return $load && ! is_page( 'members' ); } );`

== External services ==

This plugin connects your site to Heylingo, a website translation service operated by Jermaine J. Ernst, Germany. It is needed to translate your pages.

* **On every front-end page**, your visitors' browsers load the script `https://cdn.heylingo.io/heylingo.min.js`. The script sends the text of the page, the page's address and the chosen language to `https://api.heylingo.io` and receives the translations. The service also receives the visitor's IP address, as with every web request; it keeps it only as a one-way hash for the current day to limit excessive use. The script stores the visitor's chosen language in the browser (localStorage) and sets no cookies.
* **On Settings → Heylingo**, your server asks `https://api.heylingo.io/v1/embed/config` whether your site's domain has an active subscription. The request contains your site's address and nothing else.

Text translated for the first time is translated by a large language model of Anthropic PBC through its API. Terms of service: https://heylingo.io/#terms. Privacy policy: https://heylingo.io/#privacy.

== Installation ==

1. Install and activate the plugin.
2. Subscribe on [heylingo.io](https://heylingo.io/#pricing) and enter your site's domain at checkout.
3. Open Settings → Heylingo to check the connection, then choose your languages in the dashboard.

== Frequently Asked Questions ==

= Do I need an API key? =

No. Heylingo recognises your site by its domain. Subscribe with the domain your site runs on; "www." makes no difference.

= Does it work with caching and optimization plugins? =

Yes. The script must load in the page head without being deferred or delayed. The plugin marks it so that WP Rocket, LiteSpeed Cache and Cloudflare Rocket Loader leave it alone. If another optimization plugin defers or delays JavaScript, exclude `cdn.heylingo.io` there.

== Changelog ==

= 0.1.0 =
* First release: loads the Heylingo script and shows the connection status under Settings → Heylingo.
