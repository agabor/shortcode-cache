<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$shortcodes = shortcode_cache_get_cached_shortcodes();
$shortcodes_text = implode( "\n", $shortcodes );
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
                <tr>
                    <th scope="row">
                        <label for="shortcode_cache_list">
                            <?php esc_html_e( 'Shortcodes to Cache', 'shortcode-cache' ); ?>
                        </label>
                    </th>
                    <td>
                        <textarea
                            id="shortcode_cache_list"
                            name="shortcode_cache_list"
                            rows="10"
                            cols="50"
                            class="large-text"
                            placeholder="<?php esc_attr_e( 'Enter one shortcode per line', 'shortcode-cache' ); ?>"
                        ><?php echo esc_textarea( $shortcodes_text ); ?></textarea>
                        <p class="description">
                            <?php esc_html_e( 'Enter the shortcode names you want to cache, one per line. Example: products-ordered-by-discount', 'shortcode-cache' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>

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