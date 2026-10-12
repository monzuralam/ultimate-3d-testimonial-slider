<?php
/**
 * Saved shortcode instances: the `{prefix}uts_shortcodes` table.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * Creates and upgrades the `{prefix}uts_shortcodes` table and reads/writes
 * saved shortcode instances (`[uts id="5"]`).
 *
 * An instance stores only the settings it changes (plus `category`), so it
 * follows later changes to the global settings. Single items are cached in
 * the object cache (group `uts`); every write invalidates them.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class DB {

	/**
	 * Schema version. Bump it when the table definition changes.
	 *
	 * @var string
	 */
	const VERSION = '1.0';

	/**
	 * Option holding the installed schema version.
	 *
	 * @var string
	 */
	const VERSION_OPTION = 'uts_db_version';

	/**
	 * Object cache group.
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'uts';

	/**
	 * Statuses an instance can have.
	 *
	 * @var string[]
	 */
	private static $statuses = array( 'publish', 'draft', 'trash' );

	/**
	 * Hook the upgrade check.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register() {
		add_action( 'plugins_loaded', array( $this, 'maybe_upgrade' ) );
	}

	/**
	 * Create or update the table when the stored schema version is older.
	 *
	 * Activation hooks don't run on plugin updates, and on multisite each
	 * site has its own table and version option, so the check runs on every
	 * load (one autoloaded option read).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function maybe_upgrade() {
		if ( version_compare( (string) get_option( self::VERSION_OPTION, '0' ), self::VERSION, '<' ) ) {
			self::install();
		}
	}

	/**
	 * Full table name for the current site.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;

		return $wpdb->prefix . 'uts_shortcodes';
	}

	/**
	 * Create or update the table with dbDelta() and store the schema version.
	 *
	 * dbDelta() rules: one column per line, two spaces after PRIMARY KEY,
	 * KEY (not INDEX), no backticks.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table();
		$collate = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
title varchar(191) NOT NULL DEFAULT '',
settings longtext NOT NULL,
status varchar(20) NOT NULL DEFAULT 'publish',
author_id bigint(20) unsigned NOT NULL DEFAULT 0,
created_at datetime NOT NULL,
updated_at datetime NOT NULL,
PRIMARY KEY  (id),
KEY status (status)
) {$collate};"
		);

		update_option( self::VERSION_OPTION, self::VERSION );
	}

	/**
	 * Get one instance.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param int $id Instance ID.
	 * @return array|null Instance (see prepare_item()), or null if not found.
	 */
	public static function get( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return null;
		}

		$found = false;
		$item  = wp_cache_get( 'shortcode_' . $id, self::CACHE_GROUP, false, $found );
		if ( $found ) {
			return $item;
		}

		$table = self::table();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$item  = $row ? self::prepare_item( $row ) : null;

		wp_cache_set( 'shortcode_' . $id, $item, self::CACHE_GROUP );

		return $item;
	}

	/**
	 * List instances.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param array $args {
	 *     Optional.
	 *
	 *     @type string $status   publish, draft, trash, or 'any' (everything except trash). Default 'any'.
	 *     @type string $search   Search in titles. Default ''.
	 *     @type int    $per_page 1-100. Default 20.
	 *     @type int    $page     Default 1.
	 *     @type string $orderby  id, title, created_at or updated_at. Default 'updated_at'.
	 *     @type string $order    ASC or DESC. Default 'DESC'.
	 * }
	 * @return array { @type array[] $items Instances. @type int $total Matching rows. }
	 */
	public static function query( $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'   => 'any',
				'search'   => '',
				'per_page' => 20,
				'page'     => 1,
				'orderby'  => 'updated_at',
				'order'    => 'DESC',
			)
		);

		$where  = array();
		$values = array();

		if ( in_array( $args['status'], self::$statuses, true ) ) {
			$where[]  = 'status = %s';
			$values[] = $args['status'];
		} else {
			$where[]  = 'status <> %s';
			$values[] = 'trash';
		}

		$search = trim( (string) $args['search'] );
		if ( '' !== $search ) {
			$where[]  = 'title LIKE %s';
			$values[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		$orderby  = in_array( $args['orderby'], array( 'id', 'title', 'created_at', 'updated_at' ), true ) ? $args['orderby'] : 'updated_at';
		$order    = 'ASC' === strtoupper( (string) $args['order'] ) ? 'ASC' : 'DESC';
		$per_page = min( 100, max( 1, (int) $args['per_page'] ) );
		$offset   = ( max( 1, (int) $args['page'] ) - 1 ) * $per_page;

		$table     = self::table();
		$where_sql = implode( ' AND ', $where );

		// Every condition has a placeholder, so $values is never empty and both queries are prepared.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders, WordPress.DB.DirectDatabaseQuery -- table, column and direction are whitelisted above.
		$total = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}", $values )
		);

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order}, id DESC LIMIT %d OFFSET %d",
				array_merge( $values, array( $per_page, $offset ) )
			),
			ARRAY_A
		);
		// phpcs:enable

		return array(
			'items' => array_map( array( __CLASS__, 'prepare_item' ), (array) $rows ),
			'total' => $total,
		);
	}

	/**
	 * Number of instances per status.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return array Status => count (all statuses present, 0 if none).
	 */
	public static function count() {
		global $wpdb;

		$table  = self::table();
		$counts = array_fill_keys( self::$statuses, 0 );
		$rows   = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery

		foreach ( (array) $rows as $row ) {
			if ( isset( $counts[ $row['status'] ] ) ) {
				$counts[ $row['status'] ] = (int) $row['total'];
			}
		}

		return $counts;
	}

	/**
	 * Default name of an instance: "UTS #{id}".
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param int $id Instance ID.
	 * @return string
	 */
	public static function default_title( $id ) {
		/* translators: %d: saved slider ID (the same number as in its [uts id] shortcode) */
		return sprintf( __( 'UTS #%d', 'ultimate-3d-testimonial-slider' ), (int) $id );
	}

	/**
	 * Best guess of the next instance ID, used to pre-fill the default name.
	 * The real name is set from the real ID on save.
	 *
	 * Uses the table's AUTO_INCREMENT (IDs of permanently deleted rows are
	 * not reused), or the highest ID + 1 when that is higher (MySQL 8 may
	 * report a cached, older AUTO_INCREMENT).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return int
	 */
	public static function next_id() {
		global $wpdb;

		$table = self::table();

		$max  = 1 + (int) $wpdb->get_var( "SELECT MAX(id) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$auto = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s', $table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		return max( $max, $auto );
	}

	/**
	 * Create an instance.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param array $data { @type string $title, @type array $settings, @type string $status (publish|draft). }
	 * @return int|\WP_Error New instance ID.
	 */
	public static function insert( $data ) {
		global $wpdb;

		$data = is_array( $data ) ? $data : array();
		$now  = current_time( 'mysql', true );
		$row  = array(
			'title'      => isset( $data['title'] ) ? self::sanitize_title( $data['title'] ) : '',
			'settings'   => wp_json_encode( self::sanitize_settings( isset( $data['settings'] ) ? $data['settings'] : array() ) ),
			'status'     => isset( $data['status'] ) && in_array( $data['status'], array( 'publish', 'draft' ), true ) ? $data['status'] : 'publish',
			'author_id'  => get_current_user_id(),
			'created_at' => $now,
			'updated_at' => $now,
		);

		$inserted = $wpdb->insert( self::table(), $row, array( '%s', '%s', '%s', '%d', '%s', '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( false === $inserted ) {
			return new \WP_Error( 'uts_db_insert', __( 'The slider could not be saved.', 'ultimate-3d-testimonial-slider' ) );
		}

		$id = (int) $wpdb->insert_id;

		// No name given: "UTS #{id}", so the name matches its [uts id] shortcode.
		if ( '' === $row['title'] ) {
			$wpdb->update( self::table(), array( 'title' => self::default_title( $id ) ), array( 'id' => $id ), array( '%s' ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		return $id;
	}

	/**
	 * Update an instance. Only the given fields change.
	 *
	 * `settings` replaces the stored settings (send every changed option).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param int   $id   Instance ID.
	 * @param array $data Any of title, settings, status (publish|draft|trash).
	 * @return true|\WP_Error
	 */
	public static function update( $id, $data ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! self::get( $id ) ) {
			return new \WP_Error( 'uts_not_found', __( 'Slider not found.', 'ultimate-3d-testimonial-slider' ), array( 'status' => 404 ) );
		}

		$data    = is_array( $data ) ? $data : array();
		$row     = array();
		$formats = array();

		if ( array_key_exists( 'title', $data ) ) {
			$title = self::sanitize_title( $data['title'] );
			// An emptied name falls back to "UTS #{id}".
			$row['title'] = '' === $title ? self::default_title( $id ) : $title;
			$formats[]    = '%s';
		}

		if ( array_key_exists( 'settings', $data ) ) {
			$row['settings'] = wp_json_encode( self::sanitize_settings( $data['settings'] ) );
			$formats[]       = '%s';
		}

		if ( array_key_exists( 'status', $data ) ) {
			if ( ! in_array( $data['status'], self::$statuses, true ) ) {
				return new \WP_Error( 'uts_invalid_status', __( 'Invalid status.', 'ultimate-3d-testimonial-slider' ), array( 'status' => 400 ) );
			}
			$row['status'] = $data['status'];
			$formats[]     = '%s';
		}

		$row['updated_at'] = current_time( 'mysql', true );
		$formats[]         = '%s';

		$updated = $wpdb->update( self::table(), $row, array( 'id' => $id ), $formats, array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		self::flush( $id );

		if ( false === $updated ) {
			return new \WP_Error( 'uts_db_update', __( 'The slider could not be saved.', 'ultimate-3d-testimonial-slider' ) );
		}

		return true;
	}

	/**
	 * Move an instance to the trash, or delete it permanently.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param int  $id    Instance ID.
	 * @param bool $force Delete permanently instead of trashing. Default false.
	 * @return true|\WP_Error
	 */
	public static function delete( $id, $force = false ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! self::get( $id ) ) {
			return new \WP_Error( 'uts_not_found', __( 'Slider not found.', 'ultimate-3d-testimonial-slider' ), array( 'status' => 404 ) );
		}

		if ( ! $force ) {
			return self::update( $id, array( 'status' => 'trash' ) );
		}

		$deleted = $wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		self::flush( $id );

		if ( ! $deleted ) {
			return new \WP_Error( 'uts_db_delete', __( 'The slider could not be deleted.', 'ultimate-3d-testimonial-slider' ) );
		}

		return true;
	}

	/**
	 * Copy an instance. The copy is a draft titled "… (copy)".
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param int $id Instance ID.
	 * @return int|\WP_Error New instance ID.
	 */
	public static function duplicate( $id ) {
		$item = self::get( $id );
		if ( ! $item ) {
			return new \WP_Error( 'uts_not_found', __( 'Slider not found.', 'ultimate-3d-testimonial-slider' ), array( 'status' => 404 ) );
		}

		return self::insert(
			array(
				/* translators: %s: slider title */
				'title'    => sprintf( __( '%s (copy)', 'ultimate-3d-testimonial-slider' ), $item['title'] ),
				'settings' => $item['settings'],
				'status'   => 'draft',
			)
		);
	}

	/**
	 * Clean instance settings: only the keys that were sent, each sanitized
	 * by the settings schema; unknown keys are dropped. `category` must be an
	 * existing `uts_category` slug, otherwise it is dropped.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param mixed $settings Raw settings.
	 * @return array
	 */
	public static function sanitize_settings( $settings ) {
		$settings = is_array( $settings ) ? $settings : array();
		$clean    = Settings::sanitize( $settings, true );

		if ( isset( $settings['category'] ) && is_scalar( $settings['category'] ) ) {
			$slug = sanitize_title( (string) $settings['category'] );
			if ( '' !== $slug && term_exists( $slug, 'uts_category' ) ) {
				$clean['category'] = $slug;
			}
		}

		return $clean;
	}

	/**
	 * Turn a database row into an instance array.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param array $row Database row.
	 * @return array {
	 *     @type int    $id
	 *     @type string $title
	 *     @type array  $settings   Only the options this instance changes, plus `category`.
	 *     @type string $status
	 *     @type int    $author_id
	 *     @type string $created_at GMT, Y-m-d H:i:s.
	 *     @type string $updated_at GMT, Y-m-d H:i:s.
	 * }
	 */
	public static function prepare_item( $row ) {
		$settings = json_decode( (string) $row['settings'], true );

		return array(
			'id'         => (int) $row['id'],
			'title'      => (string) $row['title'],
			'settings'   => is_array( $settings ) ? $settings : array(),
			'status'     => (string) $row['status'],
			'author_id'  => (int) $row['author_id'],
			'created_at' => (string) $row['created_at'],
			'updated_at' => (string) $row['updated_at'],
		);
	}

	/**
	 * Sanitize a title.
	 *
	 * @param mixed $title Raw title.
	 * @return string At most 191 characters.
	 */
	private static function sanitize_title( $title ) {
		$title = is_scalar( $title ) ? sanitize_text_field( (string) $title ) : '';

		return function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 191 ) : substr( $title, 0, 191 );
	}

	/**
	 * Drop an instance from the object cache.
	 *
	 * @param int $id Instance ID.
	 */
	private static function flush( $id ) {
		wp_cache_delete( 'shortcode_' . $id, self::CACHE_GROUP );
	}
}
