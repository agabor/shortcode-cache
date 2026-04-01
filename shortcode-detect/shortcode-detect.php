<?php
/**
 * Plugin Name: Shortcode Cache
 * Description: Cache rendered HTML for specific shortcodes
 * Version: 1.1.14
 * Author: Gabor Angyal
 * Author URI: https://webshop.tech
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shortcode-detect
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SHORTCODE_DETECT_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHORTCODE_DETECT_URL', plugin_dir_url( __FILE__ ) );

require_once SHORTCODE_DETECT_DIR . 'includes/url-detection.php';
require_once SHORTCODE_DETECT_DIR . 'admin/settings-handler.php';
require_once SHORTCODE_DETECT_DIR . 'admin/ajax-handler.php';

add_action( 'admin_menu', 'shortcode_detect_register_admin_menu' );
add_action( 'admin_init', 'shortcode_detect_register_settings' );
add_action( 'wp_ajax_shortcode_detect_clear_detected', 'shortcode_detect_handle_clear_detected_shortcodes' );
add_action( 'wp', 'shortcode_detect_setup_detection', 999 );

function shortcode_detect_register_admin_menu() {
    $hook_suffix = add_options_page(
        __( 'Shortcode Cache Settings', 'shortcode-detect' ),
        __( 'Shortcode Cache', 'shortcode-detect' ),
        'manage_options',
        'shortcode-detect-settings',
        'shortcode_detect_render_settings_page'
    );

    add_action( "load-{$hook_suffix}", 'shortcode_detect_enqueue_admin_scripts' );
}

function shortcode_detect_enqueue_admin_scripts() {
    wp_enqueue_script(
        'shortcode-detect-manager',
        SHORTCODE_DETECT_URL . 'admin/js/cache-manager.js',
        array( 'jquery' ),
        '1.1.14',
        true
    );

    wp_enqueue_style(
        'shortcode-detect-settings-manager',
        SHORTCODE_DETECT_URL . 'admin/css/settings-manager.css',
        array(),
        '1.1.14'
    );

    wp_localize_script(
        'shortcode-detect-manager',
        'shortcodeCacheData',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        )
    );
}

function shortcode_detect_register_settings() {
    register_setting(
        'shortcode_detect_group',
        'shortcode_detect_monitored_url'
    );
}

function shortcode_detect_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Insufficient permissions', 'shortcode-detect' ) );
    }

    include SHORTCODE_DETECT_DIR . 'admin/settings-page.php';
}

function shortcode_detect_setup_detection() {
    if ( ! shortcode_detect_is_monitored_page() ) {
        return;
    }

    global $shortcode_tags;

    foreach ( $shortcode_tags as $shortcode_name => $callback ) {
        shortcode_detect_wrap_shortcode_for_detection( $shortcode_name );
    }
}

function shortcode_detect_wrap_shortcode_for_detection( $shortcode_name ) {
    global $shortcode_tags;

    if ( ! isset( $shortcode_tags[ $shortcode_name ] ) ) {
        return;
    }

    $original_callback = $shortcode_tags[ $shortcode_name ];

    $shortcode_tags[ $shortcode_name ] = function( $atts = array(), $content = '', $tag = '' ) use ( $original_callback, $shortcode_name ) {
        $instance_id = isset( $atts['id'] ) ? $atts['id'] : null;
        shortcode_detect_track_shortcode_execution( $shortcode_name, $instance_id );
        return call_user_func( $original_callback, $atts, $content, $tag );
    };
}

function shortcode_detect_track_shortcode_execution( $shortcode_name, $instance_id = null ) {
    $detected_shortcodes = get_transient( 'shortcode_detect_detected_shortcodes' );

    if ( false === $detected_shortcodes ) {
        $detected_shortcodes = array();
    }

    if ( ! is_array( $detected_shortcodes ) ) {
        $detected_shortcodes = array();
    }

    $key = $shortcode_name;
    if ( null !== $instance_id && ! empty( $instance_id ) ) {
        $key = $shortcode_name . '::' . $instance_id;
    }

    if ( ! isset( $detected_shortcodes[ $key ] ) ) {
        $detected_shortcodes[ $key ] = 0;
    }

    $detected_shortcodes[ $key ]++;

    set_transient( 'shortcode_detect_detected_shortcodes', $detected_shortcodes, WEEK_IN_SECONDS );
}