<?php

if( ! class_exists( 'Crown_Theme' ) ) {
	class Crown_Theme {


		protected static $config;


		public static function init() {

			add_action( 'after_setup_theme', array( __CLASS__, 'load' ), 0 );

		}


		public static function load() {

			// load required class files
			$include_dirs = array(
				'/classes',
				'/inc'
			);
			foreach ( $include_dirs as $include_dir ) {
				$include_dir = self::get_dir() . $include_dir;
				foreach ( scandir( $include_dir ) as $file ) {
					if ( preg_match( '/^[^\.]+\.php$/', $file ) ) {
						include_once( $include_dir . '/' . $file );
					}
				}
			}

			// initialize theme modules
			Crown_Theme_Block_Editor::init();
			Crown_Theme_Config::init();
			Crown_Theme_Main_Query::init();
			Crown_Theme_Post_Type_Templates::init();
			Crown_Theme_Scripts::init();
			Crown_Theme_Shortcode_Filters::init();
			Crown_Theme_Styles::init();
			Crown_Theme_Template_Hooks::init();

		}


		public static function get_dir() {
			return get_template_directory();
		}


		public static function get_uri() {
			return get_template_directory_uri();
		}


	}
}


Crown_Theme::init();