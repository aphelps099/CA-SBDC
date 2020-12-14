<?php

if ( ! class_exists( 'Crown_Theme_Config' ) ) {
	class Crown_Theme_Config {


		protected static $config;


		public static function init() {

			add_action( 'after_setup_theme', array( __CLASS__, 'setup_theme_textdomain' ), 1);
			add_action( 'after_setup_theme', array( __CLASS__, 'setup_theme_support' ), 1);
			add_action( 'after_setup_theme', array( __CLASS__, 'setup_image_sizes' ), 1);
			add_action( 'after_setup_theme', array( __CLASS__, 'setup_nav_menus' ), 1);
			add_action( 'after_setup_theme', array( __CLASS__, 'setup_editor_stylesheet' ), 1);

			add_action( 'init', array( __CLASS__, 'disable_emojis' ) );
			add_filter( 'image_size_names_choose', array( __CLASS__, 'filter_image_size_select_option_names' ) );
			add_action( 'widgets_init', array( __CLASS__, 'register_widget_locations' ) );

			add_filter( 'upload_mimes', array( __CLASS__, 'filter_allowed_upload_mimes' ) );
			add_filter( 'wp_check_filetype_and_ext', array( __CLASS__, 'validate_file_ext_and_type' ), 10, 4 );

			add_filter( 'crown_theme_colors', array( __CLASS__, 'filter_crown_theme_colors' ), 10, 2 );

			add_filter( 'gform_submit_button', array( __CLASS__, 'filter_gravity_form_submit_button' ), 10, 2 );

		}


		public static function setup_theme_textdomain() {
			load_theme_textdomain( 'crown_theme' );
		}


		public static function setup_theme_support() {

			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'title-tag' );
			add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style' ) );
			add_theme_support( 'post-formats', array() );
			add_theme_support( 'align-wide' );
			add_theme_support( 'responsive-embeds' );
			add_theme_support( 'editor-styles' );
			// add_theme_support( 'woocommerce' );

			if ( ( $editor_font_sizes = self::get_editor_font_sizes() ) && ! empty( $editor_font_sizes ) ) {
				add_theme_support( 'disable-custom-font-sizes' );
				add_theme_support( 'editor-font-sizes', $editor_font_sizes );
			}

			if ( ( $editor_color_palette = self::get_editor_color_palette() ) && ! empty( $editor_color_palette ) ) {
				// add_theme_support( 'disable-custom-colors' );
				add_theme_support( 'editor-color-palette', $editor_color_palette );
			}

			if ( ( $site_logo_size = self::get_site_logo_size() ) && ! empty( $site_logo_size ) ) {
				add_theme_support( 'custom-logo', $site_logo_size );
			}

		}


		public static function setup_image_sizes() {

			// configure post thumbnail size
			if ( ( $post_thumbnail_size = self::get_post_thumbnail_size() ) && ! empty( $post_thumbnail_size ) ) {
				set_post_thumbnail_size( $post_thumbnail_size['width'], $post_thumbnail_size['height'], $post_thumbnail_size['crop'] );
			}

			// configure additional image sizes
			if ( ( $image_sizes = self::get_image_sizes() ) && ! empty( $image_sizes ) ) {
				foreach ( $image_sizes as $size ) {
					add_image_size( $size['slug'], $size['width'], $size['height'], $size['crop'] );
				}
			}

		}


		public static function setup_nav_menus() {

			if ( ( $nav_menu_locations = self::get_nav_menu_locations() ) && ! empty( $nav_menu_locations ) ) {
				foreach ( $nav_menu_locations as $location ) {
					register_nav_menu( $location['slug'], $location['name'] );
				}
			}

		}


		public static function setup_editor_stylesheet() {
			add_editor_style( 'assets/css/editor-style.css' );
		}


		public static function disable_emojis() {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		}


		public static function filter_image_size_select_option_names( $sizes ) {
			global $_wp_additional_image_sizes;
			
			$sizes_config = array();
			foreach ( get_intermediate_image_sizes() as $size_name ) {
				if ( $size_name == 'post-thumbnail' ) continue;
				if ( in_array( $size_name, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
					$sizes_config[ $size_name ]['width'] = get_option( $size_name . '_size_w' );
					$sizes_config[ $size_name ]['height'] = get_option( $size_name . '_size_h' );
					$sizes_config[ $size_name ]['crop'] = (bool) get_option( $size_name . '_crop' );
				} else if ( isset( $_wp_additional_image_sizes[ $size_name ] ) ) {
					$sizes_config[ $size_name ] = array(
						'width' => $_wp_additional_image_sizes[ $size_name ]['width'],
						'height' => $_wp_additional_image_sizes[ $size_name ]['height'],
						'crop' => $_wp_additional_image_sizes[ $size_name ]['crop']
					);
				}
			}
			uasort( $sizes_config, function( $a, $b ) {
				return $a['width'] - $b['width'];
			} );

			$sizes = array();
			foreach ( $sizes_config as $size_name => $config ) {
				$label = ucwords( str_replace( '_', ' ', $size_name ) );
				$dimensions = $config['width'] . '×' . $config['height'];
				if ( ! $config['crop'] ) {
					if ( $config['height'] >= 9999 || $config['height'] == 0 ) {
						$dimensions = $config['width'] . 'px max width';
					} else {
						$dimensions .= ' max';
					}
				}
				$label .= ' (' . $dimensions . ')';
				$sizes[ $size_name ] = $label;
			}
			$sizes['full'] = 'Full Size';

			return $sizes;
		}


		public static function register_widget_locations() {

			$defaults = array(
				'id' => '',
				'name' => '',
				'description' => '',
				'class' => '',
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget' => '</section>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>'
			);

			$widget_locations = self::get( 'widget_locations' );
			if ( ! empty( $widget_locations ) ) {
				foreach ( $widget_locations as $location ) {
					$location = array_merge( $defaults, (array) $location );
					if ( empty( $location['slug'] ) ) continue;
					$location['id'] = $location['slug'];
					unset( $location['slug'] );
					register_sidebar( $location );
				}
			}

		}


		public static function filter_allowed_upload_mimes($mimes) {
			$mimes = array_merge( $mimes, (array) self::get( 'upload_mimes' ) );
			return $mimes;
		}
	
	
		public static function validate_file_ext_and_type($check, $file, $filename, $mimes) {
			if ( $check['ext'] && $check['type'] ) {
				return $check;
			}
			return array_merge( $check, wp_check_filetype( $filename, apply_filters( 'upload_mimes', array() ) ) );
		}


		public static function filter_crown_theme_colors( $colors, $context = '' ) {
			$color_palette = self::get( 'color_palette' );
			if ( empty( $color_palette ) ) return $colors;
			$colors = array_map( function( $n ) { return $n->color; }, $color_palette );
			return $colors;
		}


		public static function filter_gravity_form_submit_button( $button, $form ) {
			if ( preg_match( '/^\s*<input\s.*value=\'([^\']*)\'/', $button, $matches ) ) {
				$button = preg_replace( array( '/^<input/', '/\/?>$/' ), array(' <button', '>' . $matches[1] . '</button>'), $button );
			}
			return $button;
		}





		public static function get( $key = null ) {
			if ( self::$config === null ) {
				$path = Crown_Theme::get_dir() . '/config.json';
				self::$config = file_exists( $path ) ? json_decode( file_get_contents( $path) )  : new stdClass();
			}
			if ( ! empty( $key ) ) {
				if ( property_exists( self::$config, $key ) ) {
					return self::$config->$key;
				}
				return null;
			}
			return self::$config;
		}


		public static function get_editor_font_sizes() {
			$font_sizes = self::get( 'font_sizes' );
			if ( empty( $font_sizes ) ) return array();
			$editor_font_sizes = array_map( function( $n ) {
				$n = array_merge( array(
					'name' => '',
					'slug' => '',
					'short_name' => '',
					'size' => 0
				), (array) $n );
				$n['name'] = __( $n['name'] , 'crown_theme' );
				$n['shortName'] = __( $n['short_name'] , 'crown_theme' );
				unset( $n['short_name'] );
				return $n;
			}, $font_sizes );
			return $editor_font_sizes;
		}


		public static function get_editor_color_palette() {
			$color_palette = self::get( 'color_palette' );
			if ( empty( $color_palette ) ) return array();
			$editor_color_palette = array_map( function( $n ) {
				$n = array_merge( array(
					'name' => '',
					'slug' => '',
					'color' => ''
				), (array) $n );
				$n['name'] = __( $n['name'] , 'crown_theme' );
				return $n;
			}, $color_palette );
			return $editor_color_palette;
		}


		public static function get_site_logo_size() {
			$site_logo_size = self::get( 'site_logo_size' );
			if ( empty( $site_logo_size ) ) return null;
			$site_logo_size = array_merge( array(
				'width' => 300,
				'height' => 200,
				'flex_width' => true,
				'flex_height' => true
			), (array) $site_logo_size );
			$site_logo_size = array_merge( $site_logo_size, array(
				'flex-width' => $site_logo_size['flex_width'],
				'flex-height' => $site_logo_size['flex_height']
			) );
			unset( $site_logo_size['flex_width'] );
			unset( $site_logo_size['flex_height'] );
			return $site_logo_size;
		}


		public static function get_post_thumbnail_size() {
			$post_thumbnail_size = self::get( 'post_thumbnail_size' );
			if ( empty( $post_thumbnail_size ) ) return null;
			$post_thumbnail_size = array_merge( array(
				'width' => 150,
				'height' => 150,
				'crop' => false
			), (array) $post_thumbnail_size );
			return $post_thumbnail_size;
		}


		public static function get_image_sizes() {
			$image_sizes = self::get( 'image_sizes' );
			if ( empty( $image_sizes ) ) return array();
			$image_sizes = array_map( function( $n ) {
				$n = array_merge( array(
					'slug' => '',
					'width' => 0,
					'height' => 0,
					'crop' => false
				), (array) $n );
				return $n;
			}, $image_sizes );
			return $image_sizes;
		}


		public static function get_nav_menu_locations() {
			$nav_menu_locations = self::get( 'nav_menu_locations' );
			if ( empty( $nav_menu_locations ) ) return array();
			$nav_menu_locations = array_map( function( $n ) {
				$n = array_merge( array(
					'name' => '',
					'slug' => ''
				), (array) $n );
				return $n;
			}, $nav_menu_locations );
			return $nav_menu_locations;
		}


		public static function get_grid_breakpoints() {
			$grid_breakpoints_config = self::get( 'grid_breakpoints' );
			$grid_breakpoints = array();
			if ( empty( $grid_breakpoints_config ) || ! is_array( $grid_breakpoints_config ) ) return $grid_breakpoints;
			foreach ( $grid_breakpoints_config as $grid_breakpoint_config ) {
				$grid_breakpoint = (object) array_merge( array(
					'name' => '',
					'width' => 0
				), (array) $grid_breakpoint_config );
				if ( empty( $grid_breakpoint->name ) ) continue;
				$grid_breakpoints[] = $grid_breakpoint;
			}
			usort( $grid_breakpoints, function( $a, $b ) { return $a->width - $b->width; } );
			return $grid_breakpoints;
		}


	}
}