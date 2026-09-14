<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CRC_Shortcode {
	public function __construct() {
		add_shortcode( 'class_registration_cards', [ $this, 'render_shortcode' ] );
	}

	public function render_shortcode( $atts ) {
		$options = get_option( 'crc_settings' );
		
		$atts = shortcode_atts( [
			'limit'            => -1,
			'columns'          => isset( $options['default_columns'] ) ? $options['default_columns'] : '3',
			'category'         => '',
			'featured'         => '',
			'show_title'       => 'yes',
			'show_description' => 'yes',
			'show_image'       => 'yes',
			'show_button'      => 'yes',
			'show_location'    => 'yes',
		], $atts, 'class_registration_cards' );

		$args = [
			'post_type'      => 'class_registration',
			'posts_per_page' => intval( $atts['limit'] ),
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'meta_query'     => [
				[
					'key'   => 'crc_active',
					'value' => 'yes',
				]
			]
		];

		if ( ! empty( $atts['category'] ) ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'class_registration_category',
					'field'    => 'slug',
					'terms'    => $atts['category'],
				]
			];
		}

		if ( $atts['featured'] === 'yes' ) {
			$args['meta_query'][] = [
				'key'   => 'crc_featured',
				'value' => 'yes',
			];
		}

		$query = new WP_Query( $args );

		ob_start();
		
		$section_heading = isset( $options['section_heading'] ) ? $options['section_heading'] : '';
		$section_desc = isset( $options['section_desc'] ) ? $options['section_desc'] : '';
		$text_align = isset( $options['text_align'] ) ? $options['text_align'] : 'center';

		echo '<div class="crc-section" style="text-align: ' . esc_attr( $text_align ) . ';">';
		
		if ( $section_heading ) {
			echo '<h2 class="crc-section__heading">' . esc_html( $section_heading ) . '</h2>';
		}
		if ( $section_desc ) {
			echo '<p class="crc-section__desc">' . esc_html( $section_desc ) . '</p>';
		}

		if ( $query->have_posts() ) {
			echo '<div class="crc-grid crc-grid--cols-' . esc_attr( $atts['columns'] ) . '">';
			
			while ( $query->have_posts() ) {
				$query->the_post();
				$this->render_card( get_the_ID(), $atts );
			}
			
			echo '</div>';
		} else {
			$empty_text = isset( $options['empty_state_text'] ) ? $options['empty_state_text'] : 'No class registrations are currently available.';
			echo '<p class="crc-empty">' . esc_html( $empty_text ) . '</p>';
		}
		
		echo '</div>';
		
		wp_reset_postdata();

		return ob_get_clean();
	}

	private function render_card( $post_id, $atts ) {
		$featured = get_post_meta( $post_id, 'crc_featured', true ) === 'yes';
		$card_class = 'crc-card';
		if ( $featured ) {
			$card_class .= ' crc-card--featured';
		}

		echo '<div class="' . esc_attr( $card_class ) . '">';

		// Badges
		$badges = get_post_meta( $post_id, 'crc_badges', true );
		if ( ! empty( $badges ) && is_array( $badges ) ) {
			echo '<div class="crc-card__badges">';
			foreach ( $badges as $badge ) {
				echo '<span class="crc-badge crc-badge--' . esc_attr( $badge['style'] ) . '">' . esc_html( $badge['text'] ) . '</span>';
			}
			echo '</div>';
		}

		// Image
		if ( $atts['show_image'] === 'yes' ) {
			$image_id = get_post_meta( $post_id, 'crc_image_id', true );
			echo '<div class="crc-card__image-wrap">';
			if ( $image_id ) {
				echo wp_get_attachment_image( $image_id, 'large', false, [ 'class' => 'crc-card__image', 'loading' => 'lazy' ] );
			} else {
				echo '<div class="crc-card__image-placeholder"></div>';
			}
			echo '</div>';
		}

		echo '<div class="crc-card__content">';

		// Date / Location Row
		$custom_date = get_post_meta( $post_id, 'crc_custom_date', true );
		$start_date = get_post_meta( $post_id, 'crc_start_date', true );
		$end_date = get_post_meta( $post_id, 'crc_end_date', true );
		$schedule_text = get_post_meta( $post_id, 'crc_schedule_text', true );
		$timezone = get_post_meta( $post_id, 'crc_timezone', true );
		
		$date_display = '';
		if ( $custom_date ) {
			$date_display = $custom_date;
		} elseif ( $start_date ) {
			$date_display = date_i18n( get_option( 'date_format' ), strtotime( $start_date ) );
			if ( $end_date ) {
				$date_display .= ' - ' . date_i18n( get_option( 'date_format' ), strtotime( $end_date ) );
			}
		}

		if ( $schedule_text ) {
			if ( $date_display ) $date_display .= ' | ';
			$date_display .= $schedule_text;
		}
		if ( $timezone ) {
			$date_display .= ' ' . $timezone;
		}

		$location = get_post_meta( $post_id, 'crc_location_name', true );
		
		if ( $date_display || ( $atts['show_location'] === 'yes' && $location ) ) {
			echo '<div class="crc-card__meta">';
			if ( $date_display ) {
				echo '<div class="crc-card__meta-item crc-card__meta-date"><span class="dashicons dashicons-calendar-alt"></span> ' . esc_html( $date_display ) . '</div>';
			}
			if ( $atts['show_location'] === 'yes' && $location ) {
				echo '<div class="crc-card__meta-item crc-card__meta-location"><span class="dashicons dashicons-location"></span> ' . esc_html( $location ) . '</div>';
			}
			echo '</div>';
		}

		// Title
		if ( $atts['show_title'] === 'yes' ) {
			echo '<h3 class="crc-card__title">' . get_the_title( $post_id ) . '</h3>';
		}

		// Description
		if ( $atts['show_description'] === 'yes' ) {
			$desc = get_post_meta( $post_id, 'crc_short_desc', true );
			if ( $desc ) {
				echo '<div class="crc-card__description">' . wpautop( esc_html( $desc ) ) . '</div>';
			}
		}

		// Features Box
		$features = get_post_meta( $post_id, 'crc_features', true );
		if ( ! empty( $features ) && is_array( $features ) ) {
			echo '<div class="crc-card__features">';
			foreach ( $features as $feature ) {
				echo '<div class="crc-card__feature-row">';
				echo '<span class="crc-card__feature-label">' . esc_html( $feature['label'] ) . ':</span>';
				echo '<span class="crc-card__feature-value">' . esc_html( $feature['value'] ) . '</span>';
				echo '</div>';
			}
			echo '</div>';
		}

		echo '</div>'; // end .crc-card__content

		// Button
		if ( $atts['show_button'] === 'yes' ) {
			$btn_text = get_post_meta( $post_id, 'crc_btn_text', true );
			$btn_url = get_post_meta( $post_id, 'crc_btn_url', true );
			$btn_target = get_post_meta( $post_id, 'crc_btn_target', true );
			$btn_style = get_post_meta( $post_id, 'crc_btn_style', true );
			
			if ( $btn_text && $btn_url ) {
				$btn_class = 'crc-btn crc-btn--' . esc_attr( $btn_style );
				echo '<div class="crc-card__footer">';
				echo '<a href="' . esc_url( $btn_url ) . '" class="' . esc_attr( $btn_class ) . '" target="' . esc_attr( $btn_target ) . '">' . esc_html( $btn_text ) . '</a>';
				echo '</div>';
			}
		}

		echo '</div>'; // end .crc-card
	}
}
