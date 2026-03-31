<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$config = get_option( 'shortcode_cache_config', array() );
$monitored_url = shortcode_cache_get_monitored_url();
$show_success = isset( $_GET['settings-updated'] ) && $_GET['settings-updated'];
$cached_items = shortcode_cache_get_all_cached_items();
$detected_shortcodes = shortcode_cache_get_detected_shortcodes();
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

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="shortcode_cache_monitored_url">
                            <?php esc_html_e( 'Monitored URL', 'shortcode-cache' ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="url"
                            id="shortcode_cache_monitored_url"
                            name="shortcode_cache_monitored_url"
                            value="<?php echo esc_attr( $monitored_url ); ?>"
                            class="regular-text"
                            placeholder="<?php esc_attr_e( 'https://example.com/page', 'shortcode-cache' ); ?>"
                        />
                        <p class="description">
                            <?php esc_html_e( 'Enter the full URL of the page to monitor for shortcode detection. Shortcodes will be automatically detected when this page is visited.', 'shortcode-cache' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>

    <hr />

    <h2><?php esc_html_e( 'Shortcodes to Cache', 'shortcode-cache' ); ?></h2>

    <div class="shortcode-cache-add-form" style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
        <div style="display: flex; gap: 10px; align-items: flex-end;">
            <div style="flex: 1;">
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
                    <th scope="col"><?php esc_html_e( 'Cache by User Role', 'shortcode-cache' ); ?></th>
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
                            <label style="display: flex; align-items: center; gap: 5px;">
                                <input
                                    type="checkbox"
                                    class="shortcode-cache-role-toggle"
                                    <?php checked( isset( $item['role_based'] ) ? $item['role_based'] : false ); ?>
                                    data-index="<?php echo esc_attr( $index ); ?>"
                                />
                                <?php esc_html_e( 'Include User Role in Cache Key', 'shortcode-cache' ); ?>
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

    <h2><?php esc_html_e( 'Detected Shortcodes', 'shortcode-cache' ); ?></h2>

    <?php if ( empty( $detected_shortcodes ) ) : ?>
        <p><?php esc_html_e( 'No shortcodes detected yet. Visit the monitored URL to detect shortcodes.', 'shortcode-cache' ); ?></p>
    <?php else : ?>
        <div class="shortcode-cache-actions" style="margin-bottom: 15px;">
            <button
                type="button"
                class="button button-secondary shortcode-cache-clear-detected-btn"
            >
                <?php esc_html_e( 'Clear Detected Shortcodes', 'shortcode-cache' ); ?>
            </button>
        </div>

        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e( 'Shortcode Name', 'shortcode-cache' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Usage Count', 'shortcode-cache' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $detected_shortcodes as $shortcode_name => $count ) : ?>
                    <tr>
                        <td><?php echo esc_html( $shortcode_name ); ?></td>
                        <td><?php echo esc_html( $count ); ?></td>
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
                    <th scope="col"><?php esc_html_e( 'Parameters', 'shortcode-cache' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Action', 'shortcode-cache' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $cached_items as $cache_key => $item_data ) : ?>
                    <tr>
                        <td><?php echo esc_html( $item_data['shortcode'] ); ?></td>
                        <td><?php echo shortcode_cache_extract_parameters_from_item( $item_data ); ?></td>
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