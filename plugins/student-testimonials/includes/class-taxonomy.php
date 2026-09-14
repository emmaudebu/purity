<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST_Testimonials_Taxonomy {
	public function __construct() {
		add_action( 'init', [ $this, 'register_taxonomy' ] );
	}

	public function register_taxonomy() {
		$labels = [
			'name'                       => 'Classes / Courses',
			'singular_name'              => 'Class / Course',
			'search_items'               => 'Search Classes',
			'popular_items'              => 'Popular Classes',
			'all_items'                  => 'All Classes',
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => 'Edit Class',
			'update_item'                => 'Update Class',
			'add_new_item'               => 'Add New Class',
			'new_item_name'              => 'New Class Name',
			'separate_items_with_commas' => 'Separate classes with commas',
			'add_or_remove_items'        => 'Add or remove classes',
			'choose_from_most_used'      => 'Choose from the most used classes',
			'not_found'                  => 'No classes found.',
			'menu_name'                  => 'Classes / Courses',
		];

		$args = [
			'hierarchical'          => true, // behave like categories
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true, // Native way to add column
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => [ 'slug' => 'testimonial-class' ],
			'show_in_rest'          => true,
		];

		register_taxonomy( 'student_testimonial_class', 'student_testimonial', $args );
	}
}
