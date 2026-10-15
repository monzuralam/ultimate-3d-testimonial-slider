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
 * company to the `uts_designation` post meta and the star rating (1-5) to
 * `uts_rating` (no meta = no rating, so no stars on the frontend).
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
	 * Star rating post meta key (1-5; deleted for "No rating").
	 *
	 * @var string
	 */
	const RATING_KEY = 'uts_rating';

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
		$rating = self::rating( $post->ID );
		?>
		<p class="uts-metabox-rating">
			<label for="uts_rating"><?php esc_html_e( 'Star rating', 'ultimate-3d-testimonial-slider' ); ?></label><br>
			<select name="uts_rating" id="uts_rating">
				<option value="0"<?php selected( $rating, 0 ); ?>><?php esc_html_e( 'No rating (no stars shown)', 'ultimate-3d-testimonial-slider' ); ?></option>
				<?php for ( $i = 5; $i >= 1; $i-- ) { ?>
					<option value="<?php echo (int) $i; ?>"<?php selected( $rating, $i ); ?>><?php echo esc_html( str_repeat( '★', $i ) . str_repeat( '☆', 5 - $i ) . ' ' . sprintf( /* translators: %d: number of stars */ _n( '%d star', '%d stars', $i, 'ultimate-3d-testimonial-slider' ), $i ) ); ?></option>
				<?php } ?>
			</select>
		</p>
		<?php
	}

	/**
	 * A testimonial's star rating.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param int $post_id Testimonial ID.
	 * @return int 1-5, or 0 when it has no rating.
	 */
	public static function rating( $post_id ) {
		$rating = (int) get_post_meta( $post_id, self::RATING_KEY, true );

		return $rating >= 1 && $rating <= 5 ? $rating : 0;
	}

	/**
	 * Save the designation and rating (nonce, autosave and capability checked).
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

		if ( isset( $_POST['uts_rating'] ) ) {
			$rating = absint( wp_unslash( $_POST['uts_rating'] ) );
			if ( $rating >= 1 && $rating <= 5 ) {
				update_post_meta( $post_id, self::RATING_KEY, $rating );
			} else {
				delete_post_meta( $post_id, self::RATING_KEY );
			}
		}
	}
}
