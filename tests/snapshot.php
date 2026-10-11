<?php
/**
 * Output snapshots for Ultimate 3D Testimonial Slider.
 *
 * Captures the plugin's rendered output so a refactor can be proven to
 * produce identical output. Run it from the WordPress root with WP-CLI.
 *
 * Generate (or refresh) the reference files in tests/snapshots/:
 *
 *     wp eval-file wp-content/plugins/ultimate-3d-testimonial-slider/tests/snapshot.php
 *
 * Write only some of them (comma-separated file names), leaving the rest as is:
 *
 *     wp eval-file wp-content/plugins/ultimate-3d-testimonial-slider/tests/snapshot.php only=cpt.json
 *
 * Compare the current output against the saved reference (exit 0 when
 * identical, exit 1 and a line diff when anything differs or is missing):
 *
 *     wp eval-file wp-content/plugins/ultimate-3d-testimonial-slider/tests/snapshot.php compare
 *
 * Captured files:
 * - shortcode-default.html         [uts]
 * - shortcode-grid.html            [uts type="grid"]
 * - shortcode-fade-navigation.html [uts effect="fade" navigation="true"]
 * - rest-settings.json             GET /wp/v2/settings as an administrator
 * - admin-getting-started.html     Getting Started page body, as an administrator
 * - admin-columns.json             Testimonials list: column headers, sortable
 *                                  columns, views, and each column's cell output
 *                                  for one post with a photo and one without
 * - metabox.json                   "Client Additional" metabox: registration,
 *                                  form HTML with/without a designation, and the
 *                                  value each save case would store (no nonce,
 *                                  bad nonce, no field, value cleaned, user who
 *                                  can't edit). Meta writes are short-circuited.
 * - menu.json                      Submenu entries (title, capability, slug),
 *                                  page/load hook callback counts, Settings page
 *                                  HTML, and the activation redirect under CLI.
 * - cpt.json                       Registered `uts` post type and `uts_category`
 *                                  taxonomy (objects as plain data; closures and
 *                                  non-stdClass objects as type markers),
 *                                  supports, permastructs and the uts rewrite
 *                                  rules generated in memory
 * - assets.json                    Plugin script/style handles (src, deps,
 *                                  version kind, inline code) and which are
 *                                  enqueued on frontend cases (singular with and
 *                                  without [uts], archive) and admin screens
 *                                  (uts and non-uts), plus the react-jsx-runtime
 *                                  fallback forced by deregistering core's.
 *                                  Only this plugin's callbacks on the enqueue
 *                                  hooks run; no DB writes.
 * - settings.json                  The uts_settings option: the Settings page's
 *                                  inline window.utsSettingsData (raw and
 *                                  decoded schema/defaults), the registered
 *                                  setting (sanitize_callback only as present /
 *                                  callable), sanitize_option() results for a
 *                                  fixture set, uts_get_settings(), the effect
 *                                  of a temporary uts_settings_schema filter,
 *                                  and REST schema validation/sanitizing of a
 *                                  valid and an invalid payload. No DB writes.
 * - shortcode-matrix.json          Case name => shortcode, section IDs and
 *                                  normalized HTML for 24 attribute/filter/
 *                                  instance cases (fixed `run_order`). Runs
 *                                  last; instance IDs are NOT normalized here,
 *                                  so the shared counter is part of it.
 *
 * Normalization (so two runs on the same code are byte-identical):
 * - nonces                          -> {NONCE}
 * - asset ver= query values         -> {VER}
 * - shortcode instance IDs after the first (ucs-container-slider-2) -> ucs-container-slider-{N}
 * - 10-digit Unix timestamps within a year of now -> {TIMESTAMP}
 * - line endings -> LF; in HTML, runs of 2+ spaces/tabs become one space,
 *   each line is trimmed and blank lines are dropped, so indentation changes
 *   (e.g. templates moved into class methods) don't count as differences.
 *
 * The script is read-only: it creates no posts and writes no options. Any
 * option/post/meta write that happens while it runs is reported as a warning.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

if ( ! function_exists( 'uts_snapshot_dir' ) ) {
	/**
	 * Directory holding the reference snapshots.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	function uts_snapshot_dir() {
		return __DIR__ . '/snapshots';
	}
}

if ( ! function_exists( 'uts_snapshot_watch_writes' ) ) {
	/**
	 * Record option, post and post meta writes made while the script runs.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param string|null $entry Entry to record; null returns the log.
	 * @return array Recorded writes.
	 */
	function uts_snapshot_watch_writes( $entry = null ) {
		static $log = array();

		if ( null !== $entry ) {
			$log[] = $entry;
		}

		return $log;
	}
}

if ( ! function_exists( 'uts_snapshot_start_watch' ) ) {
	/**
	 * Hook the write watchers.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	function uts_snapshot_start_watch() {
		$option_hooks = array( 'added_option', 'updated_option', 'deleted_option' );
		foreach ( $option_hooks as $hook ) {
			add_action(
				$hook,
				function ( $option ) use ( $hook ) {
					uts_snapshot_watch_writes( $hook . ': ' . $option );
				}
			);
		}

		$post_hooks = array( 'wp_insert_post', 'deleted_post' );
		foreach ( $post_hooks as $hook ) {
			add_action(
				$hook,
				function ( $post_id ) use ( $hook ) {
					uts_snapshot_watch_writes( $hook . ': ' . $post_id );
				}
			);
		}

		$meta_hooks = array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' );
		foreach ( $meta_hooks as $hook ) {
			add_action(
				$hook,
				function ( $meta_id, $object_id, $meta_key ) use ( $hook ) {
					uts_snapshot_watch_writes( $hook . ': ' . $object_id . ' ' . $meta_key );
				},
				10,
				3
			);
		}
	}
}

if ( ! function_exists( 'uts_snapshot_normalize' ) ) {
	/**
	 * Replace values that vary between runs with stable placeholders.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param string $output   Raw output.
	 * @param bool   $is_html  Whether to trim lines and drop blank lines.
	 * @param bool   $keep_ids Keep shortcode instance IDs (-2, -3, ...) as they are.
	 * @return string
	 */
	function uts_snapshot_normalize( $output, $is_html, $keep_ids = false ) {
		$output = str_replace( array( "\r\n", "\r" ), "\n", $output );

		// Nonces in URLs, JSON, data attributes and hidden nonce fields.
		$output = preg_replace( '/(nonce[\w-]*["\']?\s*[=:]\s*["\']?)[a-f0-9]{10}(?![a-f0-9])/i', '$1{NONCE}', $output );
		$output = preg_replace( '/(name=["\'][^"\']*nonce[^"\']*["\'][^>]*?value=["\'])[a-f0-9]{10}(?![a-f0-9])/i', '$1{NONCE}', $output );
		$output = preg_replace( '/(createNonceMiddleware\(\s*["\'])[a-f0-9]{10}(?![a-f0-9])/', '$1{NONCE}', $output );

		// Asset versions (build hashes, core/plugin versions).
		$output = preg_replace( '/([?&](?:amp;|#038;)?ver=)[^&"\'\s<>]+/', '$1{VER}', $output );

		// Shortcode instance IDs beyond the first depend on how many ran before.
		if ( ! $keep_ids ) {
			$output = preg_replace( '/\bucs-container-(slider|grid|list)-\d+\b/', 'ucs-container-$1-{N}', $output );
		}

		// Unix timestamps close to now.
		$now    = time();
		$output = preg_replace_callback(
			'/(?<![\d.])\d{10}(?![\d.])/',
			function ( $matches ) use ( $now ) {
				return abs( (int) $matches[0] - $now ) < YEAR_IN_SECONDS ? '{TIMESTAMP}' : $matches[0];
			},
			$output
		);

		if ( $is_html ) {
			$output = preg_replace( '/[ \t]{2,}/', ' ', $output );
			$lines  = array_map( 'trim', explode( "\n", $output ) );
			$lines  = array_filter(
				$lines,
				function ( $line ) {
					return '' !== $line;
				}
			);
			$output = implode( "\n", $lines );
		}

		return rtrim( $output, "\n" ) . "\n";
	}
}

if ( ! function_exists( 'uts_snapshot_json' ) ) {
	/**
	 * Pretty-print data as JSON.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param mixed $data Data.
	 * @return string
	 */
	function uts_snapshot_json( $data ) {
		return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}
}

if ( ! function_exists( 'uts_snapshot_admin_user' ) ) {
	/**
	 * ID of the first administrator.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return int
	 */
	function uts_snapshot_admin_user() {
		$users = get_users(
			array(
				'role'    => 'administrator',
				'orderby' => 'ID',
				'order'   => 'ASC',
				'number'  => 1,
				'fields'  => 'ID',
			)
		);

		if ( empty( $users ) ) {
			WP_CLI::error( 'No administrator user found.' );
		}

		return (int) $users[0];
	}
}

if ( ! function_exists( 'uts_snapshot_load_admin' ) ) {
	/**
	 * Load the admin API and set the current screen without firing
	 * `current_screen`, so is_admin() is true while rendering admin output.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param string $screen_id Screen ID or hook suffix.
	 */
	function uts_snapshot_load_admin( $screen_id ) {
		require_once ABSPATH . 'wp-admin/includes/admin.php';

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- simulating the admin request.
		$GLOBALS['current_screen'] = WP_Screen::get( $screen_id );
	}
}

if ( ! function_exists( 'uts_snapshot_shortcodes' ) ) {
	/**
	 * Shortcode output, rendered as a visitor on the frontend.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return array File name => output.
	 */
	function uts_snapshot_shortcodes() {
		$shortcodes = array(
			'shortcode-default.html'         => '[uts]',
			'shortcode-grid.html'            => '[uts type="grid"]',
			'shortcode-fade-navigation.html' => '[uts effect="fade" navigation="true"]',
		);

		$files = array();
		foreach ( $shortcodes as $file => $shortcode ) {
			$files[ $file ] = '<!-- ' . $shortcode . " -->\n" . do_shortcode( $shortcode );
		}

		return $files;
	}
}

if ( ! function_exists( 'uts_snapshot_rest_settings' ) ) {
	/**
	 * GET /wp/v2/settings as the current user.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	function uts_snapshot_rest_settings() {
		$response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/settings' ) );

		return uts_snapshot_json(
			array(
				'status' => $response->get_status(),
				'data'   => rest_get_server()->response_to_data( $response, false ),
			)
		);
	}
}

if ( ! function_exists( 'uts_snapshot_columns' ) ) {
	/**
	 * Testimonials list columns: headers, sortable columns, views and the
	 * cell output of every column for one post with and one without a photo.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	function uts_snapshot_columns() {
		uts_snapshot_load_admin( 'edit-uts' );

		$table   = new WP_Posts_List_Table( array( 'screen' => 'edit-uts' ) );
		$headers = $table->get_columns();

		$picks = array(
			'with_photo'    => 'EXISTS',
			'without_photo' => 'NOT EXISTS',
		);

		$posts = array();
		foreach ( $picks as $label => $compare ) {
			$ids = get_posts(
				array(
					'post_type'        => 'uts',
					'post_status'      => 'publish',
					'posts_per_page'   => 1,
					'orderby'          => 'ID',
					'order'            => 'ASC',
					'fields'           => 'ids',
					'suppress_filters' => false,
					'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'     => '_thumbnail_id',
							'compare' => $compare,
						),
					),
				)
			);

			if ( empty( $ids ) ) {
				WP_CLI::warning( sprintf( 'No published testimonial %s; that case is not captured.', str_replace( '_', ' ', $label ) ) );
				$posts[ $label ] = null;
				continue;
			}

			$post_id = (int) $ids[0];
			$cells   = array();
			foreach ( array_keys( $headers ) as $column ) {
				ob_start();
				/** This action is documented in wp-admin/includes/class-wp-posts-list-table.php */
				do_action( 'manage_uts_posts_custom_column', $column, $post_id );
				$cells[ $column ] = ob_get_clean();
			}

			$posts[ $label ] = array(
				'id'    => $post_id,
				'title' => get_the_title( $post_id ),
				'cells' => $cells,
			);
		}

		return uts_snapshot_json(
			array(
				'headers'  => $headers,
				/** This filter is documented in wp-admin/includes/class-wp-list-table.php */
				'sortable' => apply_filters( 'manage_edit-uts_sortable_columns', array() ), // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- core hook name.
				/** This filter is documented in wp-admin/includes/class-wp-list-table.php */
				'views'    => apply_filters( 'views_edit-uts', array() ), // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- core hook name.
				'posts'    => $posts,
			)
		);
	}
}

if ( ! function_exists( 'uts_snapshot_getting_started' ) ) {
	/**
	 * Getting Started page body, rendered through its admin page hook.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	function uts_snapshot_getting_started() {
		require_once ABSPATH . 'wp-admin/includes/admin.php';

		/** This action is documented in wp-admin/includes/menu.php */
		do_action( 'admin_menu', '' );

		$hook = get_plugin_page_hookname( 'uts-getting-started', 'edit.php?post_type=uts' );
		if ( ! has_action( $hook ) ) {
			WP_CLI::error( 'The Getting Started page is not registered (hook ' . $hook . ').' );
		}

		uts_snapshot_load_admin( $hook );

		ob_start();
		/** This action is documented in wp-admin/admin.php */
		do_action( $hook );
		return ob_get_clean();
	}
}

if ( ! function_exists( 'uts_snapshot_export' ) ) {
	/**
	 * Convert a registered object to plain data for a stable JSON dump.
	 *
	 * Plain stdClass values (labels, caps) become arrays; closures and other
	 * objects (e.g. REST controller instances) become a type marker, so
	 * object identity never ends up in the snapshot.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param mixed $value Value.
	 * @param bool  $top   Whether this is the registered object itself.
	 * @return mixed
	 */
	function uts_snapshot_export( $value, $top = false ) {
		if ( $value instanceof Closure ) {
			return '{closure}';
		}

		if ( is_object( $value ) ) {
			if ( ! $top && 'stdClass' !== get_class( $value ) ) {
				return '{object ' . get_class( $value ) . '}';
			}
			$value = get_object_vars( $value );
		}

		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				$value[ $key ] = uts_snapshot_export( $item );
			}
		}

		return $value;
	}
}

if ( ! function_exists( 'uts_snapshot_cpt' ) ) {
	/**
	 * Registered `uts` post type and `uts_category` taxonomy, with their
	 * supports, permastructs and the rewrite rules generated for them.
	 *
	 * The rules are generated in memory ($wp_rewrite->rewrite_rules()), not
	 * read from the stored option, so they don't depend on the last flush.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	function uts_snapshot_cpt() {
		global $wp_rewrite;

		$post_type = get_post_type_object( 'uts' );
		$taxonomy  = get_taxonomy( 'uts_category' );

		$rules = array();
		foreach ( (array) $wp_rewrite->rewrite_rules() as $regex => $query ) {
			if ( preg_match( '#^(uts|uts_category)/#', $regex ) || preg_match( '/[?&](uts|uts_category)=|post_type=uts(&|$)/', $query ) ) {
				$rules[ $regex ] = $query;
			}
		}

		$permastructs = array();
		foreach ( array( 'uts', 'uts_category' ) as $name ) {
			$permastructs[ $name ] = isset( $wp_rewrite->extra_permastructs[ $name ] ) ? $wp_rewrite->extra_permastructs[ $name ] : null;
		}

		return uts_snapshot_json(
			array(
				'post_type'     => $post_type ? uts_snapshot_export( $post_type, true ) : null,
				'supports'      => get_all_post_type_supports( 'uts' ),
				'taxonomy'      => $taxonomy ? uts_snapshot_export( $taxonomy, true ) : null,
				'object_taxes'  => get_object_taxonomies( 'uts' ),
				'permastructs'  => $permastructs,
				'rewrite_rules' => $rules,
			)
		);
	}
}

if ( ! function_exists( 'uts_snapshot_plugin_callbacks' ) ) {
	/**
	 * Callbacks on a hook that are defined in this plugin's directory.
	 *
	 * Found by reflection, so the snapshot doesn't depend on function or
	 * method names, and other plugins' and themes' callbacks never run.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param string $hook Hook name.
	 * @return array List of array( priority, callback, accepted_args ), in run order.
	 */
	function uts_snapshot_plugin_callbacks( $hook ) {
		global $wp_filter;

		$found = array();
		if ( empty( $wp_filter[ $hook ] ) ) {
			return $found;
		}

		$dir       = wp_normalize_path( dirname( __DIR__ ) . '/' );
		$callbacks = $wp_filter[ $hook ]->callbacks;
		ksort( $callbacks );
		foreach ( $callbacks as $priority => $items ) {
			foreach ( $items as $item ) {
				$callback = $item['function'];
				try {
					if ( is_array( $callback ) ) {
						$reflection = new ReflectionMethod( $callback[0], $callback[1] );
					} elseif ( is_string( $callback ) && false !== strpos( $callback, '::' ) ) {
						$reflection = new ReflectionMethod( $callback );
					} else {
						$reflection = new ReflectionFunction( $callback );
					}
				} catch ( ReflectionException $e ) {
					continue;
				}
				if ( 0 === strpos( wp_normalize_path( (string) $reflection->getFileName() ), $dir ) ) {
					$found[] = array( $priority, $callback, (int) $item['accepted_args'] );
				}
			}
		}

		return $found;
	}
}

if ( ! function_exists( 'uts_snapshot_run_plugin_hook' ) ) {
	/**
	 * Run only this plugin's callbacks on a hook.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param string $hook Hook name.
	 * @param array  $args Hook arguments.
	 * @return array Priorities and accepted args of the callbacks that ran.
	 */
	function uts_snapshot_run_plugin_hook( $hook, $args = array() ) {
		$ran = array();
		foreach ( uts_snapshot_plugin_callbacks( $hook ) as $callback ) {
			call_user_func_array( $callback[1], array_slice( $args, 0, $callback[2] ) );
			$ran[] = 'priority ' . $callback[0] . ', ' . $callback[2] . ' arg(s)';
		}

		return $ran;
	}
}

if ( ! function_exists( 'uts_snapshot_reset_assets' ) ) {
	/**
	 * Start from fresh script and style registries.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	function uts_snapshot_reset_assets() {
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited -- resetting the registries between cases.
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;
		// phpcs:enable
		wp_scripts();
		wp_styles();
	}
}

if ( ! function_exists( 'uts_snapshot_asset_version' ) ) {
	/**
	 * Describe an asset version without its volatile value.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param mixed $ver Registered version.
	 * @return mixed
	 */
	function uts_snapshot_asset_version( $ver ) {
		if ( ! is_string( $ver ) || '' === $ver ) {
			return $ver;
		}
		if ( UTS_VERSION === $ver ) {
			return '{UTS_VERSION}';
		}
		foreach ( glob( UTS_BUILD . '/*.asset.php' ) as $asset_file ) {
			$asset = include $asset_file;
			if ( isset( $asset['version'] ) && $asset['version'] === $ver ) {
				return '{BUILD_HASH ' . basename( $asset_file, '.asset.php' ) . '}';
			}
		}

		return '{VER}';
	}
}

if ( ! function_exists( 'uts_snapshot_asset_state' ) ) {
	/**
	 * Registered and enqueued state of the plugin's handles.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param array $handles Handles to describe.
	 * @return array
	 */
	function uts_snapshot_asset_state( $handles ) {
		$state = array();
		foreach ( array(
			'script' => wp_scripts(),
			'style'  => wp_styles(),
		) as $type => $registry ) {
			foreach ( $handles as $handle ) {
				if ( ! isset( $registry->registered[ $handle ] ) ) {
					continue;
				}
				$dep    = $registry->registered[ $handle ];
				$extra  = $dep->extra;
				$record = array(
					'src'  => $dep->src,
					'deps' => $dep->deps,
					'ver'  => uts_snapshot_asset_version( $dep->ver ),
					'args' => $dep->args,
				);
				foreach ( array( 'group', 'strategy', 'data', 'before', 'after', 'rtl', 'suffix', 'conditional' ) as $key ) {
					if ( isset( $extra[ $key ] ) ) {
						$record[ $key ] = $extra[ $key ];
					}
				}
				$record['enqueued']              = in_array( $handle, $registry->queue, true );
				$state[ $type . 's' ][ $handle ] = $record;
			}
			$state[ 'enqueued_' . $type . 's' ] = array_values(
				array_filter(
					$registry->queue,
					function ( $handle ) {
						return 0 === strpos( $handle, 'uts' ) || 'react-jsx-runtime' === $handle;
					}
				)
			);
		}

		return $state;
	}
}

if ( ! function_exists( 'uts_snapshot_assets' ) ) {
	/**
	 * Asset registration and conditional enqueueing.
	 *
	 * Frontend cases fake the main query and post in memory; admin cases set
	 * the current screen. Only this plugin's callbacks on the enqueue hooks
	 * run, and the registries are reset before each case.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	function uts_snapshot_assets() {
		require_once ABSPATH . 'wp-admin/includes/admin.php';

		$handles = array( 'uts', 'uts-admin', 'uts-settings', 'react-jsx-runtime' );
		$saved   = array();
		foreach ( array( 'wp_query', 'wp_the_query', 'post', 'current_screen' ) as $name ) {
			$saved[ $name ] = isset( $GLOBALS[ $name ] ) ? $GLOBALS[ $name ] : null;
		}

		$out = array(
			'hooks' => array(
				'wp_enqueue_scripts'    => array(),
				'admin_enqueue_scripts' => array(),
			),
		);

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited -- simulating requests in memory.
		$frontend = array(
			'frontend: singular post with [uts]'    => array( true, 'Intro [uts type="grid"] outro' ),
			'frontend: singular post without [uts]' => array( true, 'No shortcode here.' ),
			'frontend: archive (not singular)'      => array( false, 'Intro [uts] outro' ),
		);
		foreach ( $frontend as $label => $case ) {
			uts_snapshot_reset_assets();
			unset( $GLOBALS['current_screen'] );

			$query                   = new WP_Query();
			$query->is_singular      = $case[0];
			$query->is_single        = $case[0];
			$query->is_archive       = ! $case[0];
			$GLOBALS['wp_query']     = $query;
			$GLOBALS['wp_the_query'] = $query;
			$GLOBALS['post']         = new WP_Post(
				(object) array(
					'ID'           => 0,
					'post_type'    => 'post',
					'post_status'  => 'publish',
					'post_content' => $case[1],
					'filter'       => 'raw',
				)
			);

			$out['hooks']['wp_enqueue_scripts'] = uts_snapshot_run_plugin_hook( 'wp_enqueue_scripts' );
			$out['cases'][ $label ]             = uts_snapshot_asset_state( $handles );
		}

		$admin = array(
			'admin: testimonials list (edit-uts)' => array( 'edit-uts', 'edit.php' ),
			'admin: add testimonial (uts)'        => array( 'uts', 'post-new.php' ),
			'admin: dashboard'                    => array( 'dashboard', 'index.php' ),
			'admin: posts list (edit-post)'       => array( 'edit-post', 'edit.php' ),
		);
		foreach ( $admin as $label => $case ) {
			uts_snapshot_reset_assets();
			$GLOBALS['current_screen'] = WP_Screen::get( $case[0] );

			$out['hooks']['admin_enqueue_scripts'] = uts_snapshot_run_plugin_hook( 'admin_enqueue_scripts', array( $case[1] ) );
			$out['cases'][ $label ]                = uts_snapshot_asset_state( $handles );
		}

		foreach ( $saved as $name => $value ) {
			$GLOBALS[ $name ] = $value;
		}
		// phpcs:enable

		// React build: core's react-jsx-runtime, then the plugin's fallback when core lacks it.
		uts_snapshot_reset_assets();
		$out['react_jsx_runtime']['core_registered'] = wp_script_is( 'react-jsx-runtime', 'registered' );
		uts_register_build( 'uts-settings', 'settings' );
		$out['react_jsx_runtime']['with_core'] = uts_snapshot_asset_state( array( 'uts-settings', 'react-jsx-runtime' ) );

		uts_snapshot_reset_assets();
		wp_deregister_script( 'react-jsx-runtime' );
		uts_register_build( 'uts-settings', 'settings' );
		$out['react_jsx_runtime']['fallback'] = uts_snapshot_asset_state( array( 'uts-settings', 'react-jsx-runtime' ) );

		uts_snapshot_reset_assets();

		return uts_snapshot_json( $out );
	}
}

if ( ! function_exists( 'uts_snapshot_settings_page_data' ) ) {
	/**
	 * The Settings page's inline `window.utsSettingsData` script.
	 *
	 * Runs only this plugin's callbacks on the page's load hook and on
	 * admin_enqueue_scripts, then reads the inline script added before the
	 * `uts-settings` handle.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return array Raw inline script and the decoded data.
	 */
	function uts_snapshot_settings_page_data() {
		require_once ABSPATH . 'wp-admin/includes/admin.php';

		$hook = get_plugin_page_hookname( 'uts-settings', 'edit.php?post_type=uts' );
		if ( ! has_action( 'load-' . $hook ) ) {
			/** This action is documented in wp-admin/includes/menu.php */
			do_action( 'admin_menu', '' );
			$hook = get_plugin_page_hookname( 'uts-settings', 'edit.php?post_type=uts' );
		}

		$saved_screen = isset( $GLOBALS['current_screen'] ) ? $GLOBALS['current_screen'] : null;
		uts_snapshot_reset_assets();
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- simulating the Settings page request.
		$GLOBALS['current_screen'] = WP_Screen::get( $hook );
		uts_snapshot_run_plugin_hook( 'load-' . $hook );
		uts_snapshot_run_plugin_hook( 'admin_enqueue_scripts', array( $hook ) );

		$before = wp_scripts()->get_data( 'uts-settings', 'before' );
		$raw    = is_array( $before ) ? implode( "\n", array_filter( $before ) ) : '';
		$data   = null;
		if ( preg_match( '/^window\.utsSettingsData = (.*);$/s', $raw, $matches ) ) {
			$data = json_decode( $matches[1], true );
		}

		uts_snapshot_reset_assets();
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restoring.
		$GLOBALS['current_screen'] = $saved_screen;

		return array(
			'raw'  => $raw,
			'data' => $data,
		);
	}
}

if ( ! function_exists( 'uts_snapshot_settings' ) ) {
	/**
	 * The `uts_settings` option: schema, defaults, registration, sanitizing,
	 * reading, the `uts_settings_schema` filter and REST schema validation.
	 *
	 * Schema and defaults come from the Settings page data; sanitizing goes
	 * through sanitize_option(); nothing is written to the database.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	function uts_snapshot_settings() {
		$registered = get_registered_settings();
		$setting    = isset( $registered['uts_settings'] ) ? $registered['uts_settings'] : null;
		$out        = array();

		$page                        = uts_snapshot_settings_page_data();
		$out['settings_page_inline'] = $page['raw'];
		$out['schema']               = isset( $page['data']['schema'] ) ? $page['data']['schema'] : null;
		$out['defaults']             = isset( $page['data']['defaults'] ) ? $page['data']['defaults'] : null;

		if ( $setting ) {
			$callback = isset( $setting['sanitize_callback'] ) ? $setting['sanitize_callback'] : null;
			unset( $setting['sanitize_callback'] );
			$out['registered']                      = uts_snapshot_export( $setting );
			$out['registered']['sanitize_callback'] = ( $callback ? 'present' : 'missing' ) . ', ' . ( is_callable( $callback ) ? 'callable' : 'not callable' );
		}

		$fixtures = array(
			'unknown keys'              => array(
				'foo'  => 'bar',
				'type' => 'grid',
			),
			'integers out of range/bad' => array(
				'count'             => '-5',
				'excerpt_length'    => '1000',
				'speed'             => 'abc',
				'coverflow_stretch' => '-200',
				'autoplay_delay'    => '2500.7',
				'grid_columns'      => 0,
			),
			'bad enums'                 => array(
				'type'       => 'carousel',
				'effect'     => 'cube',
				'image_size' => 'huge',
			),
			'boolean strings'           => array(
				'autoplay'         => 'false',
				'loop'             => '0',
				'pagination'       => 'true',
				'navigation'       => '1',
				'show_image'       => '',
				'show_title'       => 'no',
				'show_designation' => 'yes',
			),
			'colors valid'              => array(
				'accent_color'     => '#2271b1',
				'text_color'       => '#FFF',
				'background_color' => '',
			),
			'colors invalid'            => array(
				'accent_color'     => 'red',
				'text_color'       => '#12345',
				'background_color' => '2271b1',
			),
			'string fields with markup' => array(
				'type'         => ' <b>grid</b> ',
				'accent_color' => ' #2271b1 ',
			),
			'non-array string'          => 'slider',
			'non-array null'            => null,
			'non-array integer'         => 42,
			'empty array'               => array(),
			'partial array'             => array(
				'type'  => 'list',
				'count' => 3,
			),
		);

		foreach ( $fixtures as $label => $value ) {
			$out['sanitize'][ $label ] = sanitize_option( 'uts_settings', $value );
		}

		$out['get_settings'] = array(
			'no key'      => uts_get_settings(),
			'key effect'  => uts_get_settings( 'effect' ),
			'unknown key' => uts_get_settings( 'nope' ),
		);

		$filter = function ( $schema ) {
			$schema['snapshot_extra']   = array(
				'type'    => 'integer',
				'minimum' => 0,
				'maximum' => 10,
				'default' => 7,
			);
			$schema['count']['default'] = 5;
			$schema['effect']['enum'][] = 'cube';
			return $schema;
		};
		add_filter( 'uts_settings_schema', $filter );
		$page                                      = uts_snapshot_settings_page_data();
		$out['with_schema_filter']['schema']       = isset( $page['data']['schema'] ) ? $page['data']['schema'] : null;
		$out['with_schema_filter']['defaults']     = isset( $page['data']['defaults'] ) ? $page['data']['defaults'] : null;
		$out['with_schema_filter']['sanitize']     = array(
			'empty array' => sanitize_option( 'uts_settings', array() ),
			'extra/cube'  => sanitize_option(
				'uts_settings',
				array(
					'snapshot_extra' => '99',
					'effect'         => 'cube',
				)
			),
		);
		$out['with_schema_filter']['get_settings'] = uts_get_settings();
		remove_filter( 'uts_settings_schema', $filter );
		$out['with_schema_filter']['removed'] = ! has_filter( 'uts_settings_schema', $filter );

		$rest_schema = isset( $setting['show_in_rest']['schema'] ) ? $setting['show_in_rest']['schema'] : array();
		$valid       = is_array( $out['defaults'] ) ? $out['defaults'] : array();
		$invalid     = array_merge(
			$valid,
			array(
				'count' => 1000,
				'type'  => 'carousel',
				'foo'   => 'bar',
			)
		);
		foreach ( array(
			'valid'   => $valid,
			'invalid' => $invalid,
		) as $label => $payload ) {
			$result                                   = rest_validate_value_from_schema( $payload, $rest_schema, 'uts_settings' );
			$out['rest_schema'][ $label ]['validate'] = is_wp_error( $result )
				? array(
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message(),
					'data'    => $result->get_error_data(),
				)
				: $result;
			$out['rest_schema'][ $label ]['sanitize'] = rest_sanitize_value_from_schema( $payload, $rest_schema, 'uts_settings' );
		}

		return uts_snapshot_json( $out );
	}
}

if ( ! function_exists( 'uts_snapshot_shortcode_matrix' ) ) {
	/**
	 * Shortcode output for a fixed list of attribute combinations, filters,
	 * multiple instances and the uts_shortcode() function.
	 *
	 * Runs last, so the shared instance counter seen by the other captures
	 * is unchanged. Instance IDs are kept (not normalized) here, so the
	 * counter itself is part of the snapshot; the cases always run in the
	 * order listed in `run_order`.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	function uts_snapshot_shortcode_matrix() {
		$cases = array(
			'type list'                 => '[uts type="list"]',
			'category review'           => '[uts category="review"]',
			'count 2'                   => '[uts count="2"]',
			'colors'                    => '[uts accent_color="#ff0000" text_color="#333333" background_color="#f0f0f0"]',
			'border radius'             => '[uts border_radius="12"]',
			'grid 4 columns'            => '[uts type="grid" grid_columns="4"]',
			'grid 1 column'             => '[uts type="grid" grid_columns="1"]',
			'autoplay 5000'             => '[uts autoplay="true" autoplay_delay="5000"]',
			'loop'                      => '[uts loop="true"]',
			'effect cards'              => '[uts effect="cards"]',
			'effect slide'              => '[uts effect="slide"]',
			'no pagination'             => '[uts pagination="false"]',
			'no image'                  => '[uts show_image="false"]',
			'no title'                  => '[uts show_title="false"]',
			'no designation'            => '[uts show_designation="false"]',
			'full text'                 => '[uts excerpt_length="0"]',
			'thumbnail image'           => '[uts image_size="thumbnail"]',
			'empty attribute'           => '[uts count=""]',
			'invalid values'            => '[uts type="bogus" speed="99999"]',
			'uts_slider_config filter'  => '[uts]',
			'shortcode_atts_uts filter' => '[uts]',
			'three instances'           => '[uts type="list"][uts type="list"][uts type="list"]',
			'mixed do_shortcode/direct' => null,
			'category with no match'    => '[uts category="snapshot-no-such-category"]',
		);

		$config_filter = function ( $config, $settings ) {
			$config['snapshot'] = true;
			$config['speed']    = $settings['speed'] + 1;
			return $config;
		};
		$atts_filter   = function ( $out ) {
			$out['type']  = 'grid';
			$out['count'] = 1;
			return $out;
		};

		$out = array(
			'run_order' => array_keys( $cases ),
			'cases'     => array(),
		);
		foreach ( $cases as $label => $shortcode ) {
			if ( 'uts_slider_config filter' === $label ) {
				add_filter( 'uts_slider_config', $config_filter, 10, 2 );
			}
			if ( 'shortcode_atts_uts filter' === $label ) {
				add_filter( 'shortcode_atts_uts', $atts_filter );
			}

			if ( null === $shortcode ) {
				$shortcode = '[uts] + uts_shortcode( array() ) + [uts]';
				$html      = do_shortcode( '[uts]' ) . uts_shortcode( array() ) . do_shortcode( '[uts]' );
			} else {
				$html = do_shortcode( $shortcode );
			}

			remove_filter( 'uts_slider_config', $config_filter, 10 );
			remove_filter( 'shortcode_atts_uts', $atts_filter );

			preg_match_all( '/<section id="([^"]+)"/', $html, $ids );
			$out['cases'][ $label ] = array(
				'shortcode' => $shortcode,
				'ids'       => $ids[1],
				'html'      => uts_snapshot_normalize( $html, true, true ),
			);
		}

		$out['filters_removed'] = ! has_filter( 'uts_slider_config', $config_filter ) && ! has_filter( 'shortcode_atts_uts', $atts_filter );

		return uts_snapshot_json( $out );
	}
}

if ( ! function_exists( 'uts_snapshot_metabox' ) ) {
	/**
	 * "Client Additional" metabox: registration, form output and save behavior.
	 *
	 * Saves run only this plugin's `save_post_uts`/`save_post` callbacks with a
	 * faked $_POST. `update_post_metadata` is short-circuited, so the values
	 * that would be stored are recorded and nothing is written.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	function uts_snapshot_metabox() {
		global $wp_meta_boxes;

		uts_snapshot_load_admin( 'uts' );

		$posts = get_posts(
			array(
				'post_type'      => 'uts',
				'post_status'    => 'any',
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'posts_per_page' => 1,
				'meta_key'       => 'uts_designation', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_compare'   => 'EXISTS',
			)
		);
		$with    = $posts ? $posts[0] : null;
		$without = new WP_Post(
			(object) array(
				'ID'        => 0,
				'post_type' => 'uts',
			)
		);

		// Registration.
		$saved_boxes = $wp_meta_boxes;
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- isolating the registry.
		$wp_meta_boxes = array();
		$ran_add       = uts_snapshot_run_plugin_hook( 'add_meta_boxes', array( 'uts', $with ? $with : $without ) );

		$boxes = array();
		foreach ( (array) $wp_meta_boxes as $screen => $contexts ) {
			foreach ( $contexts as $context => $priorities ) {
				foreach ( $priorities as $priority => $items ) {
					foreach ( $items as $id => $box ) {
						if ( ! $box ) {
							continue;
						}
						$boxes[ $id ] = array(
							'screen'   => $screen,
							'context'  => $context,
							'priority' => $priority,
							'title'    => $box['title'],
							'args'     => $box['args'],
							'callback' => $box['callback'],
						);
					}
				}
			}
		}
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restoring the registry.
		$wp_meta_boxes = $saved_boxes;

		// Form output.
		$forms = array();
		foreach ( $boxes as $id => $box ) {
			foreach ( array(
				'with designation'    => $with,
				'without designation' => $without,
			) as $case => $post ) {
				if ( ! $post ) {
					continue;
				}
				ob_start();
				call_user_func( $box['callback'], $post, array( 'id' => $id, 'args' => $box['args'] ) );
				$forms[ $id ][ $case ] = uts_snapshot_normalize( ob_get_clean(), true );
			}
			$boxes[ $id ]['callback'] = is_callable( $box['callback'] ) ? 'callable' : 'not callable';
		}

		// Save behavior.
		$recorded = array();
		$record   = function ( $check, $object_id, $meta_key, $meta_value ) use ( &$recorded ) {
			$recorded[] = array(
				'post'  => $object_id ? 'post' : 'none',
				'key'   => $meta_key,
				'value' => $meta_value,
			);
			return true;
		};
		$deny     = function ( $caps, $cap ) {
			return 'edit_post' === $cap ? array( 'do_not_allow' ) : $caps;
		};
		add_filter( 'update_post_metadata', $record, 1, 4 );
		add_filter( 'add_post_metadata', $record, 1, 4 );

		$post_id = $with ? $with->ID : 0;
		$post    = $with ? $with : $without;
		$nonce   = wp_create_nonce( 'uts_metabox_nonce' );
		$value   = wp_slash( "  CEO <b>Acme</b> O'Brien \t" );
		$cases   = array(
			'no nonce'                    => array( array( 'uts_designation' => 'X' ), false ),
			'bad nonce'                   => array(
				array(
					'uts_metabox_nonce_field' => 'bad',
					'uts_designation'         => 'X',
				),
				false,
			),
			'valid nonce, no field'       => array( array( 'uts_metabox_nonce_field' => $nonce ), false ),
			'valid nonce, value cleaned'  => array(
				array(
					'uts_metabox_nonce_field' => $nonce,
					'uts_designation'         => $value,
				),
				false,
			),
			'valid nonce, cannot edit'    => array(
				array(
					'uts_metabox_nonce_field' => $nonce,
					'uts_designation'         => $value,
				),
				true,
			),
		);

		$saved_post = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$saves      = array();
		foreach ( $cases as $case => $setup ) {
			$_POST    = $setup[0];
			$recorded = array();
			if ( $setup[1] ) {
				add_filter( 'map_meta_cap', $deny, 10, 2 );
			}
			foreach ( array( 'save_post_uts', 'save_post' ) as $hook ) {
				uts_snapshot_run_plugin_hook( $hook, array( $post_id, $post, true ) );
			}
			if ( $setup[1] ) {
				remove_filter( 'map_meta_cap', $deny, 10 );
			}
			$saves[ $case ] = $recorded;
		}
		$_POST = $saved_post;

		remove_filter( 'update_post_metadata', $record, 1 );
		remove_filter( 'add_post_metadata', $record, 1 );

		return uts_snapshot_json(
			array(
				'add_meta_boxes_callbacks' => $ran_add,
				'save_hooks'               => array(
					'save_post_uts' => count( uts_snapshot_plugin_callbacks( 'save_post_uts' ) ),
					'save_post'     => count( uts_snapshot_plugin_callbacks( 'save_post' ) ),
				),
				'boxes'                    => $boxes,
				'forms'                    => $forms,
				'saves'                    => $saves,
			)
		);
	}
}

if ( ! function_exists( 'uts_snapshot_menu' ) ) {
	/**
	 * Admin menu: submenu entries, page hooks, the Settings page output and
	 * the activation redirect.
	 *
	 * Runs only this plugin's `admin_menu` callbacks on an empty submenu
	 * registry. The redirect flag is faked with a `pre_option` filter, so no
	 * option is written (deleting a missing option writes nothing).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	function uts_snapshot_menu() {
		global $submenu;

		uts_snapshot_load_admin( 'dashboard' );

		$parent        = 'edit.php?post_type=uts';
		$saved_submenu = $submenu;
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- isolating the registry.
		$submenu = array();
		$ran     = uts_snapshot_run_plugin_hook( 'admin_menu' );
		$entries = isset( $submenu[ $parent ] ) ? array_values( $submenu[ $parent ] ) : array();
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restoring the registry.
		$submenu = $saved_submenu;

		$pages = array();
		foreach ( $entries as $entry ) {
			$hook             = get_plugin_page_hookname( $entry[2], $parent );
			$pages[ $entry[2] ] = array(
				'menu_title'        => $entry[0],
				'capability'        => $entry[1],
				'page_title'        => isset( $entry[3] ) ? $entry[3] : null,
				'hook'              => $hook,
				'page_callbacks'    => count( uts_snapshot_plugin_callbacks( $hook ) ),
				'load_callbacks'    => count( uts_snapshot_plugin_callbacks( 'load-' . $hook ) ),
			);
		}

		// Settings page body (shared header + React mount point).
		$settings_html = '';
		if ( isset( $pages['uts-settings'] ) ) {
			ob_start();
			uts_snapshot_run_plugin_hook( $pages['uts-settings']['hook'] );
			$settings_html = uts_snapshot_normalize( ob_get_clean(), true );
		}

		// Activation redirect: flag set, but WP-CLI must not redirect.
		$flag     = function () {
			return '1';
		};
		add_filter( 'pre_option_uts_activation_redirect', $flag );
		$redirect = uts_snapshot_run_plugin_hook( 'admin_init' );
		remove_filter( 'pre_option_uts_activation_redirect', $flag );

		return uts_snapshot_json(
			array(
				'admin_menu_callbacks' => $ran,
				'pages'                => $pages,
				'settings_page_html'   => $settings_html,
				'admin_init_callbacks' => $redirect,
			)
		);
	}
}

if ( ! function_exists( 'uts_snapshot_capture' ) ) {
	/**
	 * Capture every snapshot, normalized.
	 *
	 * Order matters (shortcode instance counters, image loading attributes),
	 * so keep it fixed.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return array File name => normalized output.
	 */
	function uts_snapshot_capture() {
		wp_set_current_user( 0 );
		$files = uts_snapshot_shortcodes();

		wp_set_current_user( uts_snapshot_admin_user() );
		$files['rest-settings.json']         = uts_snapshot_rest_settings();
		$files['admin-columns.json']         = uts_snapshot_columns();
		$files['admin-getting-started.html'] = uts_snapshot_getting_started();
		$files['metabox.json']               = uts_snapshot_metabox();
		$files['menu.json']                  = uts_snapshot_menu();
		$files['cpt.json']                   = uts_snapshot_cpt();
		$files['assets.json']                = uts_snapshot_assets();
		$files['settings.json']              = uts_snapshot_settings();
		// Last: it advances the shared shortcode instance counter.
		$files['shortcode-matrix.json'] = uts_snapshot_shortcode_matrix();

		foreach ( $files as $file => $output ) {
			$files[ $file ] = uts_snapshot_normalize( $output, (bool) preg_match( '/\.html$/', $file ), 'shortcode-matrix.json' === $file );
		}

		return $files;
	}
}

if ( ! function_exists( 'uts_snapshot_diff' ) ) {
	/**
	 * Line diff (LCS) with two lines of context.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param string $expected Expected text.
	 * @param string $actual   Actual text.
	 * @return string
	 */
	function uts_snapshot_diff( $expected, $actual ) {
		$a = explode( "\n", $expected );
		$b = explode( "\n", $actual );
		$n = count( $a );
		$m = count( $b );

		// Skip the common prefix and suffix to keep the LCS table small.
		$start = 0;
		while ( $start < $n && $start < $m && $a[ $start ] === $b[ $start ] ) {
			++$start;
		}
		$end_a = $n;
		$end_b = $m;
		while ( $end_a > $start && $end_b > $start && $a[ $end_a - 1 ] === $b[ $end_b - 1 ] ) {
			--$end_a;
			--$end_b;
		}

		$x   = array_slice( $a, $start, $end_a - $start );
		$y   = array_slice( $b, $start, $end_b - $start );
		$nx  = count( $x );
		$ny  = count( $y );
		$len = array();
		for ( $i = $nx; $i >= 0; $i-- ) {
			for ( $j = $ny; $j >= 0; $j-- ) {
				if ( $nx === $i || $ny === $j ) {
					$len[ $i ][ $j ] = 0;
				} elseif ( $x[ $i ] === $y[ $j ] ) {
					$len[ $i ][ $j ] = $len[ $i + 1 ][ $j + 1 ] + 1;
				} else {
					$len[ $i ][ $j ] = max( $len[ $i + 1 ][ $j ], $len[ $i ][ $j + 1 ] );
				}
			}
		}

		// Edit script: array( op, line number in expected/actual, text ).
		$ops = array();
		for ( $k = max( 0, $start - 2 ); $k < $start; $k++ ) {
			$ops[] = array( ' ', $k + 1, $a[ $k ] );
		}
		$i = 0;
		$j = 0;
		while ( $i < $nx || $j < $ny ) {
			if ( $i < $nx && $j < $ny && $x[ $i ] === $y[ $j ] ) {
				$ops[] = array( ' ', $start + $i + 1, $x[ $i ] );
				++$i;
				++$j;
			} elseif ( $i < $nx && ( $ny === $j || $len[ $i + 1 ][ $j ] >= $len[ $i ][ $j + 1 ] ) ) {
				$ops[] = array( '-', $start + $i + 1, $x[ $i ] );
				++$i;
			} else {
				$ops[] = array( '+', $start + $j + 1, $y[ $j ] );
				++$j;
			}
		}
		$tail = min( $n, $end_a + 2 );
		for ( $k = $end_a; $k < $tail; $k++ ) {
			$ops[] = array( ' ', $k + 1, $a[ $k ] );
		}

		// Print changes with two lines of context around each.
		$keep  = array();
		$total = count( $ops );
		foreach ( $ops as $index => $op ) {
			if ( ' ' !== $op[0] ) {
				$last_context = min( $total - 1, $index + 2 );
				for ( $k = max( 0, $index - 2 ); $k <= $last_context; $k++ ) {
					$keep[ $k ] = true;
				}
			}
		}

		$out  = '';
		$last = -1;
		foreach ( $ops as $index => $op ) {
			if ( ! isset( $keep[ $index ] ) ) {
				continue;
			}
			if ( $last >= 0 && $index > $last + 1 ) {
				$out .= "  ...\n";
			}
			$out .= sprintf( "%s %5d | %s\n", $op[0], $op[1], $op[2] );
			$last = $index;
		}

		return $out;
	}
}

if ( ! function_exists( 'uts_snapshot_run' ) ) {
	/**
	 * Entry point: write the reference snapshots, or compare against them.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param array $cli_args Positional arguments passed to `wp eval-file`.
	 */
	function uts_snapshot_run( $cli_args ) {
		$compare = in_array( 'compare', (array) $cli_args, true );
		$dir     = uts_snapshot_dir();
		$only    = array();
		foreach ( (array) $cli_args as $cli_arg ) {
			if ( 0 === strpos( $cli_arg, 'only=' ) ) {
				$only = array_filter( explode( ',', substr( $cli_arg, 5 ) ) );
			}
		}

		uts_snapshot_start_watch();
		$files  = uts_snapshot_capture();
		$writes = uts_snapshot_watch_writes();
		foreach ( $writes as $write ) {
			WP_CLI::warning( 'Site data was written while capturing: ' . $write );
		}

		if ( ! $compare ) {
			if ( $only ) {
				$unknown = array_diff( $only, array_keys( $files ) );
				if ( $unknown ) {
					WP_CLI::error( 'Unknown snapshot: ' . implode( ', ', $unknown ) );
				}
				$files = array_intersect_key( $files, array_flip( $only ) );
			}
			if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
				WP_CLI::error( 'Could not create ' . $dir );
			}
			foreach ( $files as $file => $output ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- CLI dev tool writing into the plugin's tests directory.
				if ( false === file_put_contents( $dir . '/' . $file, $output ) ) {
					WP_CLI::error( 'Could not write ' . $file );
				}
				WP_CLI::log( 'Wrote tests/snapshots/' . $file );
			}
			WP_CLI::success( count( $files ) . ' snapshots written.' );
			return;
		}

		$failed = 0;
		foreach ( $files as $file => $output ) {
			$path = $dir . '/' . $file;
			if ( ! file_exists( $path ) ) {
				WP_CLI::log( 'MISSING ' . $file . ' (run without "compare" to create it)' );
				++$failed;
				continue;
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file.
			$expected = file_get_contents( $path );
			if ( $expected === $output ) {
				WP_CLI::log( 'OK      ' . $file );
				continue;
			}

			++$failed;
			WP_CLI::log( 'DIFF    ' . $file . ' (- reference, + current)' );
			WP_CLI::log( uts_snapshot_diff( $expected, $output ) );
		}

		if ( $failed > 0 ) {
			WP_CLI::log( $failed . ' of ' . count( $files ) . ' snapshots differ.' );
			WP_CLI::halt( 1 );
		}

		WP_CLI::success( 'All ' . count( $files ) . ' snapshots match.' );
	}
}

// `wp eval-file` exposes the positional arguments after the file as $args.
uts_snapshot_run( isset( $args ) ? $args : array() );
