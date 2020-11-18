<?php

if( ! class_exists( 'Crown_Theme_Scripts' ) ) {
	class Crown_Theme_Scripts {


		public static function init() {

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_scripts' ), 10 );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'localize_script_data' ), 11 );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 12 );
			
			add_action( 'wp_head', array( __CLASS__, 'output_head_scripts' ) );
			add_action( 'wp_body_open', array( __CLASS__, 'output_body_open_scripts' ) );
			add_action( 'wp_footer', array( __CLASS__, 'output_footer_scripts' ) );

		}


		public static function register_scripts() {

			wp_register_script(
				'popperjs',
				Crown_Theme::get_uri().'/lib/popperjs/dist/umd/popper.min.js',
				array(),
				filemtime( Crown_Theme::get_dir() . '/lib/popperjs/dist/umd/popper.min.js' ),
				true
			);

			wp_register_script(
				'bootstrap',
				Crown_Theme::get_uri().'/lib/bootstrap/dist/js/bootstrap.min.js',
				array( 'jquery', 'popperjs' ),
				filemtime( Crown_Theme::get_dir() . '/lib/bootstrap/dist/js/bootstrap.min.js' ),
				true
			);

			wp_register_script(
				'slick',
				Crown_Theme::get_uri().'/lib/slick/slick.min.js',
				array( 'jquery' ),
				filemtime( Crown_Theme::get_dir() . '/lib/slick/slick.min.js' ),
				true
			);

			wp_register_script(
				'blueimp-gallery',
				Crown_Theme::get_uri().'/lib/blueimp-gallery/js/blueimp-gallery.min.js',
				array(),
				filemtime( Crown_Theme::get_dir() . '/lib/blueimp-gallery/js/blueimp-gallery.min.js' ),
				true
			);

			wp_register_script(
				'odometer',
				Crown_Theme::get_uri().'/lib/odometer/odometer.min.js',
				array(),
				filemtime( Crown_Theme::get_dir() . '/lib/odometer/odometer.min.js' ),
				true
			);

			wp_register_script(
				'crown-theme-main',
				Crown_Theme::get_uri() . '/assets/js/main' . ( ! WP_DEBUG ? '.min' : '' ) . '.js',
				array( 'jquery-effects-core', 'bootstrap', 'slick', 'blueimp-gallery', 'odometer' ),
				filemtime( Crown_Theme::get_dir() . '/assets/js/main' . ( ! WP_DEBUG ? '.min' : '' ) . '.js' ),
				true
			);

		}


		public static function localize_script_data() {

			wp_localize_script( 'crown-theme-main', 'crownThemeData', array(
				'baseUrl' => get_home_url(),
				'themeUrl' => Crown_Theme::get_uri(),
				'ajaxUrl' => admin_url( 'admin-ajax.php' )
			) );

		}


		public static function enqueue_scripts() {

			wp_enqueue_script( 'crown-theme-main' );

		}


		public static function output_head_scripts() {
			?>

				<script type="text/javascript">
					document.documentElement.className = document.documentElement.className.replace('no-js', 'js');
				</script>

			<?php
		}


		public static function output_body_open_scripts() {}


		public static function output_footer_scripts() {}


	}
}