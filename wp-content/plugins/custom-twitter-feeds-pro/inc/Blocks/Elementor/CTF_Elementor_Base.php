<?php

namespace TwitterFeed\Blocks\Elementor;

use TwitterFeed\Blocks\Elementor\CTF_Elementor_Control;
use TwitterFeed\Blocks\Elementor\CTF_Elementor_Widget;
use TwitterFeed\Builder\CTF_Feed_Builder;

class CTF_Elementor_Base
{
	const VERSION = CTF_VERSION;
	const MINIMUM_ELEMENTOR_VERSION = '3.6.0';
	const MINIMUM_PHP_VERSION = '5.6';
	const NAME_SPACE = 'TwitterFeed.Blocks.Elementor.';
	private static $instance;

	public static function init()
	{
		if (!isset(self::$instance) && !self::$instance instanceof CTF_Elementor_Base) {
			self::$instance = new CTF_Elementor_Base();
			self::$instance->apply_hooks();
		}
		return self::$instance;
	}

	private function apply_hooks()
	{
		add_action('elementor/frontend/after_register_scripts', [$this, 'register_frontend_scripts']);
		add_action('elementor/frontend/after_register_styles', [$this, 'register_frontend_styles'], 10);
		add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_frontend_styles'], 10);
		add_action('elementor/controls/register', [$this, 'register_controls']);
		add_action('elementor/widgets/register', [$this,'register_widgets']);
		add_action('elementor/elements/categories_registered', [$this, 'add_smashballon_categories']);
	}

	public function register_controls($controls_manager)
	{
		$controls_manager->register(new CTF_Elementor_Control());
	}

	public function register_widgets($widgets_manager)
	{
		$widgets_manager->register(new CTF_Elementor_Widget());

		$installed_plugins = (new CTF_Feed_Builder)->install_plugins_popup();
		unset($installed_plugins['twitter']);

		foreach($installed_plugins as $plugin) {
			if (!$plugin['installed']){
				$plugin_class = str_replace('.','\\', self::NAME_SPACE) . $plugin['class'];
				$widgets_manager->register(new $plugin_class());
			}
		}
	}

	public function register_frontend_scripts(){
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

		wp_register_script(
			'ctf-elementor-handler',
			CTF_PLUGIN_URL . 'js/elementor-handler.js' ,
			array('jquery'),
			CTF_VERSION,
			true
		);

		$data_handler = array(
			'smashPlugins'  => (new CTF_Feed_Builder)->install_plugins_popup(),
			'nonce'         => wp_create_nonce('ctf-admin'),
			'ajax_handler'      =>  admin_url('admin-ajax.php'),
		);

		wp_localize_script('ctf-elementor-handler', 'sbHandler', $data_handler);

		wp_register_script(
			'ctf-elementor-preview',
			CTF_PLUGIN_URL . 'js/elementor-preview.js' ,
			array('jquery'),
			CTF_VERSION,
			true
		);
	}

	public function register_frontend_styles()
	{
		wp_register_style('ctf_styles', trailingslashit(CTF_PLUGIN_URL) . 'css/ctf-styles.min.css', array(), CTF_VERSION);
	}

	public function enqueue_frontend_styles()
	{
		wp_enqueue_style('ctf-styles');
	}

	public function add_smashballon_categories($elements_manager)
	{
		$elements_manager->add_category(
			'smash-balloon',
			[
				'title' => esc_html__('Smash Balloon', 'custom-twitter-feeds'),
				'icon' => 'fa fa-plug',
			]
		);
	}

}

