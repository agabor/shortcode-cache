<?php
/**
 * Plugin Name: Shortcode Cache
 * Description: Cache rendered HTML for specific shortcodes
 * Version: 1.0.0
 * Author: Gabor Angyal
 * Author URI: https://webshop.tech
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shortcode-cache
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SHORTCODE_CACHE_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHORTCODE_CACHE_URL', plugin_dir_url( __FILE__ ) );

require_once SHORTCODE_CACHE_DIR . 'includes/cache-operations.php';
require_once SHORTCODE_CACHE_DIR . 'includes/shortcode-caching.php';
require_once SHORTCODE_CACHE_DIR . 'includes/cache-inspector.php';
require_once SHORTCODE_CACHE_DIR . 'admin/settings-handler.php';
require_once SHORTCODE_CACHE_DIR . 'admin/ajax-handler.php';

add_action( 'admin_menu', 'shortcode_cache_register_admin_menu' );
add_action( 'admin_init', 'shortcode_cache_register_settings' );
add_action( 'init', 'shortcode_cache_initialize_shortcode_caching', 20 );
add_action( 'wp_ajax_shortcode_cache_clear', 'shortcode_cache_handle_clear_cache' );

function shortcode_cache_register_admin_menu() {
    $hook_suffix = add_options_page(
        __( 'Shortcode Cache Settings', 'shortcode-cache' ),
        __( 'Shortcode Cache', 'shortcode-cache' ),
        'manage_options',
        'shortcode-cache-settings',
        'shortcode_cache_render_settings_page'
    );

    add_action( "load-{$hook_suffix}", 'shortcode_cache_enqueue_admin_scripts' );
}

function shortcode_cache_enqueue_admin_scripts() {
    wp_enqueue_script(
        'shortcode-cache-manager',
        SHORTCODE_CACHE_URL . 'admin/js/cache-manager.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );

    wp_localize_script(
        'shortcode-cache-manager',
        'shortcodeCacheData',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'shortcode_cache_nonce' ),
        )
    );
}

function shortcode_cache_register_settings() {
    register_setting(
        'shortcode_cache_group',
        'shortcode_cache_list'
    );
}

function shortcode_cache_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Insufficient permissions', 'shortcode-cache' ) );
    }

    include SHORTCODE_CACHE_DIR . 'admin/settings-page.php';
}

function shortcode_cache_get_cached_shortcodes() {
    $shortcodes = get_option( 'shortcode_cache_list', '' );
    $shortcodes = trim( $shortcodes );

    if ( empty( $shortcodes ) ) {
        return array();
    }

    $shortcode_list = array_map( 'trim', explode( "\n", $shortcodes ) );
    $shortcode_list = array_filter( $shortcode_list );

    return array_values( $shortcode_list );
}

function shortcode_cache_initialize_shortcode_caching() {
    global $shortcode_tags;

    $shortcodes_to_cache = shortcode_cache_get_cached_shortcodes();

    foreach ( $shortcodes_to_cache as $shortcode_name ) {
        if ( isset( $shortcode_tags[ $shortcode_name ] ) ) {
            shortcode_cache_wrap_shortcode_with_cache( $shortcode_name );
        }
    }
}