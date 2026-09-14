<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST_Testimonials_Settings {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_settings_page() {
		add_submenu_page(
			'edit.php?post_type=student_testimonial',
			'Settings',
			'Settings',
			'manage_options',
			'st_testimonials_settings',
			[ $this, 'settings_page_html' ]
		);
	}

	public function register_settings() {
		register_setting( 'st_testimonials_settings_group', 'st_testimonials_settings' );

		// 1. Layout Settings
		add_settings_section( 'st_section_layout', 'Layout', null, 'st_testimonials_settings' );
		add_settings_field( 'columns', 'Desktop Columns', [ $this, 'render_select' ], 'st_testimonials_settings', 'st_section_layout', [ 'key' => 'columns', 'options' => [ 1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6 ], 'default' => 4 ] );
		add_settings_field( 'tablet_columns', 'Tablet Columns', [ $this, 'render_select' ], 'st_testimonials_settings', 'st_section_layout', [ 'key' => 'tablet_columns', 'options' => [ 1=>1, 2=>2, 3=>3, 4=>4 ], 'default' => 2 ] );
		add_settings_field( 'mobile_columns', 'Mobile Columns', [ $this, 'render_select' ], 'st_testimonials_settings', 'st_section_layout', [ 'key' => 'mobile_columns', 'options' => [ 1=>1, 2=>2 ], 'default' => 1 ] );
		add_settings_field( 'limit', 'Number of Testimonials', [ $this, 'render_text' ], 'st_testimonials_settings', 'st_section_layout', [ 'key' => 'limit', 'default' => '8', 'desc' => 'Enter -1 for all' ] );
		add_settings_field( 'card_gap', 'Card Gap (px)', [ $this, 'render_text' ], 'st_testimonials_settings', 'st_section_layout', [ 'key' => 'card_gap', 'default' => '20px' ] );

		// 2. Image Settings
		add_settings_section( 'st_section_image', 'Image', null, 'st_testimonials_settings' );
		add_settings_field( 'image_height', 'Image Height (px)', [ $this, 'render_text' ], 'st_testimonials_settings', 'st_section_image', [ 'key' => 'image_height', 'default' => '250px' ] );

		// 3. Content Settings
		add_settings_section( 'st_section_content', 'Content', null, 'st_testimonials_settings' );
		add_settings_field( 'show_content', 'Show Testimonial Content', [ $this, 'render_checkbox' ], 'st_testimonials_settings', 'st_section_content', [ 'key' => 'show_content', 'default' => 'yes' ] );
		add_settings_field( 'excerpt_length', 'Maximum Excerpt Length (words)', [ $this, 'render_text' ], 'st_testimonials_settings', 'st_section_content', [ 'key' => 'excerpt_length', 'default' => '30' ] );
		add_settings_field( 'show_class', 'Show Class/Course', [ $this, 'render_checkbox' ], 'st_testimonials_settings', 'st_section_content', [ 'key' => 'show_class', 'default' => 'yes' ] );
		add_settings_field( 'show_date', 'Show Date', [ $this, 'render_checkbox' ], 'st_testimonials_settings', 'st_section_content', [ 'key' => 'show_date', 'default' => 'yes' ] );
		add_settings_field( 'show_rating', 'Show Star Rating', [ $this, 'render_checkbox' ], 'st_testimonials_settings', 'st_section_content', [ 'key' => 'show_rating', 'default' => 'yes' ] );

		// 4. Button Settings
		add_settings_section( 'st_section_button', 'View More Button', null, 'st_testimonials_settings' );
		add_settings_field( 'show_button', 'Enable View More Button', [ $this, 'render_checkbox' ], 'st_testimonials_settings', 'st_section_button', [ 'key' => 'show_button', 'default' => 'no' ] );
		add_settings_field( 'button_text', 'Button Text', [ $this, 'render_text' ], 'st_testimonials_settings', 'st_section_button', [ 'key' => 'button_text', 'default' => 'View All Testimonials' ] );
		add_settings_field( 'button_url', 'Button URL', [ $this, 'render_text' ], 'st_testimonials_settings', 'st_section_button', [ 'key' => 'button_url', 'default' => '/testimonials/' ] );
		add_settings_field( 'button_target', 'Open in new tab', [ $this, 'render_checkbox' ], 'st_testimonials_settings', 'st_section_button', [ 'key' => 'button_target', 'default' => 'no' ] );
		add_settings_field( 'button_alignment', 'Button Alignment', [ $this, 'render_select' ], 'st_testimonials_settings', 'st_section_button', [ 'key' => 'button_alignment', 'options' => [ 'left' => 'Left', 'center' => 'Center', 'right' => 'Right' ], 'default' => 'center' ] );

		// 5. Styling Settings
		add_settings_section( 'st_section_style', 'Styling', null, 'st_testimonials_settings' );
		add_settings_field( 'card_bg', 'Card Background Color', [ $this, 'render_color' ], 'st_testimonials_settings', 'st_section_style', [ 'key' => 'card_bg', 'default' => '#FFFFFF' ] );
		add_settings_field( 'card_radius', 'Card Border Radius', [ $this, 'render_text' ], 'st_testimonials_settings', 'st_section_style', [ 'key' => 'card_radius', 'default' => '12px' ] );
		add_settings_field( 'star_color', 'Star Color', [ $this, 'render_color' ], 'st_testimonials_settings', 'st_section_style', [ 'key' => 'star_color', 'default' => '#FFC400' ] );
		add_settings_field( 'title_color', 'Title Color', [ $this, 'render_color' ], 'st_testimonials_settings', 'st_section_style', [ 'key' => 'title_color', 'default' => '#071B91' ] );
		add_settings_field( 'date_color', 'Date Color', [ $this, 'render_color' ], 'st_testimonials_settings', 'st_section_style', [ 'key' => 'date_color', 'default' => '#666666' ] );
		add_settings_field( 'button_bg', 'Button Background', [ $this, 'render_color' ], 'st_testimonials_settings', 'st_section_style', [ 'key' => 'button_bg', 'default' => '#071B91' ] );
		add_settings_field( 'button_text_color', 'Button Text Color', [ $this, 'render_color' ], 'st_testimonials_settings', 'st_section_style', [ 'key' => 'button_text_color', 'default' => '#FFFFFF' ] );
	}

	public function render_text( $args ) {
		$options = get_option( 'st_testimonials_settings' );
		$val = isset( $options[ $args['key'] ] ) ? $options[ $args['key'] ] : $args['default'];
		echo '<input type="text" name="st_testimonials_settings[' . esc_attr( $args['key'] ) . ']" value="' . esc_attr( $val ) . '" class="regular-text">';
		if ( isset( $args['desc'] ) ) {
			echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
		}
	}

	public function render_color( $args ) {
		$options = get_option( 'st_testimonials_settings' );
		$val = isset( $options[ $args['key'] ] ) ? $options[ $args['key'] ] : $args['default'];
		// Using type="color" for simple native color picker, or can enqueue wp-color-picker later
		echo '<input type="color" name="st_testimonials_settings[' . esc_attr( $args['key'] ) . ']" value="' . esc_attr( $val ) . '">';
	}

	public function render_checkbox( $args ) {
		$options = get_option( 'st_testimonials_settings' );
		$val = isset( $options[ $args['key'] ] ) ? $options[ $args['key'] ] : $args['default'];
		$checked = ( $val === 'yes' ) ? 'checked' : '';
		echo '<input type="checkbox" name="st_testimonials_settings[' . esc_attr( $args['key'] ) . ']" value="yes" ' . $checked . '>';
	}

	public function render_select( $args ) {
		$options = get_option( 'st_testimonials_settings' );
		$val = isset( $options[ $args['key'] ] ) ? $options[ $args['key'] ] : $args['default'];
		echo '<select name="st_testimonials_settings[' . esc_attr( $args['key'] ) . ']">';
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
			<h1>Student Testimonials Settings</h1>
			<div class="notice notice-info inline" style="margin-bottom: 20px;">
				<p><strong>How to display:</strong> Copy and paste the shortcode <code>[student_testimonials]</code> into any post, page, or Elementor widget.</p>
			</div>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'st_testimonials_settings_group' );
				do_settings_sections( 'st_testimonials_settings' );
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}
}
