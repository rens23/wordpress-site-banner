<?php
/**
 * Live preview pane.
 *
 * Expects $i and $suffix in scope.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

if (!defined('ABSPATH')) {
    exit;
}

$bg          = get_option('site_banner_color' . $suffix, '#024985');
$text_color  = get_option('site_banner_text_color' . $suffix, '#ffffff');
$link_color  = get_option('site_banner_link_color' . $suffix, '#f16521');
$font_size   = get_option('site_banner_font_size' . $suffix, '');
$text        = (string) get_option('site_banner_text' . $suffix);
$placeholder = sprintf(
    '<span>%s <a href="/">%s</a>.</span>',
    esc_html__('This is what your banner will look like with a', 'site-banner'),
    esc_html__('link', 'site-banner')
);
$display_text = $text !== '' ? $text : $placeholder;
$hidden = $i > 1;
?>
<div id="sb-preview-outer<?php echo esc_attr($suffix); ?>" class="sb-preview-outer" data-suffix="<?php echo esc_attr($suffix); ?>"<?php echo $hidden ? ' style="display:none;"' : ''; ?>>
    <div class="sb-preview-header">
        <h4><?php /* translators: %d: banner number */ printf(esc_html__('Banner #%d preview', 'site-banner'), (int) $i); ?></h4>
    </div>
    <div id="sb-preview-inner<?php echo esc_attr($suffix); ?>" class="sb-preview-inner">
        <div id="sb-preview<?php echo esc_attr($suffix); ?>"
             class="site-banner<?php echo esc_attr($suffix); ?> sb-preview-banner"
             style="background:<?php echo esc_attr($bg); ?>">
            <div id="sb-preview-text<?php echo esc_attr($suffix); ?>"
                 class="site-banner-text<?php echo esc_attr($suffix); ?> sb-preview-text"
                 style="color:<?php echo esc_attr($text_color); ?>;<?php echo $font_size !== '' ? 'font-size:' . esc_attr($font_size) . ';' : ''; ?>">
                <?php echo wp_kses_post($display_text); ?>
            </div>
        </div>
    </div>
</div>
<style>
    #sb-preview<?php echo esc_attr($suffix); ?> .site-banner-text<?php echo esc_attr($suffix); ?> a {
        color: <?php echo esc_attr($link_color); ?>;
    }
</style>
