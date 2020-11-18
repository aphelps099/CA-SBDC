<?php

if( ! class_exists( 'Crown_Theme_Templates' ) ) {
	class Crown_Theme_Templates {


		public static function init() {

			add_action( 'init', array( __CLASS__, 'register_post_type_templates' ) );
			add_filter( 'body_class', array(__CLASS__, 'filter_body_class' ) );

		}


		public static function register_post_type_templates() {

			$post_type_object = get_post_type_object( 'post' );
			$post_type_object->template = array(
				array( 'crown-blocks/promo', array(
					'align' => 'full',
					'featuredImageLeft' => true,
					'overline' => '[post_category]'
				) )
			);

		}


		public static function filter_body_class( $classes ) {

			if ( is_singular() ) {
				$gated_content_settings = ct_get_post_gated_content_settings();
				if ( $gated_content_settings->active ) {
					$classes[] = 'gated-content-active';
				}
			}

			return $classes;
		}


	}
}