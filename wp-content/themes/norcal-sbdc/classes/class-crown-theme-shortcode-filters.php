<?php

if ( ! class_exists( 'Crown_Theme_Shortcode_Filters' ) ) {
	class Crown_Theme_Shortcode_Filters {


		public static function init() {

			add_filter( 'crown_social_profile_links_shortcode', array( __CLASS__, 'filter_social_profile_links_shortcode' ), 10, 3 );

		}


		public static function filter_social_profile_links_shortcode( $output, $atts, $content ) {
			ob_start();
			ct_social_links();
			return ob_get_clean();
		}


	}
}