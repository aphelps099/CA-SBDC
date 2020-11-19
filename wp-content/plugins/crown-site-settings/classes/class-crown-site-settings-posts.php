<?php

use Crown\AdminPage;
use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\Input\CheckboxSet;
use Crown\Form\Input\Media as MediaInput;
use Crown\Form\Input\RadioSet;
use Crown\Form\Input\Select;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Checkbox as CheckboxInput;
use Crown\Form\Input\Textarea;
use Crown\Post\MetaBox;
use Crown\Post\Taxonomy;
use Crown\Post\Type as PostType;
use Crown\UIRule;


if ( ! class_exists( 'Crown_Site_Settings_Posts' ) ) {
	class Crown_Site_Settings_Posts {

		public static $init = false;

		public static $post_types = array();
		public static $post_topic_taxonomy = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'detactivate' ));

			// add_action( 'after_setup_theme', array( __CLASS__, 'register_post_fields' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_post_topic_taxonomy' ) );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				// foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
				// 	if ( $role->has_cap( $cap . '_posts' ) ) {
				// 		$role->add_cap( $cap . '_posts' );
				// 	}
				// }
				foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
					if ( $role->has_cap( 'manage_categories' ) ) {
						$role->add_cap( $cap . '_post_topics' );
					}
				}
				if ( $role->has_cap( 'edit_posts' ) ) {
					$role->add_cap( 'assign_post_topics' );
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				// foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
				// 	$role->remove_cap( $cap . '_posts' );
				// }
				foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $cap ) {
					$role->remove_cap ( $cap . '_post_topics' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function register_post_fields() {

			self::$post_types['post'] = new PostType( array(
				'name' => 'post',
				'metaBoxes' => array(
					
				)
			) );

		}


		public static function register_post_topic_taxonomy() {

			self::$post_topic_taxonomy = new Taxonomy( array(
				'name' => 'post_topic',
				'singularLabel' => 'Topic',
				'pluralLabel' => 'Topics',
				'postTypes' => array( 'post', 'event', 'resource' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'topics', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Topics',
						'all_items' => 'All Topics'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_post_topics',
						'edit_terms' => 'edit_post_topics',
						'delete_terms' => 'delete_post_topics',
						'assign_terms' => 'assign_post_topics'
					)
				)
			) );

		}


	}
}