<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$config = get_option( 'shortcode_cache_config', array() );
$global_allowed_roles = shortcode_cache_get_global_allowed_roles();
$show_success = isset( $_GET['settings-updated'] ) && $_GET['settings-updated'];
$cached_items = shortcode_cache_get_all_cached_items();
$all_roles = shortcode_cache_get_all_roles();
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php if ( $show_success ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'Settings saved successfully.', 'shortcode-cache' ); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="options.php">
        <?php settings_fields( 'shortcode_cache_group' ); ?>



        <?php submit_button(); ?>
    </form>

    <hr />

    <h2><?php esc_html_e( 'Role-Based Cache Settings', 'shortcode-cache' ); ?></h2>

    <div class="shortcode-cache-global-roles-section" style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
        <div style="display: flex; gap: 15px; align-items: center;">
            <div style="flex: 1;">
                <p style="margin: 0 0 10px 0; font-weight: bold;">
                    <?php esc_html_e( 'Cache for User Roles', 'shortcode-cache' ); ?>
                </p>
                <p class="description" style="margin: 0 0 10px 0;">
                    <?php esc_html_e( 'Select which user roles should use the cache. Guest users always use cache by default. Leave empty to cache for all authenticated roles.', 'shortcode-cache' ); ?>
                </p>
                <div class="shortcode-cache-global-roles-display" style="margin-top: 10px;">
                    <?php echo wp_kses_post( shortcode_cache_format_global_roles_display( $global_allowed_roles, $all_roles ) ); ?>
                </div>
            </div>
            <button
                type="button"
                class="button button-primary shortcode-cache-global-roles-btn"
            >
                <?php esc_html_e( 'Manage Roles', 'shortcode-cache' ); ?>
            </button>
        </div>
    </div>

    <hr />

    <h2><?php esc_html_e( 'Shortcodes to Cache', 'shortcode-cache' ); ?></h2>

    <div class="shortcode-cache-add-form" style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: flex-end;">
            <div>
                <label for="shortcode_cache_new_name" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    <?php esc_html_e( 'Shortcode Name', 'shortcode-cache' ); ?>
                </label>
                <input
                    type="text"
                    id="shortcode_cache_new_name"
                    class="regular-text shortcode-cache-new-name"
                    placeholder="<?php esc_attr_e( 'e.g., products-list', 'shortcode-cache' ); ?>"
                />
            </div>
            <div>
                <label for="shortcode_cache_new_id" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    <?php esc_html_e( 'ID (optional)', 'shortcode-cache' ); ?>
                </label>
                <input
                    type="text"
                    id="shortcode_cache_new_id"
                    class="regular-text shortcode-cache-new-id"
                    placeholder="<?php esc_attr_e( 'e.g., homepage-products', 'shortcode-cache' ); ?>"
                />
            </div>
            <button
                type="button"
                class="button button-primary shortcode-cache-add-btn"
            >
                <?php esc_html_e( 'Add Shortcode', 'shortcode-cache' ); ?>
            </button>
        </div>
    </div>

    <?php if ( empty( $config ) ) : ?>
        <p><?php esc_html_e( 'No shortcodes configured yet. Add your first shortcode to cache above.', 'shortcode-cache' ); ?></p>
    <?php else : ?>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e( 'Shortcode Name', 'shortcode-cache' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'ID', 'shortcode-cache' ); ?></th>
                    <th scope="col" class="shortcode-cache-role-column"><?php esc_html_e( 'Cache by Role', 'shortcode-cache' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Action', 'shortcode-cache' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $config as $index => $item ) : ?>
                    <tr class="shortcode-cache-item-row">
                        <td>
                            <span class="shortcode-cache-item-name">
                                <?php echo esc_html( $item['name'] ); ?>
                            </span>
                            <input type="hidden" class="shortcode-cache-item-index" value="<?php echo esc_attr( $index ); ?>" />
                        </td>
                        <td>
                            <span class="shortcode-cache-item-id">
                                <?php echo isset( $item['id'] ) && ! empty( $item['id'] ) ? esc_html( $item['id'] ) : '—'; ?>
                            </span>
                        </td>
                        <td class="shortcode-cache-role-column">
                            <label class="shortcode-cache-role-toggle">
                                <input
                                    type="checkbox"
                                    class="shortcode-cache-role-checkbox"
                                    data-index="<?php echo esc_attr( $index ); ?>"
                                    <?php checked( isset( $item['cache_by_role'] ) && $item['cache_by_role'] ); ?>
                                />
                                <span class="shortcode-cache-toggle-switch"></span>
                            </label>
                        </td>
                        <td>
                            <button
                                type="button"
                                class="button button-small button-danger shortcode-cache-delete-btn"
                                data-index="<?php echo esc_attr( $index ); ?>"
                            >
                                <?php esc_html_e( 'Delete', 'shortcode-cache' ); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <hr />

    <h2><?php esc_html_e( 'Cached Items', 'shortcode-cache' ); ?></h2>

    <?php if ( empty( $cached_items ) ) : ?>
        <p><?php esc_html_e( 'No cached items at the moment.', 'shortcode-cache' ); ?></p>
    <?php else : ?>
        <div class="shortcode-cache-actions" style="margin-bottom: 15px;">
            <button
                type="button"
                class="button button-secondary shortcode-cache-clear-all-btn"
            >
                <?php esc_html_e( 'Clear All Cache', 'shortcode-cache' ); ?>
            </button>
        </div>

        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e( 'Shortcode', 'shortcode-cache' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'ID', 'shortcode-cache' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Parameters', 'shortcode-cache' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Size', 'shortcode-cache' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Content', 'shortcode-cache' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Action', 'shortcode-cache' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $cached_items as $cache_key => $item_data ) : ?>
                    <tr>
                        <td><?php echo esc_html( $item_data['shortcode'] ); ?></td>
                        <td><?php echo isset( $item_data['id'] ) && ! empty( $item_data['id'] ) ? esc_html( $item_data['id'] ) : '—'; ?></td>
                        <td><?php echo shortcode_cache_extract_parameters_from_item( $item_data ); ?></td>
                        <td><?php echo esc_html( shortcode_cache_format_bytes( shortcode_cache_get_size( $cache_key, 'shortcode_cache' ) ) ); ?></td>
                        <td>
                            <button
                                type="button"
                                class="button button-small shortcode-cache-view-content-btn"
                                data-cache-key="<?php echo esc_attr( $cache_key ); ?>"
                            >
                                <?php esc_html_e( 'View Content', 'shortcode-cache' ); ?>
                            </button>
                        </td>
                        <td>
                            <button
                                type="button"
                                class="button button-small shortcode-cache-clear-btn"
                                data-cache-key="<?php echo esc_attr( $cache_key ); ?>"
                            >
                                <?php esc_html_e( 'Clear Cache', 'shortcode-cache' ); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div id="shortcode-cache-role-modal" class="shortcode-cache-modal" style="display: none;">
    <div class="shortcode-cache-modal-content">
        <div class="shortcode-cache-modal-header">
            <h2><?php esc_html_e( 'Select Roles for Cache', 'shortcode-cache' ); ?></h2>
            <button type="button" class="shortcode-cache-modal-close">&times;</button>
        </div>
        <div class="shortcode-cache-modal-body">
            <p class="description">
                <?php esc_html_e( 'Guest users always use cache by default. Select which authenticated user roles should use cache globally.', 'shortcode-cache' ); ?>
            </p>
            <div class="shortcode-cache-roles-grid">
                <?php foreach ( $all_roles as $role_slug => $role_name ) : ?>
                    <label class="shortcode-cache-role-label">
                        <input
                            type="checkbox"
                            class="shortcode-cache-role-checkbox"
                            value="<?php echo esc_attr( $role_slug ); ?>"
                        />
                        <span><?php echo esc_html( $role_name ); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="shortcode-cache-modal-footer">
            <button type="button" class="button shortcode-cache-modal-cancel">
                <?php esc_html_e( 'Cancel', 'shortcode-cache' ); ?>
            </button>
            <button type="button" class="button button-primary shortcode-cache-modal-save">
                <?php esc_html_e( 'Save Roles', 'shortcode-cache' ); ?>
            </button>
        </div>
    </div>
</div>

<div id="shortcode-cache-content-modal" class="shortcode-cache-content-modal" style="display: none;">
    <div class="shortcode-cache-content-modal-content">
        <div class="shortcode-cache-content-modal-header">
            <h2><?php esc_html_e( 'Cached Content', 'shortcode-cache' ); ?></h2>
            <button type="button" class="shortcode-cache-content-modal-close">&times;</button>
        </div>
        <div class="shortcode-cache-content-modal-body">
            <pre class="shortcode-cache-content-display"></pre>
        </div>
        <div class="shortcode-cache-content-modal-footer">
            <button type="button" class="button shortcode-cache-content-modal-cancel">
                <?php esc_html_e( 'Close', 'shortcode-cache' ); ?>
            </button>
        </div>
    </div>
</div>

<?php
function shortcode_cache_format_global_roles_display( $allowed_roles, $all_roles ) {
    if ( empty( $allowed_roles ) ) {
        return '<span class="shortcode-cache-roles-all">All authenticated roles</span>';
    }

    $role_names = array();
    foreach ( $allowed_roles as $role_slug ) {
        if ( isset( $all_roles[ $role_slug ] ) ) {
            $role_names[] = $all_roles[ $role_slug ];
        }
    }

    if ( empty( $role_names ) ) {
        return '<span class="shortcode-cache-roles-all">All authenticated roles</span>';
    }

    $output = '';
    foreach ( $role_names as $role_name ) {
        $output .= '<span class="shortcode-cache-role-badge">' . esc_html( $role_name ) . '</span>';
    }

    return $output;
}
?>