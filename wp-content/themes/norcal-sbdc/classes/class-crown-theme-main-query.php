<?php

if( ! class_exists( 'Crown_Theme_Main_Query' ) ) {
	class Crown_Theme_Main_Query {


		public static function init() {

			add_action( 'pre_get_posts', array( __CLASS__, 'set_main_query' ), 20 );

		}


		public static function set_main_query( $query ) {
			if ( is_admin() || !$query->is_main_query() ) return false;

			$query->set( 'posts_per_page', 15 );

			$cta_block_id = get_option( 'theme_config_category_tpl_cta_block_id' );
			if ( ! empty( $cta_block_id ) && ( $cta_block = get_post( $cta_block_id ) ) ) {
				$query->set( 'posts_per_page', 13 );
			}

			if ( $query->is_category() ) {
				$category = get_queried_object();
				$featured_post_ids = get_term_meta( $category->term_id, 'category_featured_post_ids', true );
				if ( empty( $featured_post_ids ) ) $featured_post_ids = array();
				$featured_post_ids = array_slice( $featured_post_ids, 0, 3 );
				$query->set( 'post__not_in', $featured_post_ids );
			}

		}


	}
}