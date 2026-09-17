<?php
/**
 * Plugin Name: Site Reviews Integration
 * Plugin URI:  https://example.com
 * Description: Adds a custom post type for reviews, a submission form [submit_review_form], and a display carousel [display_site_reviews].
 * Version:     1.0.0
 * Author:      FurnitureNG AI
 * License:     GPLv2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// 1. Register Custom Post Type for Reviews
function custom_site_reviews_cpt() {
    $labels = array(
        'name'               => 'Site Reviews',
        'singular_name'      => 'Site Review',
        'menu_name'          => 'Site Reviews',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Review',
        'edit_item'          => 'Edit Review',
        'new_item'           => 'New Review',
        'view_item'          => 'View Review',
        'search_items'       => 'Search Reviews',
        'not_found'          => 'No reviews found',
    );
    $args = array(
        'labels'              => $labels,
        'public'              => false, 
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 20,
        'menu_icon'           => 'dashicons-star-filled',
        'supports'            => array('title', 'editor'),
        'has_archive'         => false,
    );
    register_post_type('site_review', $args);
}
add_action('init', 'custom_site_reviews_cpt');

// Display shortcodes in the admin area
function custom_site_reviews_admin_notice() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'edit-site_review') {
        ?>
        <div class="notice notice-info">
            <p><strong>Site Reviews Shortcodes:</strong></p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li>To display the review submission form, use: <code>[submit_review_form]</code></li>
                <li>To display the published reviews in a sliding carousel (default), use: <code>[display_site_reviews]</code></li>
                <li>To display the reviews in a grid (disabling the carousel) with specific columns and rows, use: <code>[display_site_reviews carousel="no" columns="3" rows="2"]</code></li>
            </ul>
        </div>
        <?php
    }
}
add_action('admin_notices', 'custom_site_reviews_admin_notice');

// 2. Add Meta Box in WP Admin for Star Rating
function custom_site_reviews_meta_box() {
    add_meta_box('review_rating_meta_box', 'Review Rating', 'custom_site_reviews_meta_box_html', 'site_review', 'side', 'default');
}
add_action('add_meta_boxes', 'custom_site_reviews_meta_box');

function custom_site_reviews_meta_box_html($post) {
    $rating = get_post_meta($post->ID, '_review_rating', true) ?: '5';
    ?>
    <label for="review_rating">Rating (1-5):</label>
    <select name="review_rating" id="review_rating" style="width:100%;">
        <option value="5" <?php selected($rating, '5'); ?>>5 Stars</option>
        <option value="4" <?php selected($rating, '4'); ?>>4 Stars</option>
        <option value="3" <?php selected($rating, '3'); ?>>3 Stars</option>
        <option value="2" <?php selected($rating, '2'); ?>>2 Stars</option>
        <option value="1" <?php selected($rating, '1'); ?>>1 Star</option>
    </select>
    <?php
}

function custom_site_reviews_save_meta($post_id) {
    if (array_key_exists('review_rating', $_POST)) {
        update_post_meta($post_id, '_review_rating', sanitize_text_field($_POST['review_rating']));
    }
}
add_action('save_post', 'custom_site_reviews_save_meta');

// 3. Process the Submission Form
global $csr_submission_success;
$csr_submission_success = false;

function custom_site_reviews_handle_form() {
    global $csr_submission_success;
    if (isset($_POST['submit_custom_review']) && isset($_POST['custom_review_nonce']) && wp_verify_nonce($_POST['custom_review_nonce'], 'submit_custom_review_action')) {
        $name = sanitize_text_field($_POST['review_name']);
        $rating = intval($_POST['review_rating']);
        $content = sanitize_textarea_field($_POST['review_content']);

        if (!empty($name) && !empty($content)) {
            $post_id = wp_insert_post(array(
                'post_title'   => $name,
                'post_content' => $content,
                'post_status'  => 'pending', 
                'post_type'    => 'site_review',
            ));
            if ($post_id) {
                update_post_meta($post_id, '_review_rating', $rating);
                $csr_submission_success = true;
            }
        }
    }
}
add_action('wp_loaded', 'custom_site_reviews_handle_form');

// 4. Shortcode: The Submission Form [submit_review_form]
function custom_site_reviews_form_shortcode() {
    global $csr_submission_success;
    ob_start();
    ?>
    <style>
        .csr-form-container { background: #ffffff; padding: 32px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); max-width: 500px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; border-top: 5px solid #071B91; }
        .csr-form-container h3 { margin-top: 0; color: #071B91; font-size: 24px; margin-bottom: 8px; font-weight: 700; }
        .csr-form-container p.csr-desc { color: #666; font-size: 15px; margin-bottom: 24px; line-height: 1.5; }
        .csr-form { width: 100%; box-sizing: border-box; }
        .csr-form label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px; }
        .csr-form input[type="text"], .csr-form textarea, .csr-form select { width: 100%; box-sizing: border-box; padding: 14px; margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 15px; transition: all 0.2s ease; background: #f8fafc; color: #1e293b; }
        .csr-form input[type="text"]:focus, .csr-form textarea:focus, .csr-form select:focus { border-color: #071B91; outline: none; background: #ffffff; box-shadow: 0 0 0 3px rgba(7, 27, 145, 0.15); }
        .csr-form button { background: #071B91; color: white; border: none; padding: 16px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 16px; width: 100%; transition: background 0.2s ease; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .csr-form button:hover { background: #05105c; transform: translateY(-1px); }
        .csr-form button:active { transform: translateY(0); }
        
        /* Star Rating Styles */
        .csr-rating-stars { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 4px; margin-bottom: 20px; position: relative; flex-wrap: nowrap; }
        .csr-rating-stars input[type="radio"] { position: absolute; opacity: 0; width: 1px; height: 1px; left: 0; top: 15px; }
        .csr-rating-stars label.csr-star-label { font-size: 32px; color: #cbd5e1; cursor: pointer; transition: color 0.2s ease; margin: 0 !important; line-height: 1; display: inline-block !important; width: auto !important; flex: 0 0 auto !important; }
        .csr-rating-stars label.csr-star-label:hover,
        .csr-rating-stars label.csr-star-label:hover ~ label.csr-star-label,
        .csr-rating-stars input[type="radio"]:checked ~ label.csr-star-label { color: #FFC400; }
        
        /* Modal Styles */
        .csr-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 999999; opacity: 0; visibility: hidden; transition: all 0.3s ease; backdrop-filter: blur(4px); }
        .csr-modal-overlay.active { opacity: 1; visibility: visible; }
        .csr-modal-content { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); max-width: 400px; width: 90%; text-align: center; transform: translateY(30px) scale(0.95); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .csr-modal-overlay.active .csr-modal-content { transform: translateY(0) scale(1); }
        .csr-modal-icon { width: 72px; height: 72px; background: #eff3ff; color: #071B91; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 32px; box-shadow: 0 4px 12px rgba(7,27,145,0.15); }
        .csr-modal-title { color: #071B91; font-size: 26px; margin: 0 0 12px; font-weight: 800; }
        .csr-modal-text { color: #5f6368; font-size: 16px; margin: 0 0 30px; line-height: 1.6; }
        .csr-modal-btn { background: #071B91; color: white; border: none; padding: 14px 40px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .csr-modal-btn:hover { background: #05105c; }
    </style>
    <div class="csr-form-container">
        <h3>Leave a Review</h3>
        <p class="csr-desc">We value your feedback! Let us know how your experience was.</p>
        <form method="post" action="" class="csr-form">
            <?php wp_nonce_field('submit_custom_review_action', 'custom_review_nonce'); ?>
            <label for="review_name">Your Name</label>
            <input type="text" name="review_name" id="review_name" placeholder="John Doe" required>
            
            <label>Rating</label>
            <div class="csr-rating-stars">
                <input type="radio" id="csr-star5" name="review_rating" value="5" required />
                <label for="csr-star5" class="csr-star-label" title="5 stars">★</label>
                <input type="radio" id="csr-star4" name="review_rating" value="4" />
                <label for="csr-star4" class="csr-star-label" title="4 stars">★</label>
                <input type="radio" id="csr-star3" name="review_rating" value="3" />
                <label for="csr-star3" class="csr-star-label" title="3 stars">★</label>
                <input type="radio" id="csr-star2" name="review_rating" value="2" />
                <label for="csr-star2" class="csr-star-label" title="2 stars">★</label>
                <input type="radio" id="csr-star1" name="review_rating" value="1" />
                <label for="csr-star1" class="csr-star-label" title="1 star">★</label>
            </div>
            
            <label for="review_content">Your Review</label>
            <textarea name="review_content" id="review_content" rows="4" placeholder="Tell us about your experience..." required></textarea>
            
            <button type="submit" name="submit_custom_review">
                Submit Review
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </button>
        </form>
    </div>
    
    <?php if ($csr_submission_success) : ?>
    <div class="csr-modal-overlay" id="csrSuccessModal">
        <div class="csr-modal-content">
            <div class="csr-modal-icon">✓</div>
            <h3 class="csr-modal-title">Thank You!</h3>
            <p class="csr-modal-text">Your review has been successfully submitted and is currently pending approval.</p>
            <button class="csr-modal-btn" onclick="document.getElementById('csrSuccessModal').classList.remove('active')">Awesome</button>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                document.getElementById('csrSuccessModal').classList.add('active');
            }, 100);
        });
    </script>
    <?php endif; ?>
    
    <?php
    return ob_get_clean();
}
add_shortcode('submit_review_form', 'custom_site_reviews_form_shortcode');

// 5. Shortcode: The Carousel Display [display_site_reviews]
function custom_site_reviews_display_shortcode($atts) {
    $original_limit = ( is_array( $atts ) && isset( $atts['limit'] ) ) ? $atts['limit'] : null;

    $atts = shortcode_atts(array(
        'carousel'       => 'yes',
        'columns'        => '3',
        'rows'           => '',
        'limit'          => '12',
        'auto_slide'     => 'true',
        'slide_interval' => '3000',
    ), $atts, 'display_site_reviews');

    // If rows is defined and limit was not explicitly provided, calculate limit dynamically
    if (!empty($atts['rows']) && intval($atts['rows']) > 0 && $original_limit === null) {
        $atts['limit'] = intval($atts['columns']) * intval($atts['rows']);
    }

    $args = array(
        'post_type'      => 'site_review',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['limit']),
        'orderby'        => 'date',
        'order'          => 'DESC'
    );
    $query = new WP_Query($args);
    if (!$query->have_posts()) return '<p>No reviews yet.</p>';
    $carousel_id = 'csr-' . wp_rand(100, 999);
    $is_carousel = $atts['carousel'] === 'yes';
    $cols = intval($atts['columns']);
    
    ob_start();
    ?>
    <style>
        .csr-container { position: relative; max-width: 100%; overflow: hidden; padding: 20px 40px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        .csr-carousel { display: flex; gap: 20px; overflow-x: auto; scroll-behavior: smooth; padding-bottom: 20px; scrollbar-width: none; }
        .csr-carousel::-webkit-scrollbar { display: none; } 
        
        /* Grid Styles */
        .csr-grid { display: grid; gap: 20px; padding-bottom: 20px; }
        .csr-grid-1 { grid-template-columns: repeat(1, 1fr); }
        .csr-grid-2 { grid-template-columns: repeat(2, 1fr); }
        .csr-grid-3 { grid-template-columns: repeat(3, 1fr); }
        .csr-grid-4 { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 992px) { .csr-grid { grid-template-columns: repeat(2, 1fr) !important; } }
        @media (max-width: 768px) { .csr-grid { grid-template-columns: repeat(1, 1fr) !important; } }
        
        .csr-card { background-color: #f8f9fa; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #eaeaea; display: flex; flex-direction: column; }
        .csr-carousel .csr-card { min-width: 320px; max-width: 320px; flex-shrink: 0; }
        .csr-grid .csr-card { min-width: 100%; }
        
        .csr-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .csr-user-info { display: flex; align-items: center; gap: 12px; }
        .csr-avatar { width: 42px; height: 42px; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; }
        .csr-name { font-weight: 600; color: #202124; font-size: 15px; margin: 0; }
        .csr-g-icon { width: 20px; height: 20px; }
        .csr-rating-row { display: flex; align-items: center; gap: 6px; margin-bottom: 12px; }
        .csr-stars { color: #fbbc04; font-size: 18px; letter-spacing: 1px; }
        .csr-verified { fill: #1a73e8; width: 16px; height: 16px; display: flex; align-items: center; }
        .csr-text { color: #3c4043; font-size: 14px; line-height: 1.5; margin: 0; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; flex-grow: 1; }
        .csr-read-more { color: #70757a; font-size: 13px; margin-top: 10px; cursor: pointer; text-decoration: none; display: inline-block; }
        
        /* Navigation Arrows */
        .csr-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 44px; height: 44px; background: white; border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.15); color: #071B91; z-index: 10; transition: all 0.3s ease; padding: 0; }
        .csr-nav:hover { background: #071B91; color: white; transform: translateY(-50%) scale(1.1); box-shadow: 0 6px 20px rgba(7,27,145,0.3); }
        .csr-nav svg { width: 22px; height: 22px; stroke: currentColor; stroke-width: 2.5; fill: none; stroke-linecap: round; stroke-linejoin: round; }
        .csr-prev { left: 15px; }
        .csr-next { right: 15px; }
    </style>

    <div class="csr-container" id="container-<?php echo esc_attr($carousel_id); ?>" <?php if (!$is_carousel) echo 'style="padding-left: 0; padding-right: 0;"'; ?>>
        <?php if ($is_carousel) : ?>
        <button class="csr-nav csr-prev" onclick="document.getElementById('scroll-<?php echo esc_attr($carousel_id); ?>').scrollBy({left: -340, behavior: 'smooth'})" aria-label="Previous">
            <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <?php endif; ?>
        
        <div class="<?php echo $is_carousel ? 'csr-carousel' : 'csr-grid csr-grid-' . esc_attr($cols); ?>" id="scroll-<?php echo esc_attr($carousel_id); ?>">
            <?php while ($query->have_posts()) : $query->the_post(); 
                $rating = get_post_meta(get_the_ID(), '_review_rating', true) ?: 5;
                $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                $name = get_the_title();
                $initial = !empty($name) ? strtoupper(substr($name, 0, 1)) : 'U';
                $colors = ['#81c995', '#f28b82', '#fde293', '#8ab4f8'];
                $bg_color = $colors[strlen($name) % count($colors)];
            ?>
            <div class="csr-card">
                <div class="csr-header">
                    <div class="csr-user-info">
                        <div class="csr-avatar" style="background-color: <?php echo esc_attr($bg_color); ?>;"><?php echo esc_html($initial); ?></div>
                        <h4 class="csr-name"><?php echo esc_html($name); ?></h4>
                    </div>
                    <svg class="csr-g-icon" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                </div>
                <div class="csr-rating-row">
                    <div class="csr-stars"><?php echo esc_html($stars); ?></div>
                    <svg class="csr-verified" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                </div>
                <div class="csr-text"><?php echo wp_kses_post(get_the_content()); ?></div>
                <a href="#" class="csr-read-more" onclick="event.preventDefault(); this.previousElementSibling.style.display='block'; this.previousElementSibling.style.webkitLineClamp='unset'; this.style.display='none';">Read more</a>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        
        <?php if ($is_carousel) : ?>
        <button class="csr-nav csr-next" onclick="document.getElementById('scroll-<?php echo esc_attr($carousel_id); ?>').scrollBy({left: 340, behavior: 'smooth'})" aria-label="Next">
            <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
        <?php endif; ?>
    </div>
    
    <?php if ($is_carousel && $atts['auto_slide'] === 'true') : ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var carousel = document.getElementById('scroll-<?php echo $carousel_id; ?>');
            var interval = <?php echo intval($atts['slide_interval']); ?>;
            var autoSlideTimer;
            
            function startAutoSlide() {
                autoSlideTimer = setInterval(function() {
                    // Check if at the end of scroll
                    if (carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 10) {
                        carousel.scrollTo({ left: 0, behavior: 'smooth' });
                    } else {
                        carousel.scrollBy({ left: 340, behavior: 'smooth' });
                    }
                }, interval);
            }
            
            function stopAutoSlide() {
                clearInterval(autoSlideTimer);
            }
            
            startAutoSlide();
            
            // Pause on hover
            carousel.addEventListener('mouseenter', stopAutoSlide);
            carousel.addEventListener('mouseleave', startAutoSlide);
            
            // Pause on touch
            carousel.addEventListener('touchstart', stopAutoSlide, {passive: true});
            carousel.addEventListener('touchend', startAutoSlide, {passive: true});
        });
    </script>
    <?php endif; ?>
    
    <?php
    return ob_get_clean();
}
add_shortcode('display_site_reviews', 'custom_site_reviews_display_shortcode');
