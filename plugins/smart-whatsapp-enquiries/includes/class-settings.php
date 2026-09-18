<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SWE_Settings {
	private $options;

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_plugin_page' ] );
		add_action( 'admin_init', [ $this, 'page_init' ] );
		add_action( 'admin_notices', [ $this, 'display_admin_notices' ] );
	}

	public function add_plugin_page() {
		add_submenu_page(
			'edit.php?post_type=swe_subject',
			'Settings',
			'Settings',
			'manage_options',
			'swe-settings',
			[ $this, 'create_admin_page' ]
		);
	}

	public function display_admin_notices() {
		$options = get_option( 'swe_settings' );
		if ( empty( $options['whatsapp_number'] ) ) {
			echo '<div class="notice notice-error"><p><strong>Smart WhatsApp Enquiries:</strong> Your global WhatsApp number has not been configured. <a href="' . esc_url( admin_url( 'edit.php?post_type=swe_subject&page=swe-settings' ) ) . '">Configure it now &rarr;</a></p></div>';
		}
	}

	public function create_admin_page() {
		$this->options = get_option( 'swe_settings', $this->get_default_settings() );
		?>
		<div class="wrap">
			<h1>Smart WhatsApp Enquiries Settings</h1>
			<form method="post" action="options.php">
			<?php
				settings_fields( 'swe_option_group' );
				do_settings_sections( 'swe-setting-admin' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	public function get_default_settings() {
		return [
			'enable_widget'   => 'yes',
			'whatsapp_number' => '',
			'business_name'   => get_bloginfo( 'name' ),
			'allow_editing'   => 'yes',
			'position'        => 'right',
			'toast_enable'    => 'yes',
			'toast_text'      => 'Talk to us',
			'toast_desc'      => 'Have a question? We\'re here to help.',
			'popup_heading'   => 'How can we help?',
			'popup_subtitle'  => 'Select an option below and we\'ll connect you on WhatsApp.',
			'color_primary'   => '#25D366',
			'color_bg'        => '#ffffff',
			'color_text'      => '#333333',
			'animations'      => 'yes',
			'page_context'    => 'no'
		];
	}

	public function page_init() {
		register_setting( 'swe_option_group', 'swe_settings', [ $this, 'sanitize' ] );

		add_settings_section(
			'swe_general_section',
			'General Settings',
			null,
			'swe-setting-admin'
		);

		add_settings_field( 'enable_widget', 'Enable Widget', [ $this, 'checkbox_callback' ], 'swe-setting-admin', 'swe_general_section', [ 'id' => 'enable_widget' ] );
		add_settings_field( 'whatsapp_number', 'Global WhatsApp Number', [ $this, 'text_callback' ], 'swe-setting-admin', 'swe_general_section', [ 'id' => 'whatsapp_number', 'desc' => 'Include country code (e.g. +14155552671).' ] );
		add_settings_field( 'business_name', 'Business Name', [ $this, 'text_callback' ], 'swe-setting-admin', 'swe_general_section', [ 'id' => 'business_name', 'desc' => 'Used for the {business_name} variable.' ] );
		add_settings_field( 'allow_editing', 'Allow Message Editing', [ $this, 'checkbox_callback' ], 'swe-setting-admin', 'swe_general_section', [ 'id' => 'allow_editing', 'desc' => 'Allow visitors to edit the message before sending.' ] );
		add_settings_field( 'page_context', 'Include Page Context', [ $this, 'checkbox_callback' ], 'swe-setting-admin', 'swe_general_section', [ 'id' => 'page_context', 'desc' => 'Append the current page URL to the WhatsApp message automatically.' ] );

		add_settings_section(
			'swe_appearance_section',
			'Appearance & Texts',
			null,
			'swe-setting-admin'
		);

		add_settings_field( 'position', 'Widget Position', [ $this, 'select_callback' ], 'swe-setting-admin', 'swe_appearance_section', [ 'id' => 'position', 'options' => [ 'right' => 'Bottom Right', 'left' => 'Bottom Left' ] ] );
		add_settings_field( 'animations', 'Enable Animations', [ $this, 'checkbox_callback' ], 'swe-setting-admin', 'swe_appearance_section', [ 'id' => 'animations' ] );
		
		add_settings_field( 'toast_enable', 'Enable Toast', [ $this, 'checkbox_callback' ], 'swe-setting-admin', 'swe_appearance_section', [ 'id' => 'toast_enable' ] );
		add_settings_field( 'toast_text', 'Toast Text', [ $this, 'text_callback' ], 'swe-setting-admin', 'swe_appearance_section', [ 'id' => 'toast_text' ] );
		add_settings_field( 'toast_desc', 'Toast Description', [ $this, 'text_callback' ], 'swe-setting-admin', 'swe_appearance_section', [ 'id' => 'toast_desc' ] );
		
		add_settings_field( 'popup_heading', 'Popup Heading', [ $this, 'text_callback' ], 'swe-setting-admin', 'swe_appearance_section', [ 'id' => 'popup_heading' ] );
		add_settings_field( 'popup_subtitle', 'Popup Subtitle', [ $this, 'text_callback' ], 'swe-setting-admin', 'swe_appearance_section', [ 'id' => 'popup_subtitle' ] );

		add_settings_field( 'color_primary', 'Primary Color (Button)', [ $this, 'color_callback' ], 'swe-setting-admin', 'swe_appearance_section', [ 'id' => 'color_primary' ] );
		add_settings_field( 'color_bg', 'Popup Background Color', [ $this, 'color_callback' ], 'swe-setting-admin', 'swe_appearance_section', [ 'id' => 'color_bg' ] );
		add_settings_field( 'color_text', 'Text Color', [ $this, 'color_callback' ], 'swe-setting-admin', 'swe_appearance_section', [ 'id' => 'color_text' ] );
	}

	public function sanitize( $input ) {
		$sanitized = [];
		foreach ( $input as $key => $value ) {
			$sanitized[ $key ] = sanitize_text_field( $value );
		}
		
		if ( isset( $sanitized['whatsapp_number'] ) ) {
			$sanitized['whatsapp_number'] = preg_replace( '/[^0-9\+]/', '', $sanitized['whatsapp_number'] );
		}

		return $sanitized;
	}

	public function text_callback( $args ) {
		$val = isset( $this->options[ $args['id'] ] ) ? $this->options[ $args['id'] ] : '';
		printf(
			'<input type="text" id="%s" name="swe_settings[%s]" value="%s" style="width: 300px;" />',
			esc_attr( $args['id'] ),
			esc_attr( $args['id'] ),
			esc_attr( $val )
		);
		if ( isset( $args['desc'] ) ) {
			echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
		}
	}

	public function checkbox_callback( $args ) {
		$val = isset( $this->options[ $args['id'] ] ) ? $this->options[ $args['id'] ] : 'no';
		printf(
			'<input type="checkbox" id="%s" name="swe_settings[%s]" value="yes" %s />',
			esc_attr( $args['id'] ),
			esc_attr( $args['id'] ),
			checked( $val, 'yes', false )
		);
		if ( isset( $args['desc'] ) ) {
			echo '<span class="description">' . esc_html( $args['desc'] ) . '</span>';
		}
	}

	public function select_callback( $args ) {
		$val = isset( $this->options[ $args['id'] ] ) ? $this->options[ $args['id'] ] : '';
		echo '<select id="' . esc_attr( $args['id'] ) . '" name="swe_settings[' . esc_attr( $args['id'] ) . ']">';
		foreach ( $args['options'] as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $val, $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	}

	public function color_callback( $args ) {
		$val = isset( $this->options[ $args['id'] ] ) ? $this->options[ $args['id'] ] : '';
		printf(
			'<input type="color" id="%s" name="swe_settings[%s]" value="%s" />',
			esc_attr( $args['id'] ),
			esc_attr( $args['id'] ),
			esc_attr( $val )
		);
	}
}
