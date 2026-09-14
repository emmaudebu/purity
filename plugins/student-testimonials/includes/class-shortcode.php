<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST_Testimonials_Shortcode {
	public function __construct() {
		add_shortcode( 'student_testimonials', [ $this, 'render_shortcode' ] );
	}

	public function render_shortcode( $atts ) {
		$options = get_option( 'st_testimonials_settings' );

		// Merge defaults from settings, then override with shortcode attributes
		$defaults = [
			'columns'        => isset( $options['columns'] ) ? $options['columns'] : '4',
			'tablet_columns' => isset( $options['tablet_columns'] ) ? $options['tablet_columns'] : '2',
			'mobile_columns' => isset( $options['mobile_columns'] ) ? $options['mobile_columns'] : '1',
			'limit'          => isset( $options['limit'] ) ? $options['limit'] : '8',
			'class'          => '',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'show_button'    => isset( $options['show_button'] ) ? $options['show_button'] : 'no',
			'button_text'    => isset( $options['button_text'] ) ? $options['button_text'] : 'View All Testimonials',
			'button_url'     => isset( $options['button_url'] ) ? $options['button_url'] : '/testimonials/',
			'button_target'  => isset( $options['button_target'] ) ? $options['button_target'] : 'no',
			'button_alignment'=> isset( $options['button_alignment'] ) ? $options['button_alignment'] : 'center',
		];

		$atts = shortcode_atts( $defaults, $atts, 'student_testimonials' );

		$args = [
			'post_type'      => 'student_testimonial',
			'posts_per_page' => intval( $atts['limit'] ),
			'orderby'        => $atts['orderby'],
			'order'          => $atts['order'],
			'post_status'    => 'publish',
		];

		if ( ! empty( $atts['class'] ) ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'student_testimonial_class',
					'field'    => 'slug',
					'terms'    => $atts['class'],
				]
			];
		}

		$query = new WP_Query( $args );

		ob_start();

		if ( $query->have_posts() ) {
			// Pass settings to template if needed
			$template_args = [
				'options' => $options,
			];

			$grid_classes = sprintf( 
				'st-grid st-grid-desktop-%s st-grid-tablet-%s st-grid-mobile-%s', 
				esc_attr( $atts['columns'] ), 
				esc_attr( $atts['tablet_columns'] ), 
				esc_attr( $atts['mobile_columns'] )
			);

			echo '<div class="st-testimonials-wrapper">';
			echo '<div class="' . esc_attr( $grid_classes ) . '">';
			
			while ( $query->have_posts() ) {
				$query->the_post();
				
				// Include the card template
				if ( file_exists( ST_TESTIMONIALS_PATH . 'templates/testimonial-card.php' ) ) {
					include ST_TESTIMONIALS_PATH . 'templates/testimonial-card.php';
				}
			}
			
			echo '</div>'; // .st-grid

			if ( $atts['show_button'] === 'yes' && ! empty( $atts['button_url'] ) && ! empty( $atts['button_text'] ) ) {
				$target = $atts['button_target'] === 'yes' ? '_blank' : '_self';
				$align = esc_attr( $atts['button_alignment'] );
				echo '<div class="st-button-wrapper st-align-' . $align . '">';
				echo '<a href="' . esc_url( $atts['button_url'] ) . '" target="' . esc_attr( $target ) . '" class="st-btn">' . esc_html( $atts['button_text'] ) . '</a>';
				echo '</div>';
			}

			echo '</div>'; // .st-testimonials-wrapper
		}

		wp_reset_postdata();

		return ob_get_clean();
	}
}
