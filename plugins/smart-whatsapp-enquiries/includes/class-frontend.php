<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SWE_Frontend {
	private $options;

	public function __construct() {
		$this->options = get_option( 'swe_settings', [] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_shortcode( 'smart_whatsapp', [ $this, 'render_shortcode' ] );
		
		if ( isset( $this->options['enable_widget'] ) && $this->options['enable_widget'] === 'yes' ) {
			add_action( 'wp_footer', [ $this, 'render_global_widget' ] );
		}
	}

	public function enqueue_assets() {
		// Only enqueue if widget is enabled globally or if shortcode is used (shortcode will enqueue it too, but this handles global)
		wp_enqueue_style( 'swe-frontend', SWE_PLUGIN_URL . 'assets/css/frontend.css', [], SWE_VERSION );
		wp_enqueue_script( 'swe-frontend', SWE_PLUGIN_URL . 'assets/js/frontend.js', [], SWE_VERSION, true );
		
		// Pass dynamic variables to JS
		global $wp;
		$current_url = home_url( add_query_arg( [], $wp->request ) );
		
		wp_localize_script( 'swe-frontend', 'sweData', [
			'business_name'  => isset( $this->options['business_name'] ) ? $this->options['business_name'] : get_bloginfo( 'name' ),
			'site_name'      => get_bloginfo( 'name' ),
			'page_title'     => is_singular() ? get_the_title() : wp_title( '', false ),
			'page_url'       => $current_url,
			'global_number'  => isset( $this->options['whatsapp_number'] ) ? $this->options['whatsapp_number'] : '',
			'allow_editing'  => isset( $this->options['allow_editing'] ) && $this->options['allow_editing'] === 'yes',
			'page_context'   => isset( $this->options['page_context'] ) && $this->options['page_context'] === 'yes'
		] );

		// Inline CSS for dynamic colors and positions
		$custom_css = "
			:root {
				--swe-primary: " . ( isset($this->options['color_primary']) ? $this->options['color_primary'] : '#25D366' ) . ";
				--swe-bg: " . ( isset($this->options['color_bg']) ? $this->options['color_bg'] : '#ffffff' ) . ";
				--swe-text: " . ( isset($this->options['color_text']) ? $this->options['color_text'] : '#333333' ) . ";
			}
		";
		
		if ( isset( $this->options['position'] ) && $this->options['position'] === 'left' ) {
			$custom_css .= "
				.swe-widget-container { left: 24px; right: auto; }
				.swe-toast { right: auto; left: 70px; }
				.swe-modal { left: 24px; right: auto; transform-origin: bottom left; }
			";
		} else {
			$custom_css .= "
				.swe-widget-container { right: 24px; left: auto; }
				.swe-toast { right: 70px; left: auto; }
				.swe-modal { right: 24px; left: auto; transform-origin: bottom right; }
			";
		}

		if ( isset( $this->options['animations'] ) && $this->options['animations'] !== 'yes' ) {
			$custom_css .= "
				.swe-widget-container, .swe-toast, .swe-modal, .swe-subject-card, .swe-btn-floating {
					transition: none !important;
					animation: none !important;
				}
			";
		}

		wp_add_inline_style( 'swe-frontend', $custom_css );
	}

	public function render_shortcode( $atts ) {
		// Enqueue explicitly if shortcode is used and global widget might be disabled
		$this->enqueue_assets();
		return $this->get_widget_html();
	}

	public function render_global_widget() {
		echo $this->get_widget_html();
	}

	private function get_widget_html() {
		// Fetch active subjects
		$subjects = get_posts( [
			'post_type'      => 'swe_subject',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'meta_query'     => [
				'relation' => 'OR',
				[
					'key'   => 'swe_status',
					'value' => 'active'
				],
				[
					'key'     => 'swe_status',
					'compare' => 'NOT EXISTS' // Backward compatibility if meta is missing
				]
			]
		] );

		if ( empty( $subjects ) || empty( $this->options['whatsapp_number'] ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="swe-wrapper">
			<!-- Floating Button -->
			<div class="swe-widget-container">
				<?php if ( isset( $this->options['toast_enable'] ) && $this->options['toast_enable'] === 'yes' ) : ?>
					<div class="swe-toast">
						<div class="swe-toast-close">&times;</div>
						<div class="swe-toast-title">👋 <?php echo esc_html( isset($this->options['toast_text']) ? $this->options['toast_text'] : 'Talk to us' ); ?></div>
						<?php if ( ! empty( $this->options['toast_desc'] ) ) : ?>
							<div class="swe-toast-desc"><?php echo esc_html( $this->options['toast_desc'] ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				
				<button class="swe-btn-floating" aria-label="Open WhatsApp Chat">
					<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.82 9.82 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
				</button>
			</div>

			<!-- Main Modal Interface -->
			<div class="swe-modal">
				
				<!-- View 1: Subject Selection -->
				<div class="swe-view swe-view-subjects active">
					<div class="swe-modal-header">
						<div class="swe-modal-title"><?php echo esc_html( isset($this->options['popup_heading']) ? $this->options['popup_heading'] : 'How can we help?' ); ?></div>
						<button class="swe-modal-close" aria-label="Close">&times;</button>
					</div>
					<div class="swe-modal-subtitle"><?php echo esc_html( isset($this->options['popup_subtitle']) ? $this->options['popup_subtitle'] : 'Select an option below and we\'ll connect you on WhatsApp.' ); ?></div>
					
					<div class="swe-subjects-list">
						<?php foreach ( $subjects as $subject ) : 
							$icon = get_post_meta( $subject->ID, 'swe_icon', true );
							$desc = get_post_meta( $subject->ID, 'swe_desc', true );
							$msg  = get_post_meta( $subject->ID, 'swe_msg', true );
							$num  = get_post_meta( $subject->ID, 'swe_number', true );
						?>
							<div class="swe-subject-card" data-msg="<?php echo esc_attr($msg); ?>" data-num="<?php echo esc_attr($num); ?>" data-title="<?php echo esc_attr($subject->post_title); ?>">
								<div class="swe-subject-icon"><?php echo esc_html( $icon ); ?></div>
								<div class="swe-subject-content">
									<div class="swe-subject-title"><?php echo esc_html( $subject->post_title ); ?></div>
									<?php if ( ! empty( $desc ) ) : ?>
										<div class="swe-subject-desc"><?php echo esc_html( $desc ); ?></div>
									<?php endif; ?>
								</div>
								<div class="swe-subject-arrow">&rsaquo;</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- View 2: Message Preview -->
				<div class="swe-view swe-view-message">
					<div class="swe-modal-header">
						<button class="swe-modal-back" aria-label="Back">&lsaquo; Back</button>
						<div class="swe-modal-title" id="swe-preview-title">Subject</div>
						<button class="swe-modal-close" aria-label="Close">&times;</button>
					</div>
					<div class="swe-modal-subtitle">We'll send this message:</div>
					
					<div class="swe-message-preview-container">
						<?php if ( isset( $this->options['allow_editing'] ) && $this->options['allow_editing'] === 'yes' ) : ?>
							<textarea id="swe-message-textarea" rows="6"></textarea>
						<?php else : ?>
							<div id="swe-message-static"></div>
						<?php endif; ?>
					</div>

					<button class="swe-btn-primary" id="swe-btn-continue">
						Continue to WhatsApp
						<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
					</button>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
