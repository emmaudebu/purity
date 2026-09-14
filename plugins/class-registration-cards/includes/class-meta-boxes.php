<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CRC_Meta_Boxes {
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_meta_boxes' ] );
	}

	public function add_meta_boxes() {
		add_meta_box( 'crc_general_info', 'General Information', [ $this, 'render_general_info' ], 'class_registration', 'normal', 'high' );
		add_meta_box( 'crc_class_image', 'Class Image', [ $this, 'render_class_image' ], 'class_registration', 'normal', 'default' );
		add_meta_box( 'crc_badges', 'Badges', [ $this, 'render_badges' ], 'class_registration', 'normal', 'default' );
		add_meta_box( 'crc_schedule', 'Schedule', [ $this, 'render_schedule' ], 'class_registration', 'normal', 'default' );
		add_meta_box( 'crc_location', 'Location', [ $this, 'render_location' ], 'class_registration', 'normal', 'default' );
		add_meta_box( 'crc_features', 'Features Box', [ $this, 'render_features' ], 'class_registration', 'normal', 'default' );
		add_meta_box( 'crc_button', 'Button Settings', [ $this, 'render_button' ], 'class_registration', 'normal', 'default' );
		add_meta_box( 'crc_card_settings', 'Card Settings', [ $this, 'render_card_settings' ], 'class_registration', 'side', 'default' );
	}

	public function render_general_info( $post ) {
		wp_nonce_field( 'crc_save_meta', 'crc_meta_nonce' );
		$short_desc = get_post_meta( $post->ID, 'crc_short_desc', true );
		?>
		<p>
			<label for="crc_short_desc"><strong>Short Description</strong></label><br>
			<textarea name="crc_short_desc" id="crc_short_desc" rows="4" style="width:100%;"><?php echo esc_textarea( $short_desc ); ?></textarea>
		</p>
		<?php
	}

	public function render_class_image( $post ) {
		$image_id = get_post_meta( $post->ID, 'crc_image_id', true );
		$image_url = $image_id ? wp_get_attachment_url( $image_id ) : '';
		?>
		<div class="crc-image-wrapper">
			<input type="hidden" id="crc_image_id" name="crc_image_id" value="<?php echo esc_attr( $image_id ); ?>">
			<div id="crc_image_preview" style="margin-bottom: 10px; max-width: 100%;">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" style="max-width: 100%; height: auto;" />
				<?php endif; ?>
			</div>
			<button type="button" class="button crc-upload-image">Select / Upload Image</button>
			<button type="button" class="button crc-remove-image" style="<?php echo ! $image_url ? 'display:none;' : ''; ?>">Remove Image</button>
		</div>
		<?php
	}

	public function render_badges( $post ) {
		$badges = get_post_meta( $post->ID, 'crc_badges', true );
		if ( ! is_array( $badges ) ) {
			$badges = [];
		}
		?>
		<div id="crc-badges-container">
			<?php foreach ( $badges as $index => $badge ) : ?>
				<div class="crc-badge-row" style="margin-bottom: 10px; border: 1px solid #ccc; padding: 10px; background: #fafafa;">
					<input type="text" name="crc_badges[<?php echo $index; ?>][text]" value="<?php echo esc_attr( $badge['text'] ?? '' ); ?>" placeholder="Badge Text">
					<select name="crc_badges[<?php echo $index; ?>][style]">
						<option value="primary" <?php selected( $badge['style'] ?? '', 'primary' ); ?>>Primary</option>
						<option value="green" <?php selected( $badge['style'] ?? '', 'green' ); ?>>Green</option>
						<option value="red" <?php selected( $badge['style'] ?? '', 'red' ); ?>>Red</option>
						<option value="dark" <?php selected( $badge['style'] ?? '', 'dark' ); ?>>Dark</option>
					</select>
					<button type="button" class="button crc-remove-badge">Remove</button>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button crc-add-badge" style="margin-top: 10px;">Add Badge</button>
		<script type="text/template" id="tmpl-crc-badge">
			<div class="crc-badge-row" style="margin-bottom: 10px; border: 1px solid #ccc; padding: 10px; background: #fafafa;">
				<input type="text" name="crc_badges[{{index}}][text]" value="" placeholder="Badge Text">
				<select name="crc_badges[{{index}}][style]">
					<option value="primary">Primary</option>
					<option value="green">Green</option>
					<option value="red">Red</option>
					<option value="dark">Dark</option>
				</select>
				<button type="button" class="button crc-remove-badge">Remove</button>
			</div>
		</script>
		<?php
	}

	public function render_schedule( $post ) {
		$start_date = get_post_meta( $post->ID, 'crc_start_date', true );
		$end_date = get_post_meta( $post->ID, 'crc_end_date', true );
		$custom_date = get_post_meta( $post->ID, 'crc_custom_date', true );
		$schedule_text = get_post_meta( $post->ID, 'crc_schedule_text', true );
		$timezone = get_post_meta( $post->ID, 'crc_timezone', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="crc_custom_date">Custom Date Text</label></th>
				<td><input type="text" id="crc_custom_date" name="crc_custom_date" value="<?php echo esc_attr( $custom_date ); ?>" class="regular-text"><br>
				<small>Overrides Start/End date if provided. Example: "September 17, 2026"</small></td>
			</tr>
			<tr>
				<th><label for="crc_schedule_text">Schedule Text</label></th>
				<td><input type="text" id="crc_schedule_text" name="crc_schedule_text" value="<?php echo esc_attr( $schedule_text ); ?>" class="regular-text"><br>
				<small>Example: "Mon, Tue, Wed 10am - 1pm"</small></td>
			</tr>
			<tr>
				<th><label for="crc_timezone">Timezone</label></th>
				<td><input type="text" id="crc_timezone" name="crc_timezone" value="<?php echo esc_attr( $timezone ); ?>" class="small-text"><br>
				<small>Example: "CST"</small></td>
			</tr>
			<tr>
				<th><label for="crc_start_date">Start Date</label></th>
				<td><input type="date" id="crc_start_date" name="crc_start_date" value="<?php echo esc_attr( $start_date ); ?>"></td>
			</tr>
			<tr>
				<th><label for="crc_end_date">End Date</label></th>
				<td><input type="date" id="crc_end_date" name="crc_end_date" value="<?php echo esc_attr( $end_date ); ?>"></td>
			</tr>
		</table>
		<?php
	}

	public function render_location( $post ) {
		$location = get_post_meta( $post->ID, 'crc_location_name', true );
		$address = get_post_meta( $post->ID, 'crc_address', true );
		$maps_url = get_post_meta( $post->ID, 'crc_maps_url', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="crc_location_name">Location Name</label></th>
				<td><input type="text" id="crc_location_name" name="crc_location_name" value="<?php echo esc_attr( $location ); ?>" class="regular-text"><br>
				<small>Example: "Sheraton Hotel"</small></td>
			</tr>
			<tr>
				<th><label for="crc_address">Address (Optional)</label></th>
				<td><input type="text" id="crc_address" name="crc_address" value="<?php echo esc_attr( $address ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="crc_maps_url">Google Maps URL</label></th>
				<td><input type="url" id="crc_maps_url" name="crc_maps_url" value="<?php echo esc_url( $maps_url ); ?>" class="regular-text"></td>
			</tr>
		</table>
		<?php
	}

	public function render_features( $post ) {
		$features = get_post_meta( $post->ID, 'crc_features', true );
		if ( ! is_array( $features ) ) {
			$features = [];
		}
		?>
		<div id="crc-features-container">
			<?php foreach ( $features as $index => $feature ) : ?>
				<div class="crc-feature-row" style="margin-bottom: 10px; border: 1px solid #ccc; padding: 10px; background: #fafafa; display: flex; gap: 10px;">
					<input type="text" name="crc_features[<?php echo $index; ?>][label]" value="<?php echo esc_attr( $feature['label'] ?? '' ); ?>" placeholder="Label (e.g. Live Lectures)">
					<input type="text" name="crc_features[<?php echo $index; ?>][value]" value="<?php echo esc_attr( $feature['value'] ?? '' ); ?>" placeholder="Value (e.g. Mon, Tue 10am)" style="flex:1;">
					<button type="button" class="button crc-remove-feature">Remove</button>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button crc-add-feature" style="margin-top: 10px;">Add Feature Row</button>
		<script type="text/template" id="tmpl-crc-feature">
			<div class="crc-feature-row" style="margin-bottom: 10px; border: 1px solid #ccc; padding: 10px; background: #fafafa; display: flex; gap: 10px;">
				<input type="text" name="crc_features[{{index}}][label]" value="" placeholder="Label">
				<input type="text" name="crc_features[{{index}}][value]" value="" placeholder="Value" style="flex:1;">
				<button type="button" class="button crc-remove-feature">Remove</button>
			</div>
		</script>
		<?php
	}

	public function render_button( $post ) {
		$btn_text = get_post_meta( $post->ID, 'crc_btn_text', true );
		$btn_url = get_post_meta( $post->ID, 'crc_btn_url', true );
		$btn_target = get_post_meta( $post->ID, 'crc_btn_target', true );
		$btn_style = get_post_meta( $post->ID, 'crc_btn_style', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="crc_btn_text">Button Text</label></th>
				<td><input type="text" id="crc_btn_text" name="crc_btn_text" value="<?php echo esc_attr( $btn_text ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="crc_btn_url">Button URL</label></th>
				<td><input type="url" id="crc_btn_url" name="crc_btn_url" value="<?php echo esc_url( $btn_url ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="crc_btn_target">Target</label></th>
				<td>
					<select name="crc_btn_target" id="crc_btn_target">
						<option value="_self" <?php selected( $btn_target, '_self' ); ?>>Same Window</option>
						<option value="_blank" <?php selected( $btn_target, '_blank' ); ?>>New Window</option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="crc_btn_style">Style</label></th>
				<td>
					<select name="crc_btn_style" id="crc_btn_style">
						<option value="primary" <?php selected( $btn_style, 'primary' ); ?>>Primary</option>
						<option value="green" <?php selected( $btn_style, 'green' ); ?>>Green</option>
						<option value="dark" <?php selected( $btn_style, 'dark' ); ?>>Dark</option>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}

	public function render_card_settings( $post ) {
		$active = get_post_meta( $post->ID, 'crc_active', true );
		$featured = get_post_meta( $post->ID, 'crc_featured', true );
		if ( empty( $active ) ) $active = 'yes'; // Default to active for new
		?>
		<p>
			<label>
				<input type="checkbox" name="crc_active" value="yes" <?php checked( $active, 'yes' ); ?>>
				<strong>Active</strong> (Display on frontend)
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" name="crc_featured" value="yes" <?php checked( $featured, 'yes' ); ?>>
				<strong>Featured Card</strong> (Highlight with border)
			</label>
		</p>
		<?php
	}

	public function save_meta_boxes( $post_id ) {
		if ( ! isset( $_POST['crc_meta_nonce'] ) || ! wp_verify_nonce( $_POST['crc_meta_nonce'], 'crc_save_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = [
			'crc_short_desc' => 'sanitize_textarea_field',
			'crc_image_id' => 'intval',
			'crc_custom_date' => 'sanitize_text_field',
			'crc_schedule_text' => 'sanitize_text_field',
			'crc_timezone' => 'sanitize_text_field',
			'crc_start_date' => 'sanitize_text_field',
			'crc_end_date' => 'sanitize_text_field',
			'crc_location_name' => 'sanitize_text_field',
			'crc_address' => 'sanitize_text_field',
			'crc_maps_url' => 'esc_url_raw',
			'crc_btn_text' => 'sanitize_text_field',
			'crc_btn_url' => 'esc_url_raw',
			'crc_btn_target' => 'sanitize_text_field',
			'crc_btn_style' => 'sanitize_text_field',
		];

		foreach ( $fields as $field => $sanitizer ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, $field, $sanitizer( $_POST[ $field ] ) );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}

		// Checkboxes
		update_post_meta( $post_id, 'crc_active', isset( $_POST['crc_active'] ) ? 'yes' : 'no' );
		update_post_meta( $post_id, 'crc_featured', isset( $_POST['crc_featured'] ) ? 'yes' : 'no' );

		// Repeaters
		if ( isset( $_POST['crc_badges'] ) && is_array( $_POST['crc_badges'] ) ) {
			$badges = [];
			foreach ( $_POST['crc_badges'] as $badge ) {
				if ( ! empty( $badge['text'] ) ) {
					$badges[] = [
						'text' => sanitize_text_field( $badge['text'] ),
						'style' => sanitize_text_field( $badge['style'] ),
					];
				}
			}
			update_post_meta( $post_id, 'crc_badges', $badges );
		} else {
			delete_post_meta( $post_id, 'crc_badges' );
		}

		if ( isset( $_POST['crc_features'] ) && is_array( $_POST['crc_features'] ) ) {
			$features = [];
			foreach ( $_POST['crc_features'] as $feature ) {
				if ( ! empty( $feature['label'] ) || ! empty( $feature['value'] ) ) {
					$features[] = [
						'label' => sanitize_text_field( $feature['label'] ),
						'value' => sanitize_text_field( $feature['value'] ),
					];
				}
			}
			update_post_meta( $post_id, 'crc_features', $features );
		} else {
			delete_post_meta( $post_id, 'crc_features' );
		}
	}
}
