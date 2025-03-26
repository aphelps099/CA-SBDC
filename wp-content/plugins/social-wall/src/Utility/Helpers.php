<?php

namespace SB\SocialWall\Utility;

use TwitterFeed\Builder\CTF_Db;
use CustomFacebookFeed\Builder\CFF_Db;
use InstagramFeed\Builder\SBI_Db;
use Smashballoon\Customizer\DB as SBY_Db;

class Helpers {


    /**
     * Get All Icons Array
     *
     * @return array
     *
     * @since 1.0
     */
    public static function get_icons(){
        $icons = [];
        $icons_dir = SBSW_PLUGIN_DIR . 'assets/images/feed-editor';
        $icons_list = glob($icons_dir . "/*");
        
        foreach ($icons_list as $icon) {
            $icon_name = str_replace('.svg', '', basename($icon));
	        $icon = self::filter_icon( $icon, $icon_name );
            $icons[$icon_name] = file_get_contents($icon);
        }
        return $icons;
    }


	public static function filter_icon( $icon, $icon_name ) {
		if ( $icon_name === 'twitter' ) {
			return SBSW_PLUGIN_DIR . 'assets/images/feed-editor/x.svg';
		}
		if ( $icon_name === 'twitter-logo' ) {
			return SBSW_PLUGIN_DIR . 'assets/images/feed-editor/x-logo.svg';
		}
		return $icon;
	}

	/**
	 * Get plugins
	 *
	 * @since 2.0.1
	 */
	public static function get_active_plugins() {
		$active_plugins = array();
		$db_active_plugins = get_option( 'active_plugins', array() );

		$sb_plugins = array(
			'custom-facebook-feed-pro/custom-facebook-feed.php' => 'facebook',
			'instagram-feed-pro/instagram-feed.php' => 'instagram',
			'custom-twitter-feeds-pro/custom-twitter-feed.php' => 'twitter',
			'youtube-feed-pro/youtube-feed.php' => 'youtube',
			'tiktok-feeds/tiktok-feeds-pro.php' => 'tiktok',
			'tiktok-feeds-pro/tiktok-feeds-pro.php' => 'tiktok',
		);

		foreach( $sb_plugins as $plugin_path => $plugin ) {
			if ( in_array( $plugin_path, $db_active_plugins ) ) {
				$active_plugins["is_{$plugin}_active"] = true;
			}
		}

		return $active_plugins;
	}

	/**
	 * Get list of installed plugins
	 *
	 * @since 2.0
	 */
	public static function get_installed_plugins() {
		// get the WordPress's core list of installed plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = array();
		$db_installed_plugins = get_plugins();

		$sb_plugins = array(
			'custom-facebook-feed-pro/custom-facebook-feed.php' => 'facebook',
			'instagram-feed-pro/instagram-feed.php' => 'instagram',
			'custom-twitter-feeds-pro/custom-twitter-feed.php' => 'twitter',
			'youtube-feed-pro/youtube-feed.php' => 'youtube',
			'tiktok-feeds/tiktok-feeds-pro.php' => 'tiktok',
		);

		foreach( $sb_plugins as $plugin_path => $plugin ) {
			if ( array_key_exists( $plugin_path, $db_installed_plugins ) ) {
				$installed_plugins["is_{$plugin}_installed"] = true;
			}
			$installed_plugins["{$plugin}_plugin"] = $plugin_path;
		}

		return $installed_plugins;
	}


	/**
	 * Get active plugins list
	 *
	 * @since 2.0.1
	 */
	public static function get_active_plugins_for_sw_menu() {
		$active_plugins = array();
		$db_active_plugins = get_option('active_plugins', array());
		$sb_plugins = array(
			'custom-facebook-feed/custom-facebook-feed.php' => 'facebook',
			'custom-facebook-feed-pro/custom-facebook-feed.php' => 'facebook',
			'instagram-feed/instagram-feed.php' => 'instagram',
			'instagram-feed-pro/instagram-feed.php' => 'instagram',
			'custom-twitter-feeds/custom-twitter-feed.php' => 'twitter',
			'custom-twitter-feeds-pro/custom-twitter-feed.php' => 'twitter',
			'feeds-for-youtube/youtube-feed.php' => 'youtube',
			'youtube-feed-pro/youtube-feed.php' => 'youtube',
			'tiktok-feeds/tiktok-feeds-pro.php' => 'tiktok',
			'tiktok-feeds-pro/tiktok-feeds-pro.php' => 'tiktok',
		);

		foreach( $sb_plugins as $plugin_path => $plugin ) {
			if ( in_array( $plugin_path, $db_active_plugins ) ) {
				$active_plugins["is_{$plugin}_active"] = true;
			}
		}

		return $active_plugins;
	}

	/**
	 * Get list of installed plugins
	 *
	 * @since 2.0
	 */
	public static function get_installed_plugins_for_sw_menu() {
		// get the WordPress's core list of installed plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = array();
		$db_installed_plugins = get_plugins();

		$sb_plugins = array(
			'custom-facebook-feed/custom-facebook-feed.php' => 'facebook',
			'custom-facebook-feed-pro/custom-facebook-feed.php' => 'facebook',
			'instagram-feed/instagram-feed.php' => 'instagram',
			'instagram-feed-pro/instagram-feed.php' => 'instagram',
			'custom-twitter-feeds/custom-twitter-feed.php' => 'twitter',
			'custom-twitter-feeds-pro/custom-twitter-feed.php' => 'twitter',
			'feeds-for-youtube/youtube-feed.php' => 'youtube',
			'youtube-feed-pro/youtube-feed.php' => 'youtube',
			'tiktok-feeds/tiktok-feeds-pro.php' => 'tiktok',
			'tiktok-feeds-pro/tiktok-feeds-pro.php' => 'tiktok',
		);

		foreach( $sb_plugins as $plugin_path => $plugin ) {
			if (array_key_exists($plugin_path, $db_installed_plugins)) {
				$installed_plugins["is_{$plugin}_installed"] = true;
				$installed_plugins["{$plugin}_plugin"] = $plugin_path;
			}
		}

		return $installed_plugins;
	}


	/**
	 * Get WP Pages List
	 *
	 * @return array
	 *
	 * @since 2.0
	 */
	public static function get_wp_pages(){
		$pagesList = get_pages();
		$pagesResult = array();
		if (is_array($pagesList)) {
			foreach ($pagesList as $page) {
				array_push(
					$pagesResult,
					array(
						'id' => $page->ID,
						'title' => $page->post_title,
					)
				);
			}
		}
		return $pagesResult;
	}

	/**
	 * Get Smahballoon Plugins Info
	 *
	 * @since 2.0
	 */
	public static function get_plugins_info()
	{
		$installed_plugins = self::get_installed_plugins();
		$active_plugins = self::get_active_plugins();

		return [
			'facebook' => [
				'plugin' => $installed_plugins['facebook_plugin'],
				'download_plugin' => 'https://downloads.wordpress.org/plugin/custom-facebook-feed.zip',
				'title' => __('Custom Facebook Feed', 'social-wall'),
				'description' => __('Add Facebook posts from your timeline, albums and much more.', 'social-wall'),
				'icon' => 'fb-icon.svg',
				'installed' => isset($installed_plugins['is_facebook_installed']),
				'activated' => isset($active_plugins['is_facebook_active']),
			],
			'instagram' => [
				'plugin' => $installed_plugins['instagram_plugin'],
				'download_plugin' => 'https://downloads.wordpress.org/plugin/instagram-feed.zip',
				'title' => __('Instagram Feed', 'social-wall'),
				'description' => __('A quick and elegant way to add your Instagram posts to your website. ', 'social-wall'),
				'icon' => 'insta-icon.svg',
				'installed' => isset($installed_plugins['is_instagram_installed']),
				'activated' => isset($active_plugins['is_instagram_active']),
			],
			'twitter' => [
				'plugin' => $installed_plugins['twitter_plugin'],
				'download_plugin' => 'https://downloads.wordpress.org/plugin/custom-twitter-feeds.zip',
				'title' => __('Custom Twitter Feeds', 'social-wall'),
				'description' => __('A customizable way to display tweets from your Twitter account. ', 'social-wall'),
				'icon' => 'twitter-icon.svg',
				'installed' => isset($installed_plugins['is_twitter_installed']),
				'activated' => isset($active_plugins['is_twitter_active']),
			],
			'youtube' => [
				'plugin' => $installed_plugins['youtube_plugin'],
				'download_plugin' => 'https://downloads.wordpress.org/plugin/feeds-for-youtube.zip',
				'title' => __('Feeds for YouTube', 'social-wall'),
				'description' => __('A simple yet powerful way to display videos from YouTube. ', 'social-wall'),
				'icon' => 'youtube-icon.svg',
				'installed' => isset($installed_plugins['is_youtube_installed']),
				'activated' => isset($active_plugins['is_youtube_active']),
			]
		];
	}

	/**
	 * Get Smahballoon Recommended Plugins Info
	 *
	 * @since 2.0
	 */
	public static function get_smashballoon_recommended_plugins_info()
	{
		$installed_plugins = get_plugins();
		return [
			'wpforms'         => [
				'plugin'          => 'wpforms-lite/wpforms.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/wpforms-lite.zip',
				'title'           => __( 'WPForms', 'social-wall' ),
				'description'     => __( 'The most beginner friendly drag & drop WordPress forms plugin allowing you to create beautiful contact forms, subscription forms, payment forms, and more in minutes, not hours!', 'social-wall' ),
				'icon'            => 'plugin-wpforms.png',
				'installed'       => isset( $installed_plugins['wpforms-lite/wpforms.php'] ),
				'activated'       => is_plugin_active( 'wpforms-lite/wpforms.php' ),
			],
			'monsterinsights' => [
				'plugin'          => 'google-analytics-for-wordpress/googleanalytics.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.zip',
				'title'           => __( 'MonsterInsights', 'social-wall' ),
				'description'     => __( 'MonsterInsights makes it “effortless” to properly connect your WordPress site with Google Analytics, so you can start making data-driven decisions to grow your business.', 'social-wall' ),
				'icon'            => 'plugin-mi.png',
				'installed'       => isset( $installed_plugins['google-analytics-for-wordpress/googleanalytics.php'] ),
				'activated'       => is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ),
			],
			'optinmonster'    => [
				'plugin'          => 'optinmonster/optin-monster-wp-api.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/optinmonster.zip',
				'title'           => __( 'OptinMonster', 'social-wall' ),
				'description'     => __( 'Our high-converting optin forms like Exit-Intent® popups, Fullscreen Welcome Mats, and Scroll boxes help you dramatically boost conversions and get more email subscribers.', 'social-wall' ),
				'icon'            => 'plugin-om.png',
				'installed'       => isset( $installed_plugins['optinmonster/optin-monster-wp-api.php'] ),
				'activated'       => is_plugin_active( 'optinmonster/optin-monster-wp-api.php' ),
			],
			'wp_mail_smtp'    => [
				'plugin'          => 'wp-mail-smtp/wp_mail_smtp.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
				'title'           => __( 'WP Mail SMTP', 'social-wall' ),
				'description'     => __( 'Make sure your website\'s emails reach the inbox. Our goal is to make email deliverability easy and reliable. Trusted by over 1 million websites.', 'social-wall' ),
				'icon'            => 'plugin-smtp.png',
				'installed'       => isset( $installed_plugins['wp-mail-smtp/wp_mail_smtp.php'] ),
				'activated'       => is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ),
			],
			'rafflepress'     => [
				'plugin'          => 'rafflepress/rafflepress.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/rafflepress.zip',
				'title'           => __( 'RafflePress', 'social-wall' ),
				'description'     => __( 'Turn your visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with powerful viral giveaways & contests.', 'social-wall' ),
				'icon'            => 'plugin-rp.png',
				'installed'       => isset( $installed_plugins['rafflepress/rafflepress.php'] ),
				'activated'       => is_plugin_active( 'rafflepress/rafflepress.php' ),
			],
			'aioseo'          => [
				'plugin'          => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
				'download_plugin' => 'https://downloads.wordpress.org/plugin/all-in-one-seo-pack.zip',
				'title'           => __( 'All in One SEO Pack', 'social-wall' ),
				'description'     => __( 'Out-of-the-box SEO for WordPress. Features like XML Sitemaps, SEO for custom post types, SEO for blogs, business sites, or ecommerce sites, and much more.', 'social-wall' ),
				'icon'            => 'plugin-seo.png',
				'installed'       => isset( $installed_plugins['all-in-one-seo-pack/all_in_one_seo_pack.php'] ),
				'activated'       => is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ),
			]
		];
	}

	/**
	 * Get the plugin feeds for legacy wall feed
	 *
	 * This method will check what plugins are activated
	 * Based on the active plugins, it will return the first feed ID along with the plugin slug
	 *
	 * @since 2.0
	 */
	public static function get_legacy_feed_plugins() {
		$active_plugins = self::get_active_plugins();
		$return = array();

		if ( isset( $active_plugins['is_facebook_active'] ) ) {
			if ( class_exists( CFF_Db::class ) ) {
				$cff_feeds = CFF_Db::feeds_query();
				$return['facebook'] = (object) array(
					'id' => $cff_feeds[0]['id']
				);
			}
		}
		if ( isset( $active_plugins['is_instagram_active'] ) ) {
			if ( class_exists( SBI_Db::class ) ) {
				$sbi_feeds = SBI_Db::feeds_query();
				$return['instagram'] = (object) array(
					'id' => $sbi_feeds[0]['id']
				);
			}
		}
		if ( isset( $active_plugins['is_twitter_active'] ) ) {
			if ( class_exists( CTF_Db::class ) ) {
				$ctf_feeds = CTF_Db::feeds_query();
				$return['twitter'] = (object) array(
					'id' => $ctf_feeds[0]['id']
				);
			} else {
				$return['twitter'] = [];
			}
		}
		if ( isset( $active_plugins['is_youtube_active'] ) ) {
			if ( class_exists( SBY_Db::class ) ) {
				$sby_db = new SBY_Db();
				$sby_feeds = $sby_db->feeds_query();
				$return['youtube'] = (object) array(
					'id' => $sby_feeds[0]['id']
				);
			}
		}

		return $return;
	}

	/**
	 * Get Facebook Feeds
	 *
	 * @since 2.2
	 *
	 * @return array
	 */
	public static function get_cff_feeds() {
		if (!sbsw_facebook_feed_is_minimum_version() ||
		    ! sbsw_social_wall_is_minimum_version_for_facebook_feed()) {
			return array();
		}
		global $wpdb;

		$limit = apply_filters( 'social_wall_facebook_feeds_limit', 100 );

		$feeds_table_name = $wpdb->prefix . 'cff_feeds';
		$sql = $wpdb->prepare( "SELECT * FROM $feeds_table_name LIMIT %d", $limit );

		return $wpdb->get_results($sql, ARRAY_A);
	}

	/**
	 * Get Instagram Feeds
	 *
	 * @since 2.2
	 *
	 * @return array
	 */
	public static function get_sbi_feeds() {
		if (!sbsw_instagram_feed_is_minimum_version() ||
		    ! sbsw_social_wall_is_minimum_version_for_instagram_feed()) {
			return array();
		}
		global $wpdb;

		$limit = apply_filters( 'social_wall_instagram_feeds_limit', 100 );

		$feeds_table_name = $wpdb->prefix . 'sbi_feeds';
		$sql = $wpdb->prepare( "SELECT * FROM $feeds_table_name LIMIT %d", $limit );

		return $wpdb->get_results($sql, ARRAY_A);
	}

	/**
	 * Get Twitter Feeds
	 *
	 * @since 2.2
	 *
	 * @return array
	 */
	public static function get_ctf_feeds() {
		if (!sbsw_twitter_feed_is_minimum_version() ||
		    ! sbsw_social_wall_is_minimum_version_for_twitter_feed()) {
			return array();
		}
		global $wpdb;

		$limit = apply_filters( 'social_wall_twitter_feeds_limit', 100 );

		$feeds_table_name = $wpdb->prefix . 'ctf_feeds';
		$sql = $wpdb->prepare( "SELECT * FROM $feeds_table_name LIMIT %d", $limit );

		return $wpdb->get_results($sql, ARRAY_A);
	}

	/**
	 * Get YouTube Feeds
	 *
	 * @since 2.2
	 *
	 * @return array
	 */
	public static function get_sby_feeds() {
		if (!sbsw_youtube_feed_is_minimum_version() ||
		    ! sbsw_social_wall_is_minimum_version_for_youtube_feed()) {
			return array();
		}
		global $wpdb;

		$limit = apply_filters( 'social_wall_youtube_feeds_limit', 100 );

		$feeds_table_name = $wpdb->prefix . 'sby_feeds';
		$sql = $wpdb->prepare( "SELECT * FROM $feeds_table_name LIMIT %d", $limit );

		return $wpdb->get_results($sql, ARRAY_A);
	}

	/**
	 * Get TikTok Feeds
	 *
	 * @since 2.2
	 *
	 * @return array
	 */
	public static function get_sbtt_feeds()
	{
		if (!sbsw_tiktok_feeds_is_minimum_version() ||
		    ! sbsw_social_wall_is_minimum_version_for_tiktok_feeds()) {
			return array();
		}

		global $wpdb;

		$limit = apply_filters('social_wall_tiktok_feeds_limit', 100);

		$feeds_table_name = $wpdb->prefix . 'sbtt_feeds';
		$sql = $wpdb->prepare("SELECT * FROM $feeds_table_name LIMIT %d", $limit);

		return $wpdb->get_results($sql, ARRAY_A);
	}

}
