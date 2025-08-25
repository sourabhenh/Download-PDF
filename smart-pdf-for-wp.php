<?php
/**
 * Plugin Name: Smart PDF for WP (Lite)
 * Plugin URI: https://example.com/smart-pdf-for-wp
 * Description: Generate clean, styled PDFs of posts, pages, and custom post types. Uses server-side Dompdf if available, otherwise a reliable print-to-PDF fallback.
 * Version: 1.0.0
 * Author: Your Team
 * License: GPL-2.0-or-later
 * Text Domain: smart-pdf-for-wp
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SPDF_VERSION', '1.0.0' );
define( 'SPDF_PATH', plugin_dir_path( __FILE__ ) );
define( 'SPDF_URL', plugin_dir_url( __FILE__ ) );

require_once SPDF_PATH . 'includes/class-spdf-plugin.php';

function spdf_init_plugin() {
    return \SPDF\Plugin::instance();
}
add_action( 'plugins_loaded', 'spdf_init_plugin' );

register_activation_hook( __FILE__, function(){
    \SPDF\Plugin::instance()->activate();
} );

register_deactivation_hook( __FILE__, function(){
    \SPDF\Plugin::instance()->deactivate();
} );

