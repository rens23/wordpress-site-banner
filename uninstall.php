<?php
/**
 * Runs when the user deletes the plugin from the Plugins screen.
 * Drops every site_banner_* option and the license transients.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

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

// Remove our capability from every role that still has it.
$roles = wp_roles();
if ($roles && isset($roles->role_objects) && is_array($roles->role_objects)) {
    foreach ($roles->role_objects as $role) {
        if ($role->has_cap('manage_site_banner')) {
            $role->remove_cap('manage_site_banner');
        }
    }
}
