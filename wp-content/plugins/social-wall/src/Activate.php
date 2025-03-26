<?php

namespace SB\SocialWall;

use SW_Cron_Updater;
use SB\SocialWall\Database;

/**
 * Class Activate
 *
 * Handles plugin activation.
 */
class Activate {

	/**
	 * Activation hook.
	 *
	 * @return void
	 */
	public static function handle() {
		LegacySupport::register_classes();
		self::add_capabilities();
		self::start_cron_updater();
	}

	/**
	 * Adds capabilities to the administrator role.
	 *
	 * @return void
	 */
	public static function add_capabilities() {
		global $wp_roles;
		$wp_roles->add_cap( 'administrator', 'manage_social_wall_options' );
	}

	/**
	 * Starts the cron updater.
	 *
	 * @return void
	 */
	public static function start_cron_updater() {
		$settings = sbsw_get_database_settings();
		$cache_cron_interval_selected = isset( $settings['cache_cron_interval'] ) ? $settings['cache_cron_interval'] : '1hour';
		$cache_cron_time = isset( $settings['cache_cron_time'] ) ? $settings['cache_cron_time'] : '12';
		$cache_cron_am_pm = isset( $settings['cache_cron_am_pm'] ) ? $settings['cache_cron_am_pm'] : 'am';
		SW_Cron_Updater::start_cron_job( $cache_cron_interval_selected, $cache_cron_time, $cache_cron_am_pm );
	}
}
