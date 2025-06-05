<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Generates a 3D testimonial slider shortcode.
 *
 * This function sets up a custom WP_Query to retrieve and display posts of type 'uts',
 * allowing optional filtering based on the number of posts and category specified
 * in the shortcode attributes. It outputs the testimonials in a slider format with
 * navigation controls.
 *
 * @param array $atts Shortcode attributes. Available attributes:
 *     - 'number' (int) : Number of testimonials to display. Defaults to all.
 *     - 'category' (string) : Slug of the category to filter testimonials.
 * 
 * @return string The HTML output for the testimonial slider.
 */
function uts_shortcode($atts) {
    $args = array(
        'post_type' => 'uts',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'menu_order',
    );

    // Number
    if (isset($atts['count']) && !empty($atts['count'])) {
        $args['posts_per_page'] = absint($atts['count']);
    }

    // Category
    if (isset($atts['category']) && !empty($atts['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'uts_category',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['category']),
            )
        );
    }

    // Type
    $type = isset($atts['type']) && !empty($atts['type']) ? sanitize_text_field($atts['type']) : 'slider';

    // print_r($args);

    $query = new WP_Query($args);
    ob_start();
?>
    <section id="ucs-container-<?php echo esc_attr($type); ?>" class="ucs-container <?php echo esc_attr($type);
                                                                                    echo 'slider' === $type ? ' swiper' : ''; ?>">
        <div class="<?php echo 'slider' === $type ? 'swiper-wrapper' : 'ucs-wrapper'; ?>">
            <?php
            if ($query->have_posts()) :
                while ($query->have_posts()) :
                    $query->the_post();
            ?>
                    <div class="ucs-item <?php echo 'slider' === $type ? 'swiper-slide' : ''; ?>">
                        <?php
                        if (has_post_thumbnail()) {
                            the_post_thumbnail('large');
                        } else {
                        ?>
                            <img src="<?php echo esc_url(UTS_URL . '/assets/images/quotation.png'); ?>" alt="Quote" class="img-fluid">
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
                    </div>
            <?php
                endwhile;
            endif;
            wp_reset_query();
            ?>
        </div>
        <?php if ('slider' === $type) { ?>
            <div class="swiper-pagination"></div>
        <?php
        } ?>
    </section>
<?php
    return ob_get_clean();
}
add_shortcode('uts', 'uts_shortcode');
