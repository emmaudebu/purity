<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CRC_Settings {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_settings_page() {
		add_submenu_page(
			'edit.php?post_type=class_registration',
			'Settings',
			'Settings',
			'manage_options',
			'crc_settings',
			[ $this, 'settings_page_html' ]
		);
	}

	public function register_settings() {
		register_setting( 'crc_settings_group', 'crc_settings' );

		// Section Settings
		add_settings_section( 'crc_section_general', 'Section Settings', null, 'crc_settings' );
		add_settings_field( 'section_heading', 'Section Heading', [ $this, 'render_text_field' ], 'crc_settings', 'crc_section_general', [ 'key' => 'section_heading', 'default' => 'Ongoing Class Registration' ] );
		add_settings_field( 'section_desc', 'Section Description', [ $this, 'render_textarea_field' ], 'crc_settings', 'crc_section_general', [ 'key' => 'section_desc', 'default' => 'Secure your spot in our upcoming cohorts. High-yield curriculum led by Dr. Adekemi Akinlawon.' ] );
		add_settings_field( 'heading_size', 'Heading Font Size (px or em)', [ $this, 'render_text_field' ], 'crc_settings', 'crc_section_general', [ 'key' => 'heading_size', 'default' => '32px' ] );
		add_settings_field( 'desc_size', 'Description Font Size (px or em)', [ $this, 'render_text_field' ], 'crc_settings', 'crc_section_general', [ 'key' => 'desc_size', 'default' => '16px' ] );
		add_settings_field( 'text_align', 'Text Alignment', [ $this, 'render_select_field' ], 'crc_settings', 'crc_section_general', [ 'key' => 'text_align', 'options' => [ 'left' => 'Left', 'center' => 'Center', 'right' => 'Right' ], 'default' => 'center' ] );
		add_settings_field( 'empty_state_text', 'Empty State Text', [ $this, 'render_text_field' ], 'crc_settings', 'crc_section_general', [ 'key' => 'empty_state_text', 'default' => 'No class registrations are currently available.' ] );

		// Card Settings
		add_settings_section( 'crc_section_card', 'Card Settings', null, 'crc_settings' );
		add_settings_field( 'default_columns', 'Default Columns', [ $this, 'render_select_field' ], 'crc_settings', 'crc_section_card', [ 'key' => 'default_columns', 'options' => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4' ], 'default' => '3' ] );
		add_settings_field( 'card_radius', 'Card Border Radius (px)', [ $this, 'render_text_field' ], 'crc_settings', 'crc_section_card', [ 'key' => 'card_radius', 'default' => '8px' ] );
		add_settings_field( 'card_spacing', 'Card Spacing (Gap)', [ $this, 'render_text_field' ], 'crc_settings', 'crc_section_card', [ 'key' => 'card_spacing', 'default' => '20px' ] );
		add_settings_field( 'image_height', 'Image Height (px)', [ $this, 'render_text_field' ], 'crc_settings', 'crc_section_card', [ 'key' => 'image_height', 'default' => '200px' ] );

		// Colors
		add_settings_section( 'crc_section_colors', 'Colors', null, 'crc_settings' );
		add_settings_field( 'primary_color', 'Primary Color', [ $this, 'render_color_field' ], 'crc_settings', 'crc_section_colors', [ 'key' => 'primary_color', 'default' => '#001A72' ] );
		add_settings_field( 'secondary_color', 'Secondary Color', [ $this, 'render_color_field' ], 'crc_settings', 'crc_section_colors', [ 'key' => 'secondary_color', 'default' => '#149F41' ] );
		add_settings_field( 'heading_color', 'Heading Color', [ $this, 'render_color_field' ], 'crc_settings', 'crc_section_colors', [ 'key' => 'heading_color', 'default' => '#001A72' ] );
		add_settings_field( 'text_color', 'Text Color', [ $this, 'render_color_field' ], 'crc_settings', 'crc_section_colors', [ 'key' => 'text_color', 'default' => '#444444' ] );
		add_settings_field( 'card_bg', 'Card Background', [ $this, 'render_color_field' ], 'crc_settings', 'crc_section_colors', [ 'key' => 'card_bg', 'default' => '#FFFFFF' ] );
		add_settings_field( 'card_border', 'Card Border', [ $this, 'render_color_field' ], 'crc_settings', 'crc_section_colors', [ 'key' => 'card_border', 'default' => '#E5E5E5' ] );
		add_settings_field( 'feature_bg', 'Feature Box Background', [ $this, 'render_color_field' ], 'crc_settings', 'crc_section_colors', [ 'key' => 'feature_bg', 'default' => '#F4F7FB' ] );
	}

	public function render_text_field( $args ) {
		$options = get_option( 'crc_settings' );
		$val = isset( $options[ $args['key'] ] ) ? $options[ $args['key'] ] : $args['default'];
		echo '<input type="text" name="crc_settings[' . esc_attr( $args['key'] ) . ']" value="' . esc_attr( $val ) . '" class="regular-text">';
	}

	public function render_color_field( $args ) {
		$options = get_option( 'crc_settings' );
		$val = isset( $options[ $args['key'] ] ) ? $options[ $args['key'] ] : $args['default'];
		echo '<input type="text" name="crc_settings[' . esc_attr( $args['key'] ) . ']" value="' . esc_attr( $val ) . '" class="crc-color-picker">';
	}

	public function render_textarea_field( $args ) {
		$options = get_option( 'crc_settings' );
		$val = isset( $options[ $args['key'] ] ) ? $options[ $args['key'] ] : $args['default'];
		echo '<textarea name="crc_settings[' . esc_attr( $args['key'] ) . ']" rows="3" class="large-text">' . esc_textarea( $val ) . '</textarea>';
	}

	public function render_select_field( $args ) {
		$options = get_option( 'crc_settings' );
		$val = isset( $options[ $args['key'] ] ) ? $options[ $args['key'] ] : $args['default'];
		echo '<select name="crc_settings[' . esc_attr( $args['key'] ) . ']">';
		foreach ( $args['options'] as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select>';
	}

	public function settings_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1>Class Registration Cards Settings</h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'crc_settings_group' );
				do_settings_sections( 'crc_settings' );
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}
}
