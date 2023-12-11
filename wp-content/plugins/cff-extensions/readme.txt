=== Custom Facebook Feed Pro - Extensions ===
Author: Smash Balloon
Support Website: https://smashballoon.com/custom-facebook-feed/extensions
Requires at least: 3.0
Tested up to: 5.8
Version: 1.7.3
License: Non-distributable, Not for resale

Contains a range of extensions which extend the functionality of the Custom Facebook Feed Pro plugin.

== Description ==

Contains a range of extensions which extend the functionality of the Custom Facebook Feed Pro plugin.


== Installation ==
1. Install the plugin via the WordPress plugin directory, or by uploading the files to your web server (in the /wp-content/plugins/ directory).
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to Settings > License to enter and activate your license
4. Navigate to 'Facebook Feed > Extensions'
5. Activate the extensions you'd like to use

== Changelog ==
= 1.7.3 =
* *Carousel Extension*
* Fix: Fixed issue where pagination would multiply when using the pagination "below" setting and having multiple carousel feeds on the same page.

= 1.7.2 =
* *Album Extension*
* New: Added a header which displays the album title, description, and meta data. This can be enabled/disabled using the plugin "Header" settings: Facebook Feed > Customize > General > Header.
* Fix: Fixed a compatibility issue with the latest version of the Custom Facebook Feed Pro Plugin.

* *Carousel Extension*
* Tweak: Added hook for modifying carousel args

* *Featured Post Extension*
* Tweak: Updated API calls to use more recent version of the Facebook API
* Fix: Fixed an issue with the post author avatar not displaying for some Facebook pages

* *Masonry Extension*
* Removed Masonry extension files as it is no longer an extension due to being included in the core plugin.

= 1.7.1 =
* *Reviews Extension*
* Fix: Fixed an issue with avatars of review authors sometimes not displaying

* *Featured Post Extension*
* Tweak: Updated API calls to use more recent version of the Facebook API
* Fix: Fixed an issue with the post author avatar not displaying for some Facebook pages

= 1.7 =
* *Reviews Extension*
* New: Added a setting to hide negative recommendations
* New: Now supports the "Boxed" post style and "Box Shadow" setting added in v3.7 of the Custom Facebook Feed Pro plugin

* *Carousel Extension*
* Tweak: Added spacing if the posts have the box-shadow setting applied or are displayed in multiple columns
* Fix: Fixed an issue with the navigation arrows not working when positioned below the carousel
* Fix: Fixed an issue where the navigation wouldn't be displayed below the feed if the pagination was hidden

= 1.6 =
* *Reviews Extension*
* New: Supports recommendations
* New: Added the ability to filter reviews by word/phrase. Just use the `filter` and `exfilter` shortcode settings.
* Tweak: Replaced link to the reviewers profile with link to the reviews page instead
* Tweak: Increased size of Reviews Access Token field so entire token is visible
* Fix: Now displays the avatars of people leaving reviews
* Fix: Updated directions in the tooltip on how to get a Reviews Access Token

* *Album Extension*
* New: Now dispays full size images in the popup lightbox

* *Masonry Extension*
* Fix: When the page was resized down and then back up again the Masonry layout wasn't correctly reapplied when using the "JavaScript" method
* Fix: Fixed a Firefox display issue

= 1.5.2 =
* Reviews: The wrong number of posts would sometimes be displayed when only showing reviews with certain star ratings or when choosing to only show reviews that contain text
* Reviews: The `offset` shortcode option wasn't working correctly with reviews
* Reviews: Fixed an issue where the text length setting wouldn't be applied to the review text
* Tweak: Updated the plugin updater script to reduce requests on the WordPress Plugins page
* Tweak: Removed the "Lightbox" extension as it's no longer required due to the lightbox built into the main Custom Facebook Feed Pro plugin

= 1.5.1 =
* Reviews: Added a setting that allows you to hide reviews that don't include any text

= 1.5 =
* Masonry Columns: Includes support for loading more posts in version 3.0 of the Custom Facebook Feed Pro plugin
* Carousel: Compatible with version 3.0 of the Custom Facebook Feed Pro plugin
* Featured Post: Compatible with version 3.0 of the Custom Facebook Feed Pro plugin
* Featured Post: Added support to Featured events for the "Attending" and "Interested" counts

= 1.4.2 =
* Featured Post: Now uses the latest version of the Facebook API. Removed any dependency on version 2.0 of the Facebook API which will be deprecated on August 8th, 2016.
* Reviews: Fixed an issue where line breaks in the review text was being converted to HTML line break tags
* Reviews: Fixed an issue where 1 star checkbox on the settings page wouldn't stay enabled

= 1.4.1 =
* Masonry Columns: Fixed an issue with the CSS layout method not displaying the feed in columns in the latest update of the Firefox browser due to a deprecated CSS property

= 1.4 =
* Launched the Reviews extension

= 1.3.1 =
* Masonry Columns: Fixed an issue with the layout in some web browsers
* Masonry Columns: Fixed an issue with column padding when using the JavaScript layout option
* Masonry Columns: Fixed an issue when trying to display the Credit link at the bottom of your feed

= 1.3 =
* Launched the Carousel extension
* Launched the Masonry Columns extension
* Featured Post: Now displays comment replies

= 1.2.2 =
* Featured Post: Updated to be compatible with version 2.4 of the Facebook API

= 1.2.1 =
* Lightbox: If you have more than one Facebook feed on a page then the photos in each lightbox slideshow are now grouped by feed
* Lightbox: Added a unique class and data attribute to the lightbox to prevent conflicts with other lightboxes on your site

= 1.2 =
* Launched the [Lightbox](https://smashballoon.com/extensions/lightbox/) extension
* Album: Album extension is now compatible with the new Lightbox extension
* Date Range: Changed the date format in the Date Range extension to mm/dd/yy to workaround a bug with the PHP strtotime function

= 1.1 =
* Launched the Album extension

= 1.0 =
* Launched the Multifeed extension
* Launched the Date Range extension
* Launched the Featured Post extension