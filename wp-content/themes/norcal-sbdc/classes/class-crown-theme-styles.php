<?php

if ( ! class_exists( 'Crown_Theme_Styles' ) ) {
	class Crown_Theme_Styles {


		public static function init() {

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_styles' ), 10 );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ), 12 );

			add_action( 'login_enqueue_scripts', array( __CLASS__, 'register_login_styles' ), 10 );
			add_action( 'login_enqueue_scripts', array( __CLASS__, 'enqueue_login_styles' ), 12 );
			
			add_action( 'wp_head', array( __CLASS__, 'output_head_styles' ) );
			add_action( 'wp_body_open', array( __CLASS__, 'output_body_open_styles' ) );
			add_action( 'wp_footer', array( __CLASS__, 'output_footer_styles' ) );

		}


		public static function register_styles() {

			$styles = array(
				array(
					'handle' => 'crown-theme-google-fonts',
					'src' => 'https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap'
				),
				array(
					'handle' => 'crown-theme-typekit',
					'src' => 'https://use.typekit.net/ejb7dnd.css'
				),
				array(
					'handle' => 'slick',
					'local_path' => '/lib/slick/slick.css'
				),
				array(
					'handle' => 'slick-theme',
					'local_path' => '/lib/slick/slick-theme.css',
					'deps' => array( 'slick' )
				),
				array(
					'handle' => 'blueimp-gallery',
					'local_path' => '/lib/blueimp-gallery/css/blueimp-gallery.min.css'
				),
				array(
					'handle' => 'odometer-theme-default',
					'local_path' => '/lib/odometer/odometer-theme-default.css'
				),
				array(
					'handle' => 'jquery-oembed',
					'local_path' => '/lib/jquery-oembed/jquery.oembed.css'
				),
				array(
					'handle' => 'atcb',
					'local_path' => '/lib/atcb/css/atcb.min.css'
				),
				array(
					'handle' => 'crown-theme-style',
					'local_path' => '/assets/css/style' . ( ! WP_DEBUG ? '.min' : '' ) . '.css',
					'deps' => array( 'crown-theme-typekit', 'slick', 'blueimp-gallery', 'odometer-theme-default', 'jquery-oembed' )
				)
			);

			$styles = apply_filters( 'crown_theme_styles', $styles );

			foreach ( $styles as $style ) {

				$style = array_merge( array(
					'handle' => '',
					'local_path' => '',
					'src' => '',
					'ver' => '',
					'deps' => array(),
					'media' => 'all'
				), $style);

				if ( ! empty( $style['local_path'] ) ) {
					$style['src'] = empty( $style['src'] ) ? Crown_Theme::get_uri() . $style['local_path'] : $style['src'];
					$style['ver'] = empty( $style['ver'] ) ? filemtime( Crown_Theme::get_dir() . $style['local_path'] ) : $style['ver'];
				}

				wp_register_style( $style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media'] );

			}

		}


		public static function enqueue_styles() {

			wp_enqueue_style( 'crown-theme-style' );

		}


		public static function register_login_styles() {

			$styles = array(
				array(
					'handle' => 'crown-theme-login',
					'local_path' => '/assets/css/login' . ( ! WP_DEBUG ? '.min' : '' ) . '.css',
				)
			);

			$styles = apply_filters( 'crown_theme_login_styles', $styles );

			foreach ( $styles as $style ) {

				$style = array_merge( array(
					'handle' => '',
					'local_path' => '',
					'src' => '',
					'ver' => '',
					'deps' => array(),
					'media' => 'all'
				), $style);

				if ( ! empty( $style['local_path'] ) ) {
					$style['src'] = empty( $style['src'] ) ? Crown_Theme::get_uri() . $style['local_path'] : $style['src'];
					$style['ver'] = empty( $style['ver'] ) ? filemtime( Crown_Theme::get_dir() . $style['local_path'] ) : $style['ver'];
				}

				wp_register_style( $style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media'] );

			}

		}


		public static function enqueue_login_styles() {

			wp_enqueue_style( 'crown-theme-login' );

		}


		public static function output_head_styles() {}


		public static function output_body_open_styles() {}


		public static function output_footer_styles() {}


	}
}