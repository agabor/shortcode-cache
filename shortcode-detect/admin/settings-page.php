<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$monitored_url = shortcode_cache_get_monitored_url();
$show_success = isset( $_GET['settings-updated'] ) && $_GET['settings-updated'];
$detected_shortcodes = shortcode_cache_get_detected_shortcodes();

$parsed_detected = shortcode_cache_parse_detected_shortcodes( $detected_shortcodes );
usort( $parsed_detected, function( $a, $b ) {
    return strcmp( $a['name'], $b['name'] );
} );
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

    <h2><?php esc_html_e( 'Detected Shortcodes', 'shortcode-cache' ); ?></h2>

    <?php if ( empty( $parsed_detected ) ) : ?>
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
                    <th scope="col"><?php esc_html_e( 'ID', 'shortcode-cache' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Usage Count', 'shortcode-cache' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $parsed_detected as $shortcode_data ) : ?>
                    <tr>
                        <td><?php echo esc_html( $shortcode_data['name'] ); ?></td>
                        <td><?php echo ! empty( $shortcode_data['id'] ) ? esc_html( $shortcode_data['id'] ) : '—'; ?></td>
                        <td><?php echo esc_html( $shortcode_data['count'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
function shortcode_cache_parse_detected_shortcodes( $detected_shortcodes ) {
    $parsed = array();

    foreach ( $detected_shortcodes as $key => $count ) {
        if ( strpos( $key, '::' ) === false ) {
            $parsed[] = array(
                'name' => $key,
                'id' => '',
                'count' => $count,
            );
        } else {
            list( $name, $id ) = explode( '::', $key, 2 );
            $parsed[] = array(
                'name' => $name,
                'id' => $id,
                'count' => $count,
            );
        }
    }

    return $parsed;
}
?>