# heylingo-wordpress

WordPress plugin for the wordpress.org plugin directory. It does two things:

- Loads `https://cdn.heylingo.io/heylingo.min.js` in `<head>` on every front-end page, without
  `async`, `defer` or `?ver=`, marked so WP Rocket, LiteSpeed Cache and Cloudflare Rocket Loader
  leave it alone. The admin bar gets `translate="no"`.
- Adds Settings → Heylingo: asks `api.heylingo.io/v1/embed/config` with the site's Origin, as the
  script would, and shows "active" (with languages), "no subscription for this domain",
  "being set up" or the error. Links to the dashboard and to pricing.

It stores no options and needs no API key: the Origin selects the site, settings live in the
dashboard. The filter `heylingo_load_embed` lets a developer skip the script on a page.

```
heylingo/            the plugin as it ships (slug "heylingo")
  heylingo.php
  readme.txt         wordpress.org listing, including the required "External services" section
blueprint.json       local Playground: installs Plugin Check, signs in without a password
```

## Local development

`.claude/launch.json` in the parent folder has a `heylingo-wordpress` entry. By hand:

```bash
cd heylingo-wordpress && npx -y @wp-playground/cli@latest server --port 9400 --auto-mount=./heylingo --blueprint=./blueprint.json
```

Then open http://127.0.0.1:9400 (not `localhost`, the site URL is `127.0.0.1`). Tools → Plugin
Check, all categories: no errors or warnings as of 0.1.0 on WordPress 7.1.3.

A local site has no subscription, so the settings page shows the "no subscription" state and the
script logs `heylingo: No active heylingo subscription for 127.0.0.1.` That is expected.

## Publishing (manual)

1. wordpress.org account; put its username into `Contributors:` in `readme.txt` (now a placeholder).
2. `cd heylingo-wordpress && zip -r heylingo.zip heylingo` and upload at
   https://wordpress.org/plugins/developers/add/. Review takes days to weeks. For a plugin named
   after a brand, the team asks the submitter to prove they own it, usually by mail from an
   `@heylingo.io` address.
3. After approval: SVN repository, `trunk/` and `tags/0.1.0/`, plus `assets/` with icon
   (256×256) and banner (1544×500) from `heylingo-website/DESIGN.md`.
4. Bump `Tested up to` with every major WordPress release, or the plugin drops out of search.

## Open points

- Page builders (Elementor, Divi, Bricks) load the front end in their editor. The script skips
  the Customizer only; check whether it gets in the way in their editors.
- WooCommerce account and checkout pages show personal data, which the script would send for
  translation. Decide whether the plugin skips them by default.
- The status check runs on every visit to the settings page (5 s timeout, no cache).
