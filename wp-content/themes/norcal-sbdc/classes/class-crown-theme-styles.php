<?php

if( ! class_exists( 'Crown_Theme_Styles' ) ) {
	class Crown_Theme_Styles {


		public static function init() {

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_styles' ), 10 );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ), 12 );
			
			add_action( 'wp_head', array( __CLASS__, 'output_head_styles' ) );
			add_action( 'wp_body_open', array( __CLASS__, 'output_body_open_styles' ) );
			add_action( 'wp_footer', array( __CLASS__, 'output_footer_styles' ) );

		}


		public static function register_styles() {

			wp_register_style(
				'crown-theme-google-fonts',
				'https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,wght@0,400;0,700;0,800;0,900;1,400;1,700;1,800;1,900&family=Ubuntu+Condensed&display=swap',
				array(),
				null
			);

			wp_register_style(
				'slick',
				Crown_Theme::get_uri() . '/lib/slick/slick.css',
				array(),
				filemtime( Crown_Theme::get_dir() . '/lib/slick/slick.css' )
			);

			wp_register_style(
				'slick-theme',
				Crown_Theme::get_uri() . '/lib/slick/slick-theme.css',
				array( 'slick' ),
				filemtime( Crown_Theme::get_dir() . '/lib/slick/slick-theme.css' )
			);

			wp_register_style(
				'blueimp-gallery',
				Crown_Theme::get_uri() . '/lib/blueimp-gallery/css/blueimp-gallery.min.css',
				array(),
				filemtime( Crown_Theme::get_dir() . '/lib/blueimp-gallery/css/blueimp-gallery.min.css' )
			);

			wp_register_style(
				'odometer-theme-default',
				Crown_Theme::get_uri() . '/lib/odometer/odometer-theme-default.css',
				array(),
				filemtime( Crown_Theme::get_dir() . '/lib/odometer/odometer-theme-default.css' )
			);

			wp_register_style(
				'crown-theme-style',
				Crown_Theme::get_uri() . '/assets/css/style' . ( ! WP_DEBUG ? '.min' : '' ) . '.css',
				array( 'crown-theme-google-fonts', 'slick', 'blueimp-gallery', 'odometer-theme-default' ),
				filemtime( Crown_Theme::get_dir() . '/assets/css/style' . ( ! WP_DEBUG ? '.min' : '' ) . '.css' )
			);

		}


		public static function enqueue_styles() {

			wp_enqueue_style( 'crown-theme-style' );

		}


		public static function output_head_styles() {}


		public static function output_body_open_styles() {}


		public static function output_footer_styles() {}


	}
}