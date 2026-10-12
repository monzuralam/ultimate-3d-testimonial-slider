<?php
/**
 * Admin menu: the plugin's pages (one React app) and the activation redirect.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Getting Started, Shortcode Builder and Settings pages under
 * Testimonials. All three mount the same React app (src/admin-app), which
 * reads `?page=` to pick its screen and switches between them without
 * reloading. Also opens Getting Started once after activation.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class Menu {

	/**
	 * Parent menu slug (the `uts` post type menu).
	 *
	 * @var string
	 */
	const PARENT_SLUG = 'edit.php?post_type=uts';

	/**
	 * Script/style handle of the admin app.
	 *
	 * @var string
	 */
	const APP_HANDLE = 'uts-admin-app';

	/**
	 * Hook the pages and the activation redirect.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_pages' ) );
		add_action( 'admin_init', array( $this, 'activation_redirect' ) );
	}

	/**
	 * The app's pages: slug => title, menu title, capability.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return array
	 */
	public static function pages() {
		return array(
			'uts-getting-started' => array(
				'title'      => __( 'Getting Started', 'uts' ),
				'menu_title' => __( 'Getting Started', 'uts' ),
				'capability' => 'edit_posts',
			),
			'uts-builder'         => array(
				'title'      => __( 'Shortcode Builder', 'uts' ),
				'menu_title' => __( 'Shortcode Builder', 'uts' ),
				'capability' => REST::builder_capability(),
			),
			'uts-settings'        => array(
				'title'      => __( 'Testimonial Settings', 'uts' ),
				'menu_title' => __( 'Settings', 'uts' ),
				'capability' => 'manage_options',
			),
		);
	}

	/**
	 * Register the plugin's admin pages under Testimonials.
	 *
	 * @since 1.0.1
	 * @author Monzur Alam
	 */
	public function add_pages() {
		foreach ( self::pages() as $slug => $page ) {
			$hook = add_submenu_page(
				self::PARENT_SLUG,
				$page['title'],
				$page['menu_title'],
				$page['capability'],
				$slug,
				array( $this, 'render_app' )
			);

			add_action( 'load-' . $hook, array( $this, 'load_app' ) );
		}
	}

	/**
	 * Open Getting Started once after the plugin is activated.
	 *
	 * Skipped for bulk/network activation, AJAX, CLI and users who can't see the page.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function activation_redirect() {
		if ( ! get_option( 'uts_activation_redirect' ) ) {
			return;
		}

		delete_option( 'uts_activation_redirect' );

		if (
			wp_doing_ajax()
			|| ( defined( 'WP_CLI' ) && WP_CLI )
			|| is_network_admin()
			|| isset( $_GET['activate-multi'] ) // phpcs:ignore WordPress.Security.NonceVerification
			|| ! current_user_can( 'edit_posts' )
		) {
			return;
		}

		wp_safe_redirect( admin_url( 'edit.php?post_type=uts&page=uts-getting-started' ) );
		exit;
	}

	/**
	 * Page load: enqueue the app.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function load_app() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_app' ) );
	}

	/**
	 * Page callback: the React app mounts here.
	 *
	 * The `.wp-header-end` marker keeps admin notices above the app.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function render_app() {
		?>
		<hr class="wp-header-end uts:hidden">
		<div id="uts-admin-root" class="uts-admin-root"></div>
		<noscript>
			<div class="notice notice-error"><p><?php esc_html_e( 'Ultimate 3D Testimonial Slider needs JavaScript for this page. Please enable it in your browser.', 'uts' ); ?></p></div>
		</noscript>
		<?php
	}

	/**
	 * Enqueue the admin app, its boot data and the frontend build (for previews).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function enqueue_app() {
		if ( ! uts_register_build( self::APP_HANDLE, 'admin-app' ) ) {
			return;
		}

		if ( uts_register_build( 'uts', 'frontend' ) ) {
			Assets::enqueue_frontend();
		}

		wp_add_inline_script(
			self::APP_HANDLE,
			'window.utsAdmin = ' . wp_json_encode( self::app_data() ) . ';',
			'before'
		);

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( self::APP_HANDLE );
		wp_enqueue_script( self::APP_HANDLE );
		wp_set_script_translations( self::APP_HANDLE, 'uts' );
	}

	/**
	 * Boot data for the admin app (`window.utsAdmin`).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return array
	 */
	public static function app_data() {
		$user  = wp_get_current_user();
		$pages = array();
		foreach ( self::pages() as $slug => $page ) {
			if ( current_user_can( $page['capability'] ) ) {
				$pages[ $slug ] = array(
					'title' => $page['title'],
					'url'   => admin_url( 'edit.php?post_type=uts&page=' . $slug ),
				);
			}
		}

		$categories = array();
		$terms      = get_terms(
			array(
				'taxonomy'   => 'uts_category',
				'hide_empty' => false,
			)
		);
		foreach ( is_array( $terms ) ? $terms : array() as $term ) {
			$categories[] = array(
				'value' => $term->slug,
				'label' => $term->name,
				'count' => (int) $term->count,
			);
		}

		$status               = Getting_Started::setup_status();
		$status['shortcodes'] = DB::count();

		return array(
			'version'       => UTS_VERSION,
			'pages'         => $pages,
			'schema'        => Settings::schema(),
			'defaults'      => Settings::defaults(),
			'settings'      => Settings::get(),
			'attributeDocs' => Getting_Started::attribute_docs(),
			'categories'    => $categories,
			'status'        => $status,
			'videoId'       => Getting_Started::video_id(),
			'links'         => array(
				'docs'          => uts_link( 'docs' ),
				'support'       => uts_link( 'support' ),
				'review'        => uts_link( 'review' ),
				'github'        => uts_link( 'github' ),
				'testimonials'  => admin_url( 'edit.php?post_type=uts' ),
				'newTestimonial' => admin_url( 'post-new.php?post_type=uts' ),
				'withoutPhoto'  => admin_url( 'edit.php?post_type=uts&uts_photo=missing' ),
				'categories'    => admin_url( 'edit-tags.php?taxonomy=uts_category&post_type=uts' ),
				'newPage'       => admin_url( 'post-new.php?post_type=page' ),
				'usedOn'        => $status['page_id'] ? (string) get_permalink( $status['page_id'] ) : '',
				'usedOnTitle'   => $status['page_id'] ? get_the_title( $status['page_id'] ) : '',
			),
			'user'          => array(
				'name'           => $user->display_name,
				'canManage'      => current_user_can( 'manage_options' ),
				'canBuild'       => current_user_can( REST::builder_capability() ),
				'canCategories'  => current_user_can( 'manage_categories' ),
			),
		);
	}
}
