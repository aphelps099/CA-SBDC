<?php

namespace SB\SocialWall\Admin\Services;

use SB\SocialWall\Admin\Settings;
use SB\SocialWall\Admin\Builder;
use SB\SocialWall\Core\Abstracts\Service;
use SB\SocialWall\Admin\Services\License;

class Ajax extends Service {

	/**
	 * Registers all hooks for the class.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_sw_save_settings', [ $this, 'save_settings' ] );
		add_action( 'wp_ajax_sw_install_plugin', [ $this, 'install_plugin' ] );
		add_action( 'wp_ajax_sw_activate_plugin', [ $this, 'activate_plugin' ] );
		add_action( 'wp_ajax_sbsw_builder_update', [ Builder::class, 'sbsw_builder_update' ] );
		add_action( 'wp_ajax_sbsw_fly_preview', [ Builder::class, 'feed_customizer_fly_preview' ] );
		add_action( 'wp_ajax_sw_refresh_wall_plugins', [ Builder::class, 'refresh_wall_plugins' ] );
		add_action( 'wp_ajax_sw_get_feeds', [ Builder::class, 'refresh_feeds' ] );
		add_action( 'wp_ajax_sw_create_feed', [ Builder::class, 'create_feed' ] );
		add_action( 'wp_ajax_sw_delete_feed', [ Builder::class, 'delete_feed' ] );
		add_action( 'wp_ajax_sw_bulk_delete_feed', [ Builder::class, 'bulk_delete_feed' ] );
		add_action( 'wp_ajax_sw_duplicate_feed', [ Builder::class, 'duplicate_feed' ] );
		add_action( 'wp_ajax_sbsw_update_wall_source', [ Builder::class, 'update_source' ] );
		add_action( 'wp_ajax_sbsw_remove_wall_source', [ Builder::class, 'remove_source' ] );
		add_action( 'wp_ajax_sbsw_clear_feed_cache', [ Builder::class, 'sbsw_clear_feed_cache' ] );
		add_action( 'wp_ajax_sbsw_activate_license', [ License::class, 'activate_license' ] );
		add_action( 'wp_ajax_sbsw_deactivate_license', [ License::class, 'deactivate_license' ] );
	}

	/**
	 * Saves social wall settings.
	 *
	 * @return void
	 */
	public function save_settings() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'sbsw_admin_settings' ) ) {
			wp_send_json_error( __( 'Invalid nonce', 'social-wall' ), 403 );
		}

		$data = [];

		foreach ( Settings::get_defaults() as $key => $value ) {
			if ( isset( $_POST[ $key ] ) ) {
				$data[ $key ] = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
			}
		}

		$settings = Settings::save( $data );

		wp_send_json_success( $settings, 200 );
	}

	public function install_plugin() {
		require_once trailingslashit( SBSW_PLUGIN_DIR ) . 'src/PluginSilentUpgrader.php';
		require_once trailingslashit( SBSW_PLUGIN_DIR ) . 'src/PluginSilentUpgraderSkin.php';
		// run a security check
		check_ajax_referer( 'sbsw_admin_settings', 'nonce' );

		$plugin_path = sanitize_text_field( $_POST['plugin'] );
		$should_activate_plugin = false;

		$error = esc_html__( 'Could not install plugin. Please download from wpforms.com and install manually.', 'social-wall' );
	
		if ( empty( $_POST['plugin'] ) ) {
			wp_send_json_error( $error );
		}

		if ( isset( $_POST['installed'] ) && ($_POST['installed'] == false || $_POST['installed'] == 'false') ) {
			$plugin_path = sanitize_text_field( $_POST['downloadPlugin'] );
		} else {
			$should_activate_plugin = true;
		}

		if ( $should_activate_plugin ) {
			$activate = activate_plugins( $plugin_path );
			if ( ! is_wp_error( $activate ) ) {
				wp_send_json_success(
					array(
						'msg'          => esc_html__( 'Plugin activated', 'social-wall' ),
						'is_activated' => true,
					)
				);
			}
		}

		// Prepare variables.
		$url = esc_url_raw(
			add_query_arg(
				array(
					'page' => 'sbsw',
				),
				admin_url( 'admin.php' )
			)
		);
	
		$creds = request_filesystem_credentials( $url, '', false, false, null );

		// Check for file system permissions.
		if ( false === $creds ) {
			wp_send_json_error( $error );
		}
	
		if ( ! WP_Filesystem( $creds ) ) {
			wp_send_json_error( $error );
		}

		/*
		 * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		 */
		require_once SBSW_PLUGIN_DIR . 'src/class-install-skin.php';
	
		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );
	
		// Create the plugin upgrader with our custom skin.
		$installer = new \SB\SocialWall\PluginSilentUpgrader( new \SB\SocialWall\SW_Install_Skin() );
	
		// Error check.
		if ( ! method_exists( $installer, 'install' ) ) {
			wp_send_json_error( $error );
		}

		$installer->install( $plugin_path ); // phpcs:ignore
	
		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();
	
		$plugin_basename = $installer->plugin_info();

		if ( $plugin_basename ) {
			// Activate the plugin silently.
			$activated = activate_plugin( $plugin_basename );
	
			if ( ! is_wp_error( $activated ) ) {
				wp_send_json_success(
					array(
						'msg'          => esc_html__( 'Plugin installed and activated', 'social-wall' ),
						'is_activated' => true,
						'basename'     => $plugin_basename,
					)
				);
			}
		}

		wp_send_json_error( $error );
	}

	/**
	 * Activate plugin via AJAX request 
	 * Only sent the request from the sidebar menu
	 * 
	 * @since 2.0
	 */
	public function activate_plugin() {
		check_ajax_referer( 'sbsw_admin_settings', 'nonce' );

		if ( empty( $_POST['plugin'] ) ) {
			wp_send_json_error( $error );
		}
		$plugin_name = sanitize_text_field( $_POST['plugin'] );

		$plugin_path = sw_convert_plugin_name_to_path( $plugin_name );
		$activate = activate_plugins( $plugin_path );
		if ( ! is_wp_error( $activate ) ) {
			wp_send_json_success(
				array(
					'msg'          => esc_html__( 'Plugin activated', 'social-wall' ),
					'is_activated' => true,
				)
			);
		}
	}
}
