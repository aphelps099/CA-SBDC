<?php

namespace SB\SocialWall;

use SB\SocialWall\Core\Abstracts\Provider;
use SB\SocialWall\Admin\AdminServiceProvider;
use SB\SocialWall\Admin\Services\Blocks;

/**
 * Bootstrap class for the Social Wall plugin.
 *
 * @since 2.0
 */
class Bootstrap {

	/**
	 * Holds plugins' service providers.
	 *
	 * @var string[] $providers
	 */
	protected static $providers = [
		AdminServiceProvider::class,
	];

	/**
	 * Runs the Bootstrap process.
	 *
	 * @return void
	 */
	public static function run() {
		add_action( 'plugins_loaded', [ LegacySupport::class, 'register_classes' ], 10 );
		add_action( 'plugins_loaded', [ self::class, 'register_service_providers' ], 40 );
		add_action( 'wp_loaded', [ self::class, 'check_for_db' ], 10 );
		add_action( 'init', [ TextDomain::class, 'load_text_domain' ] );
	}

	public static function register_service_providers() {
		foreach ( self::$providers as $provider ) {
			if ( class_exists( $provider ) && is_subclass_of( $provider, Provider::class ) ) {
				new $provider();
			}
		}

		// Register blocks
		$sw_blocks = new Blocks();
		if ( $sw_blocks->allow_load() ) {
			$sw_blocks->load();
		}
	}

	public static function check_for_db() {
		Database::create_tables();

		$db_version = get_option( 'sbsw_db_version' );
		if ( ! $db_version ) {
			self::handle_legacy_feed_support();
			update_option( 'sbsw_db_version', SW_DBVERSION );
		}
	}

	/**
	 * Provide legacy feed support for previous versions
	 * 
	 * @since 2.0
	 */
	public static function handle_legacy_feed_support() {
		$legacy_feeds = Database::check_legacy_feed();

		if ( count( $legacy_feeds ) > 0 ) {
			update_option( 'sbsw_legacy_support', true );
		}
	}
}
