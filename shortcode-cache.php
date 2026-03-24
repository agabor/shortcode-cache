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

add_action( 'init', function() {
    global $shortcode_tags;
    $original_callback = $shortcode_tags['products-ordered-by-discount'];

    add_shortcode( 'products-ordered-by-discount', function( $atts ) use ( $original_callback ) {

        $cache_key = 'products_ordered_by_discount_' . md5( serialize( $atts ) );
        $group     = 'shortcode_cache';

        $output = wp_cache_get( $cache_key, $group );

        if ( false === $output ) {
            $output = call_user_func( $original_callback, $atts );
            wp_cache_set( $cache_key, $output, $group, HOUR_IN_SECONDS );
        }

        return $output;
    });

}, 20 );
