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


if ( ! class_exists( 'Crown_Faqs' ) ) {
	class Crown_Faqs {

		public static $init = false;

		public static $faq_post_type = null;
		public static $faq_topic_taxonomy = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'after_setup_theme', array( __CLASS__, 'register_faq_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_faq_topic_taxonomy' ) );

			add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					if ( $role->has_cap( $cap . '_posts' ) ) {
						$role->add_cap( $cap . '_faqs' );
					}
				}
				foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
					if ( $role->has_cap( 'manage_categories' ) ) {
						$role->add_cap( $cap . '_faq_topics' );
					}
				}
				if ( $role->has_cap( 'edit_posts' ) ) {
					$role->add_cap( 'assign_faq_topics' );
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_faqs' );
				}
				foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $cap ) {
					$role->remove_cap ( $cap . '_faq_topics' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function register_faq_post_type() {

			self::$faq_post_type = new PostType( array(
				'name' => 'faq',
				'singularLabel' => 'FAQ',
				'pluralLabel' => 'FAQs',
				'settings' => array(
					'supports' => array( 'title', 'editor', 'revisions' ),
					'rewrite' => array( 'slug' => 'faqs', 'with_front' => false ),
					'menu_icon' => 'dashicons-info',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'capability_type' => array( 'faq', 'faqs' ),
					'map_meta_cap' => true,
					'menu_position' => 39
				)
			) );

		}


		public static function register_faq_topic_taxonomy() {

			self::$faq_topic_taxonomy = new Taxonomy( array(
				'name' => 'faq_topic',
				'singularLabel' => 'Topic',
				'pluralLabel' => 'Topics',
				'postTypes' => array( 'faq' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'faq-topics', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Topics',
						'all_items' => 'All Topics'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_faq_topics',
						'edit_terms' => 'edit_faq_topics',
						'delete_terms' => 'delete_faq_topics',
						'assign_terms' => 'assign_faq_topics'
					)
				)
			) );

		}


		public static function filter_use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			return in_array( $post_type, array( 'faq' ) ) ? false : $use_block_editor;
		}


		public static function register_admin_styles( $hook ) {
			
			$screen = get_current_screen();
			if ( $screen->base == 'post' && $screen->post_type == 'team_member' ) {
				return;

				ob_start();
				?>
					<style>
						
					</style>
				<?php
				$css = trim( ob_get_clean() );
				$css = trim( preg_replace( array( '/^<style>/', '/<\/style>$/' ), '', $css ) );
				wp_add_inline_style( 'common', $css );

			} else if ( $screen->base == 'edit' && $screen->post_type == 'team_member' ) {

				$css = "
					table.wp-list-table	th.column-team-member-image,
					table.wp-list-table	td.column-team-member-image {
						width: 42px;
					}
					table.wp-list-table	td.column-team-member-image img {
						display: block;
						max-width: 100%;
						width: auto;
						height: auto;
					}
				";
				wp_add_inline_style( 'common', $css );

			}

		}


	}
}