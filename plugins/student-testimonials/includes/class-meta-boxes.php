<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST_Testimonials_Meta_Boxes {
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_meta_boxes' ] );
	}

	public function add_meta_boxes() {
		add_meta_box(
			'st_testimonial_details',
			'Testimonial Details',
			[ $this, 'render_meta_box' ],
			'student_testimonial',
			'normal',
			'high'
		);
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'st_testimonial_save_details', 'st_testimonial_details_nonce' );

		$st_rating = get_post_meta( $post->ID, 'st_rating', true );
		if ( empty( $st_rating ) ) {
			$st_rating = '5'; // Default to 5
		}

		?>
		<table class="form-table">
			<tr>
				<th><label for="st_rating">Star Rating</label></th>
				<td>
					<select id="st_rating" name="st_rating">
						<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
							<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $st_rating, $i ); ?>>
								<?php echo esc_html( $i ); ?> Star<?php echo $i > 1 ? 's' : ''; ?>
							</option>
						<?php endfor; ?>
					</select>
					<p class="description">Select the rating (1-5 stars).</p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function save_meta_boxes( $post_id ) {
		if ( ! isset( $_POST['st_testimonial_details_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['st_testimonial_details_nonce'], 'st_testimonial_save_details' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['st_rating'] ) ) {
			$rating = intval( $_POST['st_rating'] );
			if ( $rating >= 1 && $rating <= 5 ) {
				update_post_meta( $post_id, 'st_rating', $rating );
			}
		}
	}
}
