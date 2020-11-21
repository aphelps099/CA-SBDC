<?php

if ( ! class_exists( 'Crown_Theme_Template_Hooks' ) ) {
	class Crown_Theme_Template_Hooks {


		public static function init() {

			add_filter( 'body_class', array( __CLASS__, 'filter_body_class' ) );
			add_filter( 'excerpt_length', array( __CLASS__, 'filter_excerpt_length' ) );
			add_filter( 'excerpt_more', array( __CLASS__, 'filter_excerpt_more' ) );

		}


		public static function filter_body_class( $classes ) {

			if ( is_singular() ) {
				$page_header_options = get_post_meta( get_the_ID(), 'page_header_options', true );
				if ( ! empty( $page_header_options ) ) {
					$classes = array_merge( $classes, array_map( function ( $n ) { return 'page-header-' . $n; }, $page_header_options ) );
				}
			}

			return $classes;
		}


		public static function filter_excerpt_length( $length ) {
			$length = 30;
			return $length;
		}


		public static function filter_excerpt_more( $excerpt_more ) {
			$excerpt_more = '&hellip;';
			return $excerpt_more;
		}


	}
}