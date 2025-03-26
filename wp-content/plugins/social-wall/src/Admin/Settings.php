<?php

namespace SB\SocialWall\Admin;
use SB\SocialWall\Admin\Services\License;
use SB\SocialWall\GDPR_Integrations;

class Settings {
	const OPTION_KEY = 'sbsw_settings';

	/**
	 * Gets social wall settings.
	 *
	 * @return array
	 */
	public static function get() {
		$settings = get_option( self::OPTION_KEY, [] );
		$settings['license_status'] = License::get_license_status();
		$settings['license_key'] = License::get_license_key();
		$settings['gdpr_plugin_detected'] = GDPR_Integrations::gdpr_plugins_active();

		return wp_parse_args( $settings, self::get_defaults() );
	}

	/**
	 * Saves social wall settings.
	 *
	 * @return array
	 */
	public static function save( $settings ) {
		self::handle_cron_update( $settings );
		$data = wp_parse_args( $settings, self::get() );
		if ( isset( $data['license_key'] ) ) {
			$license_key = sanitize_text_field( $data['license_key'] );
			update_option( 'sbsw_license_key', $license_key );
		}
		update_option( self::OPTION_KEY, $data );
		return $data;
	}

	/**
	 * Gets default settings for social wall.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return [
			'license_key'			=> License::get_license_key(),
			'preserve_settings'		=> false,
			'gdpr'					=> 'automatic',
			'cache_cron_interval'	=> '1hour',
			'custom_css'			=> '',
			'custom_js'				=> '',
			'ajaxtheme'				=> false,
			'customtemplates'		=> false,
		];
	}

	/**
	 * Handle Cron Updates
	 * 
	 * @since 2.0
	 */
	public static function handle_cron_update( $settings ) {
		$default_settings = self::get();
		if ( isset( $settings['cache_cron_interval'] ) && $settings['cache_cron_interval'] != $default_settings['cache_cron_interval'] ) {
			sbsw_reset_cron( $settings );
		}
	}
}
