<?php
/**
 * Testimonial post type and category taxonomy.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the `uts` post type and the `uts_category` taxonomy.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class CPT {

	/**
	 * Hook the registration.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_types' ) );
	}

	/**
	 * Register the post type and taxonomy.
	 *
	 * Also called on activation (before flushing rewrite rules) and by the
	 * public uts_setup_post_type() wrapper.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register_types() {

		/**
		 * Post Type: Ultimate Testimonial Slider
		 */
		$labels = array(
			'name'                  => __( 'Ultimate 3D Testimonial Sliders', 'uts' ),
			'singular_name'         => __( 'Ultimate 3D Testimonial Slider', 'uts' ),
			'menu_name'             => __( 'Ultimate 3D Testimonial Slider', 'uts' ),
			'all_items'             => __( 'All Items', 'uts' ),
			'featured_image'        => __( 'Client Featured Image', 'uts' ),
			'set_featured_image'    => __( 'Set Client Featured Image', 'uts' ),
			'remove_featured_image' => __( 'Remove Client Featured Image', 'uts' ),
			'use_featured_image'    => __( 'Use Client Featured Image', 'uts' ),
		);

		$args = array(
			'label'                 => __( 'Ultimate 3D Testimonial Sliders', 'uts' ),
			'labels'                => $labels,
			'description'           => '',
			'public'                => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_rest'          => false,
			'rest_base'             => '',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'has_archive'           => false,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => true,
			'delete_with_user'      => false,
			'exclude_from_search'   => false,
			'capability_type'       => 'post',
			'map_meta_cap'          => true,
			'hierarchical'          => false,
			'can_export'            => false,
			'rewrite'               => array(
				'slug'       => 'uts',
				'with_front' => true,
			),
			'query_var'             => true,
			'menu_icon'             => 'dashicons-slides',
			'supports'              => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'show_in_graphql'       => false,
		);

		register_post_type( 'uts', $args );

		// register taxonomy.
		register_taxonomy(
			'uts_category',
			'uts',
			array(
				'labels'            => array(
					'name'              => __( 'Category', 'uts' ),
					'singular_name'     => __( 'Category', 'uts' ),
					'search_items'      => __( 'Search Category', 'uts' ),
					'all_items'         => __( 'All Category', 'uts' ),
					'parent_item'       => __( 'Parent Category', 'uts' ),
					'parent_item_colon' => __( 'Parent Category:', 'uts' ),
					'edit_item'         => __( 'Edit Category', 'uts' ),
					'update_item'       => __( 'Update Category', 'uts' ),
					'add_new_item'      => __( 'Add New Category', 'uts' ),
					'new_item_name'     => __( 'New Category Name', 'uts' ),
					'menu_name'         => __( 'Category', 'uts' ),
				),
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
			)
		);
	}
}
