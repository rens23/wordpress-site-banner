<?php
/**
 * Runs when the user deletes the plugin from the Plugins screen.
 * Drops every site_banner_* option and the license transients.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Direct DB queries here are appropriate: this is a one-time uninstall path,
// caching has no role, and there's no get_option() equivalent for a wildcard
// delete.
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

// Drop options. site_banner_* covers everything we register.
$wpdb->query(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'site_banner\\_%'"
);

// Drop license transients (both _transient_ and _transient_timeout_ entries).
$wpdb->query(
    "DELETE FROM {$wpdb->options}
     WHERE option_name LIKE '\\_transient\\_site\\_banner\\_license\\_%'
        OR option_name LIKE '\\_transient\\_timeout\\_site\\_banner\\_license\\_%'"
);

// phpcs:enable

// Remove our capability from every role that still has it.
$site_banner_roles = wp_roles();
if ($site_banner_roles && isset($site_banner_roles->role_objects) && is_array($site_banner_roles->role_objects)) {
    foreach ($site_banner_roles->role_objects as $site_banner_role) {
        if ($site_banner_role->has_cap('manage_site_banner')) {
            $site_banner_role->remove_cap('manage_site_banner');
        }
    }
}
