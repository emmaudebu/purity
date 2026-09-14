<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CRC_Assets {
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_assets' ] );
		add_action( 'wp_head', [ $this, 'output_custom_css' ] );
	}

	public function enqueue_admin_assets( $hook ) {
		global $post_type;
		
		// Only load on our custom post type and settings page
		if ( 'class_registration' !== $post_type && strpos( $hook, 'crc_settings' ) === false ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'crc-admin-css', CRC_PLUGIN_URL . 'admin/css/admin.css', [], CRC_VERSION );
		wp_enqueue_script( 'crc-admin-js', CRC_PLUGIN_URL . 'admin/js/admin.js', [ 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ], CRC_VERSION, true );
		
		wp_localize_script( 'crc-admin-js', 'crc_ajax', [
			'url' => admin_url( 'admin-ajax.php' )
		]);
	}

	public function enqueue_public_assets() {
		global $post;
		
		// Attempt to load only when shortcode is used (basic check)
		// Works for basic content, but maybe not for elementor/gutenberg full site editing.
		// We'll enqueue unconditionally for robust Elementor support or we can rely on wp_enqueue_scripts during shortcode execution (but styles in body aren't great).
		wp_enqueue_style( 'crc-public-css', CRC_PLUGIN_URL . 'public/css/public.css', [], CRC_VERSION );
		wp_enqueue_script( 'crc-public-js', CRC_PLUGIN_URL . 'public/js/public.js', [ 'jquery' ], CRC_VERSION, true );
	}

	public function output_custom_css() {
		$options = get_option( 'crc_settings' );
		if ( ! $options ) return;

		$primary_color = isset( $options['primary_color'] ) ? $options['primary_color'] : '#001A72';
		$secondary_color = isset( $options['secondary_color'] ) ? $options['secondary_color'] : '#149F41';
		$heading_color = isset( $options['heading_color'] ) ? $options['heading_color'] : '#001A72';
		$text_color = isset( $options['text_color'] ) ? $options['text_color'] : '#444444';
		$card_bg = isset( $options['card_bg'] ) ? $options['card_bg'] : '#FFFFFF';
		$card_border = isset( $options['card_border'] ) ? $options['card_border'] : '#E5E5E5';
		$feature_bg = isset( $options['feature_bg'] ) ? $options['feature_bg'] : '#F4F7FB';
		
		$heading_size = isset( $options['heading_size'] ) ? $options['heading_size'] : '32px';
		$desc_size = isset( $options['desc_size'] ) ? $options['desc_size'] : '16px';
		
		$card_radius = isset( $options['card_radius'] ) ? $options['card_radius'] : '8px';
		$card_spacing = isset( $options['card_spacing'] ) ? $options['card_spacing'] : '20px';
		$image_height = isset( $options['image_height'] ) ? $options['image_height'] : '200px';

		echo "<style>
			:root {
				--crc-primary: " . esc_attr( $primary_color ) . ";
				--crc-secondary: " . esc_attr( $secondary_color ) . ";
				--crc-heading-color: " . esc_attr( $heading_color ) . ";
				--crc-text-color: " . esc_attr( $text_color ) . ";
				--crc-card-bg: " . esc_attr( $card_bg ) . ";
				--crc-card-border: " . esc_attr( $card_border ) . ";
				--crc-feature-bg: " . esc_attr( $feature_bg ) . ";
				--crc-heading-size: " . esc_attr( $heading_size ) . ";
				--crc-desc-size: " . esc_attr( $desc_size ) . ";
				--crc-card-radius: " . esc_attr( $card_radius ) . ";
				--crc-card-spacing: " . esc_attr( $card_spacing ) . ";
				--crc-image-height: " . esc_attr( $image_height ) . ";
			}
		</style>";
	}
}
