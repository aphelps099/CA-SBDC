<?php

if ( ! class_exists( 'Crown_Theme_Post_Type_Templates' ) ) {
	class Crown_Theme_Post_Type_Templates {


		public static function init() {

			add_action( 'init', array( __CLASS__, 'register_post_template' ) );

		}


		public static function register_post_template() {

			// $post_type_object = get_post_type_object( 'post' );
			// $post_type_object->template = array(
			// 	array( 'crown-blocks/promo', array(
			// 		'align' => 'full',
			// 		'featuredImageLeft' => true,
			// 		'overline' => '[post_category]'
			// 	) )
			// );

		}


	}
}