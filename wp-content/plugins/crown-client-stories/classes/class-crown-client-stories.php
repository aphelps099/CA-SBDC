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


if ( ! class_exists( 'Crown_Client_Stories' ) ) {
	class Crown_Client_Stories {

		public static $init = false;

		public static $client_story_post_type = null;
		public static $client_story_industry_taxonomy = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'after_setup_theme', array( __CLASS__, 'register_client_story_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_client_story_industry_taxonomy' ) );

			// add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			add_filter( 'display_post_states', array( __CLASS__, 'filter_display_post_states'), 10, 2 );

			// add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					if ( $role->has_cap( $cap . '_posts' ) ) {
						$role->add_cap( $cap . '_client_stories' );
					}
				}
				foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
					if ( $role->has_cap( 'manage_categories' ) ) {
						$role->add_cap( $cap . '_client_story_industries' );
					}
				}
				if ( $role->has_cap( 'edit_posts' ) ) {
					$role->add_cap( 'assign_client_story_industries' );
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_client_stories' );
				}
				foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $cap ) {
					$role->remove_cap ( $cap . '_client_story_industries' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function register_client_story_post_type() {

			self::$client_story_post_type = new PostType( array(
				'name' => 'client_story',
				'singularLabel' => 'Client Story',
				'pluralLabel' => 'Client Stories',
				'settings' => array(
					'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
					'rewrite' => array( 'slug' => 'client-stories', 'with_front' => false ),
					'menu_icon' => 'dashicons-testimonial',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'capability_type' => array( 'client_story', 'client_stories' ),
					'map_meta_cap' => true,
					'menu_position' => 36,
					'template' => array(
						array( 'crown-blocks/client-story-header', array() ),
						array( 'core/paragraph', array() )
					)
				),
				'metaBoxes' => array(
					new MetaBox( array(
						'id' => 'client-story-options',
						'title' => 'Client Story Options',
						'context' => 'side',
						'fields' => array(
							new Field(array(
								'input' => new CheckboxSet( array( 'name' => 'client_story_options', 'options' => array(
									array( 'value' => 'featured-post', 'label' => 'Featured Client Story' )
								) ) )
							) )
						)
					) ),
				),
				'listTableColumns' => array(
					
				)
			) );

		}


		public static function register_client_story_industry_taxonomy() {

			self::$client_story_industry_taxonomy = new Taxonomy( array(
				'name' => 'client_story_industry',
				'singularLabel' => 'Client Story Industry',
				'pluralLabel' => 'Client Story Industries',
				'postTypes' => array( 'client_story' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'client-story-industries', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Industries',
						'all_items' => 'All Industries'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_client_story_industries',
						'edit_terms' => 'edit_client_story_industries',
						'delete_terms' => 'delete_client_story_industries',
						'assign_terms' => 'assign_client_story_industries'
					)
				)
			) );

		}


		public static function filter_use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			return in_array( $post_type, array( 'client_story' ) ) ? false : $use_block_editor;
		}


		public static function filter_display_post_states( $post_states, $post ) {
			if( $post->post_type == 'client_story' && in_array( 'featured-post', get_post_meta( $post->ID, '__client_story_options' ) ) ) {
				$post_states['post-featured'] = 'Featured';
			}
			return $post_states;
		}


		public static function register_admin_styles( $hook ) {
			
			$screen = get_current_screen();
			if ( $screen->base == 'post' && $screen->post_type == 'client_story' ) {
				return;

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