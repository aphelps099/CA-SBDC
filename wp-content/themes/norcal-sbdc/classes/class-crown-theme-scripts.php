<?php

if ( ! class_exists( 'Crown_Theme_Scripts' ) ) {
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

			$scripts = array(
				array(
					'handle' => 'popperjs',
					'local_path' => '/lib/popperjs/dist/umd/popper.min.js'
				),
				array(
					'handle' => 'bootstrap',
					'local_path' => '/lib/bootstrap/dist/js/bootstrap.min.js',
					'deps' => array( 'jquery', 'popperjs' )
				),
				array(
					'handle' => 'slick',
					'local_path' => '/lib/slick/slick.min.js',
					'deps' => array( 'jquery' )
				),
				array(
					'handle' => 'blueimp-gallery',
					'local_path' => '/lib/blueimp-gallery/js/blueimp-gallery.min.js'
				),
				array(
					'handle' => 'odometer',
					'local_path' => '/lib/odometer/odometer.min.js'
				),
				array(
					'handle' => 'google-maps-infobox',
					'local_path' => '/lib/infobox/infobox_packed.js',
					'deps' => array( 'google-maps-api' )
				),
				array(
					'handle' => 'jquery-oembed',
					'local_path' => '/lib/jquery-oembed/jquery.oembed.js',
					'deps' => array( 'jquery' )
				),
				array(
					'handle' => 'rellax',
					'local_path' => '/lib/rellax/rellax.min.js'
				),
				array(
					'handle' => 'crown-theme-main',
					'local_path' => '/assets/js/main' . ( ! WP_DEBUG ? '.min' : '' ) . '.js',
					'deps' => array( 'jquery-effects-core', 'bootstrap', 'slick', 'blueimp-gallery', 'odometer', 'jquery-oembed', 'rellax' )
				)
			);
			
			$scripts = apply_filters( 'crown_theme_scripts', $scripts );

			foreach ( $scripts as $script ) {

				$script = array_merge( array(
					'handle' => '',
					'local_path' => '',
					'src' => '',
					'ver' => '',
					'deps' => array(),
					'in_footer' => true
				), $script);

				if ( ! empty( $script['local_path'] ) ) {
					$script['src'] = empty( $script['src'] ) ? Crown_Theme::get_uri() . $script['local_path'] : $script['src'];
					$script['ver'] = empty( $script['ver'] ) ? filemtime( Crown_Theme::get_dir() . $script['local_path'] ) : $script['ver'];
				}

				wp_register_script( $script['handle'], $script['src'], $script['deps'], $script['ver'], $script['in_footer'] );

			}

		}


		public static function localize_script_data() {

			$data = array(
				'baseUrl' => get_home_url(),
				'themeUrl' => Crown_Theme::get_uri(),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'siteName' => get_bloginfo( 'name' ),
				'calendlyUrlOverride' => false
			);

			if ( isset( $_GET['program'] ) && ! empty( $_GET['program'] ) && class_exists( 'Crown_Site_Settings_Signup' ) ) {
				$program = Crown_Site_Settings_Signup::get_program( $_GET['program'] );
				if ( $program && !empty( $program->calendly_url ) ) {
					$data['calendlyUrlOverride'] = $program->calendly_url;
				}
			}

			wp_localize_script( 'crown-theme-main', 'crownThemeData', $data );

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