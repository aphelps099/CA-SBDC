<?php

use Crown\AdminPage;
use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\FieldRepeaterFlex;
use Crown\Form\Input\CheckboxSet;
use Crown\Form\Input\Media as MediaInput;
use Crown\Form\Input\RadioSet;
use Crown\Form\Input\Select;
use Crown\Form\Input\Date as DateInput;
use Crown\Form\Input\Time as TimeInput;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Checkbox as CheckboxInput;
use Crown\Form\Input\Color as ColorInput;
use Crown\Form\Input\RichTextarea;
use Crown\Form\Input\Textarea;
use Crown\Form\Input\Gallery as GalleryInput;
use Crown\ListTableColumn;
use Crown\Post\MetaBox;
use Crown\Post\Type as PostType;
use Crown\Post\Taxonomy;
use Crown\UIRule;


if ( ! class_exists( 'Crown_Jobs' ) ) {
	class Crown_Jobs {

		public static $init = false;

		public static $job_post_type = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'after_setup_theme', array( __CLASS__, 'register_job_post_type' ) );

			add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			// add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					if ( $role->has_cap( $cap . '_posts' ) ) {
						$role->add_cap( $cap . '_jobs' );
					}
				}
				// foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
				// 	if ( $role->has_cap( 'manage_categories' ) ) {
				// 		$role->add_cap( $cap . '_job_types' );
				// 	}
				// }
				// if ( $role->has_cap( 'edit_posts' ) ) {
				// 	$role->add_cap( 'assign_job_types' );
				// }
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_jobs' );
				}
				// foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $cap ) {
				// 	$role->remove_cap ( $cap . '_job_types' );
				// }
			}
			
			flush_rewrite_rules();
		}


		public static function register_job_post_type() {

			self::$job_post_type = new PostType( array(
				'name' => 'job',
				'singularLabel' => 'Job',
				'pluralLabel' => 'Jobs',
				'settings' => array(
					'supports' => array( 'title', 'editor', 'excerpt', 'revisions' ),
					'rewrite' => array( 'slug' => 'jobs', 'with_front' => false ),
					'menu_icon' => 'dashicons-businessman',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'show_ui' => is_main_site(),
					'capability_type' => array( 'job', 'jobs' ),
					'map_meta_cap' => true,
					'menu_position' => 33
				),
				'metaBoxes' => array(
					
				),
				'listTableColumns' => array(
					
				)
			) );

		}


		public static function filter_use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			return in_array( $post_type, array( 'job' ) ) ? false : $use_block_editor;
		}


		public static function register_admin_styles( $hook ) {
			
			$screen = get_current_screen();
			if ( $screen->base == 'post' && $screen->post_type == 'job' ) {

				ob_start();
				?>
					<style>
						
					</style>
				<?php
				$css = trim( ob_get_clean() );
				$css = trim( preg_replace( array( '/^<style>/', '/<\/style>$/' ), '', $css ) );
				wp_add_inline_style( 'common', $css );

			}

		}


	}
}