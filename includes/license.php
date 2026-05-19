<?php
/**
 * License verification via LicenseSeat.
 *
 * Flow:
 *   1. Customer buys on Gumroad (Gumroad is just the storefront).
 *   2. Gumroad webhook -> LicenseSeat issues a LicenseSeat license key.
 *   3. Customer pastes the LicenseSeat key into this plugin.
 *   4. On save, plugin POSTs /activate to bind this WP site's fingerprint to the key.
 *   5. site_banner_is_pro() POSTs /validate against the cached transient.
 *   6. When the key is cleared/changed, plugin POSTs /deactivate for the old key.
 *
 * Fingerprint:
 *   A UUID generated once and stored in the `site_banner_device_fingerprint`
 *   option. Stable across URL changes and plugin updates; one fingerprint per
 *   WP install.
 *
 * API key:
 *   pk_live_* style. LicenseSeat's SDKs embed this directly in client code,
 *   so it is treated as publishable, not secret. The customer (site owner)
 *   can override via wp-config.php by defining SITE_BANNER_LICENSESEAT_API_KEY
 *   before this file loads — useful if the publisher rotates the key.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('SITE_BANNER_LICENSESEAT_API_KEY')) {
    define('SITE_BANNER_LICENSESEAT_API_KEY', 'pk_live_7YWySwTczBSfsYb91aVz1Hk14cfJx5r62');
}
if (!defined('SITE_BANNER_LICENSESEAT_PRODUCT_SLUG')) {
    define('SITE_BANNER_LICENSESEAT_PRODUCT_SLUG', 'site-banner-wordpress-plugin');
}
if (!defined('SITE_BANNER_LICENSESEAT_BASE')) {
    define('SITE_BANNER_LICENSESEAT_BASE', 'https://licenseseat.com/api/v1');
}

define('SITE_BANNER_LICENSE_TRANSIENT_PREFIX', 'site_banner_license_v2_');

/**
 * Return this install's stable device fingerprint, generating one on first use.
 */
function site_banner_get_device_fingerprint() {
    $fp = get_option('site_banner_device_fingerprint');
    if (!$fp) {
        // wp_generate_uuid4 exists in every supported WP version (since 4.4).
        $fp = wp_generate_uuid4();
        update_option('site_banner_device_fingerprint', $fp, false);
    }
    return $fp;
}

/**
 * Public: is the pro tier active right now?
 *
 * Hits the validate endpoint on cache miss; otherwise returns the cached
 * verdict. Failure cases (network error, missing key, invalid response) all
 * collapse to "not pro" — fail-closed.
 */
function site_banner_is_pro() {
    $key = (string) get_option('site_banner_license_key', '');
    if ($key === '') {
        return false;
    }

    $cache_key = SITE_BANNER_LICENSE_TRANSIENT_PREFIX . md5($key);
    $cached = get_transient($cache_key);
    if ($cached === 'valid')   return true;
    if ($cached === 'invalid') return false;

    $valid = site_banner_validate_with_auto_activate($key) === true;

    set_transient($cache_key, $valid ? 'valid' : 'invalid',
        $valid ? HOUR_IN_SECONDS : 5 * MINUTE_IN_SECONDS);

    return $valid;
}

/**
 * Force a fresh check (bypassing the transient).
 *
 * Returns true if the license is valid, false otherwise. Always records a
 * `Last check` diagnostic, even when the key is empty, so the panel doesn't
 * show stale info from a previous action.
 */
function site_banner_force_verify_license() {
    $key = (string) get_option('site_banner_license_key', '');
    if ($key === '') {
        site_banner_record_license_check('validate', '', 0,
            'No license key saved. Paste your key and click "Save Changes" — that auto-activates the site.',
            false, '');
        return false;
    }
    delete_transient(SITE_BANNER_LICENSE_TRANSIENT_PREFIX . md5($key));
    return site_banner_is_pro();
}

/**
 * Call a LicenseSeat license endpoint (activate / validate / deactivate).
 * Returns true if the response indicates success/validity, false otherwise.
 *
 * Records the call (endpoint, HTTP status, body excerpt, verdict) in the
 * `site_banner_license_last_check` option so the settings page can display
 * what happened without the user needing server logs.
 */
function site_banner_licenseseat_call($action, $license_key) {
    if (!in_array($action, array('activate', 'validate', 'deactivate'), true)) {
        return false;
    }
    if ($license_key === '') {
        site_banner_record_license_check($action, '', 0, 'No license key set', false, '');
        return false;
    }

    $url = SITE_BANNER_LICENSESEAT_BASE
        . '/products/' . rawurlencode(SITE_BANNER_LICENSESEAT_PRODUCT_SLUG)
        . '/licenses/' . rawurlencode($license_key)
        . '/' . $action;

    $payload = array(
        'fingerprint' => site_banner_get_device_fingerprint(),
    );
    // On activate, label the seat with the site host so it's identifiable in the LicenseSeat dashboard.
    if ($action === 'activate') {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);
        if ($host) {
            $payload['device_name'] = $host;
        }
    }

    $response = wp_remote_post($url, array(
        'timeout' => 10,
        'headers' => array(
            'Authorization' => 'Bearer ' . SITE_BANNER_LICENSESEAT_API_KEY,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ),
        'body' => wp_json_encode($payload),
    ));

    if (is_wp_error($response)) {
        $msg = $response->get_error_message();
        site_banner_record_license_check($action, $url, 0, 'Network error: ' . $msg, false, '');
        return false;
    }
    $code = (int) wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($action === 'activate' || $action === 'deactivate') {
        $ok = ($code >= 200 && $code < 300)
            || ($action === 'activate' && ($code === 409 || $code === 422));
        $note = $ok
            ? ($action === 'activate' ? 'Activated' : 'Deactivated')
            : 'API returned non-success status';
        site_banner_record_license_check($action, $url, $code, $note, $ok, $body);
        return $ok;
    }

    // Validate: docs response shape { "object": "validation_result", "valid": bool, ... }.
    if ($code !== 200 || !is_array($data)) {
        $note = $code === 200
            ? 'Malformed response (no JSON body)'
            : 'Non-200 response (HTTP ' . $code . ')';
        if (is_array($data) && isset($data['error']) && is_array($data['error'])) {
            if (isset($data['error']['message'])) {
                $note .= ' — ' . $data['error']['message'];
            }
            if (isset($data['error']['code'])) {
                $note .= ' [' . $data['error']['code'] . ']';
            }
        }
        site_banner_record_license_check('validate', $url, $code, $note, false, $body);
        return false;
    }
    $valid = isset($data['valid']) ? (bool) $data['valid'] : false;
    if ($valid) {
        $note = 'License valid';
    } else {
        $note = isset($data['valid']) ? 'License invalid' : 'Response missing `valid` field';
        if (isset($data['message'])) $note .= ': ' . $data['message'];
        if (isset($data['code']))    $note .= ' [' . $data['code'] . ']';
    }
    site_banner_record_license_check('validate', $url, $code, $note, $valid, $body);
    return $valid;
}

/**
 * Run validate and, if it returns `device_not_activated`, attempt activate
 * and re-validate once. Returns the final verdict.
 *
 * This self-heals the common case where the user pastes a license key into
 * a fresh install but the save-time activate hook didn't fire (option value
 * unchanged, hook order, etc.). Limited to one auto-activate retry per call
 * to avoid loops.
 */
function site_banner_validate_with_auto_activate($key) {
    $valid = site_banner_licenseseat_call('validate', $key);
    if ($valid) {
        return true;
    }
    $last = site_banner_get_last_license_check();
    $body = is_array($last) ? (string) $last['body_excerpt'] : '';
    if (stripos($body, 'device_not_activated') === false) {
        return false;
    }
    if (!site_banner_licenseseat_call('activate', $key)) {
        return false;
    }
    return site_banner_licenseseat_call('validate', $key);
}

/**
 * Persist the most recent license check so the settings page can render
 * a diagnostic summary. Stored as a single option (not a transient) so the
 * record survives transient flushes.
 */
function site_banner_record_license_check($action, $url, $http_code, $note, $ok, $body_excerpt) {
    update_option('site_banner_license_last_check', array(
        'timestamp'    => time(),
        'action'       => $action,
        'url'          => $url,
        'http_code'    => (int) $http_code,
        'note'         => (string) $note,
        'ok'           => (bool) $ok,
        'body_excerpt' => substr((string) $body_excerpt, 0, 800),
    ), false);
}

/**
 * Read the most recent license check record, or null if none.
 */
function site_banner_get_last_license_check() {
    $rec = get_option('site_banner_license_last_check');
    return is_array($rec) ? $rec : null;
}

/* -------------------------------------------------------------------------
 * Activate / deactivate hooks on license-key changes
 * ---------------------------------------------------------------------- */

add_action('update_option_site_banner_license_key', 'site_banner_on_license_key_change', 10, 2);
add_action('add_option_site_banner_license_key',    'site_banner_on_license_key_add',    10, 2);

function site_banner_on_license_key_change($old_value, $new_value) {
    $old = (string) $old_value;
    $new = (string) $new_value;

    if ($old !== '' && $old !== $new) {
        site_banner_licenseseat_call('deactivate', $old);
        delete_transient(SITE_BANNER_LICENSE_TRANSIENT_PREFIX . md5($old));
    }
    if ($new !== '' && $new !== $old) {
        site_banner_licenseseat_call('activate', $new);
        delete_transient(SITE_BANNER_LICENSE_TRANSIENT_PREFIX . md5($new));
    }
}

function site_banner_on_license_key_add($option, $value) {
    $new = (string) $value;
    if ($new === '') return;
    site_banner_licenseseat_call('activate', $new);
    delete_transient(SITE_BANNER_LICENSE_TRANSIENT_PREFIX . md5($new));
}
