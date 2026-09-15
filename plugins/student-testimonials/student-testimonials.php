<?php
/**
 * Plugin Name: Student Testimonials
 * Description: A lightweight, responsive WordPress plugin for displaying student success stories and testimonials.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: student-testimonials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ST_TESTIMONIALS_VERSION', '1.0.0' );
define( 'ST_TESTIMONIALS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ST_TESTIMONIALS_URL', plugin_dir_url( __FILE__ ) );

class ST_Testimonials {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->includes();
		$this->init();
	}

	private function includes() {
		require_once ST_TESTIMONIALS_PATH . 'includes/class-settings.php';
		require_once ST_TESTIMONIALS_PATH . 'includes/class-post-type.php';
		require_once ST_TESTIMONIALS_PATH . 'includes/class-taxonomy.php';
		require_once ST_TESTIMONIALS_PATH . 'includes/class-meta-boxes.php';
		require_once ST_TESTIMONIALS_PATH . 'includes/class-shortcode.php';
		require_once ST_TESTIMONIALS_PATH . 'includes/class-assets.php';
		require_once ST_TESTIMONIALS_PATH . 'includes/class-bulk-upload.php';
		require_once ST_TESTIMONIALS_PATH . 'includes/class-student-survey.php';
	}

	private function init() {
		new ST_Testimonials_Settings();
		new ST_Testimonials_Post_Type();
		new ST_Testimonials_Taxonomy();
		new ST_Testimonials_Meta_Boxes();
		new ST_Testimonials_Shortcode();
		new ST_Testimonials_Assets();
		new ST_Testimonials_Bulk_Upload();
		new ST_Student_Survey();
	}
}

function st_testimonials_init() {
	ST_Testimonials::get_instance();
}
add_action( 'plugins_loaded', 'st_testimonials_init' );
