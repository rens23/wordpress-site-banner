<?php
/**
 * Per-banner pro fields. Expects $i, $suffix, $is_pro in scope.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

if (!defined('ABSPATH')) {
    exit;
}

$opt = function ($key) use ($suffix) {
    return get_option($key . $suffix, '');
};
$pro_attr = $is_pro ? '' : 'disabled';
$pro_placeholder = $is_pro ? '' : esc_attr__('Pro feature — add a license to enable', 'site-banner');
$section_hidden = $i > 1;
?>
<div class="sb-section sb-banner-section sb-banner-pro-section" data-suffix="<?php echo esc_attr($suffix); ?>"<?php echo $section_hidden ? ' style="display:none;"' : ''; ?>>
    <h2>
        <?php /* translators: %d: banner number */ printf(esc_html__('Banner #%d — Pro', 'site-banner'), (int) $i); ?>
        <?php if (!$is_pro): ?>
            <a class="button button-secondary" href="https://rensh.gumroad.com/l/site-banner-plugin" target="_blank" rel="noopener">
                <?php esc_html_e('Unlock with license', 'site-banner'); ?>
            </a>
        <?php endif; ?>
    </h2>

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">
                <label for="site_banner_cta_text<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Call-to-action button', 'site-banner'); ?></label>
                <p class="description"><?php esc_html_e('Renders a styled button next to the banner text. Click tracking (if enabled) also covers this button.', 'site-banner'); ?></p>
            </th>
            <td>
                <p>
                    <label for="site_banner_cta_text<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Button text', 'site-banner'); ?></label><br>
                    <input type="text" id="site_banner_cta_text<?php echo esc_attr($suffix); ?>"
                           name="site_banner_cta_text<?php echo esc_attr($suffix); ?>"
                           value="<?php echo esc_attr($opt('site_banner_cta_text')); ?>"
                           class="regular-text"
                           placeholder="<?php echo esc_attr__('Learn more', 'site-banner'); ?>"
                           <?php echo esc_attr($pro_attr); ?>>
                </p>
                <p>
                    <label for="site_banner_cta_url<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Button URL', 'site-banner'); ?></label><br>
                    <input type="url" id="site_banner_cta_url<?php echo esc_attr($suffix); ?>"
                           name="site_banner_cta_url<?php echo esc_attr($suffix); ?>"
                           value="<?php echo esc_attr($opt('site_banner_cta_url')); ?>"
                           class="regular-text"
                           placeholder="https://example.com/landing"
                           <?php echo esc_attr($pro_attr); ?>>
                </p>
                <div class="sb-grid-two">
                    <div>
                        <label for="site_banner_cta_bg_color<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Background color', 'site-banner'); ?></label>
                        <div class="sb-color-input-group">
                            <input type="text" id="site_banner_cta_bg_color<?php echo esc_attr($suffix); ?>"
                                   name="site_banner_cta_bg_color<?php echo esc_attr($suffix); ?>"
                                   value="<?php echo esc_attr($opt('site_banner_cta_bg_color')); ?>"
                                   placeholder="<?php esc_attr_e('inherit', 'site-banner'); ?>"
                                   <?php echo esc_attr($pro_attr); ?>>
                            <input type="color" class="sb-color-picker" id="site_banner_cta_bg_color<?php echo esc_attr($suffix); ?>_picker"
                                   value="<?php echo esc_attr($opt('site_banner_cta_bg_color') ?: '#ffffff'); ?>"
                                   <?php echo esc_attr($pro_attr); ?>>
                        </div>
                    </div>
                    <div>
                        <label for="site_banner_cta_text_color<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Text color', 'site-banner'); ?></label>
                        <div class="sb-color-input-group">
                            <input type="text" id="site_banner_cta_text_color<?php echo esc_attr($suffix); ?>"
                                   name="site_banner_cta_text_color<?php echo esc_attr($suffix); ?>"
                                   value="<?php echo esc_attr($opt('site_banner_cta_text_color')); ?>"
                                   placeholder="<?php esc_attr_e('inherit', 'site-banner'); ?>"
                                   <?php echo esc_attr($pro_attr); ?>>
                            <input type="color" class="sb-color-picker" id="site_banner_cta_text_color<?php echo esc_attr($suffix); ?>_picker"
                                   value="<?php echo esc_attr($opt('site_banner_cta_text_color') ?: '#000000'); ?>"
                                   <?php echo esc_attr($pro_attr); ?>>
                        </div>
                    </div>
                </div>
                <p style="margin-top:8px;">
                    <label>
                        <input type="checkbox" name="site_banner_cta_new_tab<?php echo esc_attr($suffix); ?>"
                            <?php checked((bool) $opt('site_banner_cta_new_tab')); ?>
                            <?php echo esc_attr($pro_attr); ?>>
                        <?php esc_html_e('Open in a new tab', 'site-banner'); ?>
                    </label>
                </p>
                <fieldset style="margin-top:6px;">
                    <legend><strong><?php esc_html_e('Button position', 'site-banner'); ?></strong></legend>
                    <?php
                    $current_cta_pos = $opt('site_banner_cta_position') ?: 'inline';
                    $cta_positions = array(
                        'inline' => __('Next to the text (inline)', 'site-banner'),
                        'block'  => __('Below the text (block)', 'site-banner'),
                    );
                    foreach ($cta_positions as $val => $label) {
                        printf(
                            '<label style="margin-right:14px;"><input type="radio" name="site_banner_cta_position%1$s" value="%2$s" %3$s %4$s> %5$s</label>',
                            esc_attr($suffix),
                            esc_attr($val),
                            checked($current_cta_pos, $val, false),
                            esc_attr($pro_attr),
                            esc_html($label)
                        );
                    }
                    ?>
                </fieldset>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <?php esc_html_e('Translations (WPML / Polylang)', 'site-banner'); ?>
                <p class="description"><?php esc_html_e('Pro: banner text and CTA fields are surfaced in WPML String Translation under the "Site Banner" context.', 'site-banner'); ?></p>
            </th>
            <td>
                <?php if ($is_pro): ?>
                    <p class="description">
                        <?php echo wp_kses(
                            __('After saving, go to <strong>WPML → String Translation</strong> (or <strong>Polylang → Strings</strong>) and filter by <code>admin_texts_plugin_site-banner</code> to translate per language. Polylang requires compatibility mode for the WPML hook.', 'site-banner'),
                            array('strong' => array(), 'code' => array())
                        ); ?>
                    </p>
                <?php else: ?>
                    <p class="description"><?php esc_html_e('Add a license to enable per-language translation of banner text and CTA strings.', 'site-banner'); ?></p>
                <?php endif; ?>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="site_banner_insert_inside_element<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Advanced placement', 'site-banner'); ?></label>
                <p class="description"><?php esc_html_e('Insert banner inside a specific element. Overrides the basic placement.', 'site-banner'); ?></p>
            </th>
            <td>
                <input type="text" id="site_banner_insert_inside_element<?php echo esc_attr($suffix); ?>"
                       name="site_banner_insert_inside_element<?php echo esc_attr($suffix); ?>"
                       value="<?php echo esc_attr($opt('site_banner_insert_inside_element')); ?>"
                       class="regular-text"
                       placeholder="<?php echo esc_attr(__('e.g. header, #main-nav, .site-header', 'site-banner')); ?>"
                       <?php echo esc_attr($pro_attr); ?>>
                <p class="description"><?php echo wp_kses(__('CSS selector. Uses <code>document.querySelector()</code>.', 'site-banner'), array('code' => array())); ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Schedule', 'site-banner'); ?></th>
            <td>
                <?php
                $format_dt = function ($v) {
                    if (!$v) return '';
                    $t = strtotime($v);
                    return $t ? gmdate('Y-m-d\TH:i', $t) : '';
                };
                ?>
                <div class="sb-grid-two">
                    <div>
                        <label for="site_banner_start_after_date<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Start after (UTC)', 'site-banner'); ?></label>
                        <input type="datetime-local" id="site_banner_start_after_date<?php echo esc_attr($suffix); ?>"
                               name="site_banner_start_after_date<?php echo esc_attr($suffix); ?>"
                               value="<?php echo esc_attr($format_dt($opt('site_banner_start_after_date'))); ?>"
                               <?php echo esc_attr($pro_attr); ?>>
                    </div>
                    <div>
                        <label for="site_banner_remove_after_date<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Remove after (UTC)', 'site-banner'); ?></label>
                        <input type="datetime-local" id="site_banner_remove_after_date<?php echo esc_attr($suffix); ?>"
                               name="site_banner_remove_after_date<?php echo esc_attr($suffix); ?>"
                               value="<?php echo esc_attr($format_dt($opt('site_banner_remove_after_date'))); ?>"
                               <?php echo esc_attr($pro_attr); ?>>
                    </div>
                </div>
                <p class="description"><?php esc_html_e('Values are interpreted as UTC. Leave a field blank to disable that bound.', 'site-banner'); ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Page exclusions', 'site-banner'); ?></th>
            <td>
                <p>
                    <label>
                        <input type="checkbox" name="site_banner_disabled_on_posts<?php echo esc_attr($suffix); ?>"
                            <?php checked((bool) $opt('site_banner_disabled_on_posts')); ?>
                            <?php echo esc_attr($pro_attr); ?>>
                        <?php esc_html_e('Disable on all posts', 'site-banner'); ?>
                    </label>
                </p>
                <p>
                    <label for="site_banner_disabled_paths<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Disable by path', 'site-banner'); ?></label><br>
                    <input type="text" id="site_banner_disabled_paths<?php echo esc_attr($suffix); ?>"
                           name="site_banner_disabled_paths<?php echo esc_attr($suffix); ?>"
                           value="<?php echo esc_attr($opt('site_banner_disabled_paths')); ?>"
                           class="regular-text"
                           placeholder="/shop,/cart,/shop*,*shop*"
                           <?php echo esc_attr($pro_attr); ?>>
                    <span class="description">
                        <?php echo wp_kses(__('Comma-separated. Use <code>*</code> for wildcards: <code>/shop*</code> (prefix), <code>*shop</code> (suffix), <code>*shop*</code> (contains).', 'site-banner'), array('code' => array())); ?>
                    </span>
                </p>
                <p>
                    <label><?php esc_html_e('Disable on specific pages', 'site-banner'); ?></label>
                </p>
                <?php
                $checked_ids = array_filter(array_map('trim', explode(',', (string) $opt('site_banner_disabled_pages'))));
                $pages = get_pages(array('number' => 200));
                if (empty($pages)) {
                    echo '<p class="description">' . esc_html__('No pages found.', 'site-banner') . '</p>';
                } else {
                    echo '<div class="sb-page-checklist" data-suffix="' . esc_attr($suffix) . '">';
                    foreach ($pages as $page) {
                        $id_str = (string) $page->ID;
                        printf(
                            '<label><input type="checkbox" class="sb-disabled-page-cb" value="%1$s" %2$s %3$s> %4$s <code>%5$s</code></label>',
                            esc_attr($id_str),
                            checked(in_array($id_str, $checked_ids, true), true, false),
                            esc_attr($pro_attr),
                            esc_html($page->post_title),
                            esc_html(get_page_uri($page->ID))
                        );
                    }
                    echo '</div>';
                }
                ?>
                <input type="hidden" id="site_banner_disabled_pages<?php echo esc_attr($suffix); ?>"
                       name="site_banner_disabled_pages<?php echo esc_attr($suffix); ?>"
                       value="<?php echo esc_attr($opt('site_banner_disabled_pages')); ?>">
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Site-wide custom CSS', 'site-banner'); ?></th>
            <td>
                <textarea id="site_banner_site_custom_css<?php echo esc_attr($suffix); ?>"
                          name="site_banner_site_custom_css<?php echo esc_attr($suffix); ?>"
                          class="large-text code" rows="5" <?php echo esc_attr($pro_attr); ?>><?php
                    echo esc_textarea($opt('site_banner_site_custom_css'));
                ?></textarea>
                <p>
                    <label>
                        <input type="checkbox" name="site_banner_keep_site_css<?php echo esc_attr($suffix); ?>"
                            <?php checked((bool) $opt('site_banner_keep_site_css')); ?>
                            <?php echo esc_attr($pro_attr); ?>>
                        <?php esc_html_e('Keep CSS when banner is hidden or closed', 'site-banner'); ?>
                    </label>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <?php esc_html_e('Audience', 'site-banner'); ?>
                <p class="description"><?php esc_html_e('Who can see this banner.', 'site-banner'); ?></p>
            </th>
            <td>
                <?php
                $visibility = $opt('site_banner_visibility') ?: 'everyone';
                $vis_options = array(
                    'everyone'        => __('Everyone', 'site-banner'),
                    'logged_in'       => __('Logged-in users only', 'site-banner'),
                    'logged_out'      => __('Logged-out visitors only', 'site-banner'),
                    'specific_roles'  => __('Specific roles only', 'site-banner'),
                );
                foreach ($vis_options as $value => $label) {
                    printf(
                        '<label style="display:block;margin-bottom:4px;"><input type="radio" name="site_banner_visibility%1$s" value="%2$s" %3$s %4$s> %5$s</label>',
                        esc_attr($suffix),
                        esc_attr($value),
                        checked($visibility, $value, false),
                        esc_attr($pro_attr),
                        esc_html($label)
                    );
                }
                ?>
                <div class="sb-role-targeting" data-suffix="<?php echo esc_attr($suffix); ?>" style="<?php echo $visibility === 'specific_roles' ? '' : 'display:none;'; ?>">
                    <p><strong><?php esc_html_e('Allowed roles:', 'site-banner'); ?></strong></p>
                    <div class="sb-target-roles-list">
                        <?php
                        $current_roles = array_filter(array_map('trim', explode(',', (string) $opt('site_banner_visibility_roles'))));
                        foreach (get_editable_roles() as $role_name => $role_info) {
                            $checked = in_array($role_name, $current_roles, true);
                            printf(
                                '<label><input type="checkbox" class="sb-target-role-cb" value="%1$s" %2$s %3$s> %4$s</label>',
                                esc_attr($role_name),
                                checked($checked, true, false),
                                esc_attr($pro_attr),
                                esc_html(translate_user_role($role_info['name']))
                            );
                        }
                        ?>
                    </div>
                    <input type="hidden" id="site_banner_visibility_roles<?php echo esc_attr($suffix); ?>"
                           name="site_banner_visibility_roles<?php echo esc_attr($suffix); ?>"
                           value="<?php echo esc_attr($opt('site_banner_visibility_roles')); ?>">
                </div>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Click tracking', 'site-banner'); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="site_banner_click_tracking_enabled<?php echo esc_attr($suffix); ?>"
                        <?php checked((bool) $opt('site_banner_click_tracking_enabled')); ?>
                        <?php echo esc_attr($pro_attr); ?>>
                    <?php esc_html_e('Track clicks on links inside this banner', 'site-banner'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Fires a CustomEvent("siteBanner:linkClick") on document. If a global endpoint URL is set under General — Pro, also POSTs the click data there.', 'site-banner'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Site-wide custom JavaScript', 'site-banner'); ?></th>
            <td>
                <textarea id="site_banner_site_custom_js<?php echo esc_attr($suffix); ?>"
                          name="site_banner_site_custom_js<?php echo esc_attr($suffix); ?>"
                          class="large-text code" rows="5" <?php echo esc_attr($pro_attr); ?>><?php
                    echo esc_textarea($opt('site_banner_site_custom_js'));
                ?></textarea>
                <p>
                    <label>
                        <input type="checkbox" name="site_banner_keep_site_js<?php echo esc_attr($suffix); ?>"
                            <?php checked((bool) $opt('site_banner_keep_site_js')); ?>
                            <?php echo esc_attr($pro_attr); ?>>
                        <?php esc_html_e('Keep JS when banner is hidden or closed', 'site-banner'); ?>
                    </label>
                </p>
                <p class="description"><?php esc_html_e('Stored and rendered verbatim. Only administrators can edit.', 'site-banner'); ?></p>
            </td>
        </tr>
    </table>
</div>
