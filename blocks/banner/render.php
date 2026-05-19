<?php
/**
 * Server-side render for the site-banner/banner block.
 * $attributes, $content, $block are provided by WordPress.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Helper returns fully-escaped HTML (esc_attr / wp_kses_post applied internally).
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo site_banner_render_block($attributes);
