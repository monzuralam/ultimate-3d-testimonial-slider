=== Ultimate 3D Testimonial Slider, List & Grid ===
Contributors: monzuralam, jahidcse
Donate link:
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl.html
Tags: 3d-slider, testimonial, slider-testimonial, carousel, grid testimonial
Requires at least: 5.8
Tested up to: 7.1
Stable tag: 1.1.0
Requires PHP: 5.6

Show client testimonials as a 3D slider, grid, list, masonry or scrolling ticker. Build sliders visually and place them with one shortcode.

== Description ==

Ultimate 3D Testimonial Slider shows what your clients say about you, on any page or post. Add your testimonials, design a slider in the Shortcode Builder, and paste its shortcode where you want it.

### Features

* Quick to set up, with a Getting Started guide, a live preview and a setup checklist
* Shortcode Builder: design and save sliders with a live preview, then place them with `[uts id="5"]`
* Five layouts: 3D slider, grid, list, masonry and ticker (an endless scrolling row)
* Seven slider effects: 3D coverflow, slide, fade, cards, 3D cube, flip and creative
* Autoplay, loop, speed, arrows and dots
* Colors, corner radius, columns, excerpt length and image size
* Settings page for the defaults; every setting can be overridden per slider or per shortcode
* Categories, to show different testimonials on different pages
* Several sliders on the same page
* Lightweight: no jQuery, and the CSS and JS load only on pages that use the shortcode
* Accessible: no autoplay or scrolling motion for visitors who prefer reduced motion

### How to use

1. Go to **Ultimate 3D Testimonial Slider > Add Post** and add your testimonials: the client's name as the title, their words as the content, a featured image as the photo, and their position & company in the "Client Additional" box.
1. Go to **Ultimate 3D Testimonial Slider > Shortcode Builder**, create a slider and save it.
1. Copy its shortcode, for example `[uts id="5"]`, and paste it into any page or post.

You can also use the plain shortcode without the builder:

`[uts]`

To show it in a theme file, use `do_shortcode()`:

`<?php echo do_shortcode( '[uts]' ); ?>`

### Shortcode attributes

All attributes are optional. Their defaults come from **Ultimate 3D Testimonial Slider > Settings**. Attributes always win over a saved slider and over the settings, so `[uts id="5" autoplay="false"]` uses slider 5 without autoplay.

**General**

* `id`: a slider saved in the Shortcode Builder. Example: `[uts id="5"]`
* `type`: the layout: `slider`, `grid`, `list`, `masonry` or `ticker`. Default: `slider`. Example: `[uts type="grid"]`
* `count`: how many testimonials to show (0-100). `0` shows all. Default: `0`. Example: `[uts count="3"]`
* `category`: only show testimonials from this category (use its slug). Default: all categories. Example: `[uts category="client-feedback"]`
* `excerpt_length`: words of each testimonial to show (0-500). `0` shows the full text. Default: `50`
* `image_size`: `thumbnail`, `medium`, `large` or `full`. Default: `large`

**Slider**

* `effect`: `coverflow`, `slide`, `fade`, `cards`, `cube`, `flip` or `creative`. Default: `coverflow`. Example: `[uts effect="fade"]`
* `speed`: transition speed in milliseconds (100-5000). Default: `300`
* `autoplay`: move to the next slide automatically, `true` or `false`. Default: `false`
* `autoplay_delay`: time between slides with autoplay on, in milliseconds (1000-20000). Default: `3000`. Example: `[uts autoplay="true" autoplay_delay="5000"]`
* `loop`: go back to the first slide after the last one, `true` or `false`. Default: `false`
* `pagination`: show dots, `true` or `false`. Default: `true`
* `navigation`: show previous/next arrows, `true` or `false`. Default: `false`
* `coverflow_rotate`: coverflow only, how far side slides turn in degrees (0-90). Default: `50`
* `coverflow_depth`: coverflow only, how far side slides sit back (0-500). Default: `100`
* `coverflow_stretch`: coverflow only, space between slides; negative values overlap them (-100 to 100). Default: `0`

**Display**

* `show_image`: show the client photo, `true` or `false`. Default: `true`
* `show_title`: show the client name, `true` or `false`. Default: `true`
* `show_designation`: show the position & company, `true` or `false`. Default: `true`
* `grid_columns`: grid and masonry only, columns on desktop (1-6). Smaller screens use fewer. Default: `3`. Example: `[uts type="masonry" grid_columns="4"]`
* `ticker_duration`: ticker only, seconds each testimonial takes to scroll past (2-30); higher is slower. Default: `6`. Example: `[uts type="ticker" ticker_duration="10"]`

**Style**

* `accent_color`: color of the dots and arrows, e.g. `#2271b1`. Default: blue
* `text_color`: text color of the cards. Default: theme default
* `background_color`: background color of the cards. Default: white
* `border_radius`: card corner radius in pixels (0-50). Default: `0`

### Examples

* 3 testimonials from the "client-feedback" category as a list: `[uts count="3" category="client-feedback" type="list"]`
* A 4-column masonry: `[uts type="masonry" grid_columns="4"]`
* A fade slider that plays by itself: `[uts effect="fade" autoplay="true"]`
* A slow scrolling ticker: `[uts type="ticker" ticker_duration="10"]`

### Source code

The JavaScript and CSS in the `build` folder are compiled from the `src` folder of the public repository: https://github.com/monzuralam/ultimate-3d-testimonial-slider
To build it yourself, run `npm install` and `npm run build`.

### Need more features?

Feature requests are welcome in the support forum.

== Installation ==

### Install from within WordPress

1. Go to **Plugins > Add New** in your dashboard.
1. Search for "Ultimate 3D Testimonial Slider".
1. Install and activate it.
1. The Getting Started page opens and walks you through the setup.

### Install manually

1. Upload the `ultimate-3d-testimonial-slider` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the **Plugins** screen in WordPress.
1. Go to **Ultimate 3D Testimonial Slider > Getting Started**.

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

Create a slider in **Ultimate 3D Testimonial Slider > Shortcode Builder** and paste its shortcode (for example `[uts id="5"]`) into any post or page. You can also use `[uts]` with attributes, for example `[uts count="3" category="client-feedback" type="slider"]`. In a theme file, use `<?php echo do_shortcode( '[uts]' ); ?>`.

= What is the Shortcode Builder? =

A page where you design a slider with a live preview: layout, effect, colors, category and more. Each saved slider gets its own shortcode, like `[uts id="5"]`. Change the slider later and every page that uses it updates. Drafts show nothing on your site until you publish them.

= Can I control how many testimonials are displayed? =

Yes. Set "Testimonials to show" in the builder or the settings, or use the `count` attribute: `[uts count="5"]` shows 5 testimonials.

= How do I change the order of the testimonials? =

Edit a testimonial and set **Order** in the "Page Attributes" box. Lower numbers come first.

= What layout types are available? =

Five: slider, grid, list, masonry (cards keep their own height) and ticker (an endless scrolling row that pauses on hover).

= Where can I change the slider effect, autoplay or colors? =

In **Ultimate 3D Testimonial Slider > Settings** for every shortcode, or in the Shortcode Builder for one saved slider. You can also use the setting name as a shortcode attribute, for example `[uts effect="fade" autoplay="true"]`.

= Can I show more than one slider on a page? =

Yes. Each `[uts]` shortcode works on its own, with its own options.

= What happens to my data when I delete the plugin? =

By default your settings and saved sliders are kept, so reinstalling brings them back. To remove them, turn off "Keep data when the plugin is deleted" in **Settings > Advanced** before deleting the plugin. Your testimonials are always kept.

= Is this plugin compatible with page builders like Elementor or WPBakery? =

Yes, the shortcode works inside most page builders that support shortcodes.

= I need more help please! =

Please visit the plugin support forum on WordPress.org.

== Screenshots ==


== Changelog ==

= 1.1.0 =
* **New:** Shortcode Builder: design sliders with a live preview, save them (or keep them as drafts) and place them with `[uts id="5"]`; duplicate, trash and "Where is this used?"
* **New:** Settings page for the default layout, count, excerpt length, image size, slider effect, autoplay, loop, speed, arrows, dots, visibility of image/name/designation, columns, colors and corner radius
* **New:** Masonry and ticker layouts
* **New:** Slide, fade, cards, cube, flip and creative slider effects, autoplay, loop and navigation arrows
* **New:** Every setting can be overridden as a shortcode attribute
* **New:** Getting Started dashboard with a video tutorial, a live preview of your testimonials, a setup checklist, a step-by-step "How to use" guide and a full shortcode reference
* **New:** Option to keep or remove the plugin data when the plugin is deleted
* **New:** Photo, Position & Company and Order columns in the testimonials list, plus a "Without photo" filter
* **New:** Order field (Page Attributes) on testimonials to control the display order
* **New:** Multiple sliders on the same page
* **Improvement:** Sliders don't autoplay, and the ticker doesn't scroll, for visitors who prefer reduced motion
* **Improvement:** Assets are built with @wordpress/scripts; jQuery is no longer required and Swiper is updated to 11
* **Improvement:** CSS and JS load only on pages that use the shortcode, and admin assets only on the plugin's screens
* **Removed:** The old unfinished shortcode builder code (replaced by the new Shortcode Builder)
* **Fix:** The plugin's CSS reset no longer removes margins and padding from the rest of the page
* **Fix:** Escaping of testimonial titles and designations; designation is saved only by users who can edit the testimonial

= 1.0.1 (05-06-2025) =
* **New:** Added Category
* **New:** Added List & Grid Layout
* **New:** Added Shortcode parameter
* **Update** default layout

= 1.0.0 =
* **Initial Release.**
