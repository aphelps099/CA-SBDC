<?php

namespace SB\SocialWall;

/**
 * Main class for the Social Wall plugin.
 *
 * @since 2.0
 */
class SocialWall {

	/**
	 * @var string
	 */
	public static $plugin_file;

	/**
	 * @var string
	 */
	public static $plugin_directory;

	/**
	 * @var string
	 */
	public static $plugin_url;

	/**
	 * @var string
	 */
	public static $build_url;

	/**
	 * @var string
	 */
	public static $legacy_directory;

	/**
	 * @var string
	 */
	public static $text_domain_directory;

	/**
	 * @var Container
	 */
	public static $container;

	/**
	 *  SocialWall Constructor.
	 */
	public function __construct() {
		$this->init();
		$this->register_lifecycle();

		Bootstrap::run();
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	protected function init() {
		self::$plugin_file           = SBSW_PLUGIN_FILE;
		self::$plugin_directory      = SBSW_PLUGIN_DIR;
		self::$plugin_url            = plugins_url( '', self::$plugin_file );
		self::$build_url             = self::$plugin_url . '/build';
		self::$legacy_directory      = trailingslashit( self::$plugin_directory . 'inc' );
		self::$text_domain_directory = self::$plugin_directory . '/languages';
	}

	/**
	 * Registers life-cycle hooks.
	 *
	 * @return void
	 */
	protected function register_lifecycle() {
		register_activation_hook( self::$plugin_file, [ Activate::class, 'handle' ] );
		register_deactivation_hook( self::$plugin_file, [ Deactivate::class, 'handle' ] );
	}

	/**
	 * Initializes the SocialWall class.
	 *
	 * Checks for an existing SocialWall instance
	 * and if it doesn't find one, creates it.
	 *
	 * @return SocialWall
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}
