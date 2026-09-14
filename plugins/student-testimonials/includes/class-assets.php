<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST_Testimonials_Assets {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
		add_action( 'wp_head', [ $this, 'render_dynamic_css' ] );
	}

	public function enqueue_frontend_scripts() {
		global $post;
		// Only load CSS if shortcode is present, or if we can't determine (e.g. widgets)
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'student_testimonials' ) ) {
			wp_enqueue_style( 'st-testimonials-css', ST_TESTIMONIALS_URL . 'public/css/testimonials.css', [], ST_TESTIMONIALS_VERSION );
			wp_enqueue_script( 'st-testimonials-js', ST_TESTIMONIALS_URL . 'public/js/testimonials.js', [], ST_TESTIMONIALS_VERSION, true );
		} elseif ( is_active_widget( false, false, 'text', true ) ) {
			// Basic check for text widgets
			wp_enqueue_style( 'st-testimonials-css', ST_TESTIMONIALS_URL . 'public/css/testimonials.css', [], ST_TESTIMONIALS_VERSION );
			wp_enqueue_script( 'st-testimonials-js', ST_TESTIMONIALS_URL . 'public/js/testimonials.js', [], ST_TESTIMONIALS_VERSION, true );
		} else {
			// Enqueue it generally in case it's in a builder
			wp_enqueue_style( 'st-testimonials-css', ST_TESTIMONIALS_URL . 'public/css/testimonials.css', [], ST_TESTIMONIALS_VERSION );
			wp_enqueue_script( 'st-testimonials-js', ST_TESTIMONIALS_URL . 'public/js/testimonials.js', [], ST_TESTIMONIALS_VERSION, true );
		}
	}

	public function render_dynamic_css() {
		$options = get_option( 'st_testimonials_settings' );
		
		$card_bg      = isset( $options['card_bg'] ) ? $options['card_bg'] : '#FFFFFF';
		$card_radius  = isset( $options['card_radius'] ) ? $options['card_radius'] : '12px';
		$star_color   = isset( $options['star_color'] ) ? $options['star_color'] : '#FFC400';
		$title_color  = isset( $options['title_color'] ) ? $options['title_color'] : '#071B91';
		$date_color   = isset( $options['date_color'] ) ? $options['date_color'] : '#666666';
		$btn_bg       = isset( $options['button_bg'] ) ? $options['button_bg'] : '#071B91';
		$btn_color    = isset( $options['button_text_color'] ) ? $options['button_text_color'] : '#FFFFFF';
		$card_gap     = isset( $options['card_gap'] ) ? $options['card_gap'] : '20px';
		$image_height = isset( $options['image_height'] ) ? $options['image_height'] : '250px';

		$css = "
		:root {
			--st-card-bg: " . esc_attr( $card_bg ) . ";
			--st-card-radius: " . esc_attr( $card_radius ) . ";
			--st-star-color: " . esc_attr( $star_color ) . ";
			--st-title-color: " . esc_attr( $title_color ) . ";
			--st-date-color: " . esc_attr( $date_color ) . ";
			--st-btn-bg: " . esc_attr( $btn_bg ) . ";
			--st-btn-color: " . esc_attr( $btn_color ) . ";
			--st-card-gap: " . esc_attr( $card_gap ) . ";
			--st-image-height: " . esc_attr( $image_height ) . ";
		}
		";

		echo '<style id="st-testimonials-dynamic-css">' . wp_strip_all_tags( $css ) . '</style>';
	}
}
