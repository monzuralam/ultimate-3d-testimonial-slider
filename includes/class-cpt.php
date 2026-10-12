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
			'name'                  => __( 'Ultimate 3D Testimonial Sliders', 'ultimate-3d-testimonial-slider' ),
			'singular_name'         => __( 'Ultimate 3D Testimonial Slider', 'ultimate-3d-testimonial-slider' ),
			'menu_name'             => __( 'Ultimate 3D Testimonial Slider', 'ultimate-3d-testimonial-slider' ),
			'all_items'             => __( 'All Items', 'ultimate-3d-testimonial-slider' ),
			'featured_image'        => __( 'Client Featured Image', 'ultimate-3d-testimonial-slider' ),
			'set_featured_image'    => __( 'Set Client Featured Image', 'ultimate-3d-testimonial-slider' ),
			'remove_featured_image' => __( 'Remove Client Featured Image', 'ultimate-3d-testimonial-slider' ),
			'use_featured_image'    => __( 'Use Client Featured Image', 'ultimate-3d-testimonial-slider' ),
		);

		$args = array(
			'label'                 => __( 'Ultimate 3D Testimonial Sliders', 'ultimate-3d-testimonial-slider' ),
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
					'name'              => __( 'Category', 'ultimate-3d-testimonial-slider' ),
					'singular_name'     => __( 'Category', 'ultimate-3d-testimonial-slider' ),
					'search_items'      => __( 'Search Category', 'ultimate-3d-testimonial-slider' ),
					'all_items'         => __( 'All Category', 'ultimate-3d-testimonial-slider' ),
					'parent_item'       => __( 'Parent Category', 'ultimate-3d-testimonial-slider' ),
					'parent_item_colon' => __( 'Parent Category:', 'ultimate-3d-testimonial-slider' ),
					'edit_item'         => __( 'Edit Category', 'ultimate-3d-testimonial-slider' ),
					'update_item'       => __( 'Update Category', 'ultimate-3d-testimonial-slider' ),
					'add_new_item'      => __( 'Add New Category', 'ultimate-3d-testimonial-slider' ),
					'new_item_name'     => __( 'New Category Name', 'ultimate-3d-testimonial-slider' ),
					'menu_name'         => __( 'Category', 'ultimate-3d-testimonial-slider' ),
				),
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
			)
		);
	}
}
