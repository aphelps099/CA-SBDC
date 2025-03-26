<?php

namespace SB\SocialWall;

/**
 * Class GDPR_Integrations
 *
 * Adds GDPR related workarounds for third-party plugins
 *
 * @since 2.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class GDPR_Integrations {

    /**
	 * Whether or not consent plugins that Social Wall is compatible with are active.
	 *
	 * @return bool|string
	 */
	public static function gdpr_plugins_active() {
		if ( class_exists( 'Cookie_Notice' ) ) {
			return 'Cookie Notice by dFactory';
		}
		if ( class_exists( 'Cookie_Law_Info' ) ) {
			return 'GDPR Cookie Consent by WebToffee';
		}
		if ( defined( 'CKY_APP_ASSETS_URL' ) ) {
			return 'CookieYes | GDPR Cookie Consent by CookieYes';
		}
		if ( class_exists( 'Cookiebot_WP' ) ) {
			return 'Cookiebot by Cybot A/S';
		}
		if ( class_exists( 'COMPLIANZ' ) ) {
			return 'Complianz by Really Simple Plugins';
		}
		if ( function_exists( 'BorlabsCookieHelper' ) ) {
			return 'Borlabs Cookie by Borlabs';
		}

		return false;
	}

	/**
	 * GDPR features can be added automatically, forced enabled,
	 * or forced disabled.
	 *
	 * @param $settings
	 *
	 * @return bool
	 */
	public static function doing_gdpr( $settings ) {
		$gdpr = isset( $settings['gdpr'] ) ? $settings['gdpr'] : 'auto';
		if ( $gdpr === 'no' ) {
			return false;
		}
		if ( $gdpr === 'yes' ) {
			return true;
		}
		return ( self::gdpr_plugins_active() !== false );
	}

	public static function blocking_cdn( $settings ) {
		
	}

	/**
	 * GDPR features are reliant on the image resizing features
	 *
	 * @param bool $retest
	 *
	 * @return bool
	 */
	public static function gdpr_tests_successful( $retest = false ) {
		
	}

	public static function gdpr_tests_error_message() {
		
	}

	public static function statuses() {
		
	}

}
