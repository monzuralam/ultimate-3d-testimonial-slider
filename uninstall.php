<?php
/**
 * Remove plugin options on uninstall.
 *
 * Testimonial posts are kept so content isn't lost.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit; // Exit if accessed directly.
}

delete_option('uts_settings');
