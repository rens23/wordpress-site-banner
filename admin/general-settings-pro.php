<?php
/**
 * Global pro settings: role permissions, debug mode.
 * Expects $is_pro in scope.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$is_admin     = in_array('administrator', (array) $current_user->roles, true);
$pro_attr     = $is_pro ? '' : 'disabled';
$permissions  = array_filter(array_map('trim', explode(',', (string) get_option('site_banner_role_permissions'))));
?>
<div class="sb-section sb-pro-global">
    <h2>
        <?php esc_html_e('General — Pro', 'site-banner'); ?>
        <?php if (!$is_pro): ?>
            <a class="button button-secondary" href="https://rensh.gumroad.com/l/site-banner-plugin" target="_blank" rel="noopener">
                <?php esc_html_e('Unlock with license', 'site-banner'); ?>
            </a>
        <?php endif; ?>
    </h2>

    <table class="form-table" role="presentation">
        <?php if ($is_admin): ?>
            <tr>
                <th scope="row">
                    <?php esc_html_e('Editor permissions', 'site-banner'); ?>
                    <p class="description"><?php esc_html_e('Allow these roles to edit Site Banner settings.', 'site-banner'); ?></p>
                </th>
                <td>
                    <div id="sb-role-permissions">
                        <?php
                        foreach (get_editable_roles() as $role_name => $role_info) {
                            if ($role_name === 'administrator') continue;
                            $checked = in_array($role_name, $permissions, true);
                            printf(
                                '<label><input type="checkbox" class="sb-role-cb" value="%1$s" %2$s %3$s> %4$s</label>',
                                esc_attr($role_name),
                                checked($checked, true, false),
                                esc_attr($pro_attr),
                                esc_html(translate_user_role($role_info['name']))
                            );
                        }
                        ?>
                    </div>
                    <input type="hidden" id="site_banner_role_permissions" name="site_banner_role_permissions"
                           value="<?php echo esc_attr(get_option('site_banner_role_permissions')); ?>">
                </td>
            </tr>
        <?php endif; ?>

        <tr>
            <th scope="row">
                <?php esc_html_e('Click-tracking endpoint', 'site-banner'); ?>
                <p class="description"><?php esc_html_e('Optional URL that receives a JSON POST when a tracked banner link is clicked. Leave blank for event-only tracking.', 'site-banner'); ?></p>
            </th>
            <td>
                <input type="url" id="site_banner_click_tracking_endpoint" name="site_banner_click_tracking_endpoint"
                       value="<?php echo esc_attr(get_option('site_banner_click_tracking_endpoint')); ?>"
                       class="regular-text"
                       placeholder="https://analytics.example.com/banner-click"
                       <?php echo esc_attr($pro_attr); ?>>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <?php esc_html_e('Debug mode', 'site-banner'); ?>
                <p class="description"><?php esc_html_e('Log Site Banner params to the browser console on every front-end load.', 'site-banner'); ?></p>
            </th>
            <td>
                <label>
                    <input type="checkbox" name="site_banner_debug_mode"
                        <?php checked((bool) get_option('site_banner_debug_mode')); ?>
                        <?php echo esc_attr($pro_attr); ?>>
                    <?php esc_html_e('Enable debug mode', 'site-banner'); ?>
                </label>
            </td>
        </tr>
    </table>
</div>
