<?php
/**
 * Server-side render for the site-banner/banner block.
 * $attributes, $content, $block are provided by WordPress.
 */

if (!defined('ABSPATH')) {
    exit;
}

echo site_banner_render_block($attributes);
