<?php

if( ! class_exists( 'Crown_Theme' ) ) {
	class Crown_Theme {


		protected static $config;


		public static function init() {

			add_action( 'after_setup_theme', array( __CLASS__, 'load' ), 0 );

			add_filter('terms_clauses', function( $clauses, $taxonomy, $args ) {
				global $wpdb;
				if ( isset( $args['post_types'] ) && ! empty( $args['post_types'] ) ) {
					$post_types = implode( "','", array_map( 'esc_sql', (array) $args['post_types'] ) );
					if ( is_array( $args['post_types'] ) ) {
						$post_types = implode( "','", $args['post_types'] );
					}
					$clauses['join'] .= " INNER JOIN $wpdb->term_relationships AS r ON r.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN $wpdb->posts AS p ON p.ID = r.object_id";
					$clauses['where'] .= " AND p.post_type IN ('". $post_types. "') GROUP BY t.term_id";
				}
				return $clauses;
			}, 99999, 3);

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
			Crown_Theme_Ajax_Content::init();
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


		public static function get_child_dir() {
			return get_stylesheet_directory();
		}


		public static function get_child_uri() {
			return get_stylesheet_directory_uri();
		}


		public static function is_child() {
			return self::get_dir() != self::get_child_dir();
		}


	}
}


Crown_Theme::init();