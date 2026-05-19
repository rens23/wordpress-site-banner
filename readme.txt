=== Site Banner ===
Contributors: renshakkesteegt
Tags: banner, notification, announcement, bar, cookie-bar
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.6.8
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display configurable announcement banners at the top or bottom of your WordPress site. Pro: multi-banner, scheduling, targeting, WPML, click tracking.

== Description ==

Site Banner lets you place announcement, promotion, or notification banners on your WordPress site without writing code.

**Free features**

* Banner text with rich-text editor (links allowed)
* Custom background, text, link, and close-button colors
* One-click colour presets for Info, Success, Warning, and Error styles
* Font size, z-index, and position (relative, fixed, sticky, footer)
* Optional close button with cookie-based dismissal memory
* Insert at top of `<body>` or `<header>`
* Header top margin / padding for theme compensation
* Optional `wp_body_open` server-side render path
* Per-banner custom CSS for `.site-banner`, `.site-banner-text`, `.site-banner-button`, and `.site-banner-scrolling`
* Live preview pane in the settings page (sticky as you scroll)

**Pro features** (require a license key)

* Up to 5 banners
* Call-to-action button per banner (text, URL, colours, new-tab toggle)
* WPML / Polylang translation support for banner text and CTA strings
* Schedule banners with start and end dates (UTC, native datetime picker)
* Disable on all posts, on specific pages, or by path (with `/shop*`, `*shop`, `*shop*` wildcards)
* Advanced placement via any CSS selector
* Per-banner site-wide custom CSS and JavaScript
* User-role permissions for editing the plugin
* Debug mode (console logs)
* Per-banner visibility targeting: logged-in / logged-out / specific roles
* Click tracking: dispatches `siteBanner:linkClick` CustomEvent and optionally POSTs to a configured endpoint
* Gutenberg block (`site-banner/banner`) and shortcode `[site_banner id="1"]` for inline placement

== External services ==

The Pro tier validates a license key against our license server at `https://licenseseat.com/api/v1/`. The plugin contacts this endpoint only when an admin enters a license key on the settings page. The free tier never contacts any external server.

What is sent on activate / validate / deactivate:

* The license key the admin pasted
* A randomly-generated UUIDv4 stored in this site's database as `site_banner_device_fingerprint` (no PII)
* On activate only: the site's hostname (e.g. `example.com`) as the seat label

When: only after the admin enters a license key. Results are cached in a transient for one hour. See https://licenseseat.com/privacy and https://licenseseat.com/terms.

== Third-party libraries ==

The admin preview pane uses DOMPurify for HTML sanitization, included in `vendor/purify.min.js`. Source: https://github.com/cure53/DOMPurify (MPL-2.0 / Apache-2.0).

== Installation ==

1. Upload the `site-banner` folder to `/wp-content/plugins/` (or upload the zip through **Plugins → Add New → Upload Plugin**).
2. Activate the plugin through the **Plugins** menu.
3. Visit **Site Banner** in the admin sidebar to configure your banner.

== Frequently Asked Questions ==

= What does the banner look like in my DOM? =

`<div id="site-banner_1" class="site-banner_1">
  <div class="site-banner-text_1"><span>YOUR BANNER TEXT</span></div>
  <a class="site-banner-cta_1">Learn more</a>
  <button class="site-banner-button_1">✕</button>
</div>`

The `_1` suffix is the banner number (1-5). CSS classes are scoped per banner so styles never collide.

= The banner isn't showing up, or only shows when I'm logged in. What's wrong? =

Almost always a caching issue. Browsers cache JS/CSS aggressively, and many WordPress caching/optimizer plugins do too. Site Banner automatically flushes W3 Total Cache, WP Super Cache, WP Rocket, Autoptimize, LiteSpeed, and WP Fastest Cache on every save, but some setups need an extra nudge:

1. Clear your browser cache (or open an incognito window).
2. Open your caching plugin's settings and click its "Clear cache" / "Purge all" button.
3. If you use a CDN (Cloudflare, BunnyCDN, etc.), purge the CDN cache too.

= Why is my banner covering my header (or hiding behind it)? =

Your theme uses absolute or fixed positioning for its header. Three ways to fix it, in order of simplicity:

1. Change **Placement** from "Insert at top of `<body>`" to "Insert at top of `<header>`" so the banner lives inside the header.
2. Adjust **Header top margin** or **Header top padding** so the header pushes down to make room for the banner.
3. With a Pro license, set **Position** to Fixed or Sticky and bump the **Z-index** higher than your theme's header z-index (try 999999).

= I have the Divi theme and the banner isn't showing =

Set **Placement** to "Insert at top of `<header>`". If that doesn't work, set the banner Position to Relative and add this to the Pro **Site-wide custom CSS** field (or your theme's custom CSS):

`#main-header:not(.et-fixed-header) { position: relative; }
#top-header:not(.et-fixed-header) { position: relative; }`

= Does this plugin use cookies? =

Only when you enable the close button. The cookie remembers that a visitor dismissed the banner and is set per banner (`sitebannerclosed_1`, `sitebannerclosed_2`, etc.). These fall under [strictly-necessary cookies](https://gdpr.eu/cookies/) per the GDPR and do not require consent. If a visitor has cookies disabled, the close button still works for that session but the banner will reappear on the next page load.

= How do I re-enable a banner after I clicked close? =

Three options, easiest first:

1. Set **Close button expiration** to `0` and save — that issues an expired cookie.
2. Clear your browser's cookies for the site.
3. In the browser console, run: `document.cookie = "sitebannerclosed_1=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";` (use `sitebannerclosed_2` etc. for other banners).

= Is there a Pro version? =

Yes — see the **Pro features** list above. Free tier is fully functional with one banner; Pro adds multi-banner, scheduling, targeting, the CTA button, WPML support, click tracking, the Gutenberg block, and more.

= How do I get a Pro license? =

Pro licenses are sold via Gumroad. Your license key is delivered with your purchase. Paste it into the **License** section of the settings page and save — the plugin activates this site with a stable per-install fingerprint. To move the license to a different site, click **Deactivate this site** on the original site, then save the key on the new one.

= How do I translate banner text? =

With the Pro license active, install WPML or Polylang. Save the banner text on our settings page once (this triggers WPML's string-capture). Then go to **WPML → String Translation** (or **Polylang → Strings**) and filter by context `admin_texts_plugin_site-banner`. Translate each language; the front-end automatically renders the correct one.

= Does the plugin phone home? =

Only when you save a Pro license key, and only to our license server at `https://licenseseat.com/api/v1/` for activation/validation. We send the license key you entered, an anonymous UUID fingerprint, and the site hostname. No visitor data is ever sent. The free tier never contacts any external server.

= I clicked "Verify license now" and got an error =

Open the **Last check** diagnostic panel on the settings page — it shows the exact HTTP status, the endpoint called, and the response body. The most common causes are: a typo in the license key, your host blocking outbound HTTPS, or the seat already used on another site (in which case click **Deactivate this site** on the original site first).

== Screenshots ==

1. The main settings page with the live preview pane sticky at the top.
2. Colour presets — one click sets all four banner colours to Info / Success / Warning / Error styles.
3. Pro features: schedule, page exclusions, CTA button, click tracking.
4. The banner rendered on the front-end.

== Changelog ==

= 0.6.8 =
* Plugin Check / WP Coding Standards pass: replaced `mt_rand` with `wp_rand`-equivalent, `parse_url` with `wp_parse_url`, added `sanitize_text_field` on `$_GET`/`$_SERVER` reads, replaced custom `is_checked` closure with WordPress's `checked()` function, escaped integer `$i` outputs, and added targeted phpcs:ignore comments on intentional patterns (admin-only verbatim CSS/JS, WPML hook name, uninstall direct queries).
* Removed `load_plugin_textdomain` call — WordPress 4.6+ auto-loads translations for plugins hosted on the WP directory.

= 0.6.7 =
* User-facing strings (admin notices, button text, descriptions, FAQ) no longer name the licensing back-end by brand. The External Services section still discloses the actual endpoint URL as required by directory rules.

= 0.6.6 =
* "Verify license now" button replaced with "Save & verify license" — it now submits the settings form (saving any pasted key) and then runs verify in one click. The previous link-only approach didn't save form changes.

= 0.6.5 =
* Verify-now with an empty license key now shows a specific "No license key saved — paste and Save Changes first" notice instead of generic "Verify failed", and refreshes the diagnostic panel so stale rows don't linger.

= 0.6.4 =
* Added "Deactivate this site" button next to "Verify license now". One click clears the saved license key and frees the seat. Confirms before acting.

= 0.6.3 =
* "Verify license now" no longer routes through admin-post.php (which on some hosts produced a blank wp_die page). The verify now runs inline on the settings page itself via a nonced link, then shows a success/failure notice and refreshes the diagnostic panel.

= 0.6.2 =
* Banner container is now a flex row so the CTA button truly sits next to the text in "inline" mode; "block" mode breaks to a new row.
* CTA color pickers now match the styling of the main banner color pickers (same size, same wrapper).
* Renamed the Pro role-targeting row to "Audience" so it doesn't collide with the free "Visibility" Show/Hide row.
* Expanded FAQ covering caching, header overlap, Divi-specific fix, cookies, license diagnostics, and translations.

= 0.6.1 =
* CTA colour pickers now correctly sync to the underlying text inputs (saves the chosen colour).
* CTA button can be positioned inline next to the text or as a block under it.
* Sticky-style positions (`fixed`, `sticky`, `footer`) are now Pro-gated. Free tier falls back to `relative`.
* "Verify license now" no longer dead-ends on a blank wp_die page when the license key is empty.

= 0.6.0 =
* Added pro CTA button per banner: text, URL, colours, "open in new tab".
* Added pro WPML / Polylang support: banner text and CTA strings are exposed via `wpml-config.xml` and translated through `wpml_translate_single_string`.
* Schedule fields now use the native HTML5 `datetime-local` picker.
* Plugin now loads its text domain on `plugins_loaded`.

= 0.5.1 =
* Activate now sends the site hostname as the seat label so seats are easier to identify.
* Verify-now redirect made more robust with a fallback HTML page.

= 0.5.0 =
* Preview banner is now sticky at the top of the viewport while editing.
* "Save Changes" button is sticky at the bottom of the viewport.

= 0.4.x =
* "Verify license now" button + diagnostic panel showing the last API check.
* Self-healing license check that auto-activates the device on `device_not_activated`.

= 0.3.0 =
* Licensing now uses a stable per-install fingerprint and device-bound seats.

== Upgrade Notice ==

= 0.6.0 =
Adds the pro CTA-button feature, WPML/Polylang translation support, and a native datetime picker for scheduling. No breaking changes.
