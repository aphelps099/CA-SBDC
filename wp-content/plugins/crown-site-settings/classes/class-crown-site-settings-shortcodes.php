<?php

use Crown\Shortcode;


if ( ! class_exists( 'Crown_Site_Settings_Shortcodes' ) ) {
	class Crown_Site_Settings_Shortcodes {

		public static $init = false;

		public static $shortcodes = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			add_action( 'after_setup_theme', array( __CLASS__, 'register_shortcodes') );

		}


		public static function register_shortcodes() {

			self::$shortcodes['post_category'] = new Shortcode(array(
				'tag' => 'post_category',
				'getOutputCb' => array( __CLASS__, 'get_post_category_shortcode' ),
				'defaultAtts' => array(
					'post_id' => 0
				)
			));

			self::$shortcodes['social_profile_links'] = new Shortcode(array(
				'tag' => 'social_profile_links',
				'getOutputCb' => array( __CLASS__, 'get_social_profile_links_shortcode' ),
				'defaultAtts' => array()
			));

		}


		public static function get_post_category_shortcode( $atts, $content ) {
			$post_id = $atts['post_id'];
			if ( empty( $post_id ) ) $post_id = is_singular() ? get_the_ID() : $post_id;
			if ( empty( $post_id ) ) return '';
			$primary_term_id = get_post_meta( $post_id, '_primary_term_category', true );
			if ( ! empty( $primary_term_id ) ) {
				$term = get_term( $primary_term_id, 'category' );
				if ( $term ) return $term->name;
			}
			$terms = get_the_terms( $post_id, 'category' );
			if ( empty( $terms ) ) return '';
			$term_names = array_map( function( $n ) { return $n->name; }, $terms );
			return implode( ', ', $term_names );
		}


		public static function get_social_profile_links_shortcode( $atts, $content ) {
			return apply_filters( 'crown_social_profile_links_shortcode', '', $atts, $content );
		}


	}
}