=== Social Wall ===
Author: Smash Balloon
Contributors: smashballoon
Support Website: https://smashballoon/social-wall/
Tags: Social Media, Instagram, Twitter, Facebook, YouTube
Requires at least: 5.1
Tested up to: 6.7
Stable tag: 2.3.0
License: Non-distributable, Not for resale

Social Wall allows you to display completely customizable social media feeds.

== Description ==
Display **completely customizable**, **responsive** and **search engine crawlable** social wall with your Instagram, Facebook, Twitter, and YouTube content!

= Features =
* **Completely Customizable** - by default inherits your theme's styles
* Social Wall feed content is **crawlable by search engines** adding SEO value to your site
* **Completely responsive and mobile optimized** - works on any screen size
* Display a feed in a masonry, carousel, or list layout
* Allow **filtering** of videos using keywords in the description or title
* Display **multiple feeds** from different social media sources on multiple pages or widgets
* Post caching means that your feed loads **lightning fast** and minimizes API requests
* **Infinitely load more** of your social media content with the 'Load More' button
* Fully internationalized and translatable into any language
* Display a filter to allow visitors to select the social media sources in the feed
* Enter your own custom CSS or JavaScript for even deeper customization

For simple step-by-step directions on how to set up the Social Wall plugin please refer to our [setup guide](http://smashballoon.com/social-wall/docs/setup/ 'Social Wall setup guide').

= Benefits =
* **Increase social engagement** between you and your users, customers, or fans
* **Save time** by using the Social Wall plugin to generate dynamic, search engine crawlable content on your website
* **Get more follows** by displaying your social media content directly on your site
* Display your social media content **your way** to perfectly match your website's style
* The plugin is **updated regularly** with new features, bug-fixes and API changes
* Support is quick and effective
* We're dedicated to providing the **most customizable**, **robust** and **well supported** social media plugin in the world!

== Installation ==
1. Install the Social Wall plugin either via the WordPress plugin directory, or by uploading the files to your web server (in the /wp-content/plugins/ directory).
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Install and activate the Instagram Feed Pro, Custom Facebook Feed Pro, Feeds for YouTube Pro, and Custom Twitter Feeds.
4. Navigate to the 'Social Wall' settings page to configure your Social Wall feed.
5. Use the shortcode [social-wall][instagram-feed][custom-facebook-feed][custom-twitter-feeds][youtube-feeds][/social-wall] in your page, post or widget to display your feed.
6. You can display multiple feeds with different configurations by specifying the necessary parameters directly in the shortcode: [social-wall][custom-twitter-feeds screenname="smashballoon"][/social-wall].

For simple step-by-step directions on how to set up the Social Wall plugin please refer to our [setup guide](http://smashballoon.com/social-wall/docs/setup/ 'Social Wall setup guide').

= Setting up the Social Wall WordPress Plugin =

The Social Wall plugin is brand new and so we're currently working on improving our documentation for it. If you have an issue or question please submit a support ticket and we'll get back to you as soon as we can.

1) Once you've installed the Social Wall plugin click on the "Social Feeds" item in your WordPress menu

2) If you haven't installed and activated Instagram Feed Pro, Custom Facebook Feed Pro, Custom Twitter Feeds Pro, and/or Feeds for YouTube Pro, do so now. Follow the setup directions for each plugin to connect an account or get an access token.

3) Navigate to the Customize and Style pages to customize your Social Wall.

4) Copy the generated shortcode on the "Configure" tab (ex. [social-wall][instagram-feed][custom-facebook-feed][custom-twitter-feeds][youtube-feeds][/social-wall]) shortcode and paste it into any page, post or widget where you want the social media feed to appear.

5) You can paste the [social-wall][instagram-feed][custom-facebook-feed][custom-twitter-feeds][youtube-feeds][/social-wall] shortcode directly into your page editor.

6) You can use the default WordPress 'Text' widget to display your social media feed in a sidebar or other widget area.

7) View your website to see your social media feed(s) in all their glory!

== Frequently Asked Questions ==

= Can I display multiple feeds on my site or on the same page? =

Yep. You can display multiple feeds by using our built-in shortcode options, for example: `[social-wall][youtube-feed channel="smashballoon" num=3][/social-wall]`.

= How do I embed a social wall feed directly into a WordPress page template? =

You can embed your social wall feed directly into a template file by using the WordPress [do_shortcode](http://codex.wordpress.org/Function_Reference/do_shortcode) function: `<?php echo do_shortcode('[social-wall][instagram-feed][custom-facebook-feed][custom-twitter-feeds][youtube-feeds][/social-wall]'); ?>`.

= Will Social Wall work with W3 Total Cache or other caching plugins? =

The Social WAll plugin should work in compatibility with most, if not all, caching plugins, but you may need to tweak the settings in order to allow the social media feeds to update successfully and display your latest posts.  If you are experiencing problems with your social media feeds not updating then try disabling either 'Page Caching' or 'Object Caching' in W3 Total Cache (or any other similar caching plugin) to see whether that fixes the problem.

== Screenshots ==

== Changelog ==
= 2.3.0 =
* New: TikTok videos now play in a standard video player instead of an iframe.
* Fix: Improved our integration with TikTok feeds to better handle preview images and cache updates.
* Fix: Fixed an issue preventing Social Wall from showing YouTube stats.

= 2.2.2 =
* Fix: Fixed an issue with Facebook posts and loading more posts.
* Fix: Fixed an issue with Facebook multi-feeds not working correctly.

= 2.2.1 =
* Fix: Tweets in your feed will now inherit the branding set in Custom Twitter Feeds Pro (X vs Twitter logos and color).
* Fix: Fixed a PHP error occurring in PHP 8.0+ "Creation of dynamic property SW_Feed::$atts".
* Fix: Fixed Instagram post .webp local images not working in feeds.
* Fix: Fixed MYSQL error related to a missing table when TikTok Feeds was not installed.
* Fix: Fixed TikTok posts not using local images.
* Fix: Fixed TikTok filter not working in the related tool that can be added above a feed.

= 2.2 =
* New: Added compatibility with TikTok Feeds Pro! Display your latest TikTok videos in your social wall feeds.

= 2.1 =
* New: Added support for GDPR features. This is enabled by default if you are using a supported GDPR plugin. Configure on the settings page, "Feeds" tab.
* Fix: Fixed an issue with post filtering that would hide Facebook posts unless the Facebook feed was saved.
* Fix: Fixed the thumbnail for Twitter Cards that contained YouTube links not displaying.
* Fix: Fixed YouTube's JavaScript code being included on the page even when YouTube was not a part of the feed.
* Fix: Fixed an issue causing Instagram hashtag posts to not appear in the feed when an Instagram hashtag was included.
* Fix: Fixed a fatal PHP error that would occur with PHP 8.0+ under certain circumstances.

= 2.0.2 =
* Fix: Tablet columns and number settings were not working.
* Fix: Fixed only 20 feeds showing up as available for selection when creating a new feed.
* Fix: Fixed an issue with some date format settings.

= 2.0.1 =
* Fix: Fixed undefined constant "GLOB_BRACE" causing errors on some servers.
* Fix: Free Smash Balloon plugin settings menus were not available when Social Wall was active.
* Fix: Fixed caching conflicts that would cause the wrong posts to be loaded when using the load more button under certain circumstances.
* Fix: Fixed missing support articles when visiting the "Support" page.

= 2.0 =
* Important: Minimum supported PHP version has been raised from 5.6 to 7.1.
* New: Our biggest update ever! We've completely redesigned the plugin settings from head to toe to make it easier to create, manage, and customize your Social Wall feeds.
* New: All your feeds are now displayed in one place on the "All Feeds" page. This is where you'll find the ability to edit existing feeds and any new ones that you create.
* New: Easily edit individual feed settings for new feeds instead of cumbersome shortcode options.
* New: It's now much easier to create feeds. Just click "Add New", select a feed created with the supported plugins, and you're done!
* New: Brand new feed customizer. We've completely redesigned feed customization from the ground up, reorganizing the settings to make them easier to find.
* New: Live Feed Preview. You can now see changes you make to your feeds in real time, right in the settings page. Easily preview them on desktop, tablet, and mobile sizes.
* New: We've added a new Feed Templates feature. You can now select a feed template when creating a feed to make it much quicker and easier to get started with the type of feed you want to display. Selecting a template preconfigures the feed customization settings to match that template, saving you time and effort.
* New: Color Scheme option. It's now easier than ever to change colors across your feed without needing to adjust individual color settings. Just set a color scheme to effortlessly change colors across your entire feed.
* New: You can now change the number of columns in your feed across desktop, tablet, and mobile.
* New: Easily import and export feed settings to make it simple to move feeds across sites.

= 1.0.8 =
* Fix: Updated the shortcode generator on the "Configure" tab to work with feeds created in version 2.0 of Custom Twitter Feeds.
* Fix: New Twitter cards were not being created after updating Custom Twitter Feeds Pro.

= 1.0.7 =
* Fix: Added compatibility with version 2.0 of the Custom Twitter Feeds plugin.

= 1.0.6 =
* Fix: Updated the shortcode generator on the "Configure" tab to work with feeds created in version 6.0 of Instagram Feed.

= 1.0.5 =
* Fix: Instagram Feed accounts only being used with Social Wall would be removed after a period of time.
* Fix: Fixed inability to disable some advanced settings once they were enabled.

= 1.0.4 =
* Fix: Added compatibility with version 4.0 of the Custom Facebook Feed plugin.

= 1.0.3 =
* Tweak: Added compatibility with the latest version of the Custom Facebook Feed plugin. Please update both plugins to ensure compatibility.

= 1.0.2 =
* Fix: Using "num=" in the shortcode would lead to an inconsistent number of posts actually being displayed.
* Fix: Twitter cards would not load in the feed until the social wall cache had cleared after they were generated.
* Fix: Fixed a JavaScript error that would cause carousel feeds to not work when using Internet Explorer.
* Fix: Added a maximum width to images to prevent images being too large in certain themes.
* Fix: Prevented duplicate posts/tweets/videos from appearing in the feed.

= 1.0.1 =
* New: Added support for local, resized images for Twitter. After updating to version 1.11 for Custom Twitter feeds, use local images for Twitter Cards and medium sized images for your tweets in your feed.
* Fix: Backup cache refresh feature changed from being triggered 2 days after feed missed an update to being triggered relative to cache refresh time.
* Fix: Translation files added.
* Fix: Relative date text settings would not save.
* Fix: Fixed a PHP warning "undefined index $wall_account_data".

= 1.0 =
* Launched the Social Wall plugin!

