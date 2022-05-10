<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function ust_shortcode($atts){
    $args = array(
        'post_type' => 'ust',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'ASC',

    );
    $query = new WP_Query($args);
    ob_start();
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
                if(has_post_thumbnail()){
                    the_post_thumbnail('large');
                }else{
                    ?>
                    <img src="<?php echo esc_url(UST_URL . 'assets/images/quotation.png'); ?>" alt="Quote" class="img-fluid">
                    <?php
                }
                ?>
                <div class="details-content">
                    <p><?php echo wp_trim_words(get_the_content(), 50); ?></p>
                    <h3><?php _e(sanitize_text_field(get_the_title())); ?></h3>
                    <h4>
                        <?php
                        $postion = sanitize_text_field(get_post_meta(get_the_ID(), "ust_designation", true));
                        esc_html_e($postion, 'ust');
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
add_shortcode('ust','ust_shortcode');
