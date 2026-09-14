<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = isset( $template_args['options'] ) ? $template_args['options'] : get_option( 'st_testimonials_settings' );

$show_content = isset( $options['show_content'] ) ? $options['show_content'] : 'yes';
$show_class   = isset( $options['show_class'] ) ? $options['show_class'] : 'yes';
$show_date    = isset( $options['show_date'] ) ? $options['show_date'] : 'yes';
$show_rating  = isset( $options['show_rating'] ) ? $options['show_rating'] : 'yes';
$excerpt_len  = isset( $options['excerpt_length'] ) ? intval( $options['excerpt_length'] ) : 30;

$rating = get_post_meta( get_the_ID(), 'st_rating', true );
$date   = get_the_date();
?>
<div class="st-card">
	<div class="st-card-image">
		<?php if ( has_post_thumbnail() ) : 
			$full_image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		?>
			<a href="<?php echo esc_url( $full_image_url ); ?>" class="st-lightbox-trigger">
				<?php the_post_thumbnail( 'large', [ 'loading' => 'lazy', 'class' => 'st-img' ] ); ?>
			</a>
		<?php else : ?>
			<div class="st-img-placeholder"></div>
		<?php endif; ?>
	</div>
	
	<div class="st-card-content">
		<?php if ( $show_rating === 'yes' && $rating ) : ?>
			<div class="st-stars" aria-label="<?php echo esc_attr( $rating ); ?> out of 5 stars">
				<?php
				$rating = intval( $rating );
				for ( $i = 1; $i <= 5; $i++ ) {
					if ( $i <= $rating ) {
						echo '<span class="st-star st-star-filled">★</span>';
					} else {
						echo '<span class="st-star st-star-empty">☆</span>';
					}
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ( $show_date === 'yes' && $date ) : ?>
			<div class="st-date"><?php echo esc_html( $date ); ?></div>
		<?php endif; ?>

		<?php if ( $show_class === 'yes' ) : 
			$terms = get_the_terms( get_the_ID(), 'student_testimonial_class' );
			if ( $terms && ! is_wp_error( $terms ) ) :
				$term_names = wp_list_pluck( $terms, 'name' );
		?>
			<div class="st-class-passed">
				<strong>Exam Passed:</strong> <?php echo esc_html( implode( ', ', $term_names ) ); ?>
			</div>
		<?php 
			endif;
		endif; ?>

		<?php if ( $show_content === 'yes' ) : ?>
			<div class="st-excerpt">
				<?php 
				$content = get_the_content();
				$trimmed_content = wp_trim_words( $content, $excerpt_len, '&hellip;' );
				echo wp_kses_post( wpautop( $trimmed_content ) ); 
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
