<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST_Testimonials_Post_Type {
	public function __construct() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_filter( 'manage_student_testimonial_posts_columns', [ $this, 'add_custom_columns' ] );
		add_action( 'manage_student_testimonial_posts_custom_column', [ $this, 'render_custom_columns' ], 10, 2 );
	}

	public function register_post_type() {
		$labels = [
			'name'               => 'Student Testimonials',
			'singular_name'      => 'Testimonial',
			'menu_name'          => 'Student Testimonials',
			'name_admin_bar'     => 'Testimonial',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Testimonial',
			'new_item'           => 'New Testimonial',
			'edit_item'          => 'Edit Testimonial',
			'view_item'          => 'View Testimonial',
			'all_items'          => 'All Testimonials',
			'search_items'       => 'Search Testimonials',
			'parent_item_colon'  => 'Parent Testimonials:',
			'not_found'          => 'No testimonials found.',
			'not_found_in_trash' => 'No testimonials found in Trash.',
		];

		$args = [
			'labels'              => $labels,
			'public'              => false, // Don't need single view on frontend
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-testimonial',
			'supports'            => [ 'title', 'editor', 'thumbnail', 'page-attributes' ],
			'show_in_rest'        => true, // Enable Gutenberg if they want to use it for the editor
		];

		register_post_type( 'student_testimonial', $args );
	}

	public function add_custom_columns( $columns ) {
		$new_columns = [];
		foreach ( $columns as $key => $title ) {
			if ( $key === 'date' ) {
				$new_columns['st_class']  = 'Class Passed';
				$new_columns['st_rating'] = 'Rating';
				$new_columns['date']      = $title; // Keep original date at the end
			} else {
				$new_columns[ $key ] = $title;
			}
		}
		return $new_columns;
	}

	public function render_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'st_class':
				$terms = get_the_terms( $post_id, 'student_testimonial_class' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$term_names = wp_list_pluck( $terms, 'name' );
					echo esc_html( implode( ', ', $term_names ) );
				} else {
					echo '—';
				}
				break;
			case 'st_rating':
				$rating = get_post_meta( $post_id, 'st_rating', true );
				echo esc_html( $rating ? $rating . ' Stars' : '—' );
				break;
		}
	}
}
