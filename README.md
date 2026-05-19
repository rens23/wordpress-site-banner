# Site Banner

A WordPress plugin that displays configurable announcement, promotion, or notification banners at the top or bottom of your site. Free tier supports one banner; the Pro tier adds multi-banner, scheduling, targeting, a CTA button, WPML support, click tracking, and a Gutenberg block.

## Install

The recommended install path is from the official WordPress.org plugin directory once approved (link will go here). Until then, install from this repo:

1. Download the latest release as a zip from the [Releases page](https://github.com/rens23/wordpress-site-banner/releases), **or** clone this repo and rename the folder to `site-banner`.
2. Place the `site-banner` folder in `wp-content/plugins/`.
3. Activate **Site Banner** from your **Plugins** page.
4. Visit **Site Banner** in the admin sidebar to configure your first banner.

## Free features

- Banner text with rich-text editor (links allowed)
- Background, text, link, and close-button colors
- One-click color presets — Info / Success / Warning / Error
- Font size, z-index, and position
- Optional close button with cookie-based dismissal memory
- Insert at top of `<body>` or `<header>`
- Header top margin / padding to compensate for fixed theme headers
- Optional `wp_body_open` server-render path (zero CLS)
- Per-banner custom CSS for `.site-banner`, `.site-banner-text`, `.site-banner-button`, `.site-banner-scrolling`
- Live preview pane that sticks to the top while editing
- Sticky save button at the bottom

## Pro features

Pro features unlock with a license key purchased via [Gumroad](https://rensh.gumroad.com/l/site-banner-plugin). The integration with [LicenseSeat](https://licenseseat.com) handles real device fingerprinting, seat limits, and activation/deactivation.

- **Up to 5 banners** managed from a single dropdown
- **Call-to-action button** per banner — text, URL, colors, "open in new tab", inline or block layout
- **Sticky / Fixed / Footer** positions
- **Schedule** banners with start and end dates (UTC, native datetime picker)
- **Audience targeting** — show only to logged-in, logged-out, or specific WordPress roles. Enforced server-side.
- **Page exclusions** — disable on all posts, on specific pages, or by URL path with wildcards (`/shop*`, `*checkout*`)
- **Advanced placement** via any CSS selector
- **Site-wide custom CSS and JavaScript** per banner, with "keep when banner is closed" toggles
- **Click tracking** — dispatches a `siteBanner:linkClick` JavaScript event and optionally POSTs to an endpoint you configure
- **WPML & Polylang support** — banner text and CTA strings exposed via `wpml-config.xml`, translated through `wpml_translate_single_string`
- **Gutenberg block** (`site-banner/banner`) and shortcode `[site_banner id="1"]` for inline placement
- **User permissions** — let Editors / Authors / Shop managers etc. edit banners without giving them full admin rights
- **Debug mode** — logs banner parameters to the browser console

## Privacy / external services

The Pro license check is the only external call. It runs only when you save a license key on the settings page, and only against [LicenseSeat](https://licenseseat.com). The plugin sends:

- the license key you entered,
- a randomly-generated UUIDv4 fingerprint stored in your site's `wp_options`,
- the site hostname (so seats are identifiable in your LicenseSeat dashboard).

No visitor data is ever sent. The free tier never contacts any external server.

The plugin only sets a cookie (`sitebannerclosed_N`) when the close button is enabled. These are [strictly-necessary cookies](https://gdpr.eu/cookies/) per the GDPR and do not require visitor consent.

## Third-party libraries

The admin preview uses [DOMPurify](https://github.com/cure53/DOMPurify) for HTML sanitization (`vendor/purify.min.js`). MPL-2.0 / Apache-2.0.

## Issues and contributions

File bugs and feature requests on the [Issues](https://github.com/rens23/wordpress-site-banner/issues) tab. Pull requests welcome.

## License

GPL-2.0-or-later. See [`readme.txt`](readme.txt) for the WordPress.org-formatted plugin metadata and changelog.
