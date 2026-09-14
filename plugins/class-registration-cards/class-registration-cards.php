<?php
/**
 * Plugin Name: Class Registration Cards
 * Plugin URI:  #
 * Description: Creates a dynamic Class Registration / Course Cards section via shortcode.
 * Version:     1.0.0
 * Author:      Antigravity
 * Text Domain: crc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CRC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CRC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CRC_VERSION', '1.0.0' );

// Include necessary files
require_once CRC_PLUGIN_DIR . 'includes/class-post-type.php';
require_once CRC_PLUGIN_DIR . 'includes/class-settings.php';
require_once CRC_PLUGIN_DIR . 'includes/class-meta-boxes.php';
require_once CRC_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once CRC_PLUGIN_DIR . 'includes/class-assets.php';

/**
 * Initialize the plugin
 */
function crc_init_plugin() {
	new CRC_Post_Type();
	new CRC_Settings();
	new CRC_Meta_Boxes();
	new CRC_Shortcode();
	new CRC_Assets();
}
add_action( 'plugins_loaded', 'crc_init_plugin' );
