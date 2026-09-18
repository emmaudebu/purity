<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SWE_Plugin {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->load_dependencies();
		$this->init();
	}

	private function load_dependencies() {
		require_once SWE_PLUGIN_DIR . 'includes/class-settings.php';
		require_once SWE_PLUGIN_DIR . 'includes/class-subjects.php';
		require_once SWE_PLUGIN_DIR . 'includes/class-frontend.php';
	}

	private function init() {
		new SWE_Settings();
		new SWE_Subjects();
		new SWE_Frontend();
	}

	public static function activate() {
		// Register CPT so we can insert posts
		require_once SWE_PLUGIN_DIR . 'includes/class-subjects.php';
		$subjects = new SWE_Subjects();
		$subjects->register_post_type();
		flush_rewrite_rules();

		self::seed_default_subjects();
	}

	private static function seed_default_subjects() {
		$existing_subjects = get_posts( [
			'post_type'      => 'swe_subject',
			'post_status'    => 'publish',
			'posts_per_page' => 1
		] );

		// Only seed if no subjects exist
		if ( empty( $existing_subjects ) ) {
			$defaults = [
				[
					'title' => 'NCLEX Information',
					'desc'  => 'Get information about our NCLEX review programs.',
					'msg'   => 'Hello {business_name}, I am interested in your NCLEX review program. I would like more information about the available classes, schedule, course duration, fees, and registration process. Thank you.',
					'icon'  => '🩺'
				],
				[
					'title' => 'PMHNP Information',
					'desc'  => 'Get information about our PMHNP review programs.',
					'msg'   => 'Hello {business_name}, I am interested in the PMHNP Review program. Please provide me with information about the course, upcoming classes, fees, duration, and registration process. Thank you.',
					'icon'  => '👩🏾⚕️'
				],
				[
					'title' => 'Course Registration',
					'desc'  => 'Get help with registering for a course.',
					'msg'   => 'Hello {business_name}, I would like assistance with registering for one of your review courses. Please guide me through the registration process. Thank you.',
					'icon'  => '📝'
				],
				[
					'title' => 'Pricing & Payment',
					'desc'  => 'Ask about course fees and payment options.',
					'msg'   => 'Hello {business_name}, I would like to know more about the pricing and payment options for your review programs. Please provide me with the available options. Thank you.',
					'icon'  => '💳'
				],
				[
					'title' => 'Class Schedule',
					'desc'  => 'Ask about upcoming classes and schedules.',
					'msg'   => 'Hello {business_name}, I would like information about your upcoming class schedule, including available dates and class times. Thank you.',
					'icon'  => '📅'
				],
				[
					'title' => 'Other Inquiry',
					'desc'  => 'Have another question? Contact our team.',
					'msg'   => 'Hello {business_name}, I have an inquiry and would like to speak with someone from your team. Thank you.',
					'icon'  => '💬'
				]
			];

			foreach ( $defaults as $index => $subject ) {
				$post_id = wp_insert_post( [
					'post_title'   => $subject['title'],
					'post_type'    => 'swe_subject',
					'post_status'  => 'publish',
					'menu_order'   => $index + 1
				] );

				if ( $post_id ) {
					update_post_meta( $post_id, 'swe_desc', $subject['desc'] );
					update_post_meta( $post_id, 'swe_msg', $subject['msg'] );
					update_post_meta( $post_id, 'swe_icon', $subject['icon'] );
					update_post_meta( $post_id, 'swe_status', 'active' );
				}
			}
		}
	}
}
