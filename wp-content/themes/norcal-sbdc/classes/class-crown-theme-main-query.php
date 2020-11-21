<?php

if ( ! class_exists( 'Crown_Theme_Main_Query' ) ) {
	class Crown_Theme_Main_Query {


		public static function init() {

			add_action( 'pre_get_posts', array( __CLASS__, 'set_main_query' ), 20 );

		}


		public static function set_main_query( $query ) {
			if ( is_admin() || !$query->is_main_query() ) return false;

			// modify main query args depending on context

		}


	}
}