<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CRC_Post_Type {
	public function __construct() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'init', [ $this, 'register_taxonomy' ] );
		add_filter( 'manage_class_registration_posts_columns', [ $this, 'custom_columns' ] );
		add_action( 'manage_class_registration_posts_custom_column', [ $this, 'custom_columns_data' ], 10, 2 );
		add_action( 'wp_ajax_crc_update_post_order', [ $this, 'update_post_order' ] );
	}

	public function register_post_type() {
		$labels = [
			'name'               => 'Class Registrations',
			'singular_name'      => 'Class Registration',
			'menu_name'          => 'Class Registrations',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Class',
			'edit_item'          => 'Edit Class',
			'new_item'           => 'New Class',
			'view_item'          => 'View Class',
			'search_items'       => 'Search Classes',
			'not_found'          => 'No classes found',
			'not_found_in_trash' => 'No classes found in Trash',
		];

		$args = [
			'labels'              => $labels,
			'public'              => false, // Do not need single frontend views
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-welcome-learn-more',
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => [ 'title' ], // Rest handled by meta boxes
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_in_rest'        => false,
		];

		register_post_type( 'class_registration', $args );
	}

	public function register_taxonomy() {
		$labels = [
			'name'              => 'Class Categories',
			'singular_name'     => 'Class Category',
			'search_items'      => 'Search Categories',
			'all_items'         => 'All Categories',
			'parent_item'       => 'Parent Category',
			'parent_item_colon' => 'Parent Category:',
			'edit_item'         => 'Edit Category',
			'update_item'       => 'Update Category',
			'add_new_item'      => 'Add New Category',
			'new_item_name'     => 'New Category Name',
			'menu_name'         => 'Class Categories',
		];

		$args = [
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => false,
			'show_in_rest'      => false,
		];

		register_taxonomy( 'class_registration_category', [ 'class_registration' ], $args );
	}

	public function custom_columns( $columns ) {
		$new_columns = [];
		$new_columns['cb'] = $columns['cb'];
		$new_columns['crc_drag'] = ''; // Drag handle
		$new_columns['crc_image'] = 'Image';
		$new_columns['title'] = $columns['title'];
		$new_columns['taxonomy-class_registration_category'] = $columns['taxonomy-class_registration_category'];
		$new_columns['crc_active'] = 'Active';
		$new_columns['crc_featured'] = 'Featured';
		$new_columns['date'] = $columns['date'];
		return $new_columns;
	}

	public function custom_columns_data( $column, $post_id ) {
		switch ( $column ) {
			case 'crc_drag':
				echo '<span class="dashicons dashicons-menu crc-drag-handle" style="cursor: move; color: #999;"></span>';
				break;
			case 'crc_image':
				$image_id = get_post_meta( $post_id, 'crc_image_id', true );
				if ( $image_id ) {
					echo wp_get_attachment_image( $image_id, [ 60, 60 ] );
				} else {
					echo '<div style="width:60px;height:60px;background:#eee;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;color:#999;font-size:10px;">No Image</div>';
				}
				break;
			case 'crc_active':
				$active = get_post_meta( $post_id, 'crc_active', true );
				echo $active === 'yes' ? '<span style="color:green;font-weight:bold;">Yes</span>' : '<span style="color:red;">No</span>';
				break;
			case 'crc_featured':
				$featured = get_post_meta( $post_id, 'crc_featured', true );
				echo $featured === 'yes' ? '<span style="color:orange;font-weight:bold;">★ Yes</span>' : 'No';
				break;
		}
	}

	public function update_post_order() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}

		if ( ! isset( $_POST['order'] ) || ! is_array( $_POST['order'] ) ) {
			wp_die( 'Invalid Data' );
		}

		global $wpdb;
		foreach ( $_POST['order'] as $index => $post_id ) {
			$post_id = intval( $post_id );
			$wpdb->update( 
				$wpdb->posts, 
				[ 'menu_order' => $index ], 
				[ 'ID' => $post_id ] 
			);
		}
		
		wp_send_json_success();
	}
}
