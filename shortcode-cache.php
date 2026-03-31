<?php
/**
 * Plugin Name: Shortcode Cache
 * Description: Cache rendered HTML for specific shortcodes
 * Version: 1.1.9
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
require_once SHORTCODE_CACHE_DIR . 'includes/role-utils.php';
require_once SHORTCODE_CACHE_DIR . 'includes/shortcode-caching.php';
require_once SHORTCODE_CACHE_DIR . 'includes/cache-inspector.php';
require_once SHORTCODE_CACHE_DIR . 'includes/url-detection.php';
require_once SHORTCODE_CACHE_DIR . 'admin/settings-handler.php';
require_once SHORTCODE_CACHE_DIR . 'admin/ajax-handler.php';

add_action( 'admin_menu', 'shortcode_cache_register_admin_menu' );
add_action( 'admin_init', 'shortcode_cache_register_settings' );
add_action( 'init', 'shortcode_cache_initialize_shortcode_caching', 20 );
add_action( 'wp_ajax_shortcode_cache_clear', 'shortcode_cache_handle_clear_cache' );
add_action( 'wp_ajax_shortcode_cache_clear_all', 'shortcode_cache_handle_clear_all_cache' );
add_action( 'wp_ajax_shortcode_cache_clear_detected', 'shortcode_cache_handle_clear_detected_shortcodes' );
add_action( 'wp_ajax_shortcode_cache_add', 'shortcode_cache_handle_add_shortcode' );
add_action( 'wp_ajax_shortcode_cache_delete', 'shortcode_cache_handle_delete_shortcode' );
add_action( 'wp_ajax_shortcode_cache_update_role_caching', 'shortcode_cache_handle_update_shortcode_role_caching' );
add_action( 'wp_ajax_shortcode_cache_update_global_roles', 'shortcode_cache_handle_update_global_roles' );
add_action( 'wp_ajax_shortcode_cache_get_roles', 'shortcode_cache_handle_get_available_roles' );
add_action( 'wp_ajax_shortcode_cache_get_content', 'shortcode_cache_handle_get_cached_content' );
add_action( 'wp', 'shortcode_cache_setup_detection', 999 );

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
        '1.1.9',
        true
    );

    wp_enqueue_script(
        'shortcode-cache-settings-manager',
        SHORTCODE_CACHE_URL . 'admin/js/settings-list-manager.js',
        array( 'jquery' ),
        '1.1.9',
        true
    );

    wp_enqueue_style(
        'shortcode-cache-settings-manager',
        SHORTCODE_CACHE_URL . 'admin/css/settings-manager.css',
        array(),
        '1.1.9'
    );

    wp_localize_script(
        'shortcode-cache-manager',
        'shortcodeCacheData',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        )
    );
}

function shortcode_cache_register_settings() {
    register_setting(
        'shortcode_cache_group',
        'shortcode_cache_config'
    );

    register_setting(
        'shortcode_cache_group',
        'shortcode_cache_monitored_url'
    );

    register_setting(
        'shortcode_cache_group',
        'shortcode_cache_global_roles'
    );
}

function shortcode_cache_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Insufficient permissions', 'shortcode-cache' ) );
    }

    include SHORTCODE_CACHE_DIR . 'admin/settings-page.php';
}

function shortcode_cache_get_cached_shortcodes() {
    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) ) {
        return array();
    }

    $shortcodes = array();

    foreach ( $config as $shortcode_item ) {
        if ( isset( $shortcode_item['name'] ) && ! empty( $shortcode_item['name'] ) ) {
            $shortcodes[] = $shortcode_item;
        }
    }

    return $shortcodes;
}

function shortcode_cache_initialize_shortcode_caching() {
    global $shortcode_tags;

    $shortcodes_to_cache = shortcode_cache_get_cached_shortcodes();

    foreach ( $shortcodes_to_cache as $shortcode_config ) {
        $shortcode_name = $shortcode_config['name'];
        
        if ( isset( $shortcode_tags[ $shortcode_name ] ) ) {
            shortcode_cache_wrap_shortcode_with_cache( $shortcode_name, $shortcode_config );
        }
    }
}

function shortcode_cache_setup_detection() {
    if ( ! shortcode_cache_is_monitored_page() ) {
        return;
    }

    global $shortcode_tags;

    foreach ( $shortcode_tags as $shortcode_name => $callback ) {
        shortcode_cache_wrap_shortcode_for_detection( $shortcode_name );
    }
}