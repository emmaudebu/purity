<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST_Testimonials_Bulk_Upload {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_bulk_upload_page' ] );
		add_action( 'admin_init', [ $this, 'handle_bulk_upload' ] );
	}

	public function add_bulk_upload_page() {
		add_submenu_page(
			'edit.php?post_type=student_testimonial',
			'Bulk Upload',
			'Bulk Upload',
			'edit_posts',
			'st_bulk_upload',
			[ $this, 'render_bulk_upload_page' ]
		);
	}

	public function render_bulk_upload_page() {
		?>
		<div class="wrap">
			<h1>Bulk Upload Testimonials</h1>
			<div class="notice notice-info inline" style="margin-bottom: 20px;">
				<p><strong>Shortcode:</strong> Display testimonials using <code>[student_testimonials]</code></p>
			</div>
			<p>Select the Exam / Class and choose multiple images to upload. Each image will automatically be created as a new 5-star testimonial.</p>
			
			<?php
			if ( isset( $_GET['uploaded'] ) ) {
				$count = intval( $_GET['uploaded'] );
				echo '<div class="updated notice is-dismissible"><p>Successfully created <strong>' . esc_html( $count ) . '</strong> testimonials.</p></div>';
			}
			if ( isset( $_GET['upload_error'] ) ) {
				echo '<div class="error notice is-dismissible"><p>There was an error uploading one or more files.</p></div>';
			}
			?>

			<form method="post" enctype="multipart/form-data" action="">
				<?php wp_nonce_field( 'st_bulk_upload_action', 'st_bulk_upload_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="st_class_term">Exam / Class</label></th>
						<td>
							<?php
							$terms = get_terms( [
								'taxonomy'   => 'student_testimonial_class',
								'hide_empty' => false,
							] );
							
							if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
								echo '<select name="st_class_term" id="st_class_term" style="margin-bottom: 10px;">';
								echo '<option value="">-- Select Existing Exam/Class --</option>';
								foreach ( $terms as $term ) {
									echo '<option value="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</option>';
								}
								echo '</select><br>';
							}
							?>
							<label for="st_new_class"><strong>Or type a new Exam / Class:</strong></label><br>
							<input type="text" name="st_new_class" id="st_new_class" class="regular-text" placeholder="e.g. NCLEX-RN">
							<p class="description">If you type a new class here, it will be created and assigned automatically.</p>
						</td>
					</tr>
					<tr>
						<th><label for="st_bulk_images">Testimonial Images</label></th>
						<td>
							<input type="file" name="st_bulk_images[]" id="st_bulk_images" multiple accept="image/*" required>
							<p class="description">You can select multiple images at once (e.g., holding Ctrl/Cmd or Shift).</p>
						</td>
					</tr>
				</table>
				
				<?php submit_button( 'Upload & Create Testimonials' ); ?>
			</form>
		</div>
		<?php
	}

	public function handle_bulk_upload() {
		if ( ! isset( $_POST['st_bulk_upload_nonce'] ) || ! wp_verify_nonce( $_POST['st_bulk_upload_nonce'], 'st_bulk_upload_action' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( empty( $_FILES['st_bulk_images']['name'][0] ) ) {
			return;
		}

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$files = $_FILES['st_bulk_images'];
		$uploaded_count = 0;
		$has_error = false;

		foreach ( $files['name'] as $key => $value ) {
			if ( $files['name'][ $key ] ) {
				$file = [
					'name'     => $files['name'][ $key ],
					'type'     => $files['type'][ $key ],
					'tmp_name' => $files['tmp_name'][ $key ],
					'error'    => $files['error'][ $key ],
					'size'     => $files['size'][ $key ],
				];

				$_FILES['st_bulk_upload_single'] = $file;
				$attachment_id = media_handle_upload( 'st_bulk_upload_single', 0 );

				if ( ! is_wp_error( $attachment_id ) ) {
					// Create a post
					$post_data = [
						'post_title'  => 'Testimonial - ' . current_time( 'Y-m-d H:i' ),
						'post_type'   => 'student_testimonial',
						'post_status' => 'pending',
					];

					$post_id = wp_insert_post( $post_data );

					if ( $post_id && ! is_wp_error( $post_id ) ) {
						// Set Featured Image
						set_post_thumbnail( $post_id, $attachment_id );
						
						// Set default 5 stars
						update_post_meta( $post_id, 'st_rating', 5 );

						// Set Taxonomy
						$term_id = 0;
						if ( ! empty( $_POST['st_new_class'] ) ) {
							$new_term = wp_insert_term( sanitize_text_field( $_POST['st_new_class'] ), 'student_testimonial_class' );
							if ( ! is_wp_error( $new_term ) ) {
								$term_id = $new_term['term_id'];
							} elseif ( isset( $new_term->error_data['term_exists'] ) ) {
								$term_id = $new_term->error_data['term_exists'];
							}
						} elseif ( ! empty( $_POST['st_class_term'] ) ) {
							$term_id = intval( $_POST['st_class_term'] );
						}

						if ( $term_id ) {
							wp_set_object_terms( $post_id, $term_id, 'student_testimonial_class' );
						}

						$uploaded_count++;
					} else {
						$has_error = true;
					}
				} else {
					$has_error = true;
				}
			}
		}

		$redirect_url = add_query_arg( 'page', 'st_bulk_upload', admin_url( 'edit.php?post_type=student_testimonial' ) );
		if ( $uploaded_count > 0 ) {
			$redirect_url = add_query_arg( 'uploaded', $uploaded_count, $redirect_url );
		}
		if ( $has_error ) {
			$redirect_url = add_query_arg( 'upload_error', '1', $redirect_url );
		}

		wp_redirect( $redirect_url );
		exit;
	}
}
