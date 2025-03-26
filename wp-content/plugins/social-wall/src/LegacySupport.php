<?php

namespace SB\SocialWall;

/**
 * Support class for the Social Wall legacy classes.
 *
 * @package SB\SocialWall
 */
class LegacySupport {

	/**
	 * Register legacy classes.
	 *
	 * @return void
	 */
	public static function register_classes() {
		require_once SocialWall::$legacy_directory . 'sw-functions.php';
		require_once SocialWall::$legacy_directory . 'class-sw-display-elements.php';
		require_once SocialWall::$legacy_directory . 'class-sw-feed.php';
		require_once SocialWall::$legacy_directory . 'class-sw-parse.php';
		require_once SocialWall::$legacy_directory . 'class-sw-settings.php';
		require_once SocialWall::$legacy_directory . 'class-sw-cron-updater.php';

		if ( is_admin() ) {
			require_once SocialWall::$legacy_directory . 'admin/class-sw-vars.php';
			require_once SocialWall::$legacy_directory . 'admin/admin-functions.php';
			require_once SocialWall::$legacy_directory . 'admin/class-sw-admin.php';
			sbsw_admin_init();
		}
	}
}
