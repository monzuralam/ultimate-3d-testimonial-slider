=== Ultimate 3D Testimonial Slider, List & Grid ===
Contributors: monzuralam, jahidcse
Donate link: 
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl.html
Tags: 3d-slider, Testimonial, slider-testimonial, carousel, grid testimonial,
Requires at least: 5.8
Tested up to: 7.1
Stable tag: 1.1.0
Requires PHP: 5.6

Easily create responsive 3D Testimonial Slider, list and Grid layout for WordPress website. Display clean client's testimonial on any page or post via shortcode.

== Description ==

Ultimate 3D Testimonial Slider is create awesome client's testimonial showcase for WordPress website. Hopefully it is very helpfull to add client testimonial.

### Features of the Ultimate 3D Testimonial Slider
* Quick to set up, with a Getting Started guide
* Lightweight: no jQuery, assets load only on pages that use the shortcode
* 3D coverflow, slide, fade and cards slider effects
* Slider, grid and list layouts
* Settings page for the default layout, autoplay, loop, speed, arrows, dots, colors and more
* Every setting can be overridden per shortcode
* Several sliders on the same page
* User friendly

### Display Testimonial via shortcode 
You can display Ultimate 3D Testimonial Slider into your page or post via shortcode. 

<code>[uts]</code>

if you want to show into your theme file please use do_shortcode.
<code>
    <?php echo do_shortcode('[uts]'); ?> 
</code>


---

## 🔧 Shortcode Attributes

The `[uts]` shortcode accepts the following optional attributes:

| Attribute  | Description                                                  | Default   | Example                                     |
|------------|--------------------------------------------------------------|-----------|---------------------------------------------|
| `count`    | Number of testimonials to display. `0` shows all.            | All       | `[uts count="3"]`                           |
| `category` | Filter testimonials by category slug.                        | *None*    | `[uts category="client-feedback"]`          |
| `type`     | Layout type for displaying testimonials.                     | `slider`  | `[uts type="grid"]`, `[uts type="list"]`    |
| `effect`   | Slider effect: `coverflow`, `slide`, `fade` or `cards`.      | `coverflow` | `[uts effect="fade"]`                     |
| `autoplay` | Play the slider automatically (`autoplay_delay` in ms).      | `false`   | `[uts autoplay="true" autoplay_delay="5000"]` |
| `loop`     | Loop back to the first slide.                                | `false`   | `[uts loop="true"]`                         |
| `navigation` / `pagination` | Show arrows / dots.                         | `false` / `true` | `[uts navigation="true" pagination="false"]` |
| `grid_columns` | Grid columns on desktop (1-6).                           | `3`       | `[uts type="grid" grid_columns="4"]`        |

The defaults come from **Testimonials > Settings**. Every option on that page can also be used as a shortcode attribute with the same name (for example `excerpt_length`, `show_image`, `accent_color`), and attributes always win over the settings.

---

## ✅ Example

**Display 3 testimonials from the "client-feedback" category in a list layout:**
[uts count="3" category="client-feedback" type="list"]


---

## 🧩 Display in Theme Template Files

To include the testimonial slider directly in a PHP theme file, use the `do_shortcode()` function:

```php
<?php echo do_shortcode('[uts count="3" category="client-feedback" type="grid"]'); ?>
```

### Source code
The JavaScript and CSS in the `build` folder are compiled from the `src` folder of the public repository: https://github.com/monzuralam/ultimate-3d-testimonial-slider
To build it yourself, run `npm install` and `npm run build`.

### Need More Features?
We are working on it. Feature requests are welcome in the support forum.

== Installation ==

### INSTALL ULTIMATE 3D TESTIMONIAL SLIDER FROM WITHIN WORDPRESS

1. Visit the plugins page within your dashboard and select ‘Add New’;
1. Search for ‘Ultimate 3D Testimonial Slider’;
1. Activate Ultimate 3D Testimonial Slider from your Plugins page;
1. Go to ‘after activation’ below.

### INSTALL ULTIMATE 3D TESTIMONIAL SLIDER MANUALLY

1. Upload the ‘ultimate-3d-testimonial-slider’ folder to the /wp-content/plugins/ directory;
1. Activate the Ultimate 3D Testimonial Slider plugin through the ‘Plugins’ menu in WordPress;
1. Go to ‘after activation’ below.


== 🔥 WHAT’S NEXT 🔥==
Consider checking out our other plugins:

📌 [WP Sticky Anything – Sticky Menu & Sticky Header, Sticky Sidebar](https://wordpress.org/plugins/all-in-one-wp-sticky-anything/)
All-in-One Sticky Menu & Sticky Header, Sticky Sidebar Solution for WordPress.

⏳ [All-in-One WP Preloader](https://wordpress.org/plugins/all-in-one-wp-preloader/)  
All-in-One WP Preloader gives your site a loading screen without writing any code.

🎥 [Best Youtube Video LazyLoad](https://wordpress.org/plugins/best-youtube-video-lazyload/)  
Youtube Video Lazyload improves Google PageSpeed Insights Score, GTmetrix, and Pingdom score.

== Frequently Asked Questions ==

= How do I display the testimonials on my site? =

You can display testimonials using the shortcode `[uts]` in any post or page.  
For example: `[uts count="3" category="client-feedback" type="slider"]`  
You can also add the shortcode directly in your theme PHP files using:  
```php
<?php echo do_shortcode('[uts]'); ?>

= Can I control how many testimonials are displayed? =

Yes! Use the count attribute in the shortcode to limit the number of testimonials shown.
Example: [uts count="5"] displays only 5 testimonials.

= What layout types are available? =
There are three layout types you can choose from using the type attribute. They are slider, list & grid.

= Where can I change the slider effect, autoplay or colors? =

Go to Testimonials > Settings. Those settings are the defaults for every shortcode. To change one slider only, use the setting name as a shortcode attribute, for example `[uts effect="fade" autoplay="true"]`.

= Can I show more than one slider on a page? =

Yes. Each `[uts]` shortcode works on its own, with its own options.

= Is this plugin compatible with page builders like Elementor or WPBakery? =

Yes, the shortcode works well inside most page builders that support shortcodes.

= I need more help please! =

Please visit the plugin support forum on WordPress.org for assistance.

== Screenshots ==


== Changelog ==

= 1.1.0 =
* **New:** Settings page (Testimonials > Settings) for the default layout, count, excerpt length, image size, slider effect, autoplay, loop, speed, arrows, dots, visibility of image/name/designation, grid columns, colors and corner radius
* **New:** Slide, fade and cards slider effects, autoplay, loop and navigation arrows
* **New:** Every setting can be overridden as a shortcode attribute
* **New:** Getting Started dashboard with a setup checklist, a step-by-step "How to use" guide and a full shortcode reference
* **New:** Shortcode playground: try layouts, effects and categories on your real testimonials, copy the matching shortcode, or save the options as your defaults
* **New:** Photo, Position & Company and Order columns in the testimonials list, plus a "Without photo" filter
* **New:** Order field (Page Attributes) on testimonials to control the display order
* **Improvement:** Sliders don't autoplay for visitors who prefer reduced motion
* **Removed:** The unfinished shortcode builder (replaced by the playground)
* **New:** Multiple sliders on the same page
* **Improvement:** Assets are built with @wordpress/scripts; jQuery is no longer required and Swiper is updated to 11
* **Improvement:** CSS and JS load only on pages that use the shortcode, and admin assets only on the plugin's screens
* **Fix:** The plugin's CSS reset no longer removes margins and padding from the rest of the page
* **Fix:** Escaping of testimonial titles and designations; designation is saved only by users who can edit the testimonial

= 1.0.1 (05-06-2025) =
* **New:** Added Category
* **New:** Added List & Grid Layout
* **New:** Added Shortcode parameter
* **Update** default layout 

= 1.0.0 =
* **Initial Release.**