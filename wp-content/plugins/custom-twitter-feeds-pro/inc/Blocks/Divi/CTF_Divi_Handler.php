<?php

namespace TwitterFeed\Blocks\Divi;

use TwitterFeed\Blocks\CTF_Blocks_Integration;

/**
 * Class Divi Handler.
 *
 * @since 2.5
 */
class CTF_Divi_Handler
{
	/**
	 * Constructor.
	 *
	 * @since 2.5
	 */
	public function init()
    {
		$this->load();
	}

	/**
	 * Indicate if current integration is allowed to load.
	 *
	 * @since 2.5
	 *
	 * @return bool
	 */
	public function allow_load()
	{
		if (function_exists('et_divi_builder_init_plugin')) {
			return true;
		}
		$allow_themes = ['Divi'];
		$theme        = wp_get_theme();
		$theme_name   = $theme->get_template();
		$theme_parent = $theme->parent();

		return (bool) array_intersect([$theme_name, $theme_parent], $allow_themes);
	}

	/**
	 * Load an integration.
	 *
	 * @since 2.5
	 */
	public function load()
	{
		if ($this->allow_load()) {
			$this->hooks();
		}
	}

	/**
	 * Hooks.
	 *
	 * @since 2.5
	 */
	public function hooks() {
		add_action('et_builder_ready', [$this, 'register_module']);

		if (wp_doing_ajax()) {
			add_action('wp_ajax_sb_twitterfeed_divi_preview', [$this, 'preview']);
		}

		if ($this->is_divi_builder()) {
			add_action('wp_enqueue_scripts', [$this, 'builder_scripts']);
		}
	}
 
	/**
	 * Load scripts.
	 *
	 * @since 2.5
	 */
	public function builder_scripts() {
		wp_enqueue_style('ctf_styles', trailingslashit(CTF_PLUGIN_URL) . 'css/ctf-styles.min.css', array(), CTF_VERSION);

    	wp_enqueue_script(
			'ctfscripts',
			CTF_PLUGIN_URL . 'js/ctf-scripts.min.js',
			array('jquery'),
			CTF_VERSION,
			true
		);

		wp_localize_script('ctfscripts', 'ctfOptions', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'ctf_nonce' ),
			'font_method' => 'svg',
			'placeholder' => trailingslashit( CTF_PLUGIN_URL ) . 'img/placeholder.png',
			'resized_url' => ctf_get_resized_uploads_url(),
		));

		wp_enqueue_script(
			'sbtwitter-divi',
			// The unminified version is not supported by the browser.
			CTF_PLUGIN_URL . 'js/divi-handler.min.js',
			['react', 'react-dom', 'jquery'],
			CTF_VERSION,
			true
		);

		wp_localize_script(
			'sbtwitter-divi',
			'sb_divi_builder',
			[
				'ajax_handler'		=> admin_url('admin-ajax.php'),
				'nonce'             => wp_create_nonce('ctf-admin'),
				'feed_splash' 		=> htmlspecialchars(CTF_Blocks_Integration::get_widget_cta('button'))
			]
		);
	}

	/**
	 * Register module.
	 *
	 * @since 2.5
	 */
	public function register_module()
	{
		if (!class_exists('ET_Builder_Module')) {
			return;
		}

		new SB_Twitter_Feed();
	}

	/**
	 * Ajax handler for the Feed preview.
	 *
	 * @since 2.5
	 */
	public function preview() {
		check_ajax_referer('ctf-admin' , 'nonce');

		$cap = current_user_can('manage_twitter_feed_options') ? 'manage_twitter_feed_options' : 'manage_options';
		$cap = apply_filters('ctf_settings_pages_capability', $cap );
		if (!current_user_can($cap)) {
			wp_send_json_error(); // This auto-dies.
		}

		$feed_id = absint(filter_input(INPUT_POST, 'feed_id', FILTER_SANITIZE_NUMBER_INT));

		wp_send_json_success(
			do_shortcode(
				sprintf(
					'[custom-twitter-feeds feed="%1$s"]',
					absint($feed_id)
				)
			)
		);
	}

	/**
	 * Determine if a current page is opened in the Divi Builder.
	 *
	 * @since 2.3
	 *
	 * @return bool
	 */
	private function is_divi_builder() {
		return !empty($_GET['et_fb']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}