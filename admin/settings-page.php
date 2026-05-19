<?php
/**
 * Settings page template.
 *
 * Loaded from site_banner_render_settings_page().
 * All constants and helper functions live in site-banner.php.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can(SITE_BANNER_CAPABILITY)) {
    wp_die(esc_html__('You do not have permission to access this page.', 'site-banner'));
}

// Enqueue admin assets for this page only.
wp_enqueue_style(
    'site-banner-admin',
    SITE_BANNER_PLUGIN_URL . 'admin/styles/main.css',
    array(),
    SITE_BANNER_VERSION
);
wp_enqueue_style(
    'site-banner-admin-preview',
    SITE_BANNER_PLUGIN_URL . 'admin/styles/preview-banner.css',
    array(),
    SITE_BANNER_VERSION
);
wp_enqueue_style(
    'site-banner', // Front-end stylesheet — reused so preview matches site
    SITE_BANNER_PLUGIN_URL . 'site-banner.css',
    array(),
    SITE_BANNER_VERSION
);
wp_enqueue_script(
    'site-banner-purify',
    SITE_BANNER_PLUGIN_URL . 'vendor/purify.min.js',
    array(),
    '3.0.0',
    true
);
wp_enqueue_script(
    'site-banner-admin-preview',
    SITE_BANNER_PLUGIN_URL . 'admin/preview.js',
    array('site-banner-purify'),
    SITE_BANNER_VERSION,
    true
);
wp_localize_script('site-banner-admin-preview', 'siteBannerPreview', array(
    'numBanners' => site_banner_get_num_banners(),
));

$num_banners = site_banner_get_num_banners();
$is_pro      = site_banner_is_pro();
?>
<div class="wrap site-banner-admin">
    <h1><?php esc_html_e('Site Banner Settings', 'site-banner'); ?></h1>
    <p class="sb-intro">
        <?php esc_html_e('Create and manage banners for your website.', 'site-banner'); ?>
        <?php
        echo wp_kses(
            __('Links must use HTML <code>&lt;a&gt;</code> tags, e.g. <code>This is a &lt;a href="https://example.com"&gt;link&lt;/a&gt;</code>.', 'site-banner'),
            array('code' => array())
        );
        ?>
    </p>

    <form method="post" action="options.php" class="sb-settings-form">
        <?php settings_fields(SITE_BANNER_SETTINGS_GROUP); ?>

        <div class="sb-section sb-license-section">
            <h2><?php esc_html_e('License', 'site-banner'); ?></h2>
            <?php if ($is_pro): ?>
                <p class="sb-license-status sb-license-status-valid">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Pro license active — multi-banner and all pro features unlocked.', 'site-banner'); ?>
                </p>
            <?php else: ?>
                <p class="sb-license-status sb-license-status-invalid">
                    <?php esc_html_e('No valid license. Multi-banner and pro features are disabled.', 'site-banner'); ?>
                    <a class="button button-secondary" href="https://rensh.gumroad.com/l/site-banner-plugin" target="_blank" rel="noopener">
                        <?php esc_html_e('Get a license', 'site-banner'); ?>
                    </a>
                </p>
            <?php endif; ?>

            <?php $verify_result = isset($GLOBALS['site_banner_verify_result']) ? $GLOBALS['site_banner_verify_result'] : ''; ?>
            <?php if ($verify_result === 'ok'): ?>
                <div class="notice notice-success inline"><p><?php esc_html_e('Verify succeeded — license is valid.', 'site-banner'); ?></p></div>
            <?php elseif ($verify_result === 'fail'): ?>
                <div class="notice notice-error inline"><p><?php esc_html_e('Verify failed — see the Last check diagnostic below.', 'site-banner'); ?></p></div>
            <?php elseif ($verify_result === 'no_key'): ?>
                <div class="notice notice-warning inline"><p><?php esc_html_e('No license key saved. Paste your key above and click "Save Changes" — saving auto-activates the site.', 'site-banner'); ?></p></div>
            <?php elseif ($verify_result === 'deactivated'): ?>
                <div class="notice notice-success inline"><p><?php esc_html_e('License removed. This site has been deactivated.', 'site-banner'); ?></p></div>
            <?php elseif ($verify_result === 'bad_nonce'): ?>
                <div class="notice notice-error inline"><p><?php esc_html_e('Security check failed. Reload the page and try again.', 'site-banner'); ?></p></div>
            <?php endif; ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="site_banner_license_key"><?php esc_html_e('License key', 'site-banner'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="site_banner_license_key" name="site_banner_license_key"
                               value="<?php echo esc_attr(get_option('site_banner_license_key')); ?>"
                               class="regular-text" autocomplete="off">
                        <p class="description"><?php esc_html_e('Paste the license key you received after purchase. On save, the plugin activates this site against the license server and caches the result for one hour. Removing or changing the key automatically deactivates the previous seat.', 'site-banner'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Verify now', 'site-banner'); ?></th>
                    <td>
                        <?php // Submit button inside the main form. Saves all fields (including a freshly-pasted license key) via options.php, then a wp_redirect filter appends the verify flag + nonce so the next render also runs verify. ?>
                        <button type="submit"
                                name="site_banner_verify_after_save"
                                value="1"
                                class="button button-secondary">
                            <?php esc_html_e('Save & verify license', 'site-banner'); ?>
                        </button>

                        <?php if (get_option('site_banner_license_key')): ?>
                            <?php
                            $deactivate_url = wp_nonce_url(
                                add_query_arg(
                                    array('page' => SITE_BANNER_MENU_SLUG, 'site_banner_deactivate' => '1'),
                                    admin_url('admin.php')
                                ),
                                'site_banner_deactivate'
                            );
                            ?>
                            <a href="<?php echo esc_url($deactivate_url); ?>"
                               class="button button-link-delete"
                               style="margin-left:8px;color:#b32d2e;"
                               onclick="return confirm('<?php echo esc_js(__('Remove the license key and deactivate this site? Pro features will turn off.', 'site-banner')); ?>');">
                                <?php esc_html_e('Deactivate this site', 'site-banner'); ?>
                            </a>
                        <?php endif; ?>

                        <p class="description"><?php esc_html_e('Save & verify writes the form (including a newly-pasted license key) and re-checks the license in one click. Deactivate clears the key and frees the seat.', 'site-banner'); ?></p>
                    </td>
                </tr>
                <?php $trace = get_option('site_banner_verify_trace'); if (is_array($trace) && !empty($trace)): ?>
                    <tr>
                        <th scope="row"><?php esc_html_e('Verify trace', 'site-banner'); ?></th>
                        <td>
                            <details class="sb-license-diag">
                                <summary><strong><?php esc_html_e('Last verify-now run', 'site-banner'); ?></strong> — <?php echo (int) count($trace); ?> <?php esc_html_e('steps', 'site-banner'); ?></summary>
                                <pre class="sb-license-body"><?php echo esc_html(implode("\n", $trace)); ?></pre>
                            </details>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php $last = site_banner_get_last_license_check(); if ($last): ?>
                    <tr>
                        <th scope="row"><?php esc_html_e('Last check', 'site-banner'); ?></th>
                        <td>
                            <details class="sb-license-diag" <?php echo $last['ok'] ? '' : 'open'; ?>>
                                <summary>
                                    <strong><?php echo $last['ok']
                                        ? esc_html__('Success', 'site-banner')
                                        : esc_html__('Failure', 'site-banner'); ?></strong>
                                    — <?php echo esc_html(human_time_diff($last['timestamp']) . ' ' . __('ago', 'site-banner')); ?>
                                    — <code><?php echo esc_html($last['action']); ?></code>
                                    — HTTP <code><?php echo (int) $last['http_code']; ?></code>
                                </summary>
                                <p><strong><?php esc_html_e('Note:', 'site-banner'); ?></strong> <?php echo esc_html($last['note']); ?></p>
                                <?php if (!empty($last['url'])): ?>
                                    <p><strong><?php esc_html_e('Endpoint:', 'site-banner'); ?></strong> <code><?php echo esc_html($last['url']); ?></code></p>
                                <?php endif; ?>
                                <?php if (!empty($last['body_excerpt'])): ?>
                                    <p><strong><?php esc_html_e('Response body (truncated):', 'site-banner'); ?></strong></p>
                                    <pre class="sb-license-body"><?php echo esc_html($last['body_excerpt']); ?></pre>
                                <?php endif; ?>
                            </details>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="sb-previews sb-previews-sticky">
            <?php for ($i = 1; $i <= $num_banners; $i++) {
                $suffix = site_banner_id_suffix($i);
                include SITE_BANNER_PLUGIN_DIR . 'admin/preview-banner.php';
            } ?>
            <p class="sb-note"><em><?php esc_html_e('Note: styles may vary based on your theme\'s CSS. The preview above follows you as you scroll.', 'site-banner'); ?></em></p>
        </div>

        <?php if ($num_banners > 1): ?>
            <div class="sb-section sb-banner-selector">
                <label for="sb_banner_selector"><strong><?php esc_html_e('Editing:', 'site-banner'); ?></strong></label>
                <select id="sb_banner_selector">
                    <?php for ($i = 1; $i <= $num_banners; $i++): ?>
                        <option value="<?php echo esc_attr(site_banner_id_suffix($i)); ?>">
                            <?php /* translators: %d: banner number */ printf(esc_html__('Banner #%d', 'site-banner'), $i); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $num_banners; $i++) {
            $suffix = site_banner_id_suffix($i);
            include SITE_BANNER_PLUGIN_DIR . 'admin/banner-fields.php';
            include SITE_BANNER_PLUGIN_DIR . 'admin/banner-fields-pro.php';
        } ?>

        <?php include SITE_BANNER_PLUGIN_DIR . 'admin/general-settings-pro.php'; ?>

        <div class="sb-mobile-alert">
            <strong><?php esc_html_e('Mobile testing reminder:', 'site-banner'); ?></strong>
            <?php esc_html_e('test your banner on mobile devices — theme headers often change CSS for narrow viewports.', 'site-banner'); ?>
        </div>

        <?php
        // Flip the buster value on every render so update_option always fires.
        $buster = get_option('site_banner_cache_buster') ? '' : '1';
        printf('<input type="hidden" name="site_banner_cache_buster" value="%s" />', esc_attr($buster));
        ?>

        <div class="sb-sticky-save">
            <?php submit_button(__('Save Changes', 'site-banner'), 'primary large', 'submit', false); ?>
        </div>
    </form>
</div>
