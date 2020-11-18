<?php

if( ! class_exists( 'Crown_Theme_Block_Editor' ) ) {
	class Crown_Theme_Block_Editor {


		public static function init() {

			add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_scripts' ) );
			add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_styles' ) );

		}


		public static function enqueue_block_editor_scripts() {

			wp_enqueue_script(
				'crown-theme-block-editor', 
				Crown_Theme::get_uri().'/assets/js/block-editor' . ( ! WP_DEBUG ? '.min' : '' ) . '.js',
				array( 'wp-blocks', 'wp-dom', 'jquery' ),
				filemtime( Crown_Theme::get_dir().'/assets/js/block-editor' . ( ! WP_DEBUG ? '.min' : '' ) . '.js' ),
				true
			);

		}


		public static function enqueue_block_editor_styles() {

			wp_enqueue_style(
				'google-fonts',
				'https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,wght@0,400;0,700;0,800;0,900;1,400;1,700;1,800;1,900&display=swap',
				array(),
				null
			);

		}


	}
}