<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST_Student_Survey {
	public function __construct() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_filter( 'manage_student_survey_posts_columns', [ $this, 'add_custom_columns' ] );
		add_action( 'manage_student_survey_posts_custom_column', [ $this, 'render_custom_columns' ], 10, 2 );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_shortcode( 'student_survey_form', [ $this, 'render_shortcode' ] );
		add_action( 'wp_loaded', [ $this, 'handle_form_submission' ] );
		add_action( 'admin_notices', [ $this, 'display_shortcode_notice' ] );
		add_action( 'admin_init', [ $this, 'handle_csv_export' ] );
	}

	public function display_shortcode_notice() {
		$screen = get_current_screen();
		if ( $screen && $screen->id === 'edit-student_survey' ) {
			?>
			<div class="notice notice-info" style="display: flex; justify-content: space-between; align-items: center; padding-right: 15px;">
				<div>
					<p><strong>Student Survey Shortcode:</strong> To display the Student Performance & Experience Survey form on any page, use: <code>[student_survey_form]</code></p>
				</div>
				<div>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=student_survey&export_csv=1' ) ); ?>" class="button button-primary">Export to CSV</a>
				</div>
			</div>
			<?php
		}
	}

	public function handle_csv_export() {
		if ( isset( $_GET['export_csv'] ) && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'student_survey' ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_die( 'You do not have permission to perform this action.' );
			}

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="student_surveys_' . date('Y-m-d') . '.csv"' );

			$output = fopen( 'php://output', 'w' );
			
			// Add BOM for Excel UTF-8 reading
			fputs( $output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ) );

			// CSV Headers
			fputcsv( $output, [
				'Date Submitted',
				'Overall Experience',
				'Core NCLEX-RN Prep',
				'Next Gen NCLEX (CAT) Prep',
				'Tutor Clarity & Knowledge',
				'Usefulness of Materials',
				'Confidence Change',
				'Exam Status',
				'Most Valuable Aspect',
				'One Thing to Do Better',
				'Additional Comments'
			] );

			$args = [
				'post_type'      => 'student_survey',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC'
			];
			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$post_id = get_the_ID();
					
					fputcsv( $output, [
						get_the_date( 'Y-m-d H:i:s' ),
						get_post_meta( $post_id, 'overall_experience', true ),
						get_post_meta( $post_id, 'core_prep', true ),
						get_post_meta( $post_id, 'cat_prep', true ),
						get_post_meta( $post_id, 'tutor_clarity', true ),
						get_post_meta( $post_id, 'materials_useful', true ),
						get_post_meta( $post_id, 'confidence_change', true ),
						get_post_meta( $post_id, 'exam_status', true ),
						get_post_meta( $post_id, 'most_valuable', true ),
						get_post_meta( $post_id, 'do_better', true ),
						get_post_meta( $post_id, 'additional_comments', true )
					] );
				}
			}
			wp_reset_postdata();

			fclose( $output );
			exit;
		}
	}

	public function register_post_type() {
		$labels = [
			'name'               => 'Student Surveys',
			'singular_name'      => 'Survey Response',
			'menu_name'          => 'Student Surveys',
			'add_new_item'       => 'Add New Survey Response',
			'edit_item'          => 'View Survey Response',
			'view_item'          => 'View Survey',
			'all_items'          => 'All Surveys',
			'search_items'       => 'Search Surveys',
			'not_found'          => 'No surveys found.',
		];

		$args = [
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => 21,
			'menu_icon'           => 'dashicons-clipboard',
			'supports'            => [ 'title' ],
		];

		register_post_type( 'student_survey', $args );
	}

	public function add_custom_columns( $columns ) {
		$new_columns = [];
		foreach ( $columns as $key => $title ) {
			if ( $key === 'date' ) {
				$new_columns['ss_overall']  = 'Overall Exp.';
				$new_columns['ss_exam']     = 'Exam Status';
				$new_columns['date']        = $title;
			} else {
				$new_columns[ $key ] = $title;
			}
		}
		return $new_columns;
	}

	public function render_custom_columns( $column, $post_id ) {
		if ( $column === 'ss_overall' ) {
			echo esc_html( get_post_meta( $post_id, 'overall_experience', true ) );
		} elseif ( $column === 'ss_exam' ) {
			echo esc_html( get_post_meta( $post_id, 'exam_status', true ) );
		}
	}

	public function add_meta_boxes() {
		add_meta_box( 'ss_details_meta', 'Survey Details', [ $this, 'render_meta_box' ], 'student_survey', 'normal', 'high' );
	}

	public function render_meta_box( $post ) {
		$meta = get_post_meta( $post->ID );
		
		echo '<style>.ss-meta-table { width: 100%; border-collapse: collapse; } .ss-meta-table th { text-align: left; padding: 10px; border-bottom: 1px solid #eee; width: 40%; } .ss-meta-table td { padding: 10px; border-bottom: 1px solid #eee; }</style>';
		echo '<table class="ss-meta-table">';
		
		$fields = [
			'overall_experience' => 'Overall Experience',
			'core_prep'          => 'Core NCLEX-RN Prep',
			'cat_prep'           => 'Next Gen NCLEX (CAT) Prep',
			'tutor_clarity'      => 'Tutor Clarity & Knowledge',
			'materials_useful'   => 'Usefulness of Materials',
			'confidence_change'  => 'Confidence Change',
			'exam_status'        => 'Exam Status',
			'most_valuable'      => 'Most Valuable Aspect',
			'do_better'          => 'One Thing to Do Better',
			'additional_comments'=> 'Additional Comments',
		];
		
		foreach ( $fields as $key => $label ) {
			$value = isset( $meta[$key][0] ) ? $meta[$key][0] : '—';
			echo '<tr><th>' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( $value ) ) . '</td></tr>';
		}
		echo '</table>';
	}

	public function handle_form_submission() {
		if ( isset( $_POST['submit_student_survey'] ) && isset( $_POST['ss_nonce'] ) && wp_verify_nonce( $_POST['ss_nonce'], 'submit_student_survey_action' ) ) {
			
			$title = 'Survey Response - ' . date( 'Y-m-d H:i:s' );
			
			$post_id = wp_insert_post( [
				'post_title'  => $title,
				'post_type'   => 'student_survey',
				'post_status' => 'publish',
			] );

			if ( $post_id ) {
				$fields = [
					'overall_experience', 'core_prep', 'cat_prep', 'tutor_clarity', 'materials_useful',
					'confidence_change', 'exam_status', 'most_valuable', 'do_better', 'additional_comments'
				];
				
				foreach ( $fields as $field ) {
					if ( isset( $_POST[$field] ) ) {
						update_post_meta( $post_id, $field, sanitize_textarea_field( $_POST[$field] ) );
					}
				}
				
				global $ss_submission_success;
				$ss_submission_success = true;
			}
		}
	}

	public function render_shortcode() {
		global $ss_submission_success;
		ob_start();
		?>
		<style>
			.ss-form-container { background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); max-width: 650px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; border-top: 5px solid #071B91; }
			.ss-form-container h3 { margin-top: 0; color: #071B91; font-size: 24px; margin-bottom: 8px; font-weight: 700; }
			.ss-form-container p.ss-desc { color: #666; font-size: 15px; margin-bottom: 24px; line-height: 1.5; }
			.ss-form-group { margin-bottom: 24px; }
			.ss-form-group > label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 15px; }
			.ss-form input[type="text"], .ss-form textarea { width: 100%; box-sizing: border-box; padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 15px; transition: all 0.2s ease; background: #f8fafc; color: #1e293b; }
			.ss-form input[type="text"]:focus, .ss-form textarea:focus { border-color: #071B91; outline: none; background: #ffffff; box-shadow: 0 0 0 3px rgba(7, 27, 145, 0.15); }
			
			/* Star Rating Styles (1-5) */
			.ss-rating-stars { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 4px; position: relative; flex-wrap: nowrap; margin-bottom: 5px; }
			.ss-rating-stars input[type="radio"] { position: absolute; opacity: 0; width: 1px; height: 1px; left: 0; top: 15px; }
			.ss-rating-stars label.ss-star-label { font-size: 32px; color: #cbd5e1; cursor: pointer; transition: color 0.2s ease; margin: 0 !important; line-height: 1; display: inline-block !important; width: auto !important; flex: 0 0 auto !important; }
			.ss-rating-stars label.ss-star-label:hover,
			.ss-rating-stars label.ss-star-label:hover ~ label.ss-star-label,
			.ss-rating-stars input[type="radio"]:checked ~ label.ss-star-label { color: #FFC400; }
			
			/* Radio Options */
			.ss-radio-options label { display: block; margin-bottom: 8px; cursor: pointer; font-size: 15px; color: #444; }
			.ss-radio-options input[type="radio"] { margin-right: 8px; accent-color: #071B91; }
			
			.ss-form button { background: #071B91; color: white; border: none; padding: 16px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 16px; width: 100%; transition: background 0.2s ease; margin-top: 10px; }
			.ss-form button:hover { background: #05105c; transform: translateY(-1px); }
			
			/* Modal Styles */
			.ss-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 999999; opacity: 0; visibility: hidden; transition: all 0.3s ease; backdrop-filter: blur(4px); }
			.ss-modal-overlay.active { opacity: 1; visibility: visible; }
			.ss-modal-content { background: white; padding: 40px; border-radius: 16px; text-align: center; max-width: 400px; transform: translateY(20px); transition: all 0.3s ease; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
			.ss-modal-overlay.active .ss-modal-content { transform: translateY(0); }
			.ss-modal-icon { width: 64px; height: 64px; background: #071B91; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 20px; }
			.ss-modal-content h3 { color: #071B91; margin: 0 0 10px; font-size: 24px; }
			.ss-modal-content p { color: #666; margin: 0 0 24px; line-height: 1.5; }
			.ss-modal-close { background: #f1f5f9; color: #475569; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
			.ss-modal-close:hover { background: #e2e8f0; color: #1e293b; }
		</style>

		<div class="ss-form-container">
			<h3>Student Performance & Experience Survey</h3>
			<p class="ss-desc">Your feedback helps us improve our tutoring program and better support future NCLEX-RN candidates. Ratings run from 1 (lowest) to 5 (highest).</p>
			
			<form class="ss-form" method="post" action="">
				<?php wp_nonce_field( 'submit_student_survey_action', 'ss_nonce' ); ?>
				<input type="hidden" name="submit_student_survey" value="1">
				
				<?php
				$rating_fields = [
					'overall_experience' => 'How would you rate your overall experience with Purity Tutoring Services?',
					'core_prep'          => 'How well did the tutoring sessions prepare you for core NCLEX-RN content areas?',
					'cat_prep'           => 'How well did the program prepare you for the Next Generation NCLEX (CAT) question formats?',
					'tutor_clarity'      => 'How would you rate the clarity and subject-matter knowledge of your tutor/instructor?',
					'materials_useful'   => 'How useful were the practice questions, mock exams, and study materials provided?',
				];
				
				foreach ( $rating_fields as $key => $label ) {
					?>
					<div class="ss-form-group">
						<label><?php echo esc_html( $label ); ?></label>
						<div class="ss-rating-stars">
							<input type="radio" id="<?php echo esc_attr($key); ?>5" name="<?php echo esc_attr($key); ?>" value="5" required />
							<label for="<?php echo esc_attr($key); ?>5" class="ss-star-label" title="5 Stars">&#9733;</label>
							<input type="radio" id="<?php echo esc_attr($key); ?>4" name="<?php echo esc_attr($key); ?>" value="4" />
							<label for="<?php echo esc_attr($key); ?>4" class="ss-star-label" title="4 Stars">&#9733;</label>
							<input type="radio" id="<?php echo esc_attr($key); ?>3" name="<?php echo esc_attr($key); ?>" value="3" />
							<label for="<?php echo esc_attr($key); ?>3" class="ss-star-label" title="3 Stars">&#9733;</label>
							<input type="radio" id="<?php echo esc_attr($key); ?>2" name="<?php echo esc_attr($key); ?>" value="2" />
							<label for="<?php echo esc_attr($key); ?>2" class="ss-star-label" title="2 Stars">&#9733;</label>
							<input type="radio" id="<?php echo esc_attr($key); ?>1" name="<?php echo esc_attr($key); ?>" value="1" />
							<label for="<?php echo esc_attr($key); ?>1" class="ss-star-label" title="1 Star">&#9733;</label>
						</div>
					</div>
					<?php
				}
				?>
				
				<div class="ss-form-group">
					<label>How has your confidence in passing the NCLEX-RN changed since starting with us?</label>
					<div class="ss-radio-options">
						<label><input type="radio" name="confidence_change" value="Much more confident" required> Much more confident</label>
						<label><input type="radio" name="confidence_change" value="Somewhat more confident"> Somewhat more confident</label>
						<label><input type="radio" name="confidence_change" value="Somewhat less confident"> Somewhat less confident</label>
						<label><input type="radio" name="confidence_change" value="Much less confident"> Much less confident</label>
					</div>
				</div>
				
				<div class="ss-form-group">
					<label>Have you taken the NCLEX-RN exam since completing (or while enrolled in) this program?</label>
					<div class="ss-radio-options">
						<label><input type="radio" name="exam_status" value="Yes - I passed" required> Yes — I passed</label>
						<label><input type="radio" name="exam_status" value="Yes - I did not pass"> Yes — I did not pass</label>
						<label><input type="radio" name="exam_status" value="Not yet - exam scheduled"> Not yet — exam scheduled</label>
						<label><input type="radio" name="exam_status" value="Not yet - no date scheduled"> Not yet — no date scheduled</label>
					</div>
				</div>

				<div class="ss-form-group">
					<label>What did you find most valuable about the tutoring program?</label>
					<textarea name="most_valuable" rows="3" required></textarea>
				</div>
				
				<div class="ss-form-group">
					<label>What is one thing we could do better?</label>
					<textarea name="do_better" rows="3" required></textarea>
				</div>
				
				<div class="ss-form-group">
					<label>Any additional comments?</label>
					<textarea name="additional_comments" rows="3"></textarea>
				</div>

				<button type="submit">Submit Survey</button>
			</form>
		</div>

		<?php if ( $ss_submission_success ): ?>
		<div class="ss-modal-overlay active" id="ssSuccessModal">
			<div class="ss-modal-content">
				<div class="ss-modal-icon">✓</div>
				<h3>Thank You!</h3>
				<p>Your survey has been submitted successfully. Your feedback helps us improve.</p>
				<button class="ss-modal-close" onclick="document.getElementById('ssSuccessModal').classList.remove('active')">Close</button>
			</div>
		</div>
		<?php endif; ?>

		<?php
		return ob_get_clean();
	}
}
