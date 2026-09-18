<?php
/**
 * Plugin Name: Smart WhatsApp Enquiry Widget
 * Description: A modern, lightweight guided WhatsApp customer-support button and enquiry system.
 * Version: 1.0.0
 * Author: FurnitureNG AI
 * Text Domain: smart-whatsapp-enquiries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'SWE_VERSION', '1.0.0' );
define( 'SWE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SWE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include core plugin class
require_once SWE_PLUGIN_DIR . 'includes/class-plugin.php';

// Initialize the plugin
function run_smart_whatsapp_enquiries() {
	$plugin = SWE_Plugin::get_instance();
}
add_action( 'plugins_loaded', 'run_smart_whatsapp_enquiries' );

// Activation hook
register_activation_hook( __FILE__, [ 'SWE_Plugin', 'activate' ] );
