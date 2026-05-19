<?php
/**
 * Plugin Name: Site Banner
 * Plugin URI:  https://rensh.gumroad.com/l/site-banner-plugin
 * Description: Display configurable banners at the top or bottom of your website.
 * Version:     0.6.9
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author:      Rens Hakkesteegt
 * License:     GPL-2.0-or-later
 * Text Domain: site-banner
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SITE_BANNER_VERSION', '0.6.9');
define('SITE_BANNER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SITE_BANNER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SITE_BANNER_SETTINGS_GROUP', 'site-banner-settings-group');
define('SITE_BANNER_CAPABILITY', 'manage_site_banner');
define('SITE_BANNER_MENU_SLUG', 'site-banner-settings');
define('SITE_BANNER_MAX_BANNERS', 5);

require_once SITE_BANNER_PLUGIN_DIR . 'includes/license.php';

/**
 * Banner count: 1 in the free tier, up to SITE_BANNER_MAX_BANNERS with a
 * verified Gumroad license.
 */
function site_banner_get_num_banners() {
    return site_banner_is_pro() ? SITE_BANNER_MAX_BANNERS : 1;
}

/** Suffix used in option keys / CSS classes / DOM ids for banner N. */
function site_banner_id_suffix($n) {
    return '_' . (int) $n;
}

/**
 * Apply a WPML / Polylang translation to a stored string, gated by pro.
 *
 * On free tier this is a passthrough. On pro tier, runs the value through
 * WPML's `wpml_translate_single_string` filter (Polylang implements the
 * same hook in compatibility mode). The string must be registered via
 * `wpml-config.xml` (which we ship), so WPML's String Translation will
 * surface it automatically.
 */
function site_banner_translate($value, $option_key) {
    if (!site_banner_is_pro()) {
        return $value;
    }
    // WPML's `wpml_translate_single_string` filter is the documented API; the
    // name is owned by WPML, not us, so it can't carry our prefix.
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
    return apply_filters('wpml_translate_single_string', $value, 'admin_texts_plugin_site-banner', '[' . $option_key . ']');
}

/** Convenience: list of free per-banner option keys (without the _N suffix). */
function site_banner_free_per_banner_option_keys() {
    return array(
        'site_banner_text',
        'site_banner_hide',
        'site_banner_color',
        'site_banner_text_color',
        'site_banner_link_color',
        'site_banner_close_color',
        'site_banner_font_size',
        'site_banner_z_index',
        'site_banner_position',
        'site_banner_prepend_element',
        'site_banner_close_button_enabled',
        'site_banner_close_button_expiration',
        'site_banner_custom_css',
        'site_banner_text_custom_css',
        'site_banner_button_custom_css',
        'site_banner_scrolling_custom_css',
    );
}

/** Convenience: list of banner-#1-only option keys (no suffix). */
function site_banner_free_global_option_keys() {
    return array(
        'site_banner_header_margin',
        'site_banner_header_padding',
        'site_banner_wp_body_open_enabled',
    );
}

/** Pro per-banner option keys (without the _N suffix). */
function site_banner_pro_per_banner_option_keys() {
    return array(
        'site_banner_insert_inside_element',
        'site_banner_start_after_date',
        'site_banner_remove_after_date',
        'site_banner_disabled_on_posts',
        'site_banner_disabled_pages',
        'site_banner_disabled_paths',
        'site_banner_site_custom_css',
        'site_banner_site_custom_js',
        'site_banner_keep_site_css',
        'site_banner_keep_site_js',
        'site_banner_visibility',
        'site_banner_visibility_roles',
        'site_banner_click_tracking_enabled',
        'site_banner_cta_text',
        'site_banner_cta_url',
        'site_banner_cta_bg_color',
        'site_banner_cta_text_color',
        'site_banner_cta_new_tab',
        'site_banner_cta_position',
    );
}

/** Pro global option keys (no suffix). */
function site_banner_pro_global_option_keys() {
    return array(
        'site_banner_role_permissions',
        'site_banner_debug_mode',
        'site_banner_click_tracking_endpoint',
    );
}

/* -------------------------------------------------------------------------
 * Activation / deactivation
 * ---------------------------------------------------------------------- */

// Note: load_plugin_textdomain() is no longer needed; WordPress auto-loads
// translations for plugins hosted on WordPress.org as of WP 4.6.

register_activation_hook(__FILE__, 'site_banner_activate');
function site_banner_activate() {
    $admin = get_role('administrator');
    if ($admin) {
        $admin->add_cap(SITE_BANNER_CAPABILITY);
    }
}

register_deactivation_hook(__FILE__, 'site_banner_deactivate');
function site_banner_deactivate() {
    foreach (wp_roles()->role_objects as $role) {
        if ($role->has_cap(SITE_BANNER_CAPABILITY)) {
            $role->remove_cap(SITE_BANNER_CAPABILITY);
        }
    }
}

/** No-op passthrough sanitizer for fields that must be stored verbatim. */
function site_banner_passthrough($value) {
    return $value;
}

/**
 * Sync the SITE_BANNER_CAPABILITY across user roles to match the comma-separated
 * list stored in `site_banner_role_permissions`. Administrators always have it.
 * Runs on every settings save.
 */
add_action('update_option_site_banner_role_permissions', 'site_banner_sync_role_permissions', 10, 2);
add_action('add_option_site_banner_role_permissions',    'site_banner_sync_role_permissions_on_add', 10, 2);

function site_banner_sync_role_permissions($old_value, $new_value) {
    $allowed = array_filter(array_map('trim', explode(',', (string) $new_value)));
    foreach (wp_roles()->role_objects as $role_name => $role) {
        if ($role_name === 'administrator') {
            continue;
        }
        $should_have = in_array($role_name, $allowed, true) && site_banner_is_pro();
        if ($should_have && !$role->has_cap(SITE_BANNER_CAPABILITY)) {
            $role->add_cap(SITE_BANNER_CAPABILITY);
        } elseif (!$should_have && $role->has_cap(SITE_BANNER_CAPABILITY)) {
            $role->remove_cap(SITE_BANNER_CAPABILITY);
        }
    }
}

function site_banner_sync_role_permissions_on_add($option, $value) {
    site_banner_sync_role_permissions('', $value);
}

/* -------------------------------------------------------------------------
 * Settings registration
 * ---------------------------------------------------------------------- */

add_action('admin_init', 'site_banner_register_settings');
function site_banner_register_settings() {
    $sanitizers = array(
        'site_banner_text'                   => 'wp_kses_post',
        'site_banner_custom_css'             => 'wp_strip_all_tags',
        'site_banner_text_custom_css'        => 'wp_strip_all_tags',
        'site_banner_button_custom_css'      => 'wp_strip_all_tags',
        'site_banner_scrolling_custom_css'   => 'wp_strip_all_tags',
    );

    for ($i = 1; $i <= site_banner_get_num_banners(); $i++) {
        $suffix = site_banner_id_suffix($i);
        foreach (site_banner_free_per_banner_option_keys() as $key) {
            $sanitizer = isset($sanitizers[$key]) ? $sanitizers[$key] : 'wp_filter_nohtml_kses';
            register_setting(SITE_BANNER_SETTINGS_GROUP, $key . $suffix, array('sanitize_callback' => $sanitizer));
        }
        foreach (site_banner_pro_per_banner_option_keys() as $key) {
            $args = array();
            if ($key === 'site_banner_site_custom_css') {
                $args['sanitize_callback'] = 'wp_strip_all_tags';
            } elseif ($key === 'site_banner_site_custom_js') {
                // JS is literal — do not sanitize. Admin-only field, written into a <script> tag.
                $args['sanitize_callback'] = 'site_banner_passthrough';
            } elseif ($key === 'site_banner_insert_inside_element') {
                $args['sanitize_callback'] = 'wp_strip_all_tags';
            } elseif ($key === 'site_banner_cta_url') {
                $args['sanitize_callback'] = 'esc_url_raw';
            } else {
                $args['sanitize_callback'] = 'wp_filter_nohtml_kses';
            }
            register_setting(SITE_BANNER_SETTINGS_GROUP, $key . $suffix, $args);
        }
    }

    foreach (site_banner_pro_global_option_keys() as $key) {
        $sanitizer = $key === 'site_banner_click_tracking_endpoint' ? 'esc_url_raw' : 'wp_filter_nohtml_kses';
        register_setting(SITE_BANNER_SETTINGS_GROUP, $key,
            array('sanitize_callback' => $sanitizer));
    }

    foreach (site_banner_free_global_option_keys() as $key) {
        register_setting(SITE_BANNER_SETTINGS_GROUP, $key, array('sanitize_callback' => 'wp_filter_nohtml_kses'));
    }

    // Hidden field whose value flips on every save, used to trigger cache flushing.
    register_setting(SITE_BANNER_SETTINGS_GROUP, 'site_banner_cache_buster',
        array('sanitize_callback' => 'wp_filter_nohtml_kses'));

    // Pro: license key.
    register_setting(SITE_BANNER_SETTINGS_GROUP, 'site_banner_license_key',
        array('sanitize_callback' => 'wp_filter_nohtml_kses'));
}

/* -------------------------------------------------------------------------
 * Admin menu
 * ---------------------------------------------------------------------- */

/**
 * When the settings form is submitted with our "verify after save" button,
 * options.php saves all fields then redirects back here. We hook the redirect
 * to append the verify flag + nonce so the next page render also runs verify.
 */
add_filter('wp_redirect', 'site_banner_inject_verify_on_save', 10, 2);
function site_banner_inject_verify_on_save($location, $status) {
    // Nonce verification: this filter fires AFTER options.php has already
    // validated its own nonce. We're only inspecting the request shape; we're
    // not processing form data ourselves.
    // phpcs:disable WordPress.Security.NonceVerification.Missing
    if (empty($_POST['option_page']) || sanitize_text_field(wp_unslash($_POST['option_page'])) !== SITE_BANNER_SETTINGS_GROUP) {
        return $location;
    }
    if (empty($_POST['site_banner_verify_after_save'])) {
        return $location;
    }
    // phpcs:enable WordPress.Security.NonceVerification.Missing
    if (strpos($location, 'page=' . SITE_BANNER_MENU_SLUG) === false) {
        return $location;
    }
    $location = add_query_arg(array(
        'site_banner_verify' => '1',
        '_wpnonce'           => wp_create_nonce('site_banner_verify_now'),
    ), $location);
    return $location;
}

add_action('admin_menu', 'site_banner_admin_menu');
function site_banner_admin_menu() {
    add_menu_page(
        __('Site Banner Settings', 'site-banner'),
        __('Site Banner', 'site-banner'),
        SITE_BANNER_CAPABILITY,
        SITE_BANNER_MENU_SLUG,
        'site_banner_render_settings_page',
        'dashicons-megaphone'
    );
}

/**
 * Render the settings page. If the request includes a valid `site_banner_verify`
 * action (with nonce), runs the license verify first and sets a flag the
 * settings template renders as a notice. Done inline here — no admin-post.php
 * round-trip — because admin-post requests on some hosts hit a generic wp_die.
 */
function site_banner_render_settings_page() {
    if (!current_user_can(SITE_BANNER_CAPABILITY)) {
        require SITE_BANNER_PLUGIN_DIR . 'admin/settings-page.php';
        return;
    }
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

    if (!empty($_GET['site_banner_verify'])) {
        if (wp_verify_nonce($nonce, 'site_banner_verify_now')) {
            if (get_option('site_banner_license_key', '') === '') {
                // Still record a diagnostic so the panel is fresh.
                site_banner_force_verify_license();
                $GLOBALS['site_banner_verify_result'] = 'no_key';
            } else {
                $GLOBALS['site_banner_verify_result'] = site_banner_force_verify_license() ? 'ok' : 'fail';
            }
        } else {
            $GLOBALS['site_banner_verify_result'] = 'bad_nonce';
        }
    } elseif (!empty($_GET['site_banner_deactivate'])) {
        if (wp_verify_nonce($nonce, 'site_banner_deactivate')) {
            // Clearing the option triggers update_option_site_banner_license_key,
            // which fires the LicenseSeat deactivate call on the old value.
            update_option('site_banner_license_key', '');
            $GLOBALS['site_banner_verify_result'] = 'deactivated';
        } else {
            $GLOBALS['site_banner_verify_result'] = 'bad_nonce';
        }
    }
    require SITE_BANNER_PLUGIN_DIR . 'admin/settings-page.php';
}

/* -------------------------------------------------------------------------
 * Per-page visibility (server-side)
 * ---------------------------------------------------------------------- */

/**
 * Whether the banner should be skipped on the current request.
 *
 * Folds in: explicit hide, date window, disable-on-posts, disabled-pages list,
 * disabled-paths list (server-side; JS also checks paths to catch cached pages).
 *
 * Pro-gated conditions are evaluated only when site_banner_is_pro() is true,
 * so a lapsed license can't accidentally hide a banner the user can no longer
 * configure away.
 */
function site_banner_is_hidden($suffix) {
    if (get_option('site_banner_hide' . $suffix) === 'yes') {
        return true;
    }

    if (!site_banner_is_pro()) {
        return false;
    }

    $now = time();

    $start = get_option('site_banner_start_after_date' . $suffix);
    if ($start) {
        $t = strtotime($start . ' UTC');
        if ($t !== false && $now < $t) {
            return true;
        }
    }
    $end = get_option('site_banner_remove_after_date' . $suffix);
    if ($end) {
        $t = strtotime($end . ' UTC');
        if ($t !== false && $now > $t) {
            return true;
        }
    }

    $post_id = get_the_ID();
    if (get_option('site_banner_disabled_on_posts' . $suffix) && $post_id && get_post_type($post_id) === 'post') {
        return true;
    }

    $disabled_pages = array_filter(array_map('trim', explode(',', (string) get_option('site_banner_disabled_pages' . $suffix))));
    if ($post_id && in_array((string) $post_id, $disabled_pages, true)) {
        return true;
    }

    $disabled_paths = (string) get_option('site_banner_disabled_paths' . $suffix);
    if ($disabled_paths !== '' && isset($_SERVER['REQUEST_URI'])) {
        $req_uri  = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
        $req_path = wp_parse_url($req_uri, PHP_URL_PATH);
        if ($req_path !== null && site_banner_path_matches($req_path, $disabled_paths)) {
            return true;
        }
    }

    $visibility = (string) get_option('site_banner_visibility' . $suffix, 'everyone');
    if ($visibility !== 'everyone') {
        $logged_in = is_user_logged_in();
        if ($visibility === 'logged_in' && !$logged_in) return true;
        if ($visibility === 'logged_out' && $logged_in) return true;
        if ($visibility === 'specific_roles') {
            if (!$logged_in) return true;
            $allowed = array_filter(array_map('trim', explode(',', (string) get_option('site_banner_visibility_roles' . $suffix))));
            $user_roles = (array) wp_get_current_user()->roles;
            if (empty(array_intersect($user_roles, $allowed))) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Match a path against a comma-separated list of patterns with `*` wildcards.
 * Mirrors the JS implementation in site-banner.js.
 */
function site_banner_path_matches($req_path, $patterns_csv) {
    $patterns = array_filter(array_map('trim', explode(',', $patterns_csv)));
    foreach ($patterns as $pattern) {
        if ($pattern === '') continue;
        $starts_wildcard = substr($pattern, 0, 1) === '*';
        $ends_wildcard   = substr($pattern, -1) === '*';
        if ($starts_wildcard && $ends_wildcard) {
            if (strpos($req_path, substr($pattern, 1, -1)) !== false) return true;
        } elseif ($starts_wildcard) {
            $needle = substr($pattern, 1);
            if (substr($req_path, -strlen($needle)) === $needle) return true;
        } elseif ($ends_wildcard) {
            $needle = substr($pattern, 0, -1);
            if (strpos($req_path, $needle) === 0) return true;
        } else {
            if ($req_path === $pattern) return true;
        }
    }
    return false;
}

/* -------------------------------------------------------------------------
 * Front-end enqueue
 * ---------------------------------------------------------------------- */

add_action('wp_enqueue_scripts', 'site_banner_enqueue');
function site_banner_enqueue() {
    wp_register_style('site-banner', SITE_BANNER_PLUGIN_URL . 'site-banner.css', array(), SITE_BANNER_VERSION);
    wp_enqueue_style('site-banner');

    $banners = array();
    for ($i = 1; $i <= site_banner_get_num_banners(); $i++) {
        $suffix = site_banner_id_suffix($i);
        if (site_banner_is_hidden($suffix)) {
            continue;
        }
        $text = (string) get_option('site_banner_text' . $suffix);
        if ($text === '') {
            continue; // No content, nothing to render.
        }

        $cta_text = site_banner_is_pro() ? (string) get_option('site_banner_cta_text' . $suffix) : '';
        $cta_url  = site_banner_is_pro() ? (string) get_option('site_banner_cta_url' . $suffix) : '';

        $banners[] = array(
            'suffix'                  => $suffix,
            'text'                    => site_banner_translate($text, 'site_banner_text' . $suffix),
            'prepend_element'         => get_option('site_banner_prepend_element' . $suffix, 'body'),
            'insert_inside_element'   => site_banner_is_pro() ? (string) get_option('site_banner_insert_inside_element' . $suffix) : '',
            'close_button_enabled'    => (bool) get_option('site_banner_close_button_enabled' . $suffix),
            'close_button_expiration' => (string) get_option('site_banner_close_button_expiration' . $suffix),
            'keep_site_css'           => (bool) get_option('site_banner_keep_site_css' . $suffix),
            'keep_site_js'            => (bool) get_option('site_banner_keep_site_js' . $suffix),
            'click_tracking_enabled'  => site_banner_is_pro() && (bool) get_option('site_banner_click_tracking_enabled' . $suffix),
            'cta_text'                => site_banner_translate($cta_text, 'site_banner_cta_text' . $suffix),
            'cta_url'                 => site_banner_translate($cta_url, 'site_banner_cta_url' . $suffix),
            'cta_bg_color'            => site_banner_is_pro() ? (string) get_option('site_banner_cta_bg_color' . $suffix) : '',
            'cta_text_color'          => site_banner_is_pro() ? (string) get_option('site_banner_cta_text_color' . $suffix) : '',
            'cta_new_tab'             => site_banner_is_pro() && (bool) get_option('site_banner_cta_new_tab' . $suffix),
            'cta_position'            => site_banner_is_pro() ? (string) get_option('site_banner_cta_position' . $suffix, 'inline') : 'inline',
        );
    }

    wp_register_script('site-banner', SITE_BANNER_PLUGIN_URL . 'site-banner.js', array(), SITE_BANNER_VERSION, true);
    wp_localize_script('site-banner', 'siteBannerParams', array(
        'banners'                  => $banners,
        'debug_mode'               => site_banner_is_pro() && (bool) get_option('site_banner_debug_mode'),
        'click_tracking_endpoint'  => site_banner_is_pro() ? (string) get_option('site_banner_click_tracking_endpoint') : '',
    ));
    wp_enqueue_script('site-banner');
}

/* -------------------------------------------------------------------------
 * <head> style emission
 *
 * One <style> block per banner. We emit every rule unconditionally with
 * sensible defaults so themes can't surprise us with cascading rules from
 * generic selectors like `.simple-banner` would be vulnerable to.
 * ---------------------------------------------------------------------- */

add_action('wp_head', 'site_banner_emit_styles');
function site_banner_emit_styles() {
    for ($i = 1; $i <= site_banner_get_num_banners(); $i++) {
        $suffix      = site_banner_id_suffix($i);
        $banner_cls  = 'site-banner' . $suffix;
        $text_cls    = 'site-banner-text' . $suffix;
        $button_cls  = 'site-banner-button' . $suffix;
        $scroll_cls  = 'site-banner-scrolling' . $suffix;
        $is_hidden   = site_banner_is_hidden($suffix);
        $text_empty  = ((string) get_option('site_banner_text' . $suffix)) === '';

        if ($is_hidden || $text_empty) {
            echo '<style id="' . esc_attr($banner_cls) . '-hide">.' . esc_attr($banner_cls) . '{display:none !important;}</style>' . "\n";
            continue;
        }

        $bg          = get_option('site_banner_color' . $suffix, '#024985');
        $text_color  = get_option('site_banner_text_color' . $suffix, '#ffffff');
        $link_color  = get_option('site_banner_link_color' . $suffix, '#f16521');
        $close_color = get_option('site_banner_close_color' . $suffix, '');
        $font_size   = get_option('site_banner_font_size' . $suffix, '');
        $z_index     = get_option('site_banner_z_index' . $suffix, '99999');
        $position    = get_option('site_banner_position' . $suffix, 'relative');
        // Pro-gate sticky-style positions: fall back to relative on free tier.
        if (in_array($position, array('fixed', 'sticky', 'footer'), true) && !site_banner_is_pro()) {
            $position = 'relative';
        }
        $custom_css  = get_option('site_banner_custom_css' . $suffix, '');
        $text_css    = get_option('site_banner_text_custom_css' . $suffix, '');
        $button_css  = get_option('site_banner_button_custom_css' . $suffix, '');
        $scroll_css  = get_option('site_banner_scrolling_custom_css' . $suffix, '');

        $css  = '.' . $banner_cls . '{background:' . $bg . ';z-index:' . (int) $z_index . ';';
        if ($position === 'footer') {
            $css .= 'position:fixed;bottom:0;';
        } else {
            $css .= 'position:' . $position . ';';
        }
        if ($custom_css !== '') {
            $css .= $custom_css;
        }
        $css .= '}';

        $css .= '.' . $banner_cls . ' .' . $text_cls . '{color:' . $text_color . ';';
        if ($font_size !== '') {
            $css .= 'font-size:' . $font_size . ';';
        }
        if ($text_css !== '') {
            $css .= $text_css;
        }
        $css .= '}';

        $css .= '.' . $banner_cls . ' .' . $text_cls . ' a{color:' . $link_color . ';}';

        $css .= '.' . $banner_cls . ' .' . $button_cls . '{';
        if ($close_color !== '') {
            $css .= 'color:' . $close_color . ';';
        }
        if ($button_css !== '') {
            $css .= $button_css;
        }
        $css .= '}';

        if ($scroll_css !== '') {
            $css .= '.' . $banner_cls . '.' . $scroll_cls . '{' . $scroll_css . '}';
        }

        // Banner #1 only: header margin/padding.
        if ($i === 1) {
            $header_margin  = get_option('site_banner_header_margin', '');
            $header_padding = get_option('site_banner_header_padding', '');
            if ($header_margin !== '') {
                $css .= 'header{margin-top:' . $header_margin . ';}';
            }
            if ($header_padding !== '') {
                $css .= 'header{padding-top:' . $header_padding . ';}';
            }
        }

        // Output is composed entirely of option values that were sanitized on save
        // (wp_strip_all_tags for the CSS textareas, wp_filter_nohtml_kses for the
        // scalars). Wrapping in <style> means no further escaping per WP conventions.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<style id="' . esc_attr($banner_cls) . '-styles">' . $css . '</style>' . "\n";
    }
}

/* -------------------------------------------------------------------------
 * Site-wide custom CSS/JS (pro)
 *
 * Per-banner option. Emitted in <head> as inline <style>/<script>. When the
 * banner is hidden, the asset is omitted unless the matching "keep when
 * closed" toggle is set. When the user clicks the close button at runtime,
 * site-banner.js removes the matching tag unless keep_* is set.
 * ---------------------------------------------------------------------- */

add_action('wp_head', 'site_banner_emit_site_assets');
function site_banner_emit_site_assets() {
    if (!site_banner_is_pro()) {
        return;
    }
    for ($i = 1; $i <= site_banner_get_num_banners(); $i++) {
        $suffix       = site_banner_id_suffix($i);
        $is_hidden    = site_banner_is_hidden($suffix);
        $css          = (string) get_option('site_banner_site_custom_css' . $suffix);
        $js           = (string) get_option('site_banner_site_custom_js' . $suffix);
        $keep_css     = (bool)   get_option('site_banner_keep_site_css' . $suffix);
        $keep_js      = (bool)   get_option('site_banner_keep_site_js' . $suffix);

        if ($css !== '' && (!$is_hidden || $keep_css)) {
            // CSS textarea is admin-only, wp_strip_all_tags-sanitized on save.
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '<style id="site-banner-site-css' . esc_attr($suffix) . '">' . $css . '</style>' . "\n";
        }
        if ($js !== '' && (!$is_hidden || $keep_js)) {
            // JS textarea is intentionally rendered verbatim. Admin-only feature.
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '<script id="site-banner-site-js' . esc_attr($suffix) . '">' . $js . '</script>' . "\n";
        }
    }
}

/* -------------------------------------------------------------------------
 * Reusable banner-markup helper
 *
 * Used by shortcode, Gutenberg block, and wp_body_open server-render path.
 * Respects all visibility rules (is_hidden + close cookie).
 * ---------------------------------------------------------------------- */
function site_banner_render_html_for_index($i) {
    if ($i < 1 || $i > site_banner_get_num_banners()) {
        return '';
    }
    $suffix = site_banner_id_suffix($i);
    if (site_banner_is_hidden($suffix)) {
        return '';
    }
    $text = site_banner_translate((string) get_option('site_banner_text' . $suffix), 'site_banner_text' . $suffix);
    if ($text === '') {
        return '';
    }
    if (get_option('site_banner_close_button_enabled' . $suffix)) {
        $cookie_name = 'sitebannerclosed' . $suffix;
        if (!empty($_COOKIE[$cookie_name])) {
            return '';
        }
    }

    $cta_html = '';
    if (site_banner_is_pro()) {
        $cta_text = site_banner_translate((string) get_option('site_banner_cta_text' . $suffix), 'site_banner_cta_text' . $suffix);
        $cta_url  = site_banner_translate((string) get_option('site_banner_cta_url' . $suffix),  'site_banner_cta_url' . $suffix);
        if ($cta_text !== '' && $cta_url !== '') {
            $new_tab     = (bool) get_option('site_banner_cta_new_tab' . $suffix);
            $bg_color    = (string) get_option('site_banner_cta_bg_color' . $suffix);
            $text_color  = (string) get_option('site_banner_cta_text_color' . $suffix);
            $position    = get_option('site_banner_cta_position' . $suffix, 'inline');
            $inline_css  = '';
            if ($bg_color !== '')   $inline_css .= 'background:' . esc_attr($bg_color) . ';';
            if ($text_color !== '') $inline_css .= 'color:' . esc_attr($text_color) . ';';
            $cls = 'site-banner-cta' . esc_attr($suffix) . ($position === 'block' ? ' site-banner-cta-block' : '');
            $cta_html = '<a class="' . $cls . '" '
                . 'href="' . esc_url($cta_url) . '"'
                . ($new_tab ? ' target="_blank" rel="noopener"' : '')
                . ($inline_css !== '' ? ' style="' . $inline_css . '"' : '')
                . '>' . esc_html($cta_text) . '</a>';
        }
    }

    $close_button = '';
    if (get_option('site_banner_close_button_enabled' . $suffix)) {
        $close_button = '<button aria-label="' . esc_attr__('Close', 'site-banner')
            . '" id="site-banner-close-button' . esc_attr($suffix) . '"'
            . ' class="site-banner-button' . esc_attr($suffix) . '">&#x2715;</button>';
    }

    return '<div id="site-banner' . esc_attr($suffix) . '" class="site-banner' . esc_attr($suffix) . '">'
        . '<div class="site-banner-text' . esc_attr($suffix) . '"><span>' . wp_kses_post($text) . '</span></div>'
        . $cta_html
        . $close_button
        . '</div>';
}

/* -------------------------------------------------------------------------
 * Shortcode: [site_banner id="N"]
 * ---------------------------------------------------------------------- */

add_shortcode('site_banner', 'site_banner_shortcode');
function site_banner_shortcode($atts) {
    if (!site_banner_is_pro()) {
        return '';
    }
    $atts = shortcode_atts(array('id' => '1'), $atts, 'site_banner');
    return site_banner_render_html_for_index((int) $atts['id']);
}

/* -------------------------------------------------------------------------
 * Gutenberg block: site-banner/banner
 * ---------------------------------------------------------------------- */

add_action('init', 'site_banner_register_block');
function site_banner_register_block() {
    if (!function_exists('register_block_type')) {
        return;
    }
    if (!site_banner_is_pro()) {
        return;
    }
    register_block_type(SITE_BANNER_PLUGIN_DIR . 'blocks/banner');
}

/** Render callback referenced by blocks/banner/block.json. */
function site_banner_render_block($attributes) {
    $i = isset($attributes['bannerId']) ? (int) $attributes['bannerId'] : 1;
    return site_banner_render_html_for_index($i);
}

/* -------------------------------------------------------------------------
 * Optional server-side render via wp_body_open (banner #1 only)
 * ---------------------------------------------------------------------- */

add_action('init', 'site_banner_maybe_register_body_open');
function site_banner_maybe_register_body_open() {
    if (!function_exists('wp_body_open')) {
        return;
    }
    if (!get_option('site_banner_wp_body_open_enabled')) {
        return;
    }
    add_action('wp_body_open', 'site_banner_render_body_open');
}

function site_banner_render_body_open() {
    // Helper returns fully-escaped HTML (esc_attr / wp_kses_post / esc_url applied internally).
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo site_banner_render_html_for_index(1);
}

/* -------------------------------------------------------------------------
 * Cache flushing on settings save
 *
 * The settings page submits a hidden site_banner_cache_buster field whose value
 * is flipped on every render, guaranteeing update_option fires and reaches
 * the cache-buster action hook.
 * ---------------------------------------------------------------------- */

add_action('add_option_site_banner_cache_buster',    'site_banner_flush_caches', 10, 0);
add_action('update_option_site_banner_cache_buster', 'site_banner_flush_caches', 10, 0);

function site_banner_flush_caches() {
    try {
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
        if (function_exists('wp_cache_clean_cache')) {
            global $file_prefix;
            wp_cache_clean_cache($file_prefix);
        }
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }
        if (class_exists('autoptimizeCache') && method_exists('autoptimizeCache', 'clearall')) {
            autoptimizeCache::clearall();
        }
        if (class_exists('LiteSpeed_Cache_API') && method_exists('LiteSpeed_Cache_API', 'purge_all')) {
            LiteSpeed_Cache_API::purge_all();
        }
        global $wp_fastest_cache;
        if (is_object($wp_fastest_cache) && method_exists($wp_fastest_cache, 'deleteCache')) {
            $wp_fastest_cache->deleteCache();
        }
    } catch (Exception $e) {
        // Best-effort. We never want a third-party cache plugin error to
        // prevent the user from saving banner settings.
    }
}
