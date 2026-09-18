<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SWE_Subjects {
	public function __construct() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_swe_subject', [ $this, 'save_meta_boxes' ] );
		
		add_filter( 'manage_swe_subject_posts_columns', [ $this, 'custom_columns' ] );
		add_action( 'manage_swe_subject_posts_custom_column', [ $this, 'custom_columns_data' ], 10, 2 );
	}

	public function register_post_type() {
		$labels = [
			'name'               => 'Enquiry Subjects',
			'singular_name'      => 'Subject',
			'menu_name'          => 'WhatsApp Enquiries',
			'add_new'            => 'Add New Subject',
			'add_new_item'       => 'Add New Enquiry Subject',
			'edit_item'          => 'Edit Subject',
			'all_items'          => 'Enquiry Subjects'
		];

		$args = [
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 58,
			'menu_icon'           => 'dashicons-whatsapp',
			'supports'            => [ 'title', 'page-attributes' ],
			'hierarchical'        => false,
			'has_archive'         => false,
		];

		register_post_type( 'swe_subject', $args );
	}

	public function custom_columns( $columns ) {
		$columns = [
			'cb'         => $columns['cb'],
			'icon'       => 'Icon',
			'title'      => 'Subject Name',
			'status'     => 'Status',
			'menu_order' => 'Order',
			'date'       => 'Date'
		];
		return $columns;
	}

	public function custom_columns_data( $column, $post_id ) {
		switch ( $column ) {
			case 'icon':
				echo esc_html( get_post_meta( $post_id, 'swe_icon', true ) );
				break;
			case 'status':
				$status = get_post_meta( $post_id, 'swe_status', true ) === 'active' ? 'Active' : 'Inactive';
				echo '<strong>' . esc_html( $status ) . '</strong>';
				break;
			case 'menu_order':
				$post = get_post( $post_id );
				echo esc_html( $post->menu_order );
				break;
		}
	}

	public function add_meta_boxes() {
		add_meta_box(
			'swe_subject_details',
			'Subject Configuration',
			[ $this, 'render_meta_box' ],
			'swe_subject',
			'normal',
			'high'
		);
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'swe_save_subject', 'swe_subject_nonce' );

		$desc   = get_post_meta( $post->ID, 'swe_desc', true );
		$msg    = get_post_meta( $post->ID, 'swe_msg', true );
		$icon   = get_post_meta( $post->ID, 'swe_icon', true );
		$number = get_post_meta( $post->ID, 'swe_number', true );
		$status = get_post_meta( $post->ID, 'swe_status', true ) ?: 'active';
		?>
		<style>
			.swe-field-group { margin-bottom: 20px; }
			.swe-field-group label { display: block; font-weight: 600; margin-bottom: 5px; }
			.swe-field-group input[type="text"], .swe-field-group textarea { width: 100%; max-width: 600px; }
			.swe-field-group p.description { margin-top: 5px; color: #666; }
			.swe-variables-helper { background: #f0f0f1; padding: 10px; border-radius: 4px; display: inline-block; margin-top: 10px; }
			.swe-variables-helper code { cursor: pointer; color: #2271b1; font-size: 13px; margin-right: 5px; }
		</style>

		<div class="swe-field-group">
			<label for="swe_status">Status</label>
			<select name="swe_status" id="swe_status">
				<option value="active" <?php selected( $status, 'active' ); ?>>Active</option>
				<option value="inactive" <?php selected( $status, 'inactive' ); ?>>Inactive</option>
			</select>
		</div>

		<div class="swe-field-group">
			<label for="swe_icon">Icon (Emoji)</label>
			<input type="text" name="swe_icon" id="swe_icon" value="<?php echo esc_attr( $icon ); ?>" style="width: 80px; font-size: 20px;" />
			<p class="description">Paste an emoji here (e.g. 🩺, 👩🏾⚕️, 📝).</p>
		</div>

		<div class="swe-field-group">
			<label for="swe_desc">Description</label>
			<textarea name="swe_desc" id="swe_desc" rows="2"><?php echo esc_textarea( $desc ); ?></textarea>
			<p class="description">Optional short description for this subject.</p>
		</div>

		<div class="swe-field-group">
			<label for="swe_number">Specific WhatsApp Number (Optional)</label>
			<input type="text" name="swe_number" id="swe_number" value="<?php echo esc_attr( $number ); ?>" />
			<p class="description">If empty, the global WhatsApp number from the Settings page will be used.</p>
		</div>

		<div class="swe-field-group">
			<label for="swe_msg">Predefined Message</label>
			<textarea name="swe_msg" id="swe_msg" rows="5" required><?php echo esc_textarea( $msg ); ?></textarea>
			
			<div class="swe-variables-helper">
				<strong>Available variables (click to insert):</strong><br>
				<code onclick="insertSweVar('{business_name}')">{business_name}</code>
				<code onclick="insertSweVar('{subject}')">{subject}</code>
				<code onclick="insertSweVar('{page_title}')">{page_title}</code>
				<code onclick="insertSweVar('{page_url}')">{page_url}</code>
				<code onclick="insertSweVar('{site_name}')">{site_name}</code>
			</div>
			
			<script>
				function insertSweVar(variable) {
					var textarea = document.getElementById('swe_msg');
					var startPos = textarea.selectionStart;
					var endPos = textarea.selectionEnd;
					textarea.value = textarea.value.substring(0, startPos) + variable + textarea.value.substring(endPos, textarea.value.length);
					textarea.focus();
					textarea.selectionStart = startPos + variable.length;
					textarea.selectionEnd = startPos + variable.length;
				}
			</script>
		</div>
		<?php
	}

	public function save_meta_boxes( $post_id ) {
		if ( ! isset( $_POST['swe_subject_nonce'] ) || ! wp_verify_nonce( $_POST['swe_subject_nonce'], 'swe_save_subject' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['swe_desc'] ) ) {
			update_post_meta( $post_id, 'swe_desc', sanitize_textarea_field( $_POST['swe_desc'] ) );
		}
		if ( isset( $_POST['swe_msg'] ) ) {
			update_post_meta( $post_id, 'swe_msg', sanitize_textarea_field( $_POST['swe_msg'] ) );
		}
		if ( isset( $_POST['swe_icon'] ) ) {
			update_post_meta( $post_id, 'swe_icon', sanitize_text_field( $_POST['swe_icon'] ) );
		}
		if ( isset( $_POST['swe_number'] ) ) {
			update_post_meta( $post_id, 'swe_number', sanitize_text_field( $_POST['swe_number'] ) );
		}
		if ( isset( $_POST['swe_status'] ) ) {
			update_post_meta( $post_id, 'swe_status', sanitize_text_field( $_POST['swe_status'] ) );
		}
	}
}
