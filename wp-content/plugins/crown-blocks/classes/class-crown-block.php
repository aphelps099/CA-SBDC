<?php

if ( ! class_exists( 'Crown_Block' ) ) {
	class Crown_Block {


		private static $namespace = 'crown-blocks';
		protected static $name = 'block-name'; // overridden by extended class

		private static $blocks_style_handle = 'crown-blocks-style-css';
		private static $blocks_editor_script_handle = 'crown-blocks-editor-js';
		private static $blocks_editor_style_handle = 'crown-blocks-editor-css';


		public static function get_namespace() { return self::$namespace; }
		public static function get_name() { return static::$name; }
		public static function get_block_name() { return self::get_namespace().'/'.self::get_name(); }

		public static function get_blocks_style_handle() { return self::$blocks_style_handle; }
		public static function get_blocks_editor_script_handle() { return self::$blocks_editor_script_handle; }
		public static function get_blocks_editor_style_handle() { return self::$blocks_editor_style_handle; }


		public static function init() {

			add_action( 'init', array( get_called_class(), 'register_block_scripts' ) );
			add_action( 'init', array( get_called_class(), 'register_block_styles' ) );
			add_action( 'init', array( get_called_class(), 'register' ) );

		}


		public static function register_block_scripts() {

			if ( ! wp_script_is( self::$blocks_editor_script_handle, 'registered' ) ) {

				// register block editor script
				wp_register_script(
					self::$blocks_editor_script_handle,
					plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
					array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
					filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
					true
				);

				// register localized globals for block editor script
				wp_localize_script(
					self::$blocks_editor_script_handle,
					'cbGlobal',
					array(
						'pluginDirPath' => plugin_dir_path( __DIR__ ),
						'pluginDirUrl' => plugin_dir_url( __DIR__ )
					)
				);

			}

		}


		public static function register_block_styles() {

			if ( ! wp_style_is( self::$blocks_style_handle, 'registered' ) ) {

				// register blocks style
				wp_register_style(
					self::$blocks_style_handle,
					plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
					is_admin() ? array( 'wp-editor' ) : array(),
					filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' )
				);

			}

			if ( ! wp_style_is( self::$blocks_editor_style_handle, 'registered' ) ) {

				// register blocks editor style
				wp_register_style(
					self::$blocks_editor_style_handle,
					plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ),
					array( 'wp-edit-blocks' ),
					filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' )
				);

			}

		}


		public static function register() {

			$blockArgs = array(
				'style' => self::get_blocks_style_handle(),
				'editor_script' => self::get_blocks_editor_script_handle(),
				'editor_style' => self::get_blocks_editor_style_handle()
			);

			if ( is_callable( array( get_called_class(), 'get_attributes' ) ) ) {
				$attributes = call_user_func( array( get_called_class(), 'get_attributes' ) );
				if ( is_array( $attributes ) ) {
					$blockArgs['attributes'] = $attributes;
				}
			}

			if ( is_callable( array( get_called_class(), 'render' ) ) ) {
				$blockArgs['render_callback'] = array( get_called_class(), 'render' );
			}

			// register block
			register_block_type( self::get_block_name(), $blockArgs );

		}


	}
}