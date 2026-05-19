<?php
/**
 * Per-banner field block (free tier).
 *
 * Expects $i (1-indexed banner number) and $suffix (e.g. "_1") in scope.
 *
 * Variables here are template-locals, included from site_banner_render_settings_page().
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

if (!defined('ABSPATH')) {
    exit;
}

$opt = function ($key) use ($suffix) {
    return get_option($key . $suffix, '');
};
$is_checked = function ($v) {
    return $v ? 'checked' : '';
};
$section_hidden = $i > 1;
?>
<div class="sb-section sb-banner-section" data-suffix="<?php echo esc_attr($suffix); ?>"<?php echo $section_hidden ? ' style="display:none;"' : ''; ?>>
    <h2><?php /* translators: %d: banner number */ printf(esc_html__('Banner #%d', 'site-banner'), (int) $i); ?></h2>

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">
                <label for="site_banner_text<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Banner text', 'site-banner'); ?></label>
                <p class="description"><?php esc_html_e('Leave blank to hide this banner.', 'site-banner'); ?></p>
            </th>
            <td>
                <?php
                $field_name = 'site_banner_text' . $suffix;
                if (user_can_richedit()) {
                    wp_editor(
                        get_option($field_name, ''),
                        $field_name,
                        array(
                            'wpautop'           => false,
                            'drag_drop_upload'  => true,
                            'textarea_name'     => $field_name,
                            'textarea_rows'     => 5,
                            'tinymce'           => array('forced_root_block' => false),
                        )
                    );
                } else {
                    printf(
                        '<textarea id="%1$s" name="%1$s" rows="5" class="large-text code">%2$s</textarea>',
                        esc_attr($field_name),
                        esc_textarea(get_option($field_name, ''))
                    );
                }
                ?>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Visibility', 'site-banner'); ?></th>
            <td>
                <fieldset>
                    <label>
                        <input type="radio" name="site_banner_hide<?php echo esc_attr($suffix); ?>" value="no"
                            <?php checked($opt('site_banner_hide') !== 'yes'); ?>>
                        <?php esc_html_e('Show banner', 'site-banner'); ?>
                    </label><br>
                    <label>
                        <input type="radio" name="site_banner_hide<?php echo esc_attr($suffix); ?>" value="yes"
                            <?php checked($opt('site_banner_hide') === 'yes'); ?>>
                        <?php esc_html_e('Hide banner', 'site-banner'); ?>
                    </label>
                </fieldset>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Presets', 'site-banner'); ?>
                <p class="description"><?php esc_html_e('One click sets all four colors to a style commonly used for that message type.', 'site-banner'); ?></p>
            </th>
            <td>
                <?php
                $presets = array(
                    'info'    => array('label' => __('Info', 'site-banner'),    'bg' => '#2271b1', 'text' => '#ffffff', 'link' => '#ffd866', 'close' => '#ffffff'),
                    'success' => array('label' => __('Success', 'site-banner'), 'bg' => '#1b8a3a', 'text' => '#ffffff', 'link' => '#fff6a8', 'close' => '#ffffff'),
                    'warning' => array('label' => __('Warning', 'site-banner'), 'bg' => '#f0c000', 'text' => '#1c1c1c', 'link' => '#1a4f8a', 'close' => '#1c1c1c'),
                    'error'   => array('label' => __('Error', 'site-banner'),   'bg' => '#b32d2e', 'text' => '#ffffff', 'link' => '#ffd1d1', 'close' => '#ffffff'),
                );
                foreach ($presets as $key => $p) {
                    printf(
                        '<button type="button" class="button sb-preset-btn" data-suffix="%1$s" data-bg="%2$s" data-text="%3$s" data-link="%4$s" data-close="%5$s" style="background:%2$s;color:%3$s;border-color:%2$s;">%6$s</button> ',
                        esc_attr($suffix),
                        esc_attr($p['bg']),
                        esc_attr($p['text']),
                        esc_attr($p['link']),
                        esc_attr($p['close']),
                        esc_html($p['label'])
                    );
                }
                ?>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Colors', 'site-banner'); ?></th>
            <td>
                <?php
                $color_fields = array(
                    'site_banner_color'        => array(__('Background', 'site-banner'),     '#024985'),
                    'site_banner_text_color'   => array(__('Text', 'site-banner'),           '#ffffff'),
                    'site_banner_link_color'   => array(__('Link', 'site-banner'),           '#f16521'),
                    'site_banner_close_color'  => array(__('Close button', 'site-banner'),   '#000000'),
                );
                foreach ($color_fields as $key => $info) {
                    list($label, $default) = $info;
                    $value = $opt($key);
                    $display = $value !== '' ? $value : $default;
                    printf(
                        '<div class="sb-color-row"><label for="%1$s">%2$s</label>'
                            . '<input type="text" id="%1$s" name="%1$s" value="%3$s" placeholder="%4$s">'
                            . '<input type="color" id="%1$s_picker" value="%5$s" data-target="%1$s"></div>',
                        esc_attr($key . $suffix),
                        esc_html($label),
                        esc_attr($value),
                        esc_attr($default),
                        esc_attr($display)
                    );
                }
                ?>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Layout', 'site-banner'); ?></th>
            <td>
                <div class="sb-grid-two">
                    <div>
                        <label for="site_banner_font_size<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Font size', 'site-banner'); ?></label>
                        <input type="text" id="site_banner_font_size<?php echo esc_attr($suffix); ?>"
                               name="site_banner_font_size<?php echo esc_attr($suffix); ?>"
                               value="<?php echo esc_attr($opt('site_banner_font_size')); ?>"
                               placeholder="e.g. 16px">
                    </div>
                    <div>
                        <label for="site_banner_z_index<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Z-index', 'site-banner'); ?></label>
                        <input type="number" id="site_banner_z_index<?php echo esc_attr($suffix); ?>"
                               name="site_banner_z_index<?php echo esc_attr($suffix); ?>"
                               value="<?php echo esc_attr($opt('site_banner_z_index')); ?>"
                               placeholder="99999">
                    </div>
                </div>

                <fieldset class="sb-position">
                    <legend><?php esc_html_e('Position', 'site-banner'); ?></legend>
                    <?php
                    $positions = array(
                        'relative' => __('Relative (default)', 'site-banner'),
                        'static'   => __('Static', 'site-banner'),
                        'absolute' => __('Absolute', 'site-banner'),
                        'fixed'    => __('Fixed', 'site-banner'),
                        'sticky'   => __('Sticky', 'site-banner'),
                        'footer'   => __('Footer (fixed bottom)', 'site-banner'),
                    );
                    $current = $opt('site_banner_position') ?: 'relative';
                    $pro_positions = array('fixed', 'sticky', 'footer');
                    $is_pro_here = function_exists('site_banner_is_pro') && site_banner_is_pro();
                    foreach ($positions as $value => $label) {
                        $pro_only = in_array($value, $pro_positions, true);
                        $disabled = ($pro_only && !$is_pro_here) ? 'disabled' : '';
                        $badge    = $pro_only ? ' <em style="color:#b96a00;font-size:11px;">(' . esc_html__('Pro', 'site-banner') . ')</em>' : '';
                        printf(
                            '<label style="margin-right:14px;"><input type="radio" name="site_banner_position%1$s" value="%2$s" %3$s %4$s> %5$s%6$s</label>',
                            esc_attr($suffix),
                            esc_attr($value),
                            checked($current, $value, false),
                            esc_attr($disabled),
                            esc_html($label),
                            wp_kses($badge, array('em' => array('style' => array())))
                        );
                    }
                    ?>
                </fieldset>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Close button', 'site-banner'); ?></th>
            <td>
                <label>
                    <input type="checkbox" id="site_banner_close_button_enabled<?php echo esc_attr($suffix); ?>"
                           name="site_banner_close_button_enabled<?php echo esc_attr($suffix); ?>"
                           <?php checked($opt('site_banner_close_button_enabled')); ?>>
                    <?php esc_html_e('Enable close button', 'site-banner'); ?>
                </label>
                <p class="description"><?php esc_html_e('Uses a strictly-necessary cookie (GDPR compliant).', 'site-banner'); ?></p>

                <p style="margin-top:10px;">
                    <label for="site_banner_close_button_expiration<?php echo esc_attr($suffix); ?>"><?php esc_html_e('Expiration', 'site-banner'); ?></label><br>
                    <input type="text" id="site_banner_close_button_expiration<?php echo esc_attr($suffix); ?>"
                           name="site_banner_close_button_expiration<?php echo esc_attr($suffix); ?>"
                           value="<?php echo esc_attr($opt('site_banner_close_button_expiration')); ?>"
                           placeholder="<?php echo esc_attr(sprintf('%s, e.g. 14 or 0.5 or %s', __('days', 'site-banner'), gmdate('d M Y H:i:s \U\T\C'))); ?>"
                           class="regular-text">
                    <span class="description"><?php esc_html_e('Days (e.g. 14), fractional days (0.5), or an explicit date/time.', 'site-banner'); ?></span>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Placement', 'site-banner'); ?></th>
            <td>
                <fieldset>
                    <?php $prepend = $opt('site_banner_prepend_element') ?: 'body'; ?>
                    <label>
                        <input type="radio" name="site_banner_prepend_element<?php echo esc_attr($suffix); ?>" value="body"
                            <?php checked($prepend, 'body'); ?>>
                        <?php echo wp_kses(__('Insert at top of <code>&lt;body&gt;</code>', 'site-banner'), array('code' => array())); ?>
                    </label><br>
                    <label>
                        <input type="radio" name="site_banner_prepend_element<?php echo esc_attr($suffix); ?>" value="header"
                            <?php checked($prepend, 'header'); ?>>
                        <?php echo wp_kses(__('Insert at top of <code>&lt;header&gt;</code>', 'site-banner'), array('code' => array())); ?>
                    </label>
                </fieldset>

                <?php if ($i === 1): ?>
                    <div class="sb-grid-two" style="margin-top:15px;">
                        <div>
                            <label for="site_banner_header_margin"><?php esc_html_e('Header top margin', 'site-banner'); ?></label>
                            <input type="text" id="site_banner_header_margin" name="site_banner_header_margin"
                                   value="<?php echo esc_attr(get_option('site_banner_header_margin')); ?>"
                                   placeholder="e.g. 40px">
                        </div>
                        <div>
                            <label for="site_banner_header_padding"><?php esc_html_e('Header top padding', 'site-banner'); ?></label>
                            <input type="text" id="site_banner_header_padding" name="site_banner_header_padding"
                                   value="<?php echo esc_attr(get_option('site_banner_header_padding')); ?>"
                                   placeholder="e.g. 40px">
                        </div>
                    </div>
                    <?php if (function_exists('wp_body_open')): ?>
                        <p style="margin-top:10px;">
                            <label>
                                <input type="checkbox" name="site_banner_wp_body_open_enabled"
                                    <?php checked((bool) get_option('site_banner_wp_body_open_enabled')); ?>>
                                <?php esc_html_e('Use wp_body_open hook (server-render banner #1)', 'site-banner'); ?>
                            </label>
                            <span class="description"><?php esc_html_e('Can eliminate cumulative-layout-shift issues.', 'site-banner'); ?></span>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <?php esc_html_e('Custom CSS', 'site-banner'); ?>
                <p class="description"><strong style="color:#b32d2e;"><?php esc_html_e('Warning:', 'site-banner'); ?></strong> <?php esc_html_e('bad CSS can break the banner.', 'site-banner'); ?></p>
            </th>
            <td>
                <?php
                $css_fields = array(
                    'site_banner_custom_css'           => '.site-banner' . $suffix,
                    'site_banner_text_custom_css'      => '.site-banner-text' . $suffix,
                    'site_banner_button_custom_css'    => '.site-banner-button' . $suffix,
                    'site_banner_scrolling_custom_css' => '.site-banner-scrolling' . $suffix,
                );
                foreach ($css_fields as $key => $selector) {
                    printf(
                        '<div class="sb-css-block"><div class="sb-css-label">%1$s {</div>'
                            . '<textarea id="%2$s" name="%2$s" class="large-text code" rows="3">%3$s</textarea>'
                            . '<div class="sb-css-label">}</div></div>',
                        esc_html($selector),
                        esc_attr($key . $suffix),
                        esc_textarea($opt($key))
                    );
                }
                ?>
            </td>
        </tr>
    </table>
</div>
