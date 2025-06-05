=== Ultimate 3D Testimonial Slider, List & Grid ===
Contributors: monzuralam, jahidcse
Donate link: 
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl.html
Tags: 3d-slider, Testimonial, slider-testimonial, carousel, grid testimonial,
Tested up to: 6.8
Stable tag: 1.0.1
Requires PHP: 5.6

Easily create responsive 3D Testimonial Slider, list and Grid layout for WordPress website. Display clean client's testimonial on any page or post via shortcode.

== Description ==

Ultimate 3D Testimonial Slider is create awesome client's testimonial showcase for WordPress website. Hopefully it is very helpfull to add client testimonial.

### Features of the Ultimate 3D Testimonial Slider
* Quick to set up
* Lightweight
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
| `count`    | Number of testimonials to display. If omitted, all are shown. | All       | `[uts count="3"]`                           |
| `category` | Filter testimonials by category slug.                        | *None*    | `[uts category="client-feedback"]`          |
| `type`     | Layout type for displaying testimonials.                     | `slider`  | `[uts type="grid"]`, `[uts type="list"]`    |

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

### Need Mored Features
We are working on it. Hopefully we some features next release.

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

= Is this plugin compatible with page builders like Elementor or WPBakery? =

Yes, the shortcode works well inside most page builders that support shortcodes.

= I need more help please! =

Please visit the plugin support forum on WordPress.org for assistance.

== Screenshots ==


== Changelog ==

= 1.0.1 (05-06-2025) =
* **New:** Added Category
* **New:** Added List & Grid Layout
* **New:** Added Shortcode parameter
* **Update** default layout 

= 1.0.0 =
* **Initial Release.**