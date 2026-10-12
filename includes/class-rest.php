<?php
/**
 * REST API (`uts/v1`): saved shortcodes, preview and setup status.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the `uts/v1` routes used by the admin app:
 *
 * - /shortcodes (+ /{id}, /{id}/duplicate): CRUD for saved instances. Needs the
 *   builder capability (`manage_options`, filter `uts_builder_capability`).
 * - /preview: render the shortcode for given attributes (`edit_posts`).
 * - /status: Getting Started checklist data (`edit_posts`).
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class REST {

	/**
	 * Route namespace.
	 *
	 * @var string
	 */
	const NAMESPACE_V1 = 'uts/v1';

	/**
	 * Hook the routes.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the routes.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register_routes() {
		$id_arg = array(
			'id' => array(
				'description' => __( 'Saved shortcode ID.', 'uts' ),
				'type'        => 'integer',
				'minimum'     => 1,
				'required'    => true,
			),
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/shortcodes',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_items' ),
					'permission_callback' => array( $this, 'can_manage' ),
					'args'                => $this->list_args(),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'can_manage' ),
					'args'                => $this->item_args( true ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/shortcodes/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'can_manage' ),
					'args'                => $id_arg,
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'can_manage' ),
					'args'                => array_merge( $id_arg, $this->item_args( false ) ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'can_manage' ),
					'args'                => array_merge(
						$id_arg,
						array(
							'force' => array(
								'description' => __( 'Delete permanently instead of moving to the trash.', 'uts' ),
								'type'        => 'boolean',
								'default'     => false,
							),
						)
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/shortcodes/(?P<id>\d+)/duplicate',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'duplicate_item' ),
				'permission_callback' => array( $this, 'can_manage' ),
				'args'                => $id_arg,
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/shortcodes/(?P<id>\d+)/usage',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'usage' ),
				'permission_callback' => array( $this, 'can_manage' ),
				'args'                => $id_arg,
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/preview',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'preview' ),
				'permission_callback' => array( $this, 'can_edit' ),
				'args'                => array(
					'attributes' => array(
						'description' => __( 'Shortcode attributes, e.g. { "type": "grid", "id": 5 }.', 'uts' ),
						'type'        => 'object',
						'default'     => array(),
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/status',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'status' ),
				'permission_callback' => array( $this, 'can_edit' ),
			)
		);
	}

	/**
	 * Capability needed to manage saved shortcodes.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	public static function builder_capability() {
		/**
		 * Filters the capability needed to create, edit and delete saved shortcodes.
		 *
		 * @since 1.1.0
		 *
		 * @param string $capability Default 'manage_options'.
		 */
		return (string) apply_filters( 'uts_builder_capability', 'manage_options' );
	}

	/**
	 * Permission: manage saved shortcodes.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return bool
	 */
	public function can_manage() {
		return current_user_can( self::builder_capability() );
	}

	/**
	 * Permission: preview and status (anyone who can edit posts).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return bool
	 */
	public function can_edit() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * GET /shortcodes
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function list_items( $request ) {
		$result = DB::query(
			array(
				'status'   => $request['status'],
				'search'   => $request['search'],
				'per_page' => $request['per_page'],
				'page'     => $request['page'],
				'orderby'  => $request['orderby'],
				'order'    => $request['order'],
			)
		);

		$response = rest_ensure_response( array_map( array( $this, 'prepare_item' ), $result['items'] ) );
		$response->header( 'X-WP-Total', (string) $result['total'] );
		$response->header( 'X-WP-TotalPages', (string) (int) ceil( $result['total'] / max( 1, (int) $request['per_page'] ) ) );

		return $response;
	}

	/**
	 * GET /shortcodes/{id}
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$item = DB::get( $request['id'] );
		if ( ! $item ) {
			return $this->not_found();
		}

		return rest_ensure_response( $this->prepare_item( $item ) );
	}

	/**
	 * POST /shortcodes
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$id = DB::insert( $this->item_data( $request ) );
		if ( is_wp_error( $id ) ) {
			return $id;
		}

		$response = rest_ensure_response( $this->prepare_item( DB::get( $id ) ) );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * PUT/PATCH /shortcodes/{id}
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$updated = DB::update( $request['id'], $this->item_data( $request ) );
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		return rest_ensure_response( $this->prepare_item( DB::get( $request['id'] ) ) );
	}

	/**
	 * DELETE /shortcodes/{id}
	 *
	 * Without `force`: moves to the trash and returns the item. With `force`:
	 * deletes permanently and returns { deleted: true, previous: item }.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$item = DB::get( $request['id'] );
		if ( ! $item ) {
			return $this->not_found();
		}

		$force   = (bool) $request['force'];
		$deleted = DB::delete( $request['id'], $force );
		if ( is_wp_error( $deleted ) ) {
			return $deleted;
		}

		if ( $force ) {
			return rest_ensure_response(
				array(
					'deleted'  => true,
					'previous' => $this->prepare_item( $item ),
				)
			);
		}

		return rest_ensure_response( $this->prepare_item( DB::get( $request['id'] ) ) );
	}

	/**
	 * POST /shortcodes/{id}/duplicate
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function duplicate_item( $request ) {
		$id = DB::duplicate( $request['id'] );
		if ( is_wp_error( $id ) ) {
			return $id;
		}

		$response = rest_ensure_response( $this->prepare_item( DB::get( $id ) ) );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * GET /shortcodes/{id}/usage: posts whose content uses [uts id="{id}"].
	 *
	 * Searches post content (no revisions, auto-drafts or trash) and returns
	 * at most 20 posts, newest first.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function usage( $request ) {
		global $wpdb;

		$id = (int) $request['id'];
		if ( ! DB::get( $id ) ) {
			return $this->not_found();
		}

		// Cheap SQL pre-filter, then an exact match in PHP (quotes are optional).
		$candidates = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT ID, post_content FROM {$wpdb->posts}
				WHERE post_type NOT IN ('revision', 'uts')
				AND post_status NOT IN ('trash', 'auto-draft', 'inherit')
				AND post_content LIKE %s AND post_content LIKE %s
				ORDER BY post_modified DESC LIMIT 200",
				'%' . $wpdb->esc_like( '[uts' ) . '%',
				'%' . $wpdb->esc_like( 'id=' ) . '%' . $wpdb->esc_like( (string) $id ) . '%'
			)
		);

		$posts = array();
		foreach ( (array) $candidates as $candidate ) {
			if ( ! preg_match( '/\[uts\b[^\]]*\bid\s*=\s*["\']?' . $id . '(?![\d])/', $candidate->post_content ) ) {
				continue;
			}

			$post = get_post( $candidate->ID );
			if ( ! $post ) {
				continue;
			}

			$type    = get_post_type_object( $post->post_type );
			$posts[] = array(
				'id'        => $post->ID,
				'title'     => get_the_title( $post ) !== '' ? get_the_title( $post ) : __( '(no title)', 'uts' ),
				'type'      => $type ? $type->labels->singular_name : $post->post_type,
				'status'    => $post->post_status,
				'edit_link' => (string) get_edit_post_link( $post->ID, 'raw' ),
				'link'      => (string) get_permalink( $post ),
			);

			if ( 20 === count( $posts ) ) {
				break;
			}
		}

		return rest_ensure_response( $posts );
	}

	/**
	 * POST /preview: render the shortcode for the given attributes.
	 *
	 * Unlike the frontend, a draft `id` is rendered, so the builder can preview
	 * an instance before publishing it.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function preview( $request ) {
		$atts = self::preview_attributes( $request['attributes'] );

		$allow_drafts = function ( $item ) {
			if ( $item && 'draft' === $item['status'] ) {
				$item['status'] = 'publish';
			}
			return $item;
		};

		add_filter( 'uts_shortcode_instance', $allow_drafts );
		$shortcode = new Shortcode();
		$html      = $shortcode->render( $atts );
		remove_filter( 'uts_shortcode_instance', $allow_drafts );

		return rest_ensure_response( array( 'html' => $html ) );
	}

	/**
	 * GET /status: checklist data for Getting Started.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return \WP_REST_Response
	 */
	public function status() {
		$status               = Getting_Started::setup_status();
		$status['shortcodes'] = DB::count();

		return rest_ensure_response( $status );
	}

	/**
	 * Convert preview values to shortcode attributes.
	 *
	 * Only known keys are kept (settings, `category`, `id`); values become
	 * strings, as in a real shortcode. The shortcode itself sanitizes them.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param mixed $attributes Raw attributes.
	 * @return array
	 */
	public static function preview_attributes( $attributes ) {
		$allowed = array_merge( array_keys( Settings::schema() ), array( 'category', 'id' ) );
		$atts    = array();

		foreach ( (array) $attributes as $key => $value ) {
			if ( ! in_array( $key, $allowed, true ) || is_array( $value ) || is_object( $value ) || null === $value ) {
				continue;
			}
			if ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			}
			$atts[ $key ] = sanitize_text_field( (string) $value );
		}

		return $atts;
	}

	/**
	 * Shape of a saved shortcode in responses.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param array $item Instance from DB.
	 * @return array
	 */
	public function prepare_item( $item ) {
		$author = get_userdata( $item['author_id'] );

		return array(
			'id'         => $item['id'],
			'title'      => $item['title'],
			'settings'   => (object) $item['settings'],
			'status'     => $item['status'],
			'shortcode'  => '[uts id="' . $item['id'] . '"]',
			'created_at' => mysql_to_rfc3339( $item['created_at'] ),
			'updated_at' => mysql_to_rfc3339( $item['updated_at'] ),
			'author'     => array(
				'id'   => $item['author_id'],
				'name' => $author ? $author->display_name : '',
			),
		);
	}

	/**
	 * Fields from a create/update request that were actually sent.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return array
	 */
	private function item_data( $request ) {
		$params = $request->get_params();
		$data   = array();

		foreach ( array( 'title', 'settings', 'status' ) as $field ) {
			if ( array_key_exists( $field, $params ) && null !== $params[ $field ] ) {
				$data[ $field ] = $params[ $field ];
			}
		}

		return $data;
	}

	/**
	 * Arguments for GET /shortcodes.
	 *
	 * @return array
	 */
	private function list_args() {
		return array(
			'status'   => array(
				'type'    => 'string',
				'enum'    => array( 'any', 'publish', 'draft', 'trash' ),
				'default' => 'any',
			),
			'search'   => array(
				'type'    => 'string',
				'default' => '',
			),
			'page'     => array(
				'type'    => 'integer',
				'minimum' => 1,
				'default' => 1,
			),
			'per_page' => array(
				'type'    => 'integer',
				'minimum' => 1,
				'maximum' => 100,
				'default' => 20,
			),
			'orderby'  => array(
				'type'    => 'string',
				'enum'    => array( 'id', 'title', 'created_at', 'updated_at' ),
				'default' => 'updated_at',
			),
			'order'    => array(
				'type'    => 'string',
				'enum'    => array( 'asc', 'desc', 'ASC', 'DESC' ),
				'default' => 'desc',
			),
		);
	}

	/**
	 * Arguments for creating/updating a saved shortcode.
	 *
	 * The `settings` schema comes from Settings::schema(), without defaults
	 * (an instance stores only the options it changes) and with `category`.
	 *
	 * @param bool $creating Whether this is a create request.
	 * @return array
	 */
	private function item_args( $creating ) {
		$properties = array();
		foreach ( Settings::schema() as $key => $property ) {
			unset( $property['default'] );
			$properties[ $key ] = $property;
		}
		$properties['category'] = array(
			'type'      => 'string',
			'maxLength' => 200,
		);

		return array(
			'title'    => array(
				'description' => __( 'Slider name, shown in the builder.', 'uts' ),
				'type'        => 'string',
				'maxLength'   => 191,
			),
			'settings' => array(
				'description'          => __( 'Options this slider changes from the global settings.', 'uts' ),
				'type'                 => 'object',
				'properties'           => $properties,
				'additionalProperties' => false,
			),
			'status'   => array(
				'type' => 'string',
				'enum' => $creating ? array( 'publish', 'draft' ) : array( 'publish', 'draft', 'trash' ),
			),
		);
	}

	/**
	 * 404 error for an unknown saved shortcode.
	 *
	 * @return \WP_Error
	 */
	private function not_found() {
		return new \WP_Error( 'uts_not_found', __( 'Slider not found.', 'uts' ), array( 'status' => 404 ) );
	}
}
