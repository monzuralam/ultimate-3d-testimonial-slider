<?php
/**
 * "Client Additional" metabox on the testimonial edit screen.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * Adds the "Client Additional" metabox and saves the client's position and
 * company to the `uts_designation` post meta.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class Metabox {

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'uts_metabox_nonce';

	/**
	 * Nonce field name.
	 *
	 * @var string
	 */
	const NONCE_FIELD = 'uts_metabox_nonce_field';

	/**
	 * Post meta key.
	 *
	 * @var string
	 */
	const META_KEY = 'uts_designation';

	/**
	 * Hook the metabox and its save handler.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add' ) );
		add_action( 'save_post_uts', array( $this, 'save' ) );
	}

	/**
	 * Register the metabox on the testimonial screen.
	 *
	 * @since 1.0.1
	 * @author Monzur Alam
	 */
	public function add() {
		add_meta_box(
			'uts_custom_metabox',
			__( 'Client Additional', 'ultimate-3d-testimonial-slider' ),
			array( $this, 'render' ),
			'uts',
			'normal'
		);
	}

	/**
	 * Metabox content.
	 *
	 * @since 1.0.1
	 * @author Monzur Alam
	 *
	 * @param \WP_Post $post Current testimonial.
	 */
	public function render( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		$designation = get_post_meta( $post->ID, self::META_KEY, true );
		?>
		<label for="uts_designation"><?php esc_html_e( 'Position & Company Name', 'ultimate-3d-testimonial-slider' ); ?></label>
		<input type="text" name="uts_designation" id="uts_designation" class="regular-text" value="<?php echo esc_attr( $designation ); ?>" placeholder="<?php esc_attr_e( 'CEO, Company Name', 'ultimate-3d-testimonial-slider' ); ?>">
		<?php
	}

	/**
	 * Save the designation (nonce, autosave and capability checked).
	 *
	 * @since 1.0.1
	 * @author Monzur Alam
	 *
	 * @param int $post_id Post ID.
	 */
	public function save( $post_id ) {
		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['uts_designation'] ) ) {
			update_post_meta( $post_id, self::META_KEY, sanitize_text_field( wp_unslash( $_POST['uts_designation'] ) ) );
		}
	}
}
