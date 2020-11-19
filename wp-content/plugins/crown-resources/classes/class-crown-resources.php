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


if ( ! class_exists( 'Crown_Resources' ) ) {
	class Crown_Resources {

		public static $init = false;

		public static $resource_post_type = null;
		public static $resource_type_taxonomy = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'after_setup_theme', array( __CLASS__, 'register_resource_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_resource_type_taxonomy' ) );

			// add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					if ( $role->has_cap( $cap . '_posts' ) ) {
						$role->add_cap( $cap . '_resources' );
					}
				}
				foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
					if ( $role->has_cap( 'manage_categories' ) ) {
						$role->add_cap( $cap . '_resource_types' );
					}
				}
				if ( $role->has_cap( 'edit_posts' ) ) {
					$role->add_cap( 'assign_resource_types' );
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_resources' );
				}
				foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $cap ) {
					$role->remove_cap ( $cap . '_resource_types' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function register_resource_post_type() {

			self::$resource_post_type = new PostType( array(
				'name' => 'resource',
				'singularLabel' => 'Resource',
				'pluralLabel' => 'Resources',
				'settings' => array(
					'supports' => array( 'title', 'editor', 'excerpt', 'revisions' ),
					'rewrite' => array( 'slug' => 'resources', 'with_front' => false ),
					'menu_icon' => 'dashicons-text-page',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'capability_type' => array( 'resource', 'resources' ),
					'map_meta_cap' => true,
					'menu_position' => 32
				),
				'metaBoxes' => array(
					
				),
				'listTableColumns' => array(
					
				)
			) );

		}


		public static function register_resource_type_taxonomy() {

			self::$resource_type_taxonomy = new Taxonomy( array(
				'name' => 'resource_type',
				'singularLabel' => 'Resource Type',
				'pluralLabel' => 'Resource Types',
				'postTypes' => array( 'resource' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'resource-types', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Types',
						'all_items' => 'All Types'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_resource_types',
						'edit_terms' => 'edit_resource_types',
						'delete_terms' => 'delete_resource_types',
						'assign_terms' => 'assign_resource_types'
					)
				),
				'fields' => array(
					new Field( array(
						'label' => 'Color',
						'input' => new ColorInput( array( 'name' => 'resource_type_color', 'colorpickerOptions' => array( 'palettes' => apply_filters( 'crown_theme_colors', array(), 'resource_type_color' ) ) ) )
					) )
				),
				'listTableColumns' => array(
					new ListTableColumn(array(
						'key' => 'resource-type-color',
						'title' => '',
						'position' => 1,
						'outputCb' => function( $term_id, $args ) {
							$color = get_term_meta( $term_id, 'resource_type_color', true );
							if ( ! empty( $color ) ) {
								$screen = get_current_screen();
								echo '<a class="color" style="background-color: ' . $color . ';" href="' . get_edit_term_link( $term_id, $screen->taxonomy, $screen->post_type ) . '">' . $color . '</a>';
							}
						}
					))
				)
			) );

		}


		public static function filter_use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			return in_array( $post_type, array( 'resource' ) ) ? false : $use_block_editor;
		}


		public static function register_admin_styles( $hook ) {
			
			$screen = get_current_screen();
			if ( $screen->base == 'post' && $screen->post_type == 'resource' ) {
				return;

				ob_start();
				?>
					<style>
						
					</style>
				<?php
				$css = trim( ob_get_clean() );
				$css = trim( preg_replace( array( '/^<style>/', '/<\/style>$/' ), '', $css ) );
				wp_add_inline_style( 'common', $css );

			} else if ( $screen->base == 'edit-tags' && $screen->taxonomy == 'resource_type' ) {

				ob_start();
				?>
					<style>
						.wp-list-table .column-resource-type-color { width: 30px; }
						.wp-list-table .column-resource-type-color .color { display: block; overflow: hidden; text-indent: -100000px; width: 30px; height: 30px; border-radius: 3px; }
					</style>
				<?php
				$css = trim( ob_get_clean() );
				$css = trim( preg_replace( array( '/^<style>/', '/<\/style>$/' ), '', $css ) );
				wp_add_inline_style( 'common', $css );

			}

		}


	}
}