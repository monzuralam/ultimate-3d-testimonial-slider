<?php
/**
 * Admin menu: the plugin's submenu pages, shared header and activation redirect.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Getting Started and Settings pages under Testimonials,
 * renders the header shared by both, loads the Settings page app and opens
 * Getting Started once after activation.
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
	 * Register the plugin's admin pages under Testimonials.
	 *
	 * @since 1.0.1
	 * @author Monzur Alam
	 */
	public function add_pages() {
		$page = Plugin::instance()->component( 'UTS\Getting_Started' );

		$getting_started = add_submenu_page(
			self::PARENT_SLUG,
			__( 'Getting Started', 'uts' ),
			__( 'Getting Started', 'uts' ),
			'edit_posts',
			'uts-getting-started',
			array( $page, 'render' )
		);

		$settings = add_submenu_page(
			self::PARENT_SLUG,
			__( 'Testimonial Settings', 'uts' ),
			__( 'Settings', 'uts' ),
			'manage_options',
			'uts-settings',
			array( $this, 'render_settings_page' )
		);

		add_action( 'load-' . $getting_started, array( $page, 'load' ) );
		add_action( 'load-' . $settings, array( $this, 'load_settings_page' ) );
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
	 * Shared header (plugin name, version and tabs) for the plugin's admin pages.
	 *
	 * The `.wp-header-end` marker makes WordPress put admin notices below it.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param string $active Active tab key.
	 */
	public static function header( $active ) {
		$tabs = array(
			'getting-started' => array( __( 'Getting Started', 'uts' ), admin_url( 'edit.php?post_type=uts&page=uts-getting-started' ), 'edit_posts' ),
			'settings'        => array( __( 'Settings', 'uts' ), admin_url( 'edit.php?post_type=uts&page=uts-settings' ), 'manage_options' ),
			'testimonials'    => array( __( 'All Testimonials', 'uts' ), admin_url( 'edit.php?post_type=uts' ), 'edit_posts' ),
			'categories'      => array( __( 'Categories', 'uts' ), admin_url( 'edit-tags.php?taxonomy=uts_category&post_type=uts' ), 'manage_categories' ),
		);
		?>
		<div class="uts-admin-header uts:flex uts:flex-wrap uts:items-center uts:gap-x-8 uts:gap-y-1 uts:border-b uts:border-wp-border uts:bg-white uts:px-5 uts:md:px-8">
			<div class="uts:flex uts:items-center uts:gap-3 uts:py-3">
				<span class="dashicons dashicons-slides uts:text-wp-blue uts:text-[26px] uts:size-[26px]" aria-hidden="true"></span>
				<span class="uts:text-lg uts:font-semibold uts:text-gray-900"><?php esc_html_e( 'Ultimate 3D Testimonial Slider', 'uts' ); ?></span>
				<span class="uts:rounded-full uts:border uts:border-wp-border uts:px-2 uts:py-0.5 uts:text-xs uts:text-wp-muted">v<?php echo esc_html( UTS_VERSION ); ?></span>
			</div>
			<nav class="uts:flex uts:flex-1 uts:flex-wrap uts:gap-x-6" aria-label="<?php esc_attr_e( 'Plugin pages', 'uts' ); ?>">
				<?php
				foreach ( $tabs as $key => $tab ) {
					if ( ! current_user_can( $tab[2] ) ) {
						continue;
					}
					$is_active = $key === $active;
					?>
					<a href="<?php echo esc_url( $tab[1] ); ?>"
						class="uts:-mb-px uts:border-b-2 uts:py-4 uts:text-sm uts:font-medium uts:no-underline <?php echo $is_active ? 'uts:border-wp-blue uts:text-wp-blue' : 'uts:border-transparent uts:text-gray-700 uts:hover:text-wp-blue'; ?>"
						<?php echo $is_active ? 'aria-current="page"' : ''; ?>>
						<?php echo esc_html( $tab[0] ); ?>
					</a>
				<?php } ?>
			</nav>
			<a href="<?php echo esc_url( uts_link( 'support' ) ); ?>" target="_blank" rel="noopener noreferrer" class="uts:flex uts:items-center uts:gap-1 uts:py-3 uts:text-sm uts:font-medium uts:text-wp-blue uts:no-underline">
				<span class="dashicons dashicons-sos" aria-hidden="true"></span>
				<?php esc_html_e( 'Get Help', 'uts' ); ?>
			</a>
		</div>
		<hr class="wp-header-end uts:hidden">
		<?php
	}

	/**
	 * Settings page: enqueue assets on load.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function load_settings_page() {
		add_action( 'admin_enqueue_scripts', array( $this, 'settings_page_assets' ) );
	}

	/**
	 * Settings page callback: the React app mounts here.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function render_settings_page() {
		self::header( 'settings' );
		echo '<div class="wrap"><div id="uts-settings-root"></div></div>';
	}

	/**
	 * Settings page assets.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function settings_page_assets() {
		if ( ! uts_register_build( 'uts-settings', 'settings' ) ) {
			return;
		}

		wp_add_inline_script(
			'uts-settings',
			'window.utsSettingsData = ' . wp_json_encode(
				array(
					'schema'   => Settings::schema(),
					'defaults' => Settings::defaults(),
				)
			) . ';',
			'before'
		);

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'uts-settings' );
		wp_enqueue_script( 'uts-settings' );
		wp_set_script_translations( 'uts-settings', 'uts' );
	}
}
