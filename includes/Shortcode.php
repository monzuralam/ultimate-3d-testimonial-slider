<?php

namespace UTS;

// don't call the file directly
if (!defined('ABSPATH')) {
    wp_die(__('You can\'t access this page', 'uts'));
}

/**
 * Shortcode
 */
class Shortcode {
    /**
     * The single instance of the class.
     *
     * @var Enqueue
     * @since 1.0.1
     */
    private static $instance = null;

    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('uts', array($this, 'render_shortcode'));
    }

    /**
     * Render Shortcode
     *
     * @param array $atts
     * @return void
     */
    public function render_shortcode($atts){
        $args = array(
            'post_type' => 'uts',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'order' => 'ASC',
    
        );
        $query = new \WP_Query($args);
        ob_start();
        wp_enqueue_style('uts');
        wp_enqueue_script('modernizr');
        wp_enqueue_script('gallery');
        wp_enqueue_script('uts');
    ?>
        <section id="ucs-container" class="ucs-container">
            <div class="ucs-wrapper">
                <?php
                if ($query->have_posts()) :
                    while ($query->have_posts()) :
                        $query->the_post();
                ?>
                        <a>
                            <?php
                            if (has_post_thumbnail()) {
                                the_post_thumbnail('large');
                            } else {
                            ?>
                                <img src="<?php echo esc_url(UTS_ASSETS . '/images/quotation.png'); ?>" alt="<?php echo esc_attr__('Quote', 'uts'); ?>" class="img-fluid">
                            <?php
                            }
                            ?>
                            <div class="details-content">
                                <p><?php echo wp_trim_words(get_the_content(), 50); ?></p>
                                <h3><?php _e(sanitize_text_field(get_the_title())); ?></h3>
                                <h4>
                                    <?php
                                    $postion = sanitize_text_field(get_post_meta(get_the_ID(), "uts_designation", true));
                                    esc_html_e($postion, 'uts');
                                    ?>
                                </h4>
                            </div>
                        </a>
                <?php
                    endwhile;
                endif;
                wp_reset_query();
                ?>
            </div>
            <nav>
                <span class="ucs-prev"><i class="fas fa-angle-left"></i></span>
                <span class="ucs-next"><i class="fas fa-angle-right"></i></span>
            </nav>
        </section>
    <?php
        return ob_get_clean();
    }

    /**
     * Instance
     *
     * @return void
     */
    public static function instance() {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

Shortcode::instance();