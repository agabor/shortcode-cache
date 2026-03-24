<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$shortcodes = shortcode_cache_get_cached_shortcodes();
$shortcodes_text = implode( "\n", $shortcodes );
$show_success = isset( $_GET['settings-updated'] ) && $_GET['settings-updated'];
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
</div>